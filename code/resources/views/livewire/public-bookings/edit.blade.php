<div>
    @include('livewire.public-bookings.partials.header')
    <section class="box"><div class="box-header"><h2 class="box-title">{{ __('public_bookings.edit_title') }}</h2></div><div class="box-body">
        @if ($errors->any())<div class="alert alert-danger mb-5">{{ $errors->first() }}</div>@endif
        <form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="form-label">{{ __('bookings.form.date') }}</label><input type="date" min="{{ now()->toDateString() }}" wire:model.live="booking_date" class="form-control" required></div>
                <div><label class="form-label">{{ __('bookings.form.time') }}</label><select wire:model="booking_time" class="form-control" required><option value="">{{ __('bookings.form.select_time') }}</option>@foreach($slots as $value => $slot)<option value="{{ $value }}">{{ $slot['label'] }}</option>@endforeach</select></div>
                <div><label class="form-label">{{ __('bookings.form.people') }}</label><input type="number" min="1" wire:model="pax" class="form-control" required></div>
            </div>
            <div class="mt-4"><label class="form-label">{{ __('bookings.form.notes') }}</label><textarea wire:model="note" class="form-control"></textarea></div>
            <div class="flex flex-wrap gap-3 mt-6"><button type="submit" class="ti-btn ti-btn-primary">{{ __('public_bookings.save_changes') }}</button><a href="{{ $viewUrl }}" class="ti-btn ti-btn-light">{{ __('public_bookings.cancel') }}</a></div>
        </form>
    </div></section>
</div>
