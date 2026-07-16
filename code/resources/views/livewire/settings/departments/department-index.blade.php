<div class="content">
    <div class="main-content">
        <div class="md:flex block items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Reparti</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a href="{{ route('settings.departments') }}" class="flex items-center text-textmuted">Reparti</a>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex xl:my-auto right-content align-items-center">
                <div class="pe-1 xl:mb-0">
                    <button wire:click="$dispatch('click-create-department')" type="button" class="ti-btn ti-btn-info-full text-white ti-btn-icon me-2 btn-b !mb-0">
                        <i class="las text-3xl la-plus"></i>
                    </button>
                </div>

                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('settings.departments') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
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
                        <div class="box-title pb-0">ELENCO REPARTI</div>
                    </div>
                </div>

                <div class="box-body">
                    <div class="pb-3">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cerca reparto" class="form-control">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered whitespace-nowrap min-w-full">
                            <thead>
                                <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                    <th class="border border-defaultborder dark:border-defaultborder/10 text-start">Azioni</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Nome reparto</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Reparto di produzione</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Usa stampante</th>
                                    <th class="border-b dark:border-defaultborder/10 text-start">Numero stampante</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($departments as $department)
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td class="whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-5">
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-edit-department', { id: '{{ $department->id }}' })"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-warning text-white hover:bg-warning">
                                                        <i class="las text-3xl la-pen"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                            Modifica
                                                        </span>
                                                    </button>
                                                </div>
                                                <div class="hs-tooltip ti-main-tooltip">
                                                    <button wire:click="$dispatch('click-delete-department', { id: '{{ $department->id }}' })"
                                                            class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm bg-danger text-white hover:bg-danger">
                                                        <i class="las text-3xl la-trash"></i>
                                                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                                                            Elimina
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">{{ $department->name }}</td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            @if ($department->production)
                                                <span class="badge bg-success/10 !text-success">SI</span>
                                            @else
                                                <span class="badge bg-danger/10 !text-danger">NO</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            @if ($department->use_printer)
                                                <span class="badge bg-success/10 !text-success">SI</span>
                                            @else
                                                <span class="badge bg-danger/10 !text-danger">NO</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                            {{ $department->printer_number ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <td colspan="5" class="text-center text-sm text-gray-500">Nessun reparto trovato.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $departments->links() }}
                    </div>
                </div>
            </div>
        @endif

        <livewire:settings.departments.department-create />
        <livewire:settings.departments.department-edit />
        <livewire:settings.departments.department-delete />
    </div>
</div>
