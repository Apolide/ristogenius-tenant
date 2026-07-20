<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <h5 class="page-title text-[1.3125rem] font-medium">{{ $booking ? __('bookings.edit') : __('bookings.new') }}</h5>
        <div class="flex gap-2"><a href="{{ route('bookings.index') }}" class="ti-btn ti-btn-light"><i class="las la-arrow-left"></i> {{ __('bookings.back') }}</a>@unless($booking)<a href="{{ route('bookings.walk-in') }}" class="ti-btn ti-btn-info">Walk In</a>@endunless</div>
    </div>
    <div class="box"><div class="box-body">
        @if ($errors->any()) <div class="alert alert-danger mb-5">{{ $errors->first() }}</div> @endif
        <form wire:submit="save" @unless($booking) oninput="const phone = this.elements.phone; const email = this.elements.email; phone.required = email.value.trim() === ''; email.required = phone.value.trim() === ''; this.elements.phone_region.required = phone.value.trim() !== '';" @endunless>
            @if($booking)
                <div class="mb-5 rounded-lg border border-warning/40 bg-warning/10 p-4">
                    <label class="flex items-start gap-3 font-medium">
                        <input type="checkbox" wire:model.live="send_booking_proposal" class="form-check-input mt-1">
                        <span>
                            {{ __('bookings.form.send_proposal') }}
                            <span class="block text-sm font-normal text-textmuted">{{ __('bookings.form.send_proposal_help') }}</span>
                        </span>
                    </label>
                    @if($send_booking_proposal)
                        <div class="mt-4">
                            <label class="block text-sm font-medium" for="restaurant-note">{{ __('bookings.form.restaurant_note') }}</label>
                            <textarea id="restaurant-note" wire:model="restaurant_note" class="form-control" rows="4" required></textarea>
                            @error('restaurant_note')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                            @error('send_booking_proposal')<p class="text-danger text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-sm font-medium">{{ __('bookings.form.phone') }} <i class="las la-lock text-gray-700 dark:text-gray-300" aria-hidden="true"></i></label><input value="{{ $phone ?: '-' }}" class="form-control disabled:cursor-not-allowed disabled:border-gray-400 disabled:bg-gray-200 disabled:text-gray-400 disabled:opacity-100 dark:disabled:border-gray-600 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" disabled aria-disabled="true"></div>
                    <div><label class="block text-sm font-medium">Email <i class="las la-lock text-gray-700 dark:text-gray-300" aria-hidden="true"></i></label><input value="{{ $email ?: '-' }}" class="form-control disabled:cursor-not-allowed disabled:border-gray-400 disabled:bg-gray-200 disabled:text-gray-400 disabled:opacity-100 dark:disabled:border-gray-600 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" disabled aria-disabled="true"></div>
                    <div><label class="block text-sm font-medium">{{ __('bookings.form.firstname') }} <i class="las la-lock text-gray-700 dark:text-gray-300" aria-hidden="true"></i></label><input value="{{ $firstname }}" class="form-control disabled:cursor-not-allowed disabled:border-gray-400 disabled:bg-gray-200 disabled:text-gray-400 disabled:opacity-100 dark:disabled:border-gray-600 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" disabled aria-disabled="true"></div>
                    <div><label class="block text-sm font-medium">{{ __('bookings.form.lastname') }} <i class="las la-lock text-gray-700 dark:text-gray-300" aria-hidden="true"></i></label><input value="{{ $lastname ?: '-' }}" class="form-control disabled:cursor-not-allowed disabled:border-gray-400 disabled:bg-gray-200 disabled:text-gray-400 disabled:opacity-100 dark:disabled:border-gray-600 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" disabled aria-disabled="true"></div>
                </div>
            @else
                <p class="text-textmuted mb-2">{{ __('bookings.form.hint') }}</p>
                <p id="booking-contact-help" class="text-textmuted text-sm mb-2">{{ __('bookings.messages.contact_required') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="relative"><label class="block text-sm font-medium">{{ __('bookings.form.phone') }}</label><div class="flex gap-2">
                        <select name="phone_region" wire:model="phone_region" class="form-control max-w-[260px]" autocomplete="tel-country-code" @required(trim($phone) !== '')><option value="">{{ __('bookings.form.prefix') }}</option>@foreach($countries as $country)<option value="{{ $country['region'] }}">{{ $country['name'] }} {{ $country['flag'] }} {{ $country['prefix'] }}</option>@endforeach</select>
                        <input name="phone" wire:model.live.debounce.800ms="phone" class="form-control" inputmode="tel" autocomplete="tel-national" maxlength="25" aria-describedby="booking-contact-help" @required(trim($email) === '')>
                    </div>@error('phone')<p class="text-danger text-sm">{{ $message }}</p>@enderror @include('livewire.bookings.partials.customer-suggestions', ['field' => 'phone'])</div>
                    <div class="relative"><label class="block text-sm font-medium">Email</label><input type="email" name="email" wire:model.live.debounce.800ms="email" class="form-control" autocomplete="email" aria-describedby="booking-contact-help" @required(trim($phone) === '')>@error('email')<p class="text-danger text-sm">{{ $message }}</p>@enderror @include('livewire.bookings.partials.customer-suggestions', ['field' => 'email'])</div>
                    <div class="relative"><label class="block text-sm font-medium">{{ __('bookings.form.firstname') }}</label><input wire:model.live.debounce.800ms="firstname" class="form-control" required>@include('livewire.bookings.partials.customer-suggestions', ['field' => 'firstname'])</div>
                    <div class="relative"><label class="block text-sm font-medium">{{ __('bookings.form.lastname') }}</label><input wire:model.live.debounce.800ms="lastname" class="form-control">@include('livewire.bookings.partials.customer-suggestions', ['field' => 'lastname'])</div>
                </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                <div><label class="block text-sm font-medium">{{ __('bookings.form.date') }}</label><input type="date" min="{{ now()->toDateString() }}" wire:model.live="booking_date" class="form-control" required></div>
                <div><label class="block text-sm font-medium">{{ __('bookings.form.time') }}</label><select wire:model="booking_time" class="form-control" required><option value="">{{ __('bookings.form.select_time') }}</option>@foreach($slots as $value => $slot)<option value="{{ $value }}">{{ $slot['label'] }}</option>@endforeach</select></div>
                <div><label class="block text-sm font-medium">{{ __('bookings.form.people') }}</label><input type="number" min="1" wire:model="pax" class="form-control" required></div>
                <div><label class="block text-sm font-medium">{{ __('bookings.form.customer_language') }} @if($booking)<i class="las la-lock text-gray-700 dark:text-gray-300" aria-hidden="true"></i>@endif</label>@if($booking)<input value="{{ $languages[$lang]['flag'] ?? '' }} {{ strtoupper($lang) }} — {{ $languages[$lang]['label'] ?? $lang }}" class="form-control disabled:cursor-not-allowed disabled:border-gray-400 disabled:bg-gray-200 disabled:text-gray-400 disabled:opacity-100 dark:disabled:border-gray-600 dark:disabled:bg-gray-700 dark:disabled:text-gray-400" disabled aria-disabled="true">@else<select wire:model="lang" class="form-control" required>@foreach($languages as $code => $language)<option value="{{ $code }}">{{ $language['flag'] }} {{ strtoupper($code) }} — {{ $language['label'] }}</option>@endforeach</select>@endif</div>
                <div><label class="block text-sm font-medium">{{ __('bookings.form.status') }} @if($booking)<i class="las la-lock text-gray-700 dark:text-gray-300" aria-hidden="true"></i>@endif</label><input value="{{ __('bookings.statuses.'.($booking ? $status : 'accepted')) }}" class="form-control @if($booking) disabled:cursor-not-allowed disabled:border-gray-400 disabled:bg-gray-200 disabled:text-gray-400 disabled:opacity-100 dark:disabled:border-gray-600 dark:disabled:bg-gray-700 dark:disabled:text-gray-400 @else bg-light @endif" @if($booking) disabled aria-disabled="true" @else readonly aria-readonly="true" @endif></div>
            </div>
            <div class="mt-3"><label class="block text-sm font-medium">{{ __('bookings.form.notes') }}</label><textarea wire:model="note" class="form-control"></textarea></div>
            <div class="mt-4 flex flex-wrap items-center gap-3"><button type="button" wire:click="openTablePicker" class="ti-btn ti-btn-info"><i class="las la-chair"></i> {{ __('bookings.form.assign_tables') }}</button>@if(count($selectedTablesIds))<span class="badge bg-success/10 text-success">{{ count($selectedTablesIds) }} {{ count($selectedTablesIds) === 1 ? __('bookings.tables.one') : __('bookings.tables.many') }}</span>@else<span class="text-sm text-textmuted">{{ __('bookings.tables.none') }}</span>@endif</div>
            <div class="flex gap-3 mt-5"><button class="ti-btn ti-btn-primary h-12" type="submit">{{ __('bookings.form.save') }}</button>@if($booking)<button type="button" wire:click="delete" wire:confirm="{{ __('bookings.form.delete_confirm') }}" class="ti-btn ti-btn-danger-full h-12">{{ __('bookings.form.delete') }}</button>@endif</div>
        </form>
        @if($tablePickerOpen) @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'confirmTables', 'closeAction' => 'closeTablePicker']) @endif
    </div></div>
</div></div>
