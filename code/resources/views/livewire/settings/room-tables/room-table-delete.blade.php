<div>
    @if ($roomTableId && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="flex justify-between">
                    <div>
                        <div class="box-title pb-0">ELIMINA TAVOLO</div>
                        <p class="text-xs text-gray-500 font-normal">Sei sicuro di voler eliminare il tavolo {{ $roomTableName }}?</p>
                    </div>
                </div>
            </div>

            <div class="box-body">
                <div class="flex space-x-2">
                    <button wire:click="delete" class="ti-btn ti-btn-danger-full ti-btn-wave me-[0.375rem]">
                        Si, elimina
                    </button>
                    <button wire:click="abort" type="button" class="ti-btn ti-btn-light">
                        Annulla
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
