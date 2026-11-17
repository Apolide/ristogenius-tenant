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
            throw ValidationException::withMessages(['fiscal' => __('fiscal.messages.sandbox_token_required')]);
        }
    }

    public function execute(string $action, array $data, ?string $operationId = null): array
    {
        $this->assertAvailable();
        if ($action === 'send_invoice') {
            $validation = $this->execute('validate_invoice', $data, $operationId);
            if (($validation['valid'] ?? false) !== true) {
                throw new FiscalFailure(false, __('fiscal.messages.openapi_invoice_xml_validation_failed'));
            }
        }
        [$method, $path, $payload] = $this->command($action, $data);
        $tariff = config('fiscal.environment') === 'sandbox' ? 0 : config('fiscal.tariffs_micros.'.$action);
        // Persist the attempt BEFORE sending: a process crash cannot erase its cost trail.
        $call = FiscalApiCall::create([
            'fiscal_operation_id' => $operationId, 'user_id' => auth()->id(), 'provider' => 'openapi', 'environment' => 'sandbox',
            'action' => $action, 'request_hash' => hash('sha256', json_encode([$method, $path, $payload, isset($data['xml']) ? hash('sha256', $data['xml']) : null], JSON_THROW_ON_ERROR)),
            'outcome' => 'in_flight', 'estimated_cost_micros' => $tariff, 'cost_status' => $tariff === 0 ? 'sandbox' : ($tariff === null ? 'unpriced' : 'estimated'),
        ]);
        $started = microtime(true);
        try {
            $request = Http::withToken(config('fiscal.openapi.token'))
                ->timeout(config('fiscal.openapi.timeout'))->connectTimeout(config('fiscal.openapi.connect_timeout'))
                ->withOptions(['allow_redirects' => false]);
            if (in_array($action, ['receipt_pdf', 'invoice_pdf'], true)) {
                $request = $request->accept('application/pdf')->contentType('application/pdf');
            } elseif (in_array($action, ['send_invoice', 'validate_invoice', 'invoice_xml'], true)) {
                $request = $request->accept($action === 'invoice_xml' ? 'application/xml' : 'application/json')->contentType('application/xml');
            } else {
                $request = $request->acceptJson()->asJson();
            }
            // Intentionally no retry(), including for provisioning, returns and closes.
            $options = $method === 'GET' ? ['query' => $payload] : (in_array($action, ['send_invoice', 'validate_invoice'], true) ? ['body' => $data['xml']] : ['json' => $payload ?: new \stdClass]);
            $response = $request->send($method, self::BASE.$path, $options);
        } catch (ConnectionException) {
            $call->update(['outcome' => 'unknown', 'duration_ms' => (int) ((microtime(true) - $started) * 1000)]);
            throw new FiscalFailure(true, __('fiscal.messages.verify_outcome_after_connection_loss'));
        }
        $call->update(['http_status' => $response->status(), 'duration_ms' => (int) ((microtime(true) - $started) * 1000)]);
        $body = $response->json();
        $details = FiscalErrorDetails::extract(is_array($body) ? $body : null, (string) config('fiscal.openapi.token'));
        if (! $response->successful()) {
            $emailConflict = $action === 'create_cashier' && $response->status() === 409 && $details['error_code'] === '652';
            $uncertain = ! $emailConflict && ! in_array($response->status(), [400, 401, 402, 403, 404, 405, 422, 429], true);
            $call->update(['outcome' => $uncertain ? 'unknown' : 'rejected'] + $details);
            $message = match (true) {
                $emailConflict => __('fiscal.messages.cashier_creation_rejected_duplicate_email'),
                $response->status() === 404 && $action === 'configuration' => __('fiscal.messages.sandbox_company_unavailable'),
                $response->status() === 401 => __('fiscal.messages.authentication_not_accepted_for').$method.' '.$path.__('fiscal.messages.check_sandbox_token_expiry_and_scopes_for_this_route'),
                $response->status() === 422 && $action === 'configure' && $details['error_code'] === '112' => __('fiscal.messages.vat_number_already_configured'),
                $response->status() === 422 => __('fiscal.messages.the_fiscal_service_rejected_the_submitted_data'),
                $uncertain => __('fiscal.messages.fiscal_outcome_requires_manual_verification'),
                default => __('fiscal.messages.fiscal_operation_rejected'),
            };
            $message .= ' HTTP '.$response->status().($details['error_code'] !== null ? __('fiscal.messages.openapi_code').$details['error_code'] : '').'.';
            if ($details['error_message'] !== null) {
                $message .= __('fiscal.messages.openapi_details').$details['error_message'];
            }
            throw new FiscalFailure($uncertain, $message, $response->status(), $details['error_code']);
        }
        if (in_array($action, ['receipt_pdf', 'invoice_pdf'], true)) {
            if (str_starts_with($response->body(), '%PDF-')) {
                $call->update(['outcome' => 'succeeded']);

                return ['pdf' => $response->body()];
            }
            $message = __('fiscal.messages.issued_document_pdf_unavailable');
            if (is_array($body) && ($body['success'] ?? null) === false && $details['error_message']) {
                $message .= __('fiscal.messages.openapi_details').$details['error_message'];
            }
            $call->update(['outcome' => 'invalid_format', 'error_code' => $details['error_code'] ?? 'PDF_FORMAT', 'error_message' => $message]);
            throw new FiscalFailure(false, $message, $response->status(), $details['error_code']);
        }
        if ($action === 'invoice_xml' && str_contains($response->body(), 'FatturaElettronica') && ! str_contains($response->body(), '<!DOCTYPE')) {
            $call->update(['outcome' => 'succeeded']);

            return ['xml' => $response->body()];
        }
        if (! is_array($body) || (($body['success'] ?? null) !== true && ! (in_array($action, ['send_invoice', 'invoice', 'validate_invoice', 'invoices'], true) && ! array_key_exists('success', $body) && is_array($body['data'] ?? null)))) {
            // Even a malformed 2xx can follow a successful fiscal mutation.
            $call->update(['outcome' => 'unknown'] + $details);
            throw new FiscalFailure(true, __('fiscal.messages.inconclusive_fiscal_response').($details['error_message'] ? __('fiscal.messages.openapi_details').$details['error_message'] : ''));
        }
        try {
            $result = $this->normalize($action, $body);
        } catch (\Throwable) {
            $call->update(['outcome' => 'unknown']);
            throw new FiscalFailure(true, __('fiscal.messages.unreadable_fiscal_response'));
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
            'enable_invoices' => ['PATCH', '/IT-configurations/'.$fiscal, ['customer_invoice' => true]],
            'send_invoice' => ['POST', '/IT-invoices', []],
            'validate_invoice' => ['POST', '/IT-invoices_validate', []],
            'invoice', 'invoice_pdf', 'invoice_xml' => ['GET', '/IT-invoices/'.$id, []],
            'invoices' => ['GET', '/IT-invoices', ['fiscal_id' => $data['fiscal_id'], 'document_number' => $data['number'], 'direction' => 'outgoing', 'skip' => $data['skip'] ?? 0, 'limit' => 100]],
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
            'configure', 'add_store', 'configuration', 'enable_invoices' => [
                'id' => $data['id'] ?? null, 'fiscal_id' => $data['fiscal_id'] ?? null, 'enabled' => ($data['e_receipts'] ?? false) === true,
                'invoice_enabled' => ($data['customer_invoice'] ?? false) === true,
                'stores' => array_map(fn ($store) => Arr::only($store, ['store_id', 'label', 'address', 'status', 'operational_status', 'pem_serial', 'active', 'cash_registers']), $data['stores'] ?? []),
            ],
            'connection' => is_array($data) && array_is_list($data) ? ['authenticated' => true] : throw new FiscalFailure(true, __('fiscal.messages.invalid_connection_check_response')),
            'send_invoice', 'invoice' => Arr::only($data, ['id', 'state', 'direction', 'fiscal_id', 'type', 'document_number', 'issue_date', 'total_amount_including_tax', 'total_gross_amount', 'recipient', 'details']),
            'validate_invoice' => ['valid' => ($data['valid'] ?? false) === true, 'message' => $data['message'] ?? ''],
            'invoices' => ['documents' => array_map(fn ($row) => Arr::only($row, ['id', 'state', 'direction', 'fiscal_id', 'type', 'document_number', 'issue_date', 'total_amount_including_tax', 'total_gross_amount', 'recipient', 'details']), $data)],
            'create_cashier', 'cashier' => Arr::only($data, ['id', 'fiscal_id', 'email', 'name', 'status']),
            'cashiers' => ['cashiers' => array_map(fn ($cashier) => Arr::only($cashier, ['id', 'fiscal_id', 'email', 'name', 'status']), $data)],
            'issue', 'receipt', 'return', 'void' => $this->document($data),
            'receipts' => ['documents' => array_map(fn ($document) => $this->document($document), $data)],
            'returnable' => ['items' => array_map(fn ($item) => Arr::only($item, ['id', 'quantity', 'returned_quantity', 'description', 'unit_price']), $data)],
            'close' => ['event_id' => $body['event_id'] ?? null],
            'store_events' => ['events' => array_map(fn ($event) => Arr::only($event, ['id', 'store_id', 'fiscal_id', 'action', 'created_at']), $data)],
            default => throw new FiscalFailure(true, __('fiscal.messages.unrecognized_fiscal_response_format')),
        };
    }

    private function document(array $data): array
    {
        return Arr::only($data, ['id', 'fiscal_id', 'type', 'store_id', 'cr_id', 'cashier_id', 'status', 'document_number', 'document_date',
            'items', 'total_amount', 'additional_text', 'parent_receipt_id', 'is_voidable', 'is_returnable',
            'cash_payment_amount', 'electronic_payment_amount', 'ticket_restaurant_payment_amount', 'ticket_restaurant_quantity']);
    }
}
