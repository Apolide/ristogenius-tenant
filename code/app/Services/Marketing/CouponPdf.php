<?php

namespace App\Services\Marketing;

use App\Models\CouponAssignment;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;

class CouponPdf
{
    public function filename(CouponAssignment $assignment, array $branding, string $language): string
    {
        $restaurant = Str::slug($branding['name']) ?: 'ristorante';
        $coupon = Str::slug($assignment->snapshot['slug'] ?? $assignment->coupon?->slug ?? $this->title($assignment, $language)) ?: 'offerta';

        return mb_substr($restaurant, 0, 80).'-coupon-'.mb_substr($coupon, 0, 100).'.pdf';
    }

    public function render(CouponAssignment $assignment, string $language, array $branding): string
    {
        $options = new Options;
        $options->setDefaultFont('DejaVu Sans');
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $options->setChroot(public_path());
        $writer = new Writer(new ImageRenderer(new RendererStyle(300, 4), new SvgImageBackEnd));
        $qr = 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($assignment->url($language)));
        $baseSummary = app(CouponPresentation::class)->summary($assignment->snapshot, $language);
        $summary = [array_key_first($baseSummary) => reset($baseSummary)];
        $start = $assignment->snapshot['valid_from'] ?? null;
        $end = $assignment->snapshot['valid_until'] ?? null;
        $date = fn ($value) => \Carbon\Carbon::parse($value)->format('d/m/Y');
        $summary[__('marketing.pdf_validity', [], $language)] = match (true) {
            ! empty($start) && ! empty($end) => $date($start).' – '.$date($end),
            ! empty($end) => __('marketing.pdf_until', ['date' => $date($end)], $language),
            ! empty($start) => __('marketing.pdf_from', ['date' => $date($start)], $language),
            default => __('marketing.no_expiry', [], $language),
        };
        $rules = $assignment->snapshot['rules'] ?? [];
        $minPax = $rules['min_pax'] ?? 1;
        $summary[__('marketing.pdf_people', [], $language)] = ! empty($rules['max_pax']) ? $minPax.'–'.$rules['max_pax'] : __('marketing.pdf_minimum_people', ['count' => $minPax], $language);
        if (! empty($rules['weekdays']) && count(array_unique($rules['weekdays'])) < 7) {
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            $summary[__('marketing.weekdays', [], $language)] = collect($rules['weekdays'])->map(fn ($day) => __('marketing.'.$days[(int) $day - 1], [], $language))->implode(', ');
        }
        $maxUses = $rules['max_uses'] ?? null;
        $summary[__('marketing.usage_mode', [], $language)] = $maxUses === null ? __('marketing.unlimited', [], $language) : ($maxUses === 1 ? __('marketing.single', [], $language) : __('marketing.pdf_limited_uses', ['count' => $maxUses], $language));
        if ($assignment->uses_count > 0) {
            $summary[__('marketing.uses', [], $language)] = $assignment->uses_count.' / '.($maxUses ?? __('marketing.unlimited', [], $language));
        }
        $pdf = new Dompdf($options);
        // A6 voucher: 105 × 148 mm, rather than an almost empty A4 page.
        $pdf->setPaper([0, 0, 297.64, 419.53]);
        $pdf->loadHtml(view('marketing.coupon-pdf', [
            'assignment' => $assignment, 'language' => $language, 'branding' => $branding,
            'title' => $this->title($assignment, $language), 'summary' => $summary,
            'logo' => $this->logo($branding['logo_url'] ?? null), 'qr' => $qr,
        ])->render(), 'UTF-8');
        $pdf->render();

        return $pdf->output();
    }

    private function title(CouponAssignment $assignment, string $language): string
    {
        return $assignment->snapshot['translations'][$language]['title'] ?? $assignment->snapshot['name'];
    }

    private function logo(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $public = realpath(public_path());
        $path = realpath(public_path(ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/')));
        if (! $path || ! str_starts_with($path, $public.DIRECTORY_SEPARATOR) || ! is_file($path) || filesize($path) > 2 * 1024 * 1024) {
            return null;
        }
        $dimensions = @getimagesize($path);
        if (! $dimensions || $dimensions[0] * $dimensions[1] > 10000000) {
            return null;
        }
        $image = @imagecreatefromstring(file_get_contents($path));
        if (! $image) {
            return null;
        }
        // Composite PNG/WebP alpha on the white voucher before encoding the logo.
        $width = min(600, $dimensions[0]);
        $height = max(1, (int) round($dimensions[1] * $width / $dimensions[0]));
        $opaque = imagecreatetruecolor($width, $height);
        imagefill($opaque, 0, 0, imagecolorallocate($opaque, 255, 255, 255));
        imagealphablending($opaque, true);
        imagecopyresampled($opaque, $image, 0, 0, 0, 0, $width, $height, $dimensions[0], $dimensions[1]);
        ob_start();
        imagepng($opaque);
        $png = ob_get_clean();
        imagedestroy($opaque);
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
