<div class="service-date-navigation">
    <button type="button" wire:click="previousDay" class="ti-btn ti-btn-primary !m-0" aria-label="{{ __('bookings.back') }}"><i class="las la-arrow-left text-xl" aria-hidden="true"></i><span class="hidden md:block">{{ __('bookings.back') }}</span></button>
    <div class="flex items-center justify-center gap-2">
        <input type="date" wire:model.live="date" class="form-control" style="max-width:190px" aria-label="{{ __('Data di servizio') }}" onchange="this.blur()">
        <button type="button" wire:click="selectToday" class="ti-btn ti-btn-primary !m-0">{{ __('bookings.today') }}</button>
    </div>
    <button type="button" wire:click="nextDay" class="ti-btn ti-btn-primary !m-0" aria-label="{{ __('bookings.next') }}"><span class="hidden md:block">{{ __('bookings.next') }}</span><i class="las la-arrow-right text-xl" aria-hidden="true"></i></button>
</div>
