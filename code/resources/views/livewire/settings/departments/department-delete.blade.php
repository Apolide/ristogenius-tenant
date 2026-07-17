<div>
    @if ($departmentId && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">{{ __('department_settings.delete_title') }}</div>
                        <p class="text-xs text-gray-500 font-normal">{{ __('department_settings.delete_confirmation', ['name' => $departmentName]) }}</p>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="flex space-x-2">
                    <button wire:click="delete" class="ti-btn ti-btn-danger-full ti-btn-wave me-[0.375rem]">
                        {{ __('department_settings.confirm_delete') }}
                    </button>
                    <button wire:click="abort" type="button" class="ti-btn ti-btn-light">
                        {{ __('department_settings.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
