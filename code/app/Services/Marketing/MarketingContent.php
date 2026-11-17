<?php

namespace App\Services\Marketing;

use DOMDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MarketingContent
{
    public function textLength(int $limit): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($limit): void {
            // Match the browser maxlength counter, including emoji surrogate pairs.
            if (is_string($value) && intdiv(strlen(mb_convert_encoding($value, 'UTF-16LE', 'UTF-8')), 2) > $limit) {
                $fail(__('validation.max.string', ['attribute' => $attribute, 'max' => $limit]));
            }
        };
    }

    public function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><html><body><div>'.$html.'</div></body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $allowed = ['p', 'div', 'span', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'hr'];
        foreach (iterator_to_array($document->getElementsByTagName('*')) as $node) {
            if (in_array($node->tagName, ['html', 'body'])) {
                continue;
            }
            if (! in_array($node->tagName, $allowed)) {
                $node->parentNode?->removeChild($node);

                continue;
            }
            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = $attribute->value;
                if (! in_array($name, ['href', 'src', 'alt', 'title', 'width', 'height', 'style', 'colspan', 'rowspan', 'border', 'cellpadding', 'cellspacing'])) {
                    $node->removeAttribute($name);
                } elseif (in_array($name, ['src', 'href']) && ! preg_match('~^(https?://|mailto:|tel:|/(?!/)|#)~i', preg_replace('/[\x00-\x20]+/', '', $value))) {
                    $node->removeAttribute($name);
                } elseif ($name === 'style') {
                    $styles = [];
                    foreach (explode(';', $value) as $declaration) {
                        if (preg_match('/^\s*(color|background-color|text-align|font-size|font-weight|width|height|padding|margin|border|border-collapse)\s*:\s*([#a-zA-Z0-9\s.,%()\-]+)$/', $declaration, $m) && ! preg_match('/url|expression|javascript|behavior/i', $m[2])) {
                            $styles[] = $m[1].':'.$m[2];
                        }
                    }
                    $node->setAttribute('style', implode(';', $styles));
                }
            }
        }
        $body = $document->getElementsByTagName('body')->item(0);
        $result = '';
        foreach ($body->childNodes as $node) {
            $result .= $document->saveHTML($node);
        }

        return $result;
    }

    public function image(UploadedFile $file): string
    {
        $extension = $file->guessExtension();
        abort_unless(in_array($extension, ['jpg', 'jpeg', 'png', 'webp']), 422);
        $directory = 'marketing-assets/'.Str::slug(config('tenant.slug')).'/offers';
        File::ensureDirectoryExists(public_path($directory), 0775);
        $name = Str::uuid().'.'.$extension;
        File::put(public_path($directory.'/'.$name), $file->get());

        return $directory.'/'.$name;
    }

    public function translations(array $translations): array
    {
        foreach ($translations as &$translation) {
            foreach (['html', 'description', 'terms'] as $field) {
                if (isset($translation[$field])) {
                    $translation[$field] = $this->clean($translation[$field]);
                }
            }
        }

        return $translations;
    }
}
