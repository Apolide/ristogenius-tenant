<?php

namespace App\Http\Controllers;

use App\Services\Fiscal\FiscalDocumentService;

class FiscalDocumentXmlController extends Controller
{
    public function __invoke(string $document, FiscalDocumentService $service)
    {
        $service->authorize();
        $doc = $service->document($document);
        abort_unless($doc->document_kind === 'invoice' && isset($doc->snapshot['xml']), 404);

        return response($doc->snapshot['xml'], 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="fattura-sandbox.xml"', 'Cache-Control' => 'private, no-store']);
    }
}
