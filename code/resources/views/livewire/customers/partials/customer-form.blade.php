<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="w-full mb-5">
        <label for="firstname" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.firstname') }}</label>
        <input wire:model="firstname" type="text" name="firstname" id="firstname" class="form-control" placeholder="{{ __('customers.form.firstname') }}">
        @error('firstname') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="lastname" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.lastname') }}</label>
        <input wire:model="lastname" type="text" name="lastname" id="lastname" class="form-control" placeholder="{{ __('customers.form.lastname') }}">
        @error('lastname') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="w-full mb-5">
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.email') }}</label>
        <input wire:model="email" type="email" name="email" id="email" class="form-control" placeholder="{{ __('customers.form.email') }}">
        @error('email') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.phone') }}</label>
        <input required wire:model="phone" type="tel" name="phone" id="phone" class="form-control" placeholder="{{ __('customers.form.phone') }}">
        @error('phone') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="w-full mb-5">
        <label for="telegramid" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.telegram') }}</label>
        <input wire:model="telegramid" type="text" name="telegramid" id="telegramid" class="form-control" placeholder="{{ __('customers.form.telegram') }}">
        @error('telegramid') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="birthdate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.birthdate') }}</label>
        <input wire:model="birthdate" type="date" name="birthdate" id="birthdate" class="form-control">
        @error('birthdate') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="w-full mb-5">
        <label for="region_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.region') }}</label>
        <select wire:model.live="region_id" id="region_id" class="form-control">
            <option value="">{{ __('customers.form.select_region') }}</option>
            @foreach ($regions as $region)
                <option value="{{ $region->id }}">{{ $region->name }}</option>
            @endforeach
        </select>
        @error('region_id') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="province_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.province') }}</label>
        <select wire:model.live="province_id" id="province_id" class="form-control" @disabled(! $region_id)>
            <option value="">{{ __('customers.form.select_province') }}</option>
            @foreach ($provinces as $province)
                <option value="{{ $province->id }}">{{ $province->name }}</option>
            @endforeach
        </select>
        @error('province_id') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="comuni_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('customers.form.municipality') }}</label>
        <select wire:model.live="comuni_id" id="comuni_id" class="form-control" @disabled(! $province_id)>
            <option value="">{{ __('customers.form.select_municipality') }}</option>
            @foreach ($comuniList as $comune)
                <option value="{{ $comune->id }}">{{ $comune->name }}</option>
            @endforeach
        </select>
        @error('comuni_id') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
    </div>
</div>

<div class="flex flex-col mb-5">
    <label for="note" class="mb-1 font-semibold text-defaulttextcolor">{{ __('customers.form.notes') }}</label>
    <textarea id="note" wire:model.lazy="note" rows="5" class="form-control mt-1 block w-full sm:text-sm" placeholder="{{ __('customers.form.notes_placeholder') }}"></textarea>
    @error('note') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
</div>

<div class="w-full mb-5">
    <input wire:model.live="consent_marketing" type="checkbox" id="checkbox-consent-marketing" class="ti-switch">
    <label for="checkbox-consent-marketing" class="text-sm text-gray-500 ltr:ml-3 rtl:mr-3 dark:text-white/70 cursor-pointer">{{ __('customers.form.marketing') }}</label>
</div>
