<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TenantPublicAssetService
{
    public function replaceUploadedFile(UploadedFile $file, string $directory, string $filename): string
    {
        $directory = trim($directory, '/');
        $filename = $this->filename($filename, $file->getClientOriginalExtension());
        $relativeDirectory = $this->relativeDirectory($directory);
        $absoluteDirectory = public_path($relativeDirectory);

        File::ensureDirectoryExists($absoluteDirectory, 0775);

        foreach (File::glob($absoluteDirectory.'/'.pathinfo($filename, PATHINFO_FILENAME).'.*') as $existingFile) {
            File::delete($existingFile);
        }

        File::copy($file->getRealPath(), $absoluteDirectory.'/'.$filename);

        return $relativeDirectory.'/'.$filename;
    }

    public function urlIfExists(string $directory, string $filename): ?string
    {
        $relativePath = $this->relativeDirectory(trim($directory, '/')).'/'.$filename;

        if (! File::exists(public_path($relativePath))) {
            return null;
        }

        return asset($relativePath);
    }

    public function firstUrlForBaseName(string $directory, string $filename): ?string
    {
        $relativeDirectory = $this->relativeDirectory(trim($directory, '/'));
        $matches = File::glob(public_path($relativeDirectory).'/'.pathinfo($filename, PATHINFO_FILENAME).'.*');

        return $matches ? asset($relativeDirectory.'/'.basename($matches[0])) : null;
    }

    public function mirrorMediaIfLocal(Media $media, string $directory): ?string
    {
        $relativeDirectory = $this->relativeDirectory(trim($directory, '/'));
        $absoluteDirectory = public_path($relativeDirectory);
        $absolutePath = $absoluteDirectory.'/'.$media->file_name;

        if (File::exists($absolutePath)) {
            return asset($relativeDirectory.'/'.$media->file_name);
        }

        if (! File::exists($media->getPath())) {
            return null;
        }

        File::ensureDirectoryExists($absoluteDirectory, 0775);

        foreach (File::glob($absoluteDirectory.'/'.pathinfo($media->file_name, PATHINFO_FILENAME).'.*') as $existingFile) {
            File::delete($existingFile);
        }

        File::copy($media->getPath(), $absolutePath);

        return asset($relativeDirectory.'/'.$media->file_name);
    }

    private function relativeDirectory(string $directory): string
    {
        return 'tenant-assets/'.$this->tenantSlug().'/'.$directory;
    }

    private function filename(string $filename, string $extension): string
    {
        return pathinfo($filename, PATHINFO_FILENAME).'.'.strtolower($extension);
    }

    private function tenantSlug(): string
    {
        return Str::slug(config('tenant.slug', 'tenant'));
    }
}
