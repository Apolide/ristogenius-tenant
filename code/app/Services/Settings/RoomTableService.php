<?php

namespace App\Services\Settings;

use App\Models\Room;
use App\Models\RoomTable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoomTableService
{
    public function getRoomTables(string $search = ''): LengthAwarePaginator
    {
        return RoomTable::query()
            ->with('room')
            ->when($search !== '', function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('room', function ($roomQuery) use ($search) {
                        $roomQuery->where('name', 'like', "%{$search}%");
                    });
            })
            ->join('settings_rooms', 'settings_room_tables.room_id', '=', 'settings_rooms.id')
            ->orderBy('settings_rooms.order')
            ->orderBy('settings_rooms.name')
            ->orderBy('settings_room_tables.name')
            ->select('settings_room_tables.*')
            ->paginate(10);
    }

    public function getRoomTableById(string $id): RoomTable
    {
        return RoomTable::query()->with('room')->findOrFail($id);
    }

    public function createRoomTable(array $data): RoomTable
    {
        return RoomTable::query()->create($this->normalizeData($data));
    }

    public function updateRoomTable(string $id, array $data): bool
    {
        return $this->getRoomTableById($id)->update($this->normalizeData($data));
    }

    public function deleteRoomTable(string $id): int
    {
        return RoomTable::destroy($id);
    }

    public function getRoomOptions(): Collection
    {
        return Room::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function normalizeData(array $data): array
    {
        $data['name'] = trim((string) $data['name']);
        $data['type'] = trim((string) $data['type']);
        $data['min_people'] = (int) ($data['min_people'] ?? 1);
        $data['max_people'] = (int) ($data['max_people'] ?? $data['min_people']);
        $data['status'] = $data['status'] ?? 'free';
        $data['x'] = max(0, (int) ($data['x'] ?? 0));
        $data['y'] = max(0, (int) ($data['y'] ?? 0));
        $data['w'] = max(1, (int) ($data['w'] ?? 78));
        $data['h'] = max(1, (int) ($data['h'] ?? 78));
        $data['rotation'] = ((int) ($data['rotation'] ?? 0)) % 360;

        return $data;
    }
}
