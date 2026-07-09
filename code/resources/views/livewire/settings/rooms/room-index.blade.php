<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Gestione delle sale</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a href="{{ route('settings.rooms') }}" class="flex items-center text-textmuted">Sala</a>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex xl:my-auto right-content align-items-center gap-3 md:gap-5">
                <div class="pe-1 xl:mb-0">
                    <button wire:click="$dispatch('click-create-room')" type="button" class="ti-btn ti-btn-info-full text-white ti-btn-icon">
                        <i class="las text-3xl la-plus"></i>
                    </button>
                </div>
                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('settings.room-tables') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon">
                        <i class="las text-3xl la-table"></i>
                    </a>
                </div>
                <div class="pe-1 xl:mb-0">
                    <a href="/manage/settings/rooms/printqr" class="ti-btn ti-btn-info-full text-white ti-btn-icon">
                        <i class="las text-3xl la-print"></i>
                    </a>
                </div>
                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('settings.rooms') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                        <i class="las text-3xl la-redo-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger !mb-5" role="alert">{{ $errors->first() }}</div>
        @endif

        @if ($isVisible)
            <div class="box">
                <div class="box-header border-none">
                    <div>
                        <div class="box-title pb-0">ELENCO SALE</div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="pb-3">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cerca sala" class="form-control">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered whitespace-nowrap min-w-full">
                            <thead>
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <th class="border border-defaultborder dark:border-defaultborder/10 text-start">Azioni</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Nome</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Attivo</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Costo servizio</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Costo servizio %</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Ordinamento</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Capienza</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Fumatori</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($rooms as $room)
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td class="whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-5">
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <a href="{{ route('rooms.planner', ['room' => $room->id, 'mode' => 'view']) }}"
                                                       class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-info text-white hover:bg-info">
                                                        <i class="las text-3xl la-eye"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">Vedi</span>
                                                    </a>
                                                </div>
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-edit-room', { id: '{{ $room->id }}' })"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                        <i class="las text-3xl la-pen"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">Modifica</span>
                                                    </button>
                                                </div>
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-delete-room', { id: '{{ $room->id }}' })"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-danger text-white hover:bg-danger">
                                                        <i class="las text-3xl la-trash"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">Elimina</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $room->name }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            @if ($room->active)
                                                <span class="badge bg-success/10 !text-success">Si</span>
                                            @else
                                                <span class="badge bg-danger/10 !text-danger">No</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ number_format((float) $room->service_charge, 2, ',', '.') }} &euro;</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ number_format((float) $room->service_charge_percentage, 2, ',', '.') }} %</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $room->order }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $room->capacity }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            @if ($room->smoking_allowed)
                                                <span class="badge bg-success/10 !text-success">Si</span>
                                            @else
                                                <span class="badge bg-danger/10 !text-danger">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td colspan="8" class="text-center text-sm text-gray-500">Nessuna sala trovata.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $rooms->links() }}
                    </div>
                </div>
            </div>
        @endif

        <livewire:settings.rooms.room-show />
        <livewire:settings.rooms.room-create />
        <livewire:settings.rooms.room-edit />
        <livewire:settings.rooms.room-delete />
    </div>
</div>
