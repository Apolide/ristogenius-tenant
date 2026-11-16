<?php

namespace App\Services\Fiscal;

use App\Contracts\Fiscal\FiscalProvider;
use App\Models\FiscalDocument;
use App\Models\FiscalOperation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FiscalOperationRunner
{
    public function __construct(private FiscalProvider $provider) {}

    public function run(string $resource, string $action, callable $prepare, callable $apply): FiscalOperation
    {
        $this->provider->assertAvailable();
        try {
            $operation = DB::transaction(function () use ($resource, $action, $prepare) {
                $operation = FiscalOperation::create(['resource_key' => $resource, 'active_key' => $resource, 'action' => $action,
                    'user_id' => auth()->id(), 'request' => []]);
                $operation->update(['request' => $prepare($operation)]);

                return $operation->fresh();
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['fiscal' => 'Esiste già un’operazione per questa risorsa. Verificane l’esito prima di riprovare.']);
        }
        try {
            $result = $this->provider->execute($action, $operation->request, $operation->id);
        } catch (FiscalFailure $error) {
            $this->failure($operation, $error->uncertain, $error->getMessage());
            throw ValidationException::withMessages(['fiscal' => $error->getMessage()]);
        } catch (\Throwable $error) {
            // Unexpected transport/decoding failures must never unlock a write.
            $this->failure($operation, true, 'Esito non disponibile. Verifica sul servizio fiscale.');
            throw ValidationException::withMessages(['fiscal' => 'Esito non disponibile. Verifica sul servizio fiscale prima di procedere.']);
        }
        // A durable response also covers a crash between the API reply and local commit.
        $operation->update(['response' => $result, 'status' => 'received']);
        try {
            DB::transaction(function () use ($operation, $result, $apply) {
                $locked = FiscalOperation::lockForUpdate()->findOrFail($operation->id);
                $apply($result, $locked);
                $locked->update(['status' => 'succeeded', 'active_key' => null, 'error' => null]);
            });
        } catch (\Throwable $error) {
            $this->failure($operation, true, 'Risposta ricevuta, riconciliazione locale necessaria.');
            throw ValidationException::withMessages(['fiscal' => 'Risposta ricevuta ma non riconciliata. Verifica l’esito, senza reinviare.']);
        }

        return $operation->fresh();
    }

    private function failure(FiscalOperation $operation, bool $uncertain, string $message): void
    {
        DB::transaction(function () use ($operation, $uncertain, $message) {
            $operation->update(['status' => $uncertain ? 'unknown' : 'rejected', 'error' => mb_substr($message, 0, 255),
                'active_key' => $uncertain ? $operation->resource_key : null]);
            if ($operation->fiscal_document_id) {
                FiscalDocument::whereKey($operation->fiscal_document_id)->update(['status' => $uncertain ? 'unknown' : 'rejected']);
            }
        });
    }
}
