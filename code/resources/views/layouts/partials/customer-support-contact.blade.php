<section class="section text-defaulttextcolor dark:text-defaulttextcolor/70 bg-[#f9fafb] section-bg" id="contact">
    <div class="container">
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12">
                <p class="text-[0.75rem] font-semibold text-success mb-1 text-center">
                    <span class="landing-section-heading">Assistenza</span>
                </p>
                <div class="landing-title"></div>

                <h3 class="font-semibold mb-2 text-center">Serve aiuto? Contattaci</h3>

                <div class="max-w-3xl mx-auto">
                    <p class="text-textmuted fs-15 mb-4 font-normal text-center">
                        Questo è un canale di supporto generico per chi utilizza Risto Pilot.
                    </p>
                    <p class="text-textmuted fs-15 mb-4 font-normal text-center">
                        Puoi scriverci per: problemi di accesso, chiarimenti sul profilo, fidelity card e punti, segnalazioni tecniche o richieste generiche.
                    </p>
                    <p class="text-textmuted fs-15 mb-5 font-normal text-center">
                        Compila il form qui sotto: ti rispondiamo il prima possibile.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 items-start">
            {{-- Form (Livewire) --}}
            <div class="col-span-12 lg:col-span-8">
                <livewire:contacts />
            </div>

            {{-- Box a destra --}}
            <div class="col-span-12 lg:col-span-4">
                <div class="card bg-white dark:bg-bodybg !shadow-none rounded-sm">
                    <div class="box-body px-[3rem] py-[1.5rem]">
                        <div class="flex mb-3">
                            <div class="contact-icon contact-icon-3 border !border-success bg-success/10">
                                <i class="fe fe-headphones !text-success text-[1.0625rem]"></i>
                            </div>
                        </div>

                        <div class="flex mb-2">
                            <img src="https://ristopilot.com/assets/images/media/ristopilot-home-razzo.png" alt="Assistenza Risto Pilot" />
                        </div>

                        <p class="text-sm text-gray-600 mt-4">
                            Per aiutarci a rispondere più velocemente, indica nel messaggio cosa stai cercando di fare e cosa non funziona.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section text-defaulttextcolor dark:text-defaulttextcolor/70 bg-white dark:bg-bodybg">
    <div class="container">
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12">
                <p class="text-[0.75rem] font-semibold text-success mb-1 text-center">
                    <span class="landing-section-heading">Come funziona</span>
                </p>
                <div class="landing-title"></div>
                <h3 class="font-semibold mb-2 text-center">Supporto in 4 step</h3>

                <ul class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-12">
                    @php
                        $steps = [
                            ['n' => 1, 't' => 'Scrivi la richiesta', 'd' => 'Compila il form con i tuoi dati e descrivi in modo chiaro il problema o la richiesta.'],
                            ['n' => 2, 't' => 'Prendiamo in carico', 'd' => 'Verifichiamo le informazioni e, se serve, ti chiediamo un dettaglio aggiuntivo.'],
                            ['n' => 3, 't' => 'Ti rispondiamo', 'd' => 'Ricevi una risposta sul contatto indicato con le indicazioni utili.'],
                            ['n' => 4, 't' => 'Chiudiamo la richiesta', 'd' => 'Confermiamo la soluzione o ti guidiamo ai prossimi passi.'],
                        ];
                    @endphp

                    @foreach($steps as $s)
                        <li class="bg-gray-100 p-5 pb-10 text-center rounded-sm">
                            <div class="flex flex-col items-center">
                                <div class="flex-shrink-0 relative -mt-16">
                                    <div class="flex items-center justify-center h-20 w-20 rounded-full bg-green-500 text-white border-4 border-white text-xl font-semibold">
                                        {{ $s['n'] }}
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h4 class="text-lg leading-6 font-semibold text-gray-900">{{ $s['t'] }}</h4>
                                    <p class="mt-2 text-base leading-6 text-gray-500">{{ $s['d'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

            </div>
        </div>
    </div>
</section>