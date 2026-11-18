<?php

namespace App\Services\Fidelity;

use App\Models\FidelityCard;
use App\Services\TenantBrandingService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Dompdf\Dompdf;
use Dompdf\Options;

class FidelityPdf
{
    public function render(FidelityCard $card): string
    {
        $brand = app(TenantBrandingService::class)->branding();
        $path = public_path(ltrim(parse_url($brand['logo_url'] ?? '', PHP_URL_PATH) ?: '', '/'));
        $logo = is_file($path) ? $path : null;
        $writer = new Writer(new ImageRenderer(new RendererStyle(280, 4), new SvgImageBackEnd));
        $url = $card->statusUrl();
        $qr = 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($url));

        $pdf = new Dompdf(new Options([
            'defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false,
            'isPhpEnabled' => false, 'isJavascriptEnabled' => false, 'chroot' => public_path(),
        ]));
        $pdf->setPaper('a6');
        $pdf->loadHtml(view('fidelity.pdf', compact('card', 'brand', 'logo', 'qr', 'url'))->render(), 'UTF-8');
        $pdf->render();

        return $pdf->output();
    }
}
