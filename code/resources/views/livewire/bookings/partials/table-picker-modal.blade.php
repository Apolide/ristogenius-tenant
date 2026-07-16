<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-3" role="dialog" aria-modal="true" aria-labelledby="table-picker-title">
    <div style="max-height: 80vh; min-width: 340px;" class="flex max-h-[80vh] w-full min-w-[300px] max-w-[560px] flex-col rounded-md bg-white p-4 shadow-xl dark:bg-bodybg sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="table-picker-title" class="text-lg font-bold sm:text-xl">Gestione tavoli assegnati</h2>
                <p class="text-xs text-textmuted">Seleziona uno o più tavoli per questa prenotazione.</p>
            </div>
            <button type="button" wire:click="{{ $closeAction }}" class="ti-btn ti-btn-light ti-btn-icon !m-0" aria-label="Chiudi"><i class="las la-times text-xl"></i></button>
        </div>

        <div class="mt-3">
            <label for="table-picker-search" class="sr-only">Cerca tavolo o sala</label>
            <input id="table-picker-search" type="search" wire:model.live.debounce.300ms="searchTable" class="form-control w-full" placeholder="Cerca tavolo o sala">
        </div>

        <div class="mt-3 min-h-[180px] flex-1 overflow-y-auto pe-1">
            @php
                $selectedTables = $tables->filter(fn ($table) => in_array($table->id, $tablePickerSelection, true));
                $availableTables = $tables->reject(fn ($table) => in_array($table->id, $tablePickerSelection, true));
            @endphp

            @if($selectedTables->isNotEmpty())
                <p class="mb-2 text-sm font-bold text-info">Tavoli selezionati</p>
                <div class="mb-5 grid grid-cols-2 gap-2">
                    @foreach($selectedTables as $table)
                        @include('livewire.bookings.partials.table-picker-option', ['table' => $table, 'selected' => true])
                    @endforeach
                </div>
            @endif

            <p class="mb-2 text-sm font-bold text-success">Tavoli disponibili</p>
            <div class="grid grid-cols-2 gap-2">
                @forelse($availableTables as $table)
                    @include('livewire.bookings.partials.table-picker-option', ['table' => $table, 'selected' => false])
                @empty
                    <p class="col-span-2 rounded bg-light p-3 text-center text-sm text-textmuted">Nessun tavolo disponibile con questi criteri.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-3 flex shrink-0 gap-3 border-t bg-white pt-4 dark:bg-bodybg">
            <button type="button" wire:click="{{ $saveAction }}" class="ti-btn ti-btn-success-full h-11">Conferma selezione</button>
            <button type="button" wire:click="{{ $closeAction }}" class="ti-btn ti-btn-secondary h-11">Chiudi</button>
        </div>
    </div>
</div>
