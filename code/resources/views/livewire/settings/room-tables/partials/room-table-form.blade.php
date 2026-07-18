<div class="grid md:grid-cols-2 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="room-table-name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.tables.name') }}</label>
        <input required wire:model="name" type="text" id="room-table-name" class="form-control" placeholder="{{ __('room_settings.tables.name') }}">
        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="room-table-type" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.tables.type') }}</label>
        <input required wire:model="type" type="text" id="room-table-type" class="form-control" placeholder="{{ __('room_settings.tables.type_placeholder') }}">
        @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid md:grid-cols-3 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="room-table-room" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.tables.room') }}</label>
        <select required wire:model="room_id" id="room-table-room" class="form-control">
            <option value="">{{ __('room_settings.tables.select_room') }}</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
            @endforeach
        </select>
        @error('room_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="room-table-min-people" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.tables.min_people') }}</label>
        <input required wire:model="min_people" type="number" min="1" id="room-table-min-people" class="form-control" placeholder="{{ __('room_settings.tables.min_people') }}">
        @error('min_people') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="room-table-max-people" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.tables.max_people') }}</label>
        <input required wire:model="max_people" type="number" min="1" id="room-table-max-people" class="form-control" placeholder="{{ __('room_settings.tables.max_people') }}">
        @error('max_people') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
