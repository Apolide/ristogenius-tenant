<?php

namespace App\Services\Fiscal;

use App\Contracts\Fiscal\FiscalProvider;
use App\Models\FiscalApiCall;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class OpenapiFiscalProvider implements FiscalProvider
{
    private const BASE = 'https://test.invoice.openapi.com';

    public function assertAvailable(): void
    {
        $token = (string) config('fiscal.openapi.token');
        if (config('fiscal.environment') !== 'sandbox' || config('fiscal.provider') !== 'openapi' || $token === '' || preg_match('/^X+$/', $token)) {
            throw ValidationException::withMessages(['fiscal' => 'Configura OPENAPI_INVOICE_SANDBOX_TOKEN sul server. E-Receipts è disponibile qui solo in sandbox.']);
        }
    }

    public function execute(string $action, array $data, ?string $operationId = null): array
    {
        $this->assertAvailable();
        [$method, $path, $payload] = $this->command($action, $data);
        $tariff = config('fiscal.environment') === 'sandbox' ? 0 : config('fiscal.tariffs_micros.'.$action);
        // Persist the attempt BEFORE sending: a process crash cannot erase its cost trail.
        $call = FiscalApiCall::create([
            'fiscal_operation_id' => $operationId, 'user_id' => auth()->id(), 'provider' => 'openapi', 'environment' => 'sandbox',
            'action' => $action, 'request_hash' => hash('sha256', json_encode([$method, $path, $payload], JSON_THROW_ON_ERROR)),
            'outcome' => 'in_flight', 'estimated_cost_micros' => $tariff, 'cost_status' => $tariff === 0 ? 'sandbox' : ($tariff === null ? 'unpriced' : 'estimated'),
        ]);
        $started = microtime(true);
        try {
            $request = Http::withToken(config('fiscal.openapi.token'))
                ->timeout(config('fiscal.openapi.timeout'))->connectTimeout(config('fiscal.openapi.connect_timeout'))
                ->withOptions(['allow_redirects' => false]);
            if ($action === 'receipt_pdf') {
                $request = $request->accept('application/pdf')->contentType('application/pdf');
            } else {
                $request = $request->acceptJson()->asJson();
            }
            // Intentionally no retry(), including for provisioning, returns and closes.
            $response = $request->send($method, self::BASE.$path, $method === 'GET' ? ['query' => $payload] : ['json' => $payload ?: new \stdClass]);
        } catch (ConnectionException) {
            $call->update(['outcome' => 'unknown', 'duration_ms' => (int) ((microtime(true) - $started) * 1000)]);
            throw new FiscalFailure(true, 'Connessione interrotta: esito da verificare. Nessun reinvio automatico.');
        }
        $call->update(['http_status' => $response->status(), 'duration_ms' => (int) ((microtime(true) - $started) * 1000)]);
        $body = $response->json();
        $details = FiscalErrorDetails::extract(is_array($body) ? $body : null, (string) config('fiscal.openapi.token'));
        if (! $response->successful()) {
            $emailConflict = $action === 'create_cashier' && $response->status() === 409 && $details['error_code'] === '652';
            $uncertain = ! $emailConflict && ! in_array($response->status(), [400, 401, 402, 403, 404, 405, 422, 429], true);
            $call->update(['outcome' => $uncertain ? 'unknown' : 'rejected'] + $details);
            $message = match (true) {
                $emailConflict => 'Email già utilizzata nei sistemi Openapi: la creazione del cassiere è stata rifiutata. Se non compare fra i cassieri di questa azienda, inserisci un’email diversa nel campo Email cassiere Openapi. L’email di accesso a RistoPilot è separata.',
                $response->status() === 404 && $action === 'configuration' => 'Azienda non trovata o non accessibile con questo token nella sandbox Invoice. Se non è ancora stata creata, usa “Attiva azienda e prima sede sandbox”; se un invio è rimasto incerto, verificane l’esito prima di reinviare.',
                $response->status() === 401 => 'Autenticazione non accettata per '.$method.' '.$path.'. Verifica token sandbox, scadenza e scope di questa rotta.',
                $response->status() === 422 && $action === 'configure' && $details['error_code'] === '112' => 'Il fornitore segnala una configurazione già esistente per questa partita IVA. Verifica con “Sincronizza azienda e sedi”; se non è accessibile, usa una partita IVA di test diversa oppure richiedi una verifica a Openapi.',
                $response->status() === 422 => 'Il servizio fiscale ha rifiutato i dati inviati.',
                $uncertain => 'Esito fiscale da verificare prima di procedere. Nessun reinvio automatico.',
                default => 'Operazione fiscale rifiutata.',
            };
            $message .= ' HTTP '.$response->status().($details['error_code'] !== null ? ' · Codice Openapi '.$details['error_code'] : '').'.';
            if ($details['error_message'] !== null) {
                $message .= ' Dettaglio Openapi: '.$details['error_message'];
            }
            throw new FiscalFailure($uncertain, $message, $response->status(), $details['error_code']);
        }
        if ($action === 'receipt_pdf') {
            if (str_starts_with($response->body(), '%PDF-')) {
                $call->update(['outcome' => 'succeeded']);

                return ['pdf' => $response->body()];
            }
            $message = 'Il servizio fiscale non ha restituito un file PDF. Il documento resta emesso: puoi riprovare il download o verificare con il fornitore.';
            if (is_array($body) && ($body['success'] ?? null) === false && $details['error_message']) {
                $message .= ' Dettaglio Openapi: '.$details['error_message'];
            }
            $call->update(['outcome' => 'invalid_format', 'error_code' => $details['error_code'] ?? 'PDF_FORMAT', 'error_message' => $message]);
            throw new FiscalFailure(false, $message, $response->status(), $details['error_code']);
        }
        if (! is_array($body) || ($body['success'] ?? null) !== true) {
            // Even a malformed 2xx can follow a successful fiscal mutation.
            $call->update(['outcome' => 'unknown'] + $details);
            throw new FiscalFailure(true, 'Risposta fiscale non conclusiva: verifica l’esito prima di procedere.'.($details['error_message'] ? ' Dettaglio Openapi: '.$details['error_message'] : ''));
        }
        try {
            $result = $this->normalize($action, $body);
        } catch (\Throwable) {
            $call->update(['outcome' => 'unknown']);
            throw new FiscalFailure(true, 'Risposta fiscale non interpretabile: verifica l’esito.');
        }
        $call->update(['outcome' => 'succeeded']);

        return $result;
    }

    private function command(string $action, array $data): array
    {
        $id = rawurlencode((string) ($data['id'] ?? ''));
        $fiscal = rawurlencode((string) ($data['fiscal_id'] ?? ''));

        return match ($action) {
            'configure' => ['POST', '/IT-configurations', [
                'fiscal_id' => $data['fiscal_id'], 'name' => $data['name'], 'email' => $data['email'],
                'e_receipts' => true, 'receipts' => false, 'customer_invoice' => false, 'supplier_invoice' => false, 'legal_storage' => false,
                'merchant_address' => $data['address'], 'stores' => $data['stores'],
            ]],
            'add_store' => ['PATCH', '/IT-configurations/'.$fiscal, ['e_receipts' => true, 'stores' => $data['stores']]],
            'connection' => ['GET', '/IT-configurations', []],
            'configuration' => ['GET', '/IT-configurations/'.$fiscal, []],
            'create_cashier' => ['POST', '/IT-cashiers', Arr::only($data, ['fiscal_id', 'name', 'email'])],
            'cashiers' => ['GET', '/IT-cashiers', []],
            'cashier' => ['GET', '/IT-cashiers/'.$id, []],
            'issue' => ['POST', '/IT-e-receipts', $this->receiptPayload($data)],
            'receipt' => ['GET', '/IT-e-receipts/'.$id, []],
            'receipt_pdf' => ['GET', '/IT-e-receipts/'.$id, []],
            'receipts' => ['GET', '/IT-e-receipts', Arr::only($data, ['fiscal_id', 'store_id', 'parent_receipt_id', 'type', 'skip']) + ['limit' => 100]],
            'returnable' => ['GET', '/IT-e-receipts/'.$id.'/returnable-items', []],
            'return' => ['PATCH', '/IT-e-receipts/'.$id, ['cashier_id' => $data['cashier_id'], 'cr_id' => $data['register_id'], 'items' => $data['items']]],
            'void' => ['DELETE', '/IT-e-receipts/'.$id, ['cashier_id' => $data['cashier_id'], 'cr_id' => $data['register_id']]],
            'close' => ['POST', '/IT-store/'.$id.'/close', []],
            'store_events' => ['GET', '/IT-store-events', ['store_id' => $data['store_id'], 'fiscal_id' => $data['fiscal_id'], 'limit' => 100]],
            default => throw new \InvalidArgumentException('Unsupported fiscal command.'),
        };
    }

    private function receiptPayload(array $data): array
    {
        return [
            'fiscal_id' => $data['fiscal_id'], 'type' => 'sale', 'store_id' => $data['store_id'], 'cr_id' => $data['register_id'], 'cashier_id' => $data['cashier_id'],
            'items' => array_map(fn ($item) => ['id' => $item['id'], 'type' => $item['type'], 'description' => $item['description'],
                'quantity' => FiscalMoney::decimal($item['quantity'] * 100), 'unit_price' => FiscalMoney::decimal($item['unit_cents']), 'vat_rate_code' => $item['vat_code']], $data['items']),
            'cash_payment_amount' => FiscalMoney::decimal($data['payments']['cash']), 'electronic_payment_amount' => FiscalMoney::decimal($data['payments']['card']),
            'ticket_restaurant_payment_amount' => FiscalMoney::decimal($data['payments']['voucher']), 'ticket_restaurant_quantity' => $data['voucher_count'],
            'additional_text' => $data['reference'],
        ];
    }

    private function normalize(string $action, array $body): array
    {
        $data = $body['data'] ?? [];

        return match ($action) {
            'configure', 'add_store', 'configuration' => [
                'id' => $data['id'] ?? null, 'fiscal_id' => $data['fiscal_id'] ?? null, 'enabled' => ($data['e_receipts'] ?? false) === true,
                'stores' => array_map(fn ($store) => Arr::only($store, ['store_id', 'label', 'address', 'status', 'operational_status', 'pem_serial', 'active', 'cash_registers']), $data['stores'] ?? []),
            ],
            'connection' => is_array($data) && array_is_list($data) ? ['authenticated' => true] : throw new FiscalFailure(true, 'Risposta di verifica connessione non valida.'),
            'create_cashier', 'cashier' => Arr::only($data, ['id', 'fiscal_id', 'email', 'name', 'status']),
            'cashiers' => ['cashiers' => array_map(fn ($cashier) => Arr::only($cashier, ['id', 'fiscal_id', 'email', 'name', 'status']), $data)],
            'issue', 'receipt', 'return', 'void' => $this->document($data),
            'receipts' => ['documents' => array_map(fn ($document) => $this->document($document), $data)],
            'returnable' => ['items' => array_map(fn ($item) => Arr::only($item, ['id', 'quantity', 'returned_quantity', 'description', 'unit_price']), $data)],
            'close' => ['event_id' => $body['event_id'] ?? null],
            'store_events' => ['events' => array_map(fn ($event) => Arr::only($event, ['id', 'store_id', 'fiscal_id', 'action', 'created_at']), $data)],
            default => throw new FiscalFailure(true, 'Formato della risposta fiscale non riconosciuto.'),
        };
    }

    private function document(array $data): array
    {
        return Arr::only($data, ['id', 'fiscal_id', 'type', 'store_id', 'cr_id', 'cashier_id', 'status', 'document_number', 'document_date',
            'items', 'total_amount', 'additional_text', 'parent_receipt_id', 'is_voidable', 'is_returnable',
            'cash_payment_amount', 'electronic_payment_amount', 'ticket_restaurant_payment_amount', 'ticket_restaurant_quantity']);
    }
}
