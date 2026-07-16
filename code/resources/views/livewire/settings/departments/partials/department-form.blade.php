<div class="w-full mb-5">
    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nome reparto</label>
    <input required wire:model="name" type="text" id="name" class="form-control" placeholder="Nome reparto">
    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>

<div class="grid md:grid-cols-2 grid-cols-1 gap-5">
    <div class="w-full mb-5">
        <label for="production" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Reparto di produzione</label>
        <label class="inline-flex items-center gap-2">
            <input wire:model="production" type="checkbox" id="production" class="form-check-input">
            <span class="text-sm text-gray-700 dark:text-gray-200">Usato per produzione prodotti/comande</span>
        </label>
        @error('production') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="w-full mb-5">
        <label for="use_printer" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Usa stampante</label>
        <label class="inline-flex items-center gap-2">
            <input wire:model.live="use_printer" type="checkbox" id="use_printer" class="form-check-input">
            <span class="text-sm text-gray-700 dark:text-gray-200">Invia le comande a una stampante dedicata</span>
        </label>
        @error('use_printer') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
</div>

@if ($use_printer)
    <div class="w-full mb-5">
        <label for="printer_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Numero stampante</label>
        <input required wire:model="printer_number" type="number" min="1" max="65535" id="printer_number" class="form-control" placeholder="Numero stampante">
        @error('printer_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>
@endif
