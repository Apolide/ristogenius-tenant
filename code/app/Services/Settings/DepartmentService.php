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
                $query->where('name', 'like', "%{$search}%");
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
        $data['production'] = (bool) ($data['production'] ?? false);
        $data['use_printer'] = (bool) ($data['use_printer'] ?? false);
        $data['printer_number'] = $data['use_printer'] ? ($data['printer_number'] ?? null) : null;

        return $data;
    }
}
