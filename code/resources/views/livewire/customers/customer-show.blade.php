<div class="content">
    <div class="main-content">
        <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title text-[1.3125rem] font-medium text-defaulttextcolor mb-0">Clienti</h5>
                <nav>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="/">
                                Home
                                <i class="ti ti-chevrons-right flex-shrink-0 mx-3 overflow-visible text-textmuted rtl:rotate-180"></i>
                            </a>
                        </li>
                        <li class="text-[12px]">
                            <a class="flex items-center text-primary hover:text-primary" href="{{ route('customers.index') }}">Clienti</a>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="flex xl:my-auto right-content align-items-center gap-3 md:gap-5">
                <div class="pe-1 xl:mb-0">
                    <a href="{{ route('customers.edit', $customer->id) }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon">
                        <i class="las text-3xl la-pen"></i>
                    </a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success !mb-5" role="alert">{{ session('success') }}</div>
        @endif

        <div class="box">
            <div class="box-header border-none">
                <div class="box-title pb-0">SCHEDA {{ $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname) }}</div>
            </div>
            <div class="box-body">
                <a href="{{ route('customers.index') }}" class="ti-btn ti-btn-light w-full h-12">Indietro</a>
                <div class="my-5"></div>

                <div class="table-responsive">
                    <table class="table table-bordered whitespace-nowrap min-w-full">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <th class="text-start">ID</th>
                                <td>{{ $customer->id }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Nome</th>
                                <td>{{ $customer->firstname ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Cognome</th>
                                <td>{{ $customer->lastname ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Telefono</th>
                                <td>{{ $customer->phone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Email</th>
                                <td>{{ $customer->email ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Regione</th>
                                <td>{{ $customer->region?->name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Provincia</th>
                                <td>{{ $customer->province?->name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Comune</th>
                                <td>{{ $customer->comune?->name ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Data di nascita</th>
                                <td>{{ $customer->birthdate?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Marketing</th>
                                <td>
                                    @if ($customer->consent_marketing)
                                        <span class="badge bg-success/10 !text-success">SI</span>
                                    @else
                                        <span class="badge bg-danger/10 !text-danger">NO</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-start">Telegram</th>
                                <td>
                                    @if ($customer->telegramid)
                                        <span class="badge bg-success/10 !text-success">SI</span>
                                    @else
                                        <span class="badge bg-danger/10 !text-danger">NO</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-start">Blacklist</th>
                                <td>
                                    @if ($customer->blacklisted)
                                        <span class="badge bg-danger/10 !text-danger">SI</span>
                                    @else
                                        <span class="badge bg-success/10 !text-success">NO</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-start">Ultima visita</th>
                                <td>{{ $customer->last_action_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Sorgente</th>
                                <td>{{ $customer->registration_source ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-start">Note</th>
                                <td class="whitespace-normal">{{ $customer->note ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
