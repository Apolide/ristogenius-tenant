<?php

namespace App\Livewire\Settings\Rooms;

use App\Models\Room;
use App\Models\RoomTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomTablePlanner extends Component
{
    public ?Room $currentRoom = null;

    public Collection $rooms;

    public string $mode = 'view';

    public array $layout = [
        'width' => 1200,
        'height' => 700,
        'grid' => 20,
    ];

    public array $tables = [];

    public function mount(Room $room): void
    {
        $this->rooms = Room::query()
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $this->mode = request('mode') === 'edit' ? 'edit' : 'view';
        $this->loadRoom($room->id);
    }

    #[On('room.changed')]
    public function changeRoom(string $roomId): void
    {
        $this->loadRoom($roomId);
        $this->dispatch('planner.refresh', tables: $this->tables);
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode === 'edit' ? 'edit' : 'view';
    }

    #[On('planner.save-state')]
    public function saveState(array $layout = [], array $tables = []): void
    {
        if ($this->mode !== 'edit' || ! $this->currentRoom) {
            return;
        }

        foreach ($tables as $table) {
            if (! isset($table['id'])) {
                continue;
            }

            RoomTable::query()
                ->where('room_id', $this->currentRoom->id)
                ->whereKey($table['id'])
                ->update([
                    'x' => max(0, (int) ($table['x'] ?? 0)),
                    'y' => max(0, (int) ($table['y'] ?? 0)),
                    'w' => max(1, (int) ($table['w'] ?? 78)),
                    'h' => max(1, (int) ($table['h'] ?? 78)),
                    'rotation' => ((int) ($table['rotation'] ?? 0)) % 360,
                    'status' => $table['status'] ?? 'free',
                ]);
        }

        $this->loadRoom($this->currentRoom->id);
    }

    #[On('planner.add-many')]
    public function addMany(string $type = 'quadrato', int $count = 1, int $cols = 3, string $prefix = ''): void
    {
        if ($this->mode !== 'edit' || ! $this->currentRoom) {
            return;
        }

        $type = in_array($type, ['circolare', 'quadrato', 'rettangolare'], true) ? $type : 'quadrato';
        $count = max(1, min(100, $count));
        $cols = max(1, min(20, $cols));
        $prefix = trim($prefix);
        $existingNames = RoomTable::query()
            ->where('room_id', $this->currentRoom->id)
            ->pluck('name')
            ->all();

        $nextNumber = $this->nextTableNumber($existingNames);
        $cellW = 100;
        $cellH = 100;

        for ($i = 0; $i < $count; $i++) {
            $name = $this->uniqueTableName($prefix, $nextNumber + $i, $existingNames);
            $existingNames[] = $name;

            RoomTable::query()->create([
                'room_id' => $this->currentRoom->id,
                'name' => $name,
                'type' => $type,
                'min_people' => 1,
                'max_people' => $type === 'rettangolare' ? 4 : 2,
                'status' => 'free',
                'x' => ($i % $cols) * $cellW,
                'y' => intdiv($i, $cols) * $cellH,
                'w' => $type === 'rettangolare' ? 98 : 78,
                'h' => 78,
                'rotation' => 0,
            ]);
        }

        $this->loadRoom($this->currentRoom->id);
        $this->dispatch('planner.refresh', tables: $this->tables);
    }

    #[On('planner.align')]
    public function align(string $mode, array $ids): void
    {
        if ($this->mode !== 'edit' || ! $this->currentRoom || $ids === []) {
            return;
        }

        $tables = RoomTable::query()
            ->where('room_id', $this->currentRoom->id)
            ->whereIn('id', $ids)
            ->get();

        if ($tables->isEmpty()) {
            return;
        }

        if (! in_array($mode, ['left', 'top', 'right', 'bottom'], true)) {
            return;
        }

        $left = $tables->min('x');
        $top = $tables->min('y');
        $right = $tables->max(fn (RoomTable $table) => $table->x + $table->w);
        $bottom = $tables->max(fn (RoomTable $table) => $table->y + $table->h);

        foreach ($tables as $table) {
            $table->update(match ($mode) {
                'left' => ['x' => $left],
                'top' => ['y' => $top],
                'right' => ['x' => max(0, $right - $table->w)],
                'bottom' => ['y' => max(0, $bottom - $table->h)],
                default => [],
            });
        }

        $this->loadRoom($this->currentRoom->id);
        $this->dispatch('planner.refresh', tables: $this->tables);
    }

    public function render()
    {
        return view('livewire.settings.rooms.room-table-planner')
            ->title(__('room_planner.title'));
    }

    private function loadRoom(string $roomId): void
    {
        $this->currentRoom = Room::query()->findOrFail($roomId);
        $this->tables = $this->currentRoom->tables()
            ->orderBy('name')
            ->get()
            ->map(fn (RoomTable $table): array => [
                'id' => $table->id,
                'label' => $table->name,
                'type' => $table->type,
                'status' => $table->status,
                'x' => $table->x,
                'y' => $table->y,
                'w' => $table->w,
                'h' => $table->h,
                'rotation' => $table->rotation,
            ])
            ->all();
    }

    private function nextTableNumber(array $names): int
    {
        $numbers = collect($names)
            ->map(fn (string $name): int => (int) preg_replace('/\D+/', '', $name))
            ->filter()
            ->values();

        return ($numbers->max() ?? 0) + 1;
    }

    private function uniqueTableName(string $prefix, int $number, array $existingNames): string
    {
        $name = $prefix . $number;

        while (in_array($name, $existingNames, true)) {
            $number++;
            $name = $prefix . $number;
        }

        return $name !== '' ? $name : (string) Str::uuid();
    }
}
