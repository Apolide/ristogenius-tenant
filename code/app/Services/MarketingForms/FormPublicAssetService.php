<?php

namespace App\Services\MarketingForms;

use App\Models\MarketingForm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormPublicAssetService
{
    public function replaceBackground(MarketingForm $form, UploadedFile $file): string
    {
        $this->deleteBackground($form);

        $extension = strtolower($file->getClientOriginalExtension());
        $storagePath = $file->storeAs(
            "marketing-forms/{$form->getKey()}/backgrounds",
            "background.{$extension}",
            'local',
        );

        $this->publish($form, Storage::disk('local')->path($storagePath), $extension);

        return $storagePath;
    }

    public function backgroundUrl(MarketingForm $form): ?string
    {
        $matches = File::glob(public_path($this->relativeDirectory($form)).'/background.*');
        if ($matches) {
            return asset($this->relativeDirectory($form).'/'.basename($matches[0]));
        }

        if (! $form->image_path) {
            return null;
        }

        $source = $this->sourcePath($form->image_path);
        if (! $source) {
            return null;
        }

        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $this->publish($form, $source, $extension);

        return asset($this->relativeDirectory($form)."/background.{$extension}");
    }

    public function deleteBackground(MarketingForm $form): void
    {
        if ($form->image_path) {
            Storage::disk('local')->delete($form->image_path);
            Storage::disk('public')->delete($form->image_path); // Compatibility with earlier uploads.
        }

        File::deleteDirectory(public_path($this->relativeDirectory($form)));
    }

    public function relativeDirectory(MarketingForm $form): string
    {
        $tenant = Str::slug((string) config('tenant.slug', 'tenant')) ?: 'tenant';

        return "marketing-assets/{$tenant}/forms/{$form->getKey()}";
    }

    private function publish(MarketingForm $form, string $source, string $extension): void
    {
        $directory = public_path($this->relativeDirectory($form));
        File::ensureDirectoryExists($directory, 0775);
        File::delete(File::glob($directory.'/background.*'));
        File::copy($source, $directory."/background.{$extension}");
    }

    private function sourcePath(string $path): ?string
    {
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->path($path);
            }
        }

        return null;
    }
}
