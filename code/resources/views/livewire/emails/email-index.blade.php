@section('title', 'Gestione Email')

<div class="content">
    <div class="py-3"></div>
    <div class="main-content">
        <h5 class="page-title">Gestione Email (Globale)</h5>

        <div>
            @if($isVisible)
                <button wire:click="$dispatch('click-create-email')" class="ti-btn ti-btn-info-full">
                    <i class="las la-plus"></i> Crea Email
                </button>

                <div class="box mt-5">
                    <div class="box-header border-none">
                        <div class="flex justify-between">
                            <div>
                                <div class="box-title pb-0">Elenco Email</div>
                                <p class="text-xs text-gray-500 font-normal">
                                    Hai {{ $emails->count() }} email.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered whitespace-nowrap min-w-full">
                                <thead>
                                    <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                        <th scope="col"
                                            class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                            Azioni
                                        </th>
                                        <th scope="col"
                                            class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                            Oggetto
                                        </th>
                                        <th scope="col"
                                            class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                            URL
                                        </th>
                                        <th scope="col"
                                            class="border border-defaultborder dark:border-defaultborder/10 text-start">
                                            Button Label
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($emails as $email)
                                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                                <a
                                                    href="/funnels/email/edit/{{$email->id}}"
                                                    class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm border border/10 bg-warning text-white hover:bg-warning disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                    <i class="las la-pen text-2xl"></i>
                                                </a>
                                                <button
                                                    wire:click="$dispatch('click-delete-email', { id: '{{ $email->id }}' })"
                                                    class="ti-btn ti-btn-icon hs-tooltip-toggle inline-flex items-center gap-x-2 text-sm font-semibold rounded-sm border border/10 bg-danger text-white hover:bg-danger disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                    <i class="las la-trash text-2xl"></i>
                                                </button>
                                            </td>
                                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                                {{ $email->subject }}
                                            </td>
                                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                                {{ $email->url }}
                                            </td>
                                            <td class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                                {{ $email->button_label }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="!border-defaultborder dark:!border-defaultborder/10">
                                            <td colspan="4" class="whitespace-nowrap text-sm text-gray-800 dark:text-gray-200">
                                                Nessuna email trovata.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <livewire:emails.email-create />
            {{-- <livewire:emails.email-edit /> --}}
            <livewire:emails.email-delete />
        </div>
    </div>
    
</div>
