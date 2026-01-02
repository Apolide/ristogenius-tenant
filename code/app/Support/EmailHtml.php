<?php

namespace App\Support;

use DOMDocument;

class EmailHtml
{
    public static function makeImagesResponsive(string $html, int $containerWidthPx = 570): string
    {
        // DOMDocument richiede HTML completo; aggiungo header xml per UTF-8
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);

        // NOIMPLIED/NODEFDTD per non aggiungere <html><body> extra
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        $imgs = $dom->getElementsByTagName('img');

        foreach ($imgs as $img) {
            $class = $img->getAttribute('class') ?? '';

            // Non toccare logo o eventuali icone marcate esplicitamente
            if (str_contains($class, 'logo') || str_contains($class, 'no-fluid')) {
                continue;
            }

            // Outlook: meglio dare una width "fisica" pari alla colonna (570)
            // così non sfonda mai.
            $img->setAttribute('width', (string) $containerWidthPx);

            // Stile fluid (mobile): width 100% con max-width 570
            $existing = trim($img->getAttribute('style') ?? '');
            $extra = "width:100%;max-width:{$containerWidthPx}px;height:auto;display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;";

            // evita doppio ;; e preserva stile esistente
            $style = rtrim($existing, ';');
            $style = $style !== '' ? $style . ';' . $extra : $extra;

            $img->setAttribute('style', $style);
        }

        return $dom->saveHTML();
    }
}
