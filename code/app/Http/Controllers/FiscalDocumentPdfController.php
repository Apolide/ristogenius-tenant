<?php

namespace App\Http\Controllers;

use App\Services\Fiscal\FiscalDocumentService;

class FiscalDocumentPdfController extends Controller
{
    public function __invoke(string $document, FiscalDocumentService $service)
    {
        $pdf = $service->pdf($document);

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="documento-sandbox.pdf"', 'Cache-Control' => 'private, no-store']);
    }
}
