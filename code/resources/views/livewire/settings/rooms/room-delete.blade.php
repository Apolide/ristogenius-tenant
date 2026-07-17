<div>
    @if ($roomId && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">{{ __('room_settings.rooms.delete') }}</div>
                        <p class="text-xs text-gray-500 font-normal">{{ __('room_settings.rooms.delete_confirm', ['name'=>$roomName]) }}</p>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="flex space-x-2">
                    <button wire:click="delete" class="ti-btn ti-btn-danger-full ti-btn-wave me-[0.375rem]">
                        {{ __('room_settings.rooms.delete_button') }}
                    </button>
                    <button wire:click="abort" type="button" class="ti-btn ti-btn-light">
                        {{ __('room_settings.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
