<div>
    @if ($roomTableId && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">{{ __('room_settings.tables.edit') }}</div>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <form wire:submit.prevent="update">
                    @include('livewire.settings.room-tables.partials.room-table-form')

                    <button wire:target="update" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
                        {{ __('room_settings.save') }}
                    </button>
                    <button wire:click="abort" type="button" class="ti-btn ti-btn-light mr-3">
                        {{ __('room_settings.cancel') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
