<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Impostazioni</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <span class="flex items-center text-textmuted">Impostazioni</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="box mb-4">
            <div class="box-header border-none">
                <div>
                    <div class="box-title pb-0">Prenotazioni</div>
                    <p class="text-xs text-gray-500 font-normal">Configurazioni operative salvate sul profilo tenant.</p>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <th class="border border-defaultborder dark:border-defaultborder/10 text-start">Azioni</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">Nome</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.opening-hours') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>Orari</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.pax-capacity') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>Pax massimi per slot orario</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.max-sitting-time') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>Tempo massimo permanenza al tavolo (in minuti)</td>
                            </tr>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.notification-modes') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>Modalita di invio notifiche</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="box mb-4">
            <div class="box-header border-none">
                <div>
                    <div class="box-title pb-0">Automazioni</div>
                    <p class="text-xs text-gray-500 font-normal">Regole automatiche collegate alle prenotazioni.</p>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <thead>
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <th class="border border-defaultborder dark:border-defaultborder/10 text-start">Azioni</th>
                                <th class="border-b dark:border-defaultborder/10 text-start">Nome</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                <td class="whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('settings.booking-remind-hours') }}" class="ti-btn ti-btn-icon bg-warning text-white hover:bg-warning">
                                        <i class="las text-3xl la-pen"></i>
                                    </a>
                                </td>
                                <td>Quante ore prima ricordare al cliente della prenotazione?</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
