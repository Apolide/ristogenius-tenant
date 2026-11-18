<?php

namespace App\Http\Controllers;

use App\Models\FiscalCopyRequest;
use App\Models\FiscalDocument;
use App\Models\FiscalProfile;
use App\Services\Fiscal\FiscalSetupService;

class FiscalCopyController extends Controller
{
    public function __invoke(string $copy, string $document, FiscalSetupService $setup)
    {
        $request = FiscalCopyRequest::findOrFail($copy);
        $profile = FiscalProfile::where('environment', 'sandbox')->firstOrFail();
        $doc = FiscalDocument::where('cash_payment_batch_id', $request->cash_payment_batch_id)->where('fiscal_id', $profile->fiscal_id)->where('environment', 'sandbox')->whereNull('parent_id')->whereIn('status', ['issued', 'voided'])->findOrFail($document);
        abort_unless($request->queued_at && $doc->remote_id, 404);
        $pdf = $setup->read($doc->document_kind === 'invoice' ? 'invoice_pdf' : 'receipt_pdf', ['id' => $doc->remote_id])['pdf'];

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="documento-sandbox.pdf"', 'Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow']);
    }
}
