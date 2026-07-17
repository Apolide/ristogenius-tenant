<div class="content">
    <div class="main-content">
        <div x-data="{ activeDay: 'lunedi' }" class="w-full">
            <div class="mb-6">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Pax per slot orario</h5>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Imposta capienza standard e override a range data.</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
            @endif

            <form wire:submit.prevent="save" class="space-y-6">
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1.4fr_minmax(180px,260px)] md:items-end">
                        <div>
                            <label for="fallback" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">Fallback pax</label>
                            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Numero di pax usato quando una singola fascia oraria non ha un valore specifico.</p>
                        </div>
                        <input type="number" wire:model="fallback" id="fallback" min="0" class="form-control">
                    </div>

                    <div class="mt-4">
                        <button type="button" wire:click="applyFallbackToWeekly" class="ti-btn ti-btn-light">Applica fallback a tutti gli slot settimanali</button>
                    </div>
                </section>

                <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                    <div role="tablist" aria-label="Giorni della settimana" class="flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                        @foreach ($days as $dayKey => $day)
                            <button type="button" role="tab" :aria-selected="activeDay === '{{ $dayKey }}'" @click="activeDay = '{{ $dayKey }}'"
                                class="ti-btn !mb-0 inline-flex items-center justify-center rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                :class="activeDay === '{{ $dayKey }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                {{-- <span class="sm:hidden">{{ $day['short'] }}</span>
                                <span class="hidden sm:inline">{{ $day['label'] }}</span> --}}
                                <span class="">{{ $day['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @foreach ($days as $dayKey => $day)
                    <section x-show="activeDay === '{{ $dayKey }}'" x-transition.opacity role="tabpanel" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6">
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $day['label'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Massimo pax per fascia oraria.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                            @foreach ($meals as $mealKey => $mealLabel)
                                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="mb-4">
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ $mealLabel }}</h4>
                                    </div>

                                    <div class="space-y-3">
                                        @forelse ($slots[$dayKey][$mealKey] as $slotIndex => $slot)
                                            <div class="grid grid-cols-1 gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-700 sm:grid-cols-[minmax(0,1fr)_120px] sm:items-center">
                                                <label for="{{ $dayKey }}_{{ $mealKey }}_{{ $slotIndex }}" class="text-sm text-gray-700 dark:text-gray-200">{{ $slot['label'] }}</label>
                                                <input type="number" wire:model="weekly.{{ $dayKey }}.{{ $mealKey }}.{{ $slotIndex }}" id="{{ $dayKey }}_{{ $mealKey }}_{{ $slotIndex }}" placeholder="Pax" min="0" class="form-control">
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Servizio chiuso o senza slot configurati.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6">
                    <div class="mb-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Configurazione per range specifici</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestisci eccezioni o periodi con capienza diversa rispetto alla regola settimanale.</p>
                    </div>

                    <div class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-[180px_180px_150px_150px_auto] md:items-end">
                        <div>
                            <label for="pax_range_start" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Dal</label>
                            <input type="date" id="pax_range_start" wire:model="new_range_start" class="form-control">
                        </div>
                        <div>
                            <label for="pax_range_end" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Al</label>
                            <input type="date" id="pax_range_end" wire:model="new_range_end" class="form-control">
                        </div>
                        <div>
                            <label for="pax_pranzo" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pax pranzo</label>
                            <input type="number" id="pax_pranzo" wire:model="new_range_pax.pranzo" min="0" class="form-control">
                        </div>
                        <div>
                            <label for="pax_cena" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pax cena</label>
                            <input type="number" id="pax_cena" wire:model="new_range_pax.cena" min="0" class="form-control">
                        </div>
                        <button type="button" wire:click="addRangeConfiguration" class="ti-btn ti-btn-primary-full">Aggiungi</button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($exceptions as $exception)
                            <div class="flex flex-col gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $exception->starts_on->format('d/m/Y') }} - {{ $exception->ends_on->format('d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Pranzo: {{ $exception->payload['pranzo'] ?? 'standard' }} / Cena: {{ $exception->payload['cena'] ?? 'standard' }}
                                    </div>
                                </div>
                                <button type="button" wire:click="removeRangeConfiguration({{ $exception->id }})" class="ti-btn ti-btn-danger">Rimuovi</button>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nessun range specifico configurato.</p>
                        @endforelse
                    </div>
                </section>

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full">Salva configurazione pax</button>
                    <a href="{{ route('settings.index') }}" class="ti-btn ti-btn-light">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>
