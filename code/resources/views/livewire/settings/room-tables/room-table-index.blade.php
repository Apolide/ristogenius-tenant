<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Tavoli</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="{{ route('settings.rooms') }}">
                                Sala
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-textmuted" href="javascript:void(0);">Tavoli</a>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex xl:my-auto right-content align-items-center gap-3 md:gap-5">
                <div class="pe-1 xl:mb-0">
                    <button wire:click="$dispatch('click-create-room-table')" type="button" class="ti-btn ti-btn-info-full text-white ti-btn-icon">
                        <i class="las text-3xl la-plus"></i>
                    </button>
                </div>
                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('settings.room-tables') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
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
                <div class="box-body">
                    <div class="pb-3">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cerca tavolo o sala" class="form-control">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered whitespace-nowrap min-w-full">
                            <thead>
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <th class="border border-defaultborder dark:border-defaultborder/10 text-start">Azioni</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Nome o numero tavolo</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Tipo</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Sala</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Min Persone</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Max Persone</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($roomTables as $roomTable)
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td class="whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-5">
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-edit-room-table', { id: '{{ $roomTable->id }}' })"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                        <i class="las text-3xl la-pen"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                            Modifica
                                                        </span>
                                                    </button>
                                                </div>
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-delete-room-table', { id: '{{ $roomTable->id }}' })"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-danger text-white hover:bg-danger">
                                                        <i class="las text-3xl la-trash"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                            Elimina
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $roomTable->name }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $roomTable->type }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $roomTable->room?->name }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $roomTable->min_people }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $roomTable->max_people }}</td>
                                    </tr>
                                @empty
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td colspan="6" class="text-center text-sm text-gray-500">Nessun tavolo trovato.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $roomTables->links() }}
                    </div>
                </div>
            </div>
        @endif

        <livewire:settings.room-tables.room-table-create />
        <livewire:settings.room-tables.room-table-edit />
        <livewire:settings.room-tables.room-table-delete />
    </div>
</div>
