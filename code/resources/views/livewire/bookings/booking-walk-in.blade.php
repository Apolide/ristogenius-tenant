<div class="content"><div class="main-content">
    <div class="flex items-center justify-between mb-6 page-header-breadcrumb"><h5 class="page-title text-[1.3125rem] font-medium">{{ __('bookings.walk_in') }}</h5><a href="{{ route('bookings.index') }}" class="ti-btn ti-btn-light"><i class="las la-arrow-left"></i> {{ __('bookings.back') }}</a></div>
    <div class="box"><div class="box-header"><div><div class="box-title">{{ __('bookings.walkin_page.create') }}</div><p class="text-textmuted">{{ __('bookings.walkin_page.description') }}</p></div></div><div class="box-body">
        @if($errors->any())<div class="alert alert-danger mb-5">{{ $errors->first() }}</div>@endif
        <form wire:submit="save"><div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div><label class="block text-sm font-medium">{{ __('bookings.form.date') }}</label><input type="date" min="{{ now()->toDateString() }}" wire:model.live="booking_date" class="form-control" required></div>
            <div><label class="block text-sm font-medium">{{ __('bookings.form.time') }}</label><select wire:model.live="booking_time" class="form-control" required><option value="">{{ __('bookings.form.select_time') }}</option>@foreach($slots as $value => $slot)<option value="{{ $value }}">{{ $slot['label'] }}</option>@endforeach</select>@error('booking_time')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror</div>
            <div><label class="block text-sm font-medium">{{ __('bookings.form.people') }}</label><input type="number" min="1" wire:model="pax" class="form-control" required></div>
            <div><label class="block text-sm font-medium">{{ __('bookings.form.customer_language') }}</label><select wire:model="lang" class="form-control" required>@foreach($languages as $code => $language)<option value="{{ $code }}">{{ $language['flag'] }} {{ strtoupper($code) }} — {{ $language['label'] }}</option>@endforeach</select></div>
        </div><div class="mt-3"><label class="block text-sm font-medium">{{ __('bookings.walkin_page.table_assignment') }}</label><div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="openTablePicker" class="ti-btn ti-btn-primary"><i class="las la-chair"></i> {{ __('bookings.walkin_page.select_tables') }}</button>
            @if($booking_time === '')<small class="text-textmuted">{{ __('bookings.tables.select_first') }}</small>@elseif(count($selectedTablesIds))<span class="badge bg-success/10 text-success">{{ count($selectedTablesIds) }} {{ count($selectedTablesIds) === 1 ? __('bookings.tables.one') : __('bookings.tables.many') }}</span>@else<small class="text-textmuted">{{ __('bookings.tables.none') }}</small>@endif
        </div></div>
        <div class="mt-3"><label class="block text-sm font-medium">{{ __('bookings.form.notes') }}</label><textarea wire:model="note" class="form-control"></textarea></div><button type="submit" class="ti-btn ti-btn-primary mt-5">{{ __('bookings.walkin_page.save') }}</button></form>
    </div></div>
    <div class="box"><div class="box-header"><div class="box-title">{{ __('bookings.walkin_page.stats', ['date' => \Carbon\Carbon::parse($booking_date)->format('d/m/Y')]) }}</div></div><div class="box-body">@include('livewire.bookings.partials.timeslot-stats')</div></div>
    @if($tablePickerOpen) @include('livewire.bookings.partials.table-picker-modal', ['saveAction' => 'confirmTables', 'closeAction' => 'closeTablePicker']) @endif
</div></div>
