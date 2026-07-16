<?php

namespace App\Services\Settings;

use App\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomService
{
    public function getRooms(string $search = ''): LengthAwarePaginator
    {
        return Room::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(10);
    }

    public function getRoomById(string $id): Room
    {
        return Room::query()->findOrFail($id);
    }

    public function createRoom(array $data): Room
    {
        return Room::query()->create($this->normalizeData($data));
    }

    public function updateRoom(string $id, array $data): bool
    {
        return $this->getRoomById($id)->update($this->normalizeData($data));
    }

    public function deleteRoom(string $id): int
    {
        return Room::destroy($id);
    }

    private function normalizeData(array $data): array
    {
        $data['active'] = (bool) ($data['active'] ?? false);
        $data['smoking_allowed'] = (bool) ($data['smoking_allowed'] ?? false);
        $data['service_charge'] = $data['service_charge'] ?? 0;
        $data['service_charge_percentage'] = $data['service_charge_percentage'] ?? 0;
        $data['order'] = $data['order'] ?? 1;
        $data['capacity'] = $data['capacity'] ?? 0;

        return $data;
    }
}
