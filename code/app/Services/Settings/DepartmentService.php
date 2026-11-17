<?php

namespace App\Services\Settings;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function getDepartments(string $search = ''): LengthAwarePaginator
    {
        return Department::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($names) use ($search): void {
                    $names->where('name', 'like', "%{$search}%")
                        ->orWhere('name_translations->en', 'like', "%{$search}%")
                        ->orWhere('name_translations->de', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);
    }

    public function getDepartmentById(string $id): Department
    {
        return Department::query()->findOrFail($id);
    }

    public function createDepartment(array $data): Department
    {
        return Department::query()->create($this->normalizeData($data));
    }

    public function updateDepartment(string $id, array $data): bool
    {
        return $this->getDepartmentById($id)->update($this->normalizeData($data));
    }

    public function deleteDepartment(string $id): int
    {
        return Department::destroy($id);
    }

    private function normalizeData(array $data): array
    {
        if (array_key_exists('nameTranslations', $data)) {
            $data['name_translations'] = collect($data['nameTranslations'])
                ->only(['en', 'de'])
                ->map(fn ($name) => trim((string) $name))
                ->filter(fn ($name) => $name !== '')
                ->all();
            unset($data['nameTranslations']);
        }
        $data['production'] = (bool) ($data['production'] ?? false);
        $data['use_printer'] = (bool) ($data['use_printer'] ?? false);
        $data['printer_number'] = $data['use_printer'] ? ($data['printer_number'] ?? null) : null;

        return $data;
    }
}
