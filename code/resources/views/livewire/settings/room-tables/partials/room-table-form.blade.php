<div class="grid md:grid-cols-2 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="room-table-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome o numero tavolo</label>
        <input required wire:model="name" type="text" id="room-table-name" class="form-control" placeholder="Nome o numero tavolo">
        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="room-table-type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipo</label>
        <input required wire:model="type" type="text" id="room-table-type" class="form-control" placeholder="Tipo tavolo">
        @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid md:grid-cols-3 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="room-table-room" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sala</label>
        <select required wire:model="room_id" id="room-table-room" class="form-control">
            <option value="">Seleziona sala</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
            @endforeach
        </select>
        @error('room_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="room-table-min-people" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Min persone</label>
        <input required wire:model="min_people" type="number" min="1" id="room-table-min-people" class="form-control" placeholder="Min persone">
        @error('min_people') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="room-table-max-people" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Max persone</label>
        <input required wire:model="max_people" type="number" min="1" id="room-table-max-people" class="form-control" placeholder="Max persone">
        @error('max_people') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
