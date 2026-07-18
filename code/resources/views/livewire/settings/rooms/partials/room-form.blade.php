<div class="w-full mb-5">
    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.name') }}</label>
    <input required wire:model="name" type="text" id="name" class="form-control" placeholder="{{ __('room_settings.rooms.name') }}">
    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>

<div class="grid md:grid-cols-2 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="capacity" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.capacity') }}</label>
        <input required wire:model="capacity" type="number" min="0" id="capacity" class="form-control" placeholder="{{ __('room_settings.rooms.capacity') }}">
        @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.order') }}</label>
        <input required wire:model="order" type="number" min="1" id="order" class="form-control" placeholder="{{ __('room_settings.rooms.order') }}">
        @error('order') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid md:grid-cols-2 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="service_charge" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.service_charge') }}</label>
        <input required wire:model="service_charge" type="number" min="0" step="0.01" id="service_charge" class="form-control" placeholder="{{ __('room_settings.rooms.service_charge') }}">
        @error('service_charge') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="service_charge_percentage" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.service_charge_percentage') }}</label>
        <input required wire:model="service_charge_percentage" type="number" min="0" max="100" step="0.01" id="service_charge_percentage" class="form-control" placeholder="{{ __('room_settings.rooms.service_charge_percentage') }}">
        @error('service_charge_percentage') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid md:grid-cols-2 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="active" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.active') }}</label>
        <label class="inline-flex items-center gap-2">
            <input wire:model="active" type="checkbox" id="active" class="form-check-input">
            <span class="text-sm text-gray-700 dark:text-gray-200">{{ __('room_settings.rooms.available') }}</span>
        </label>
        @error('active') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="smoking_allowed" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('room_settings.rooms.smoking') }}</label>
        <label class="inline-flex items-center gap-2">
            <input wire:model="smoking_allowed" type="checkbox" id="smoking_allowed" class="form-check-input">
            <span class="text-sm text-gray-700 dark:text-gray-200">{{ __('room_settings.rooms.smoking_allowed') }}</span>
        </label>
        @error('smoking_allowed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>
