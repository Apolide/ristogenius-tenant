<div>
    @if($emailId && $isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="box-title">ELIMINA EMAIL</div>
                <p class="text-sm text-gray-500">Sei sicuro di voler eliminare questa email?</p>
            </div>
            <div class="box-body">
                <div class="flex space-x-3">
                    <button wire:click="delete" class="ti-btn ti-btn-danger-full">Sì, elimina</button>
                    <button wire:click="abort" class="ti-btn ti-btn-light">Annulla</button>
                </div>
            </div>
        </div>
    @endif
</div>
