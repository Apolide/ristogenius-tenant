<div class="content">
    <div class="main-content">
        <div x-data="{ activeDay: 'lunedi' }" class="w-full">
            <div class="mb-6">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Orari di apertura</h5>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configura orari standard e chiusure speciali a range.</p>
            </div>

            @if (session('success'))
                <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
            @endif

            <form wire:submit.prevent="save" class="space-y-6">
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-[1.4fr_minmax(180px,260px)] md:items-end">
                        <div>
                            <label for="timerange" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">Taglio orari</label>
                            <p class="mb-3 text-sm text-gray-500 dark:text-gray-400">Determina la durata degli slot generati per pranzo e cena.</p>
                        </div>
                        <select id="timerange" wire:model="value.timerange" class="form-control">
                            <option value="15">15 minuti</option>
                            <option value="30">30 minuti</option>
                            <option value="60">60 minuti</option>
                        </select>
                    </div>
                </section>

                <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                    <div role="tablist" aria-label="Giorni della settimana" class="flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                        @foreach ($days as $dayKey => $day)
                            <button type="button" role="tab" :aria-selected="activeDay === '{{ $dayKey }}'" @click="activeDay = '{{ $dayKey }}'"
                                class="ti-btn !mb-0 inline-flex items-center justify-center rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                :class="activeDay === '{{ $dayKey }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                {{-- <span class="sm:hidden">{{ $day['short'] }}</span>
                                <span class="hidden sm:inline-block">{{ $day['label'] }}</span> --}}
                                <span class="">{{ $day['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @foreach ($days as $dayKey => $day)
                    <section x-show="activeDay === '{{ $dayKey }}'" x-transition.opacity role="tabpanel" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6">
                        <div class="mb-6">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $day['label'] }}LUNEDI</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Gestisci disponibilita e fasce orarie.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                            @foreach ($meals as $mealKey => $mealLabel)
                                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="mb-4">
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $mealLabel }}</h3>
                                    </div>

                                    <fieldset class="mb-4">
                                        <legend class="mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ $mealLabel }} aperto</legend>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                                                <input wire:model="value.weekly.{{ $dayKey }}.{{ $mealKey }}.open" class="h-4 w-4" type="radio" value="1">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Si</span>
                                            </label>
                                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                                                <input wire:model="value.weekly.{{ $dayKey }}.{{ $mealKey }}.open" class="h-4 w-4" type="radio" value="0">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">No</span>
                                            </label>
                                        </div>
                                    </fieldset>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white" for="{{ $dayKey }}_{{ $mealKey }}_start">Inizio {{ strtolower($mealLabel) }}</label>
                                            <input wire:model="value.weekly.{{ $dayKey }}.{{ $mealKey }}.start" type="time" id="{{ $dayKey }}_{{ $mealKey }}_start" class="form-control">
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white" for="{{ $dayKey }}_{{ $mealKey }}_end">Fine {{ strtolower($mealLabel) }}</label>
                                            <input wire:model="value.weekly.{{ $dayKey }}.{{ $mealKey }}.end" type="time" id="{{ $dayKey }}_{{ $mealKey }}_end" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6">
                    <div class="mb-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Chiusure speciali / sold out</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configura range di date in cui pranzo o cena non devono essere prenotabili.</p>
                    </div>

                    <div class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-[180px_180px_auto_auto_auto] md:items-end">
                        <div>
                            <label for="new_range_start" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Dal</label>
                            <input type="date" id="new_range_start" wire:model="new_range_start" class="form-control">
                        </div>
                        <div>
                            <label for="new_range_end" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Al</label>
                            <input type="date" id="new_range_end" wire:model="new_range_end" class="form-control">
                        </div>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                            <input wire:model="new_special_pranzo" type="checkbox" class="h-4 w-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Pranzo chiuso</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700">
                            <input wire:model="new_special_cena" type="checkbox" class="h-4 w-4">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Cena chiusa</span>
                        </label>
                        <button type="button" wire:click="addSpecialClosing" class="ti-btn ti-btn-primary-full">Aggiungi</button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($exceptions as $exception)
                            <div class="flex flex-col gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $exception->starts_on->format('d/m/Y') }} - {{ $exception->ends_on->format('d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        @if ($exception->payload['pranzo_closed'] ?? false) Pranzo chiuso @endif
                                        @if (($exception->payload['pranzo_closed'] ?? false) && ($exception->payload['cena_closed'] ?? false)) / @endif
                                        @if ($exception->payload['cena_closed'] ?? false) Cena chiusa @endif
                                    </div>
                                </div>
                                <button type="button" wire:click="removeSpecialClosing({{ $exception->id }})" class="ti-btn ti-btn-danger">Rimuovi</button>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nessuna chiusura speciale configurata.</p>
                        @endforelse
                    </div>
                </section>

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled" class="ti-btn ti-btn-primary-full">Salva orari</button>
                    <a href="{{ route('settings.index') }}" class="ti-btn ti-btn-light">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>
