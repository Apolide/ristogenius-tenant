<div>
    @if ($isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">{{ __('customers.form.create') }}</div>
            </div>
            <div class="box-body">
                <form wire:submit.prevent="save">
                    @include('livewire.customers.partials.customer-form')

                    <button wire:target="save" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">{{ __('customers.save') }}</button>
                    <button wire:click="abort" type="button" class="ti-btn ti-btn-light mr-3">{{ __('customers.cancel') }}</button>
                </form>
            </div>
        </div>
    @endif
</div>
