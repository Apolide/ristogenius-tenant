<?php

namespace App\Services\PublicOrders;

use App\Models\RoomTable;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TableQrService
{
    public function url(RoomTable $table, string $language = 'it'): string
    {
        return route('public-orders.table', ['language' => $language, 'tableToken' => $table->ordering_token]);
    }

    public function svg(RoomTable $table, string $language = 'it'): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(500, 24), new SvgImageBackEnd));
        $qr = preg_replace('/<\?xml[^>]*\?>/', '', $writer->writeString($this->url($table, $language)));
        $qr = preg_replace('/<svg /', '<svg x="50" y="30" ', $qr, 1);
        $name = htmlspecialchars($table->name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $room = htmlspecialchars($table->room->name, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="800" viewBox="0 0 600 800"><rect width="600" height="800" fill="white"/>'.$qr.'<text x="300" y="670" text-anchor="middle" font-family="Arial,sans-serif" font-size="100" font-weight="bold">'.$name.'</text><text x="300" y="740" text-anchor="middle" font-family="Arial,sans-serif" font-size="27" fill="#718096">'.$room.'</text></svg>';
    }
}
