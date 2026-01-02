<div>
    @if($isVisible)
        <div class="box">
            <div class="box-header border-none">
                <div class="box-title">CREA NUOVA EMAIL</div>
            </div>
            <div class="box-body">
                <form wire:submit.prevent="save">
                    <div class="mb-3">
                        <label for="subject" class="block mb-1">Oggetto</label>
                        <input wire:model="subject" type="text" id="subject" class="form-control">
                        @error('subject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="body" class="block mb-1">Testo</label>
                        <textarea wire:model="body" id="body" class="form-control"></textarea>
                        @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="url" class="block mb-1">URL</label>
                        <input wire:model="url" type="text" id="url" class="form-control">
                        @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="button_label" class="block mb-1">Button Label</label>
                        <input wire:model="button_label" type="text" id="button_label" class="form-control">
                        @error('button_label') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="ti-btn ti-btn-primary-full">Salva</button>
                    <button type="button" wire:click="abort" class="ti-btn ti-btn-light">Annulla</button>
                </form>
            </div>
        </div>
    @endif
</div>
