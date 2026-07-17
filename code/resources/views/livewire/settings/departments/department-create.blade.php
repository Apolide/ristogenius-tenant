<div>
    @if ($isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">{{ __('department_settings.new_title') }}</div>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <form wire:submit.prevent="save">
                    @include('livewire.settings.departments.partials.department-form')

                    <button wire:target="save" wire:loading.attr="disabled" type="submit" class="ti-btn ti-btn-primary-full ti-btn-wave me-[0.375rem]">
                        {{ __('department_settings.save') }}
                    </button>
                    <button wire:click="abort" type="button" class="ti-btn ti-btn-light mr-3">
                        {{ __('department_settings.cancel') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
