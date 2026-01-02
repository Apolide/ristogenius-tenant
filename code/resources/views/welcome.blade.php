@extends('layouts.landing')

@section('content')

@include('layouts.sidebar.landing')

<div class="main-content !p-0 landing-main dark:text-defaulttextcolor/70">

    <style>
        body {
            font-size: 1.1rem;
        }

        h6 {
            font-size: 1.2rem;
        }

        [data-nav-layout=horizontal] .landing-body .landing-banner {
            background-image: url('{{asset('assets/images/media/landing/banner-landing-home.jpg')}}');
        }

        [data-nav-layout=horizontal] .landing-body .featur-icon {
            width: 125px;
            height: 125px;
        }
    </style>


    <!-- Start::Home Content -->
    <div class="landing-banner" id="home">
        <section class="section !pt-[6rem]">
            <div class="container !pt-[5rem]">
                <div class="flex justify-center">

                    <div>
                        <div class="py-4 mt-28 pb-4 text-center">
                            <div class="mb-3 flex justify-center">
                                {{-- <h1 class="font-semibold text-fixed-white op-9">Risto Pilot</h1> --}}
                                <a href="https://ristopilot.com"><img src="/assets/images/brand-logos/desktop-white-home.png" /></a>
                            </div>
                        </div>
                        <div class="py-4 pb-4 text-center">
                            <p class="landing-banner-heading mb-3 cursor-default">La suite dei locali vincenti.</p>
                            {{-- <div class="fs-16 mb-5 text-fixed-white op-7">Con Risto Pilot elimini la complessità nella gestione del ristorante. Automatizzi gli ordini ai fornitori, ottimizzi la gestione della spesa,
                                coinvolgi i tuoi clienti con comunicazioni mirate e raccogli più prenotazioni online. Tutto con un unico strumento accessibile da qualsiasi device.</div> --}}
                            {{-- <div class="fs-16 mb-5 text-fixed-white op-7">Tutto ciò che ti serve, su ogni dispositivo.</div> --}}

                            <!-- <a href="/" class="m-1 ti-btn ti-btn-primary-full">
                            Scopri di più
                            <i class="fe fe-eye ms-2 align-middle"></i>
                        </a>
                        <a href="/" class="m-1 ti-btn ti-btn-info-full">
                            Inizia ora
                            <i class="fe fe-arrow-right rtl:rotate-180 ms-2 rtl:ms-0 align-middle"></i>
                        </a> -->
                        </div>
                        <div class="py-14 pb-4 text-center">

                            {{-- class="bg-cyan-500 hover:bg-cyan-400 p-6 rounded-md text-white py-6 text-2xl"
                            rounded-md ti-btn-teal ti-btn-border-down border-0 p-6 py-6 me-[0.375rem]
                            ti-btn-success border-2 border-white p-6 rounded-md !text-white py-6 text-2xl
                            --}}

                            <a href="#features" class="ti-btn-success border-2 border-white p-6 rounded-md !text-white py-6 text-2xl">
                                Scopri di più

                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    {{-- <section id="video" class="section section-bg">
        <div class="container text-center">
            <h3 class="font-semibold mb-4">Guarda il nostro video</h3>
            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background-color: #000;">
                <iframe src="https://www.youtube.com/embed/P0aeDrB2T-k" title="Video Risto Pilot" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen
                        style="position: absolute; top:0; left:0; width:100%; height:100%;">
                </iframe>
            </div>
            <p class="text-textmuted fs-15 mt-3">Dai un'occhiata a come Risto Pilot può rivoluzionare la gestione del tuo ristorante.</p>
        </div>
    </section> --}}
    <section id="video" class="section section-bg">
        <div class="container text-center">
            <h2 class="font-semibold mb-4">App Gestione Ristorante</h2>
            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background-color: #000;">
                <iframe
                        src="https://www.youtube.com/embed/videoseries?list=PLkdD53R2TKpv4AaUkRQdyZXbbVTxyKkBS"
                        title="Playlist Risto Pilot"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        style="position: absolute; top:0; left:0; width:100%; height:100%;">
                </iframe>
            </div>
            <p class="text-textmuted fs-15 mt-3">
                <a href="https://www.youtube.com/playlist?list=PLkdD53R2TKpv4AaUkRQdyZXbbVTxyKkBS">Guarda la playlist completa</a>
            </p>
        </div>
    </section>

    <section class="section text-defaulttextcolor dark:text-defaulttextcolor/70  section-bg " id="features">
        <div class="container text-center position-relative">
            <p class="text-[0.75rem] font-semibold text-success mb-1">
                <span class="landing-section-heading">Caratteristiche</span>
            </p>
            <div class="landing-title"></div>
            <h3 class="font-semibold mb-2">Caratteristiche principali</h3>
            <div class="row justify-content-center">
                <div class="col-xl-7">
                    <p class="text-textmuted fs-15 mb-5 font-normal">
                        Integra tutti gli strumenti di cui hai bisogno per semplificare il lavoro quotidiano, ridurre gli sprechi, fidelizzare i clienti e aumentare le prenotazioni.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-x-6 justify-center">
                <div class="xl:col-span-12 col-span-12">
                    <div class="grid grid-cols-12 gap-x-6 justify-evenly">

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/assistente-ai-gestione-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <!-- Icona SVG per "Assistente AI" -->
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <!-- Cerchio principale -->
                                            <circle cx="64" cy="64" r="63.5" fill="#9bf63c"></circle>
                                            <!-- Forma di base per richiamare l’idea di IA/Chat -->
                                            <path fill="#8E44AD" d="M20 38h88v36H80l-8 14 3-10H20z"></path>
                                            <!-- Dettagli bianchi a contrasto, per richiamare "cloud" o "help" -->
                                            <path fill="#FFF" d="M35 48h58v16H70l-4 7 1-5H35z"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Assistente AI
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Sfrutta l’intelligenza artificiale per suggerimenti proattivi, automatizzare risposte e ottimizzare i processi.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/gestione-clienti-e-marketing-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" class="inline-flex" height="50" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 128 128" viewBox="0 0 128 128">
                                            <circle cx="64" cy="64" r="63.5" fill="#54C0EB"></circle>
                                            <path fill="#84DBFF" d="M19.2,109c11.5,11.4,27.3,18.5,44.8,18.5c17.5,0,33.3-7.1,44.8-18.5H19.2z">
                                            </path>
                                            <rect width="19.6" height="10.4" x="54.2" y="92.7" fill="#FFF"></rect>
                                            <rect width="19.6" height="2.3" x="54.2" y="92.7" fill="#E6E9EE"></rect>
                                            <path fill="#E6E9EE" d="M82.2,109H45.8l0,0c0-3.3,2.7-6,6-6h24.4C79.5,103.1,82.2,105.7,82.2,109L82.2,109z">
                                            </path>
                                            <path fill="#324A5E" d="M103,92.7H25c-2.4,0-4.4-2-4.4-4.4V34.7c0-2.4,2-4.4,4.4-4.4h78c2.4,0,4.4,2,4.4,4.4v53.7   C107.4,90.7,105.4,92.7,103,92.7z">
                                            </path>
                                            <path fill="#FFF" d="M20.6,84v4.4c0,2.4,1.9,4.3,4.3,4.3H103c2.4,0,4.3-1.9,4.3-4.3V84H20.6z">
                                            </path>
                                            <rect width="80.3" height="46.9" x="23.9" y="33.4" fill="#FFF"></rect>
                                            <circle cx="100.3" cy="88.3" r="2" fill="#FF7058"></circle>
                                            <circle cx="94.7" cy="88.3" r="2" fill="#4CDBC4"></circle>
                                            <circle cx="89.1" cy="88.3" r="2" fill="#54C0EB"></circle>
                                            <rect width="9.7" height="27.7" x="32.3" y="46.7" fill="#ACB3BA"></rect>
                                            <rect width="9.7" height="15.8" x="45.7" y="58.7" fill="#4CDBC4"></rect>
                                            <rect width="9.7" height="23.1" x="59.1" y="51.3" fill="#FFD05B"></rect>
                                            <rect width="9.7" height="35.7" x="72.6" y="38.7" fill="#84DBFF"></rect>
                                            <rect width="9.7" height="8.1" x="86" y="66.3" fill="#FF7058"></rect>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Gestione Clienti & Marketing
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Crea comunicazioni mirate, invia coupon e fidelizza i clienti in base alle lore recensioni e le loro preferenze.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>


                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/gestione-prenotazioni-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" class="inline-flex" height="50" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 128 128" viewBox="0 0 128 128">
                                            <circle cx="64" cy="64" r="63.5" fill="#54C0EB"></circle>
                                            <path fill="#FFF" d="M42.2,96H23.6c-1.6,0-2.8-1.3-2.8-2.8V34.8c0-1.6,1.3-2.8,2.8-2.8h18.6c1.6,0,2.8,1.3,2.8,2.8v58.3   C45.1,94.7,43.8,96,42.2,96z">
                                            </path>
                                            <rect width="18.7" height="36.8" x="23.6" y="35.8" fill="#4CDBC4">
                                            </rect>
                                            <circle cx="32.9" cy="83.9" r="7.2" fill="#E6E9EE"></circle>
                                            <circle cx="32.9" cy="83.9" r="5" fill="#324A5E"></circle>
                                            <path fill="#FFF" d="M68.8,96H50.2c-1.6,0-2.8-1.3-2.8-2.8V34.8c0-1.6,1.3-2.8,2.8-2.8h18.6c1.6,0,2.8,1.3,2.8,2.8v58.3   C71.6,94.7,70.4,96,68.8,96z">
                                            </path>
                                            <rect width="18.7" height="36.8" x="50.1" y="35.8" fill="#FF7058">
                                            </rect>
                                            <circle cx="59.5" cy="83.9" r="7.2" fill="#E6E9EE"></circle>
                                            <circle cx="59.5" cy="83.9" r="5" fill="#324A5E"></circle>
                                            <path fill="#FFF" d="M109,92.7l-18,4.6c-1.5,0.4-3.1-0.5-3.5-2.1L73.2,38.7c-0.4-1.5,0.5-3.1,2.1-3.5l18-4.6   c1.5-0.4,3.1,0.5,3.5,2.1l14.3,56.5C111.5,90.8,110.6,92.4,109,92.7z">
                                            </path>
                                            <rect width="18.7" height="36.8" x="80.4" y="36.1" fill="#FFD05B" transform="rotate(-14.193 89.778 54.551)"></rect>
                                            <circle cx="97" cy="83.2" r="7.2" fill="#E6E9EE"></circle>
                                            <circle cx="97" cy="83.2" r="5" fill="#324A5E"></circle>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Gestione Prenotazioni
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Crea form di prenotazione per i tuoi clienti, integrali sul tuo sito e nelle ads per incrementare le tue prenotazioni.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/gestione-tavoli-sale-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" class="inline-flex" height="50" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 128 128" viewBox="0 0 128 128">
                                            <circle cx="64" cy="64" r="63.5" fill="#FFD05B"></circle>
                                            <path fill="#FFF" d="M30,103.8l0-79.7c0-1.8,1.5-3.3,3.3-3.3h50.1l0,11.4c0,1.8,1.5,3.3,3.3,3.3H98l0,68.3   c0,1.8-1.5,3.3-3.3,3.3H33.3C31.5,107.1,30,105.6,30,103.8z">
                                            </path>
                                            <path fill="#E6E9EE" d="M83.3,20.9h11.4c1.8,0,3.3,1.5,3.3,3.3l0,11.4H86.6c-1.8,0-3.3-1.5-3.3-3.3L83.3,20.9z">
                                            </path>
                                            <path fill="#CED5E0" d="M83.3,20.9h11.4c1.8,0,3.3,1.5,3.3,3.3l0,11.4L83.3,20.9z"></path>
                                            <rect width="54.6" height="2.4" x="36.7" y="50.7" fill="#E6E9EE"></rect>
                                            <rect width="54.6" height="2.4" x="36.7" y="58.2" fill="#E6E9EE"></rect>
                                            <rect width="54.6" height="2.4" x="36.7" y="65.8" fill="#E6E9EE"></rect>
                                            <rect width="54.6" height="2.4" x="36.7" y="73.4" fill="#E6E9EE"></rect>
                                            <rect width="23.5" height="2.4" x="67.8" y="80.9" fill="#84DBFF"></rect>
                                            <rect width="23.5" height="2.4" x="67.8" y="88.5" fill="#84DBFF"></rect>
                                            <rect width="54.6" height="2.4" x="36.7" y="43.1" fill="#E6E9EE"></rect>
                                            <rect width="29.6" height="2.4" x="36.7" y="35.6" fill="#84DBFF"></rect>
                                            <path fill="#FF7058"
                                                  d="M41.1,83.3c-4.4,4.4-4.4,11.5,0,15.9s11.5,4.4,15.9,0c4.4-4.4,4.4-11.5,0-15.9   C52.6,78.9,45.5,78.9,41.1,83.3z M41.9,84.1c3.4-3.4,8.7-3.8,12.6-1.3l-1.6,1.6c-3-1.7-6.9-1.3-9.5,1.2c-2.6,2.6-3,6.5-1.2,9.5   l-1.6,1.6C38.1,92.8,38.5,87.5,41.9,84.1z M43.1,94.3c-1.3-2.5-0.9-5.7,1.2-7.7c2.1-2.1,5.2-2.5,7.7-1.2L43.1,94.3z M54.9,88.2   c1.3,2.5,0.9,5.7-1.2,7.7c-2.1,2.1-5.2,2.5-7.7,1.2L54.9,88.2z M56.1,98.3c-3.4,3.4-8.7,3.8-12.6,1.3l1.6-1.6   c3,1.7,6.9,1.3,9.5-1.2c2.6-2.6,3-6.5,1.2-9.5l1.6-1.6C60,89.6,59.5,94.9,56.1,98.3z">
                                            </path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Gestione Tavoli Intuitiva
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Configura sale e tavoli con facilità, ottimizzando lo spazio e migliorando l’organizzazione del servizio.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/lista-spesa-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" class="inline-flex" height="50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">
                                            <circle cx="64" cy="64" r="64" fill="#42A3DB"></circle>
                                            <path fill="#347CBE" d="M85.5 26.6 66.1 61 33.3 98.6 62.6 128H64c33.7 0 61.3-26 63.8-59.1L85.5 26.6z">
                                            </path>
                                            <path fill="#CD2F30" d="M73.1 57.7h-16c3.6 18.7 11.1 36.6 22.1 52.5.3-5 1-9.8 1.8-14.5 4.6 1.3 9.2 2.3 13.7 3-9.7-12.2-17-26.1-21.6-41z">
                                            </path>
                                            <path fill="#F04D45" d="M54.9 57.7c-4.6 15-11.9 28.9-21.6 40.9 4.5-.7 9.1-1.7 13.7-3 .9 4.7 1.5 9.5 1.8 14.5 11-15.9 18.4-33.8 22.1-52.5h-16z">
                                            </path>
                                            <path fill="#FFF"
                                                  d="M93.5 52c1.8-1.8 1.8-4.7 0-6.5-1.3-1.3-1.7-3.3-1-5 1-2.4-.1-5-2.5-6-1.7-.7-2.8-2.4-2.8-4.3 0-2.5-2.1-4.6-4.6-4.6-1.9 0-3.5-1.1-4.3-2.8-1-2.4-3.7-3.5-6-2.5-1.7.7-3.7.3-5-1-1.8-1.8-4.7-1.8-6.5 0-1.3 1.3-3.3 1.7-5 1-2.4-1-5 .1-6 2.5-.7 1.7-2.4 2.8-4.3 2.8-2.5 0-4.6 2.1-4.6 4.6 0 1.9-1.1 3.5-2.8 4.3-2.4 1-3.5 3.7-2.5 6 .7 1.7.3 3.7-1 5-1.8 1.8-1.8 4.7 0 6.5 1.3 1.3 1.7 3.3 1 5-1 2.4.1 5 2.5 6 1.7.7 2.8 2.4 2.8 4.3 0 2.5 2.1 4.6 4.6 4.6 1.9 0 3.5 1.1 4.3 2.8 1 2.4 3.7 3.5 6 2.5 1.7-.7 3.7-.3 5 1 1.8 1.8 4.7 1.8 6.5 0 1.3-1.3 3.3-1.7 5-1 2.4 1 5-.1 6-2.5.7-1.7 2.4-2.8 4.3-2.8 2.5 0 4.6-2.1 4.6-4.6 0-1.9 1.1-3.5 2.8-4.3 2.4-1 3.5-3.7 2.5-6-.7-1.7-.3-3.7 1-5z">
                                            </path>
                                            <path fill="#FFCD0A" d="M64 70.8c-12.2 0-22.1-9.9-22.1-22.1 0-12.2 9.9-22.1 22.1-22.1 12.2 0 22.1 9.9 22.1 22.1 0 12.2-9.9 22.1-22.1 22.1z">
                                            </path>
                                            <path fill="#FFF" d="M59.9 61c-.6 0-1.1-.2-1.5-.7l-8.3-9.2c-.7-.8-.7-2.1.1-2.8.8-.7 2.1-.7 2.8.1l6.7 7.5 15.1-18.8c.7-.9 2-1 2.8-.3.9.7 1 2 .3 2.8L61.4 60.2c-.3.5-.9.8-1.5.8z">
                                            </path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Lista Spesa Intelligente
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Programma ordini automatici ai fornitori, confronta i prezzi e ottimizza gli acquisti senza sprechi.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/calendario-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="64" cy="64" r="63.5" fill="#4CDBC4"></circle>
                                            <!-- Sagoma di un carrello/clipboard -->
                                            <path fill="#3BB39B" d="M30 40h68v38H30z"></path>
                                            <!-- Dettagli bianchi -->
                                            <path fill="#FFF" d="M38 46h52v26H38z"></path>
                                            <!-- Icona check di conferma -->
                                            <path fill="#FF7058" d="M48 56h8v2h-8zM48 60h20v2H48zM72 56h8v2h-8z"></path>
                                            <path fill="#54C0EB" d="M52 66l4 4 12-12-2-2-10 10-2-2-2 2z"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Calendario
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Organizza i tuoi impegni e ricevi reminder su WhatsApp: non perderai più un evento o una scadenza.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/timbrature-personale-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="64" cy="64" r="63.5" fill="#FF7058"></circle>
                                            <!-- Sagoma di un orologio -->
                                            <circle cx="64" cy="64" r="35" fill="#FFF"></circle>
                                            <!-- Lancette -->
                                            <path fill="#FF7058" d="M64 35h4v28h-4z"></path>
                                            <path fill="#FF7058" d="M64 63l14 14-2.8 2.8-14-14z"></path>
                                            <!-- Dettagli orologio -->
                                            <circle cx="64" cy="64" r="3" fill="#324A5E"></circle>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Timbrature & Dipendenti
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Registra digitalmente le presenze del personale, calcola i costi orari e genera report accurati.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>


                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/agenda-turni-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <!-- Icona SVG per "Agenda Turni" -->
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="64" cy="64" r="63.5" fill="#9aa9ad"></circle>
                                            <!-- Layout di un calendario -->
                                            <rect x="30" y="40" width="68" height="48" fill="#FFF"></rect>
                                            <rect x="30" y="40" width="68" height="10" fill="#FF7058"></rect>
                                            <!-- Alcuni quadratini come giorni -->
                                            <rect x="38" y="54" width="10" height="10" fill="#FFD05B"></rect>
                                            <rect x="52" y="54" width="10" height="10" fill="#FFD05B"></rect>
                                            <rect x="66" y="54" width="10" height="10" fill="#FFD05B"></rect>
                                            <rect x="80" y="54" width="10" height="10" fill="#FFD05B"></rect>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Agenda Turni
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Pianifica i turni in modo flessibile, gestisci assegnazioni e sostituzioni in un’unica dashboard.
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/comande-e-cassa-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <!-- Icona SVG per "Agenda Turni" -->
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <!-- Sfondo circolare -->
                                            <circle cx="64" cy="64" r="63.5" fill="#2b7bd2"></circle>

                                            <!-- Blocco-notes -->
                                            <rect x="34" y="36" width="60" height="56" rx="4" fill="#FFFFFF"></rect>
                                            <!-- Barra intestazione del blocco-notes -->
                                            <rect x="34" y="36" width="60" height="10" fill="#8CC152"></rect>

                                            <!-- Righe delle comande -->
                                            <rect x="42" y="54" width="32" height="6" fill="#8CC152"></rect>
                                            <rect x="42" y="68" width="32" height="6" fill="#8CC152"></rect>
                                            <rect x="42" y="82" width="32" height="6" fill="#8CC152"></rect>

                                            <!-- Spunte di stato -->
                                            <path d="M83 56 l5 5 l10 -10" stroke="#FF7058" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M83 70 l5 5 l10 -10" stroke="#FF7058" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M83 84 l5 5 l10 -10" stroke="#FF7058" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Comande e Cassa
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Crea le comande ai tavoli, controlla lo stato deile singole comande e verifica in cassa cosa si è consumato
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>


                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/ordini-dai-clienti"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <!-- Icona SVG per "Agenda Turni" -->
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <!-- Sfondo circolare -->
                                            <circle cx="64" cy="64" r="63.5" fill="#E55E9C"></circle>

                                            <!-- Smartphone -->
                                            <rect x="44" y="30" width="40" height="68" rx="6" fill="#FFFFFF"></rect>
                                            <!-- Altoparlante superiore -->
                                            <rect x="58" y="34" width="12" height="2" fill="#B0B0B0"></rect>

                                            <!-- QR code (tre quadrati guida + pattern) -->
                                            <!-- Quadrati guida -->
                                            <rect x="50" y="36" width="10" height="10" fill="#FF7058"></rect>
                                            <rect x="68" y="36" width="10" height="10" fill="#FF7058"></rect>
                                            <rect x="50" y="64" width="10" height="10" fill="#FF7058"></rect>
                                            <!-- Piccoli moduli -->
                                            <rect x="60" y="48" width="6" height="6" fill="#FFD05B"></rect>
                                            <rect x="74" y="52" width="6" height="6" fill="#FFD05B"></rect>
                                            <rect x="60" y="70" width="6" height="6" fill="#FFD05B"></rect>

                                            <!-- Tasto home -->
                                            <circle cx="64" cy="92" r="4" fill="#B0B0B0"></circle>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Ordini dai Clienti
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Il cliente ordina in autonomia dal tavolo, usando i menu digitali associati, e invia la comanda ai reparti di produzione
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>


                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/kds"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                            wow fadeInUp reveal revealleft p-6 active
                                            transition-all duration-300 ease-out
                                            hover:-translate-y-1 hover:shadow-xl
                                            hover:bg-gray-50 dark:hover:bg-bodybg/80
                                            cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <!-- Icona SVG per "Agenda Turni" -->
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <!-- Cerchio di sfondo -->
                                            <circle cx="64" cy="64" r="63.5" fill="#F19C65"></circle>

                                            <!-- Monitor -->
                                            <rect x="28" y="36" width="72" height="48" rx="4" fill="#FFFFFF"></rect>
                                            <!-- Barra superiore del monitor -->
                                            <rect x="28" y="36" width="72" height="10" fill="#FF7058"></rect>

                                            <!-- Cartellini ordine (ticket) -->
                                            <rect x="36" y="50" width="16" height="20" fill="#FFD05B"></rect>
                                            <rect x="56" y="50" width="16" height="20" fill="#FFD05B"></rect>
                                            <rect x="76" y="50" width="16" height="20" fill="#FFD05B"></rect>

                                            <!-- Supporto del monitor -->
                                            <rect x="60" y="84" width="8" height="10" fill="#B0B0B0"></rect>
                                            <rect x="48" y="94" width="32" height="6" rx="3" fill="#B0B0B0"></rect>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            KDS
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Ogni area di produzione riceve le uscite in tempo reale dalle comande dei camerieri o dagli ordini dei clienti
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>


                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/centralino-telefonico-prenotazioni-ristorante"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                                wow fadeInUp reveal revealleft p-6 active
                                                transition-all duration-300 ease-out
                                                hover:-translate-y-1 hover:shadow-xl
                                                hover:bg-gray-50 dark:hover:bg-bodybg/80
                                                cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="64" cy="64" r="63.5" fill="#67d3b3"></circle>

                                            <rect x="36" y="56" width="56" height="28" rx="4" fill="#FFFFFF"></rect>
                                            <rect x="36" y="46" width="56" height="8" rx="2" fill="#FF7058"></rect>

                                            <rect x="44" y="60" width="8" height="8" fill="#FFD05B"></rect>
                                            <rect x="56" y="60" width="8" height="8" fill="#FFD05B"></rect>
                                            <rect x="68" y="60" width="8" height="8" fill="#FFD05B"></rect>
                                            <rect x="44" y="70" width="8" height="8" fill="#FFD05B"></rect>
                                            <rect x="56" y="70" width="8" height="8" fill="#FFD05B"></rect>
                                            <rect x="68" y="70" width="8" height="8" fill="#FFD05B"></rect>

                                            <path d="M92 64h14" stroke="#FF7058" stroke-width="4" fill="none" stroke-linecap="round"></path>
                                            <path d="M100 58l6 6-6 6" stroke="#FF7058" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Centralino Telefonico
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Con l'IVR non perdi nessuna chiamata e indirizzi i clienti ai form di prenotazione o al telefono
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>


                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/takeaway"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                                wow fadeInUp reveal revealleft p-6 active
                                                transition-all duration-300 ease-out
                                                hover:-translate-y-1 hover:shadow-xl
                                                hover:bg-gray-50 dark:hover:bg-bodybg/80
                                                cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-label="Takeaway e Consegna" role="img">
                                            <!-- Sfondo -->
                                            <circle cx="64" cy="64" r="63.5" fill="#77d436"></circle>

                                            <!-- Borsa Takeaway -->
                                            <rect x="24" y="50" width="40" height="44" rx="6" fill="#FFFFFF"></rect>
                                            <path d="M34 50c0-8 6-14 14-14s14 6 14 14" fill="none" stroke="#FF7058" stroke-width="4" stroke-linecap="round"></path>
                                            <rect x="24" y="58" width="40" height="8" rx="2" fill="#FF7058"></rect>
                                            <circle cx="36" cy="62" r="2" fill="#FFFFFF"></circle>
                                            <circle cx="52" cy="62" r="2" fill="#FFFFFF"></circle>

                                            <!-- Carta di pagamento -->
                                            <rect x="30" y="72" width="28" height="16" rx="3" fill="#FFD05B"></rect>
                                            <rect x="30" y="76" width="28" height="3" rx="1.5" fill="#FFFFFF" opacity="0.9"></rect>
                                            <rect x="34" y="82" width="10" height="3" rx="1.5" fill="#FFFFFF" opacity="0.9"></rect>

                                            <!-- Scooter consegna -->
                                            <path d="M70 78h16c6 0 10-4 12-10l3-10h-14" fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M87 58h16" fill="none" stroke="#FF7058" stroke-width="5" stroke-linecap="round"></path>
                                            <path d="M101 58l5 10" fill="none" stroke="#FF7058" stroke-width="5" stroke-linecap="round"></path>

                                            <!-- Sella / corpo -->
                                            <path d="M70 78c2-10 10-18 22-18" fill="none" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round"></path>
                                            <path d="M80 70h10" fill="none" stroke="#FFD05B" stroke-width="5" stroke-linecap="round"></path>

                                            <!-- Ruote -->
                                            <circle cx="76" cy="88" r="7" fill="#FFFFFF"></circle>
                                            <circle cx="76" cy="88" r="3" fill="#6772E5"></circle>

                                            <circle cx="104" cy="88" r="7" fill="#FFFFFF"></circle>
                                            <circle cx="104" cy="88" r="3" fill="#6772E5"></circle>

                                            <!-- Striscia "movimento" -->
                                            <path d="M66 88h6" fill="none" stroke="#FF7058" stroke-width="4" stroke-linecap="round"></path>
                                            <path d="M60 82h8" fill="none" stroke="#FF7058" stroke-width="4" stroke-linecap="round" opacity="0.9"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Takeaway
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            I clienti possono ordinare da casa, pagare con carta o contanti, per ricevere consegna o ritirare nel ristorante
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/fidelity-card"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                                wow fadeInUp reveal revealleft p-6 active
                                                transition-all duration-300 ease-out
                                                hover:-translate-y-1 hover:shadow-xl
                                                hover:bg-gray-50 dark:hover:bg-bodybg/80
                                                cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-label="Fidelity Card" role="img">
                                            <!-- Sfondo -->
                                            <circle cx="64" cy="64" r="63.5" fill="#00a5e2"></circle>

                                            <!-- Tessera -->
                                            <rect x="28" y="40" width="72" height="48" rx="8" fill="#FFFFFF"></rect>

                                            <!-- Banda superiore -->
                                            <rect x="28" y="48" width="72" height="8" rx="3" fill="#FF7058"></rect>

                                            <!-- Chip -->
                                            <rect x="36" y="60" width="18" height="14" rx="3" fill="#FFD05B"></rect>
                                            <path d="M39 63h12M39 67h12M45 60v14" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" opacity="0.95"></path>

                                            <!-- Stellina premio -->
                                            <path d="M82 60l2.4 5.2 5.7.5-4.3 3.6 1.3 5.5-5.1-2.9-5.1 2.9 1.3-5.5-4.3-3.6 5.7-.5z"
                                                    fill="#FFD05B"></path>

                                            <!-- Barra punti / progress -->
                                            <rect x="36" y="78" width="56" height="6" rx="3" fill="#E9ECFF"></rect>
                                            <rect x="36" y="78" width="34" height="6" rx="3" fill="#6772E5"></rect>

                                            <!-- Punti (bollini) -->
                                            <circle cx="98" cy="79" r="3" fill="#FF7058"></circle>
                                            <circle cx="108" cy="79" r="3" fill="#FF7058" opacity="0.6"></circle>

                                            <!-- Freccia "ritorno" (customer return) -->
                                            <path d="M98 56c8 0 14 6 14 14" fill="none" stroke="#FF7058" stroke-width="4" stroke-linecap="round"></path>
                                            <path d="M112 70l-6 0 0-6" fill="none" stroke="#FF7058" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Fidelity Card
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Fai tornare i tuoi clienti, o invitane di nuovi, facendoli iscrivere e guadagnare punti ad ogni presenza nel ristorante
                                        </p>

                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/sondaggi"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                                wow fadeInUp reveal revealleft p-6 active
                                                transition-all duration-300 ease-out
                                                hover:-translate-y-1 hover:shadow-xl
                                                hover:bg-gray-50 dark:hover:bg-bodybg/80
                                                cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-label="Sondaggio Clienti" role="img">
                                            <!-- Sfondo -->
                                            <circle cx="64" cy="64" r="63.5" fill="#6772E5"></circle>

                                            <!-- Scheda sondaggio -->
                                            <rect x="30" y="34" width="52" height="64" rx="8" fill="#FFFFFF"></rect>
                                            <rect x="30" y="42" width="52" height="8" rx="3" fill="#FF7058"></rect>

                                            <!-- Checkbox + righe -->
                                            <rect x="38" y="58" width="10" height="10" rx="2" fill="#FFD05B"></rect>
                                            <path d="M40.5 63l2.2 2.2 4.8-5" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <rect x="52" y="60" width="24" height="4" rx="2" fill="#E9ECFF"></rect>

                                            <rect x="38" y="74" width="10" height="10" rx="2" fill="#E9ECFF"></rect>
                                            <rect x="52" y="76" width="24" height="4" rx="2" fill="#E9ECFF"></rect>

                                            <rect x="38" y="90" width="10" height="10" rx="2" fill="#E9ECFF"></rect>
                                            <rect x="52" y="92" width="24" height="4" rx="2" fill="#E9ECFF"></rect>

                                            <!-- Stellina recensione (cliente soddisfatto) -->
                                            <path d="M96 46l2.6 5.8 6.4.6-4.8 4 1.5 6.1-5.7-3.3-5.7 3.3 1.5-6.1-4.8-4 6.4-.6z"
                                                    fill="#FFD05B"></path>

                                            <!-- Bubble commento -->
                                            <path d="M88 70c0-6 5-11 11-11h8c6 0 11 5 11 11v7c0 6-5 11-11 11h-5l-6 6v-6h-3c-6 0-11-5-11-11z"
                                                    fill="#FFFFFF" opacity="0.95"></path>
                                            <rect x="96" y="70" width="18" height="4" rx="2" fill="#E9ECFF"></rect>
                                            <rect x="96" y="78" width="14" height="4" rx="2" fill="#E9ECFF"></rect>

                                            <!-- Coupon premio -->
                                            <path d="M84 92h34c2 0 4 2 4 4v6c-3 0-6 2.5-6 6s3 6 6 6v6c0 2-2 4-4 4H84c-2 0-4-2-4-4v-6c3 0 6-2.5 6-6s-3-6-6-6v-6c0-2 2-4 4-4z"
                                                    fill="#FF7058"></path>
                                            <path d="M100 94v34" stroke="#FFFFFF" stroke-width="3" stroke-dasharray="5 5" opacity="0.9"></path>
                                            <circle cx="92" cy="109" r="4" fill="#FFD05B"></circle>
                                            <circle cx="113" cy="109" r="4" fill="#FFD05B" opacity="0.85"></circle>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Sondaggio Clienti
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Raccogli feedback post-servizio, premia la compilazione con un coupon e trasformi i clienti soddisfatti in recensioni positive
                                        </p>
                                    </div>

                                </div>
                            </a>
                        </div>

                        <div class="xl:col-span-3 lg:col-span-6 md:col-span-6 sm:col-span-6 col-span-12 mb-4">
                            <a href="/statistiche"
                               class="group block rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">

                                <div class="card rounded-sm bg-white dark:bg-bodybg features main-features main-features-8
                                                wow fadeInUp reveal revealleft p-6 active
                                                transition-all duration-300 ease-out
                                                hover:-translate-y-1 hover:shadow-xl
                                                hover:bg-gray-50 dark:hover:bg-bodybg/80
                                                cursor-pointer">

                                    <!-- Icona -->
                                    <div class="bg-img mb-3 transition-transform duration-300 group-hover:scale-105">
                                        <svg width="50" height="50" class="inline-flex" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" aria-label="Statistiche" role="img">
                                            <!-- Sfondo -->
                                            <circle cx="64" cy="64" r="63.5" fill="#67afff"></circle>

                                            <!-- Card report -->
                                            <rect x="28" y="34" width="72" height="60" rx="10" fill="#FFFFFF"></rect>
                                            <rect x="28" y="42" width="72" height="8" rx="3" fill="#FF7058"></rect>

                                            <!-- Mini KPI pills -->
                                            <rect x="36" y="54" width="16" height="8" rx="4" fill="#E9ECFF"></rect>
                                            <rect x="56" y="54" width="16" height="8" rx="4" fill="#E9ECFF"></rect>
                                            <rect x="76" y="54" width="16" height="8" rx="4" fill="#E9ECFF"></rect>

                                            <!-- Barre -->
                                            <rect x="38" y="70" width="10" height="18" rx="3" fill="#FFD05B"></rect>
                                            <rect x="54" y="62" width="10" height="26" rx="3" fill="#FFD05B"></rect>
                                            <rect x="70" y="74" width="10" height="14" rx="3" fill="#FFD05B"></rect>
                                            <rect x="86" y="58" width="10" height="30" rx="3" fill="#FFD05B"></rect>

                                            <!-- Line chart sopra le barre -->
                                            <path d="M40 78 L58 68 L74 80 L90 64"
                                                    fill="none" stroke="#FF7058" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <circle cx="40" cy="78" r="3" fill="#FF7058"></circle>
                                            <circle cx="58" cy="68" r="3" fill="#FF7058"></circle>
                                            <circle cx="74" cy="80" r="3" fill="#FF7058"></circle>
                                            <circle cx="90" cy="64" r="3" fill="#FF7058"></circle>

                                            <!-- Lente "analisi / controllo" -->
                                            <circle cx="92" cy="98" r="10" fill="#FFFFFF" opacity="0.95"></circle>
                                            <circle cx="92" cy="98" r="6" fill="none" stroke="#6772E5" stroke-width="4"></circle>
                                            <path d="M100 106l8 8" stroke="#6772E5" stroke-width="4" stroke-linecap="round"></path>
                                        </svg>
                                    </div>

                                    <!-- Testo -->
                                    <div>
                                        <h5 class="font-bold text-gray-900 transition-colors duration-300 group-hover:text-indigo-600">
                                            Statistiche
                                        </h5>

                                        <p class="mb-0 cursor-pointer">
                                            Report e grafici in app per tenere tutto sotto controllo: capisci subito cosa non sta funzionando e prendi decisioni più rapide
                                        </p>
                                    </div>

                                </div>
                            </a>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>


    <section class="section landing-Features text-defaulttextcolor dark:text-defaulttextcolor/70 " id="features">
        <div class="container text-center">
            <p class="text-[0.75rem] font-semibold text-success mb-1"><span class="landing-section-heading !text-white">Tecnologia al Servizio della Ristorazione</span>
            </p>
            <h3 class="font-semibold mb-2 !text-white">Strumenti Tecnologici di Ultima Generazione</h3>
            <div>
                <div class="xl:col-span-7 col-span-12">
                    <p class="text-white opacity-[0.8] text-[1.125rem] mb-4 font-normal">
                        Risto Pilot sfrutta tecnologie moderne per offrirti la massima flessibilità: utilizzo multi-dispositivo, menu digitale con QR-code, gestione sala, prenotazioni e integrazioni con i principali
                        canali di marketing online.
                    </p>
                </div>
            </div>
            <div class="text-start">
                <div class="justify-center">
                    <div class="">
                        <div class="feature-logos sm:mt-[3rem] flex-wrap">
                            <div class="sm:ms-[3rem] ms-2 text-center">
                                <img src="/assets/images/media/landing/icons-tech/mini-devices-icon.png" alt="image" class="featur-icon">
                                <h5 class="mt-3 text-white text-[1.25rem] ">Su ogni dispositivo</h5>
                            </div>
                            <div class="sm:ms-[3rem] ms-2 text-center">
                                <img src="/assets/images/media/landing/icons-tech/mini-qr-code-icon.png" alt="image" class="featur-icon">
                                <h5 class="mt-3 text-white text-[1.25rem] ">Menu Digitale</h5>
                            </div>
                            <div class="sm:ms-[3rem] ms-2 text-center">
                                <img src="/assets/images/media/landing/icons-tech/mini-stats-icon.png" alt="image" class="featur-icon">
                                <h5 class="mt-3 text-white text-[1.25rem] ">Analisi e Statistiche</h5>
                            </div>
                            <div class="sm:ms-[3rem] ms-2 text-center">
                                <img src="/assets/images/media/landing/icons-tech/mini-automation-icon.png" alt="image" class="featur-icon">
                                <h5 class="mt-3 text-white text-[1.25rem] ">Automazioni</h5>
                            </div>
                            <div class="sm:ms-[3rem] ms-2 text-center">
                                <img src="/assets/images/media/landing/icons-tech/mini-support-icon.png" alt="image" class="featur-icon">
                                <h5 class="mt-3 text-white text-[1.25rem] ">Supporto Dedicato</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination mt-4"></div>
            </div>
        </div>
    </section>


    <section class="section dark:bg-bodybg text-defaulttextcolor dark:text-defaulttextcolor/70 " id="about">
        <div class="container text-center">
            <p class="text-[0.75rem] font-semibold text-success mb-1"><span class="landing-section-heading">Missione</span></p>
            <div class="landing-title"></div>
            <h3 class="font-semibold mb-2">Semplificare il tuo lavoro.</h3>
            <p class="text-textmuted fs-15 mb-3 font-normal text-[1.125rem]">Risto Pilot permette ai ristoratori di concentrarsi su ciò che conta davvero: offrire un’esperienza indimenticabile ai propri clienti.</p>
            <div class="flex justify-center items-center mx-0">
                {{-- <div class="grid grid-cols-12 justify-center items-center mx-0"> --}}
                    {{-- <div class="xxl:col-span-5 xl:col-span-5 lg:col-span-5 col-span-12 text-center">
                        <img src="../assets/images/media/landing/ristopilot-work-simplifier .png" alt="" class="img-fluid inline-flex">
                    </div> --}}
                    <div class="pt-5 pb-0 px-lg-2 px-5 text-start">

                        {{-- <div class="xxl:col-span-7 xl:col-span-7 lg:col-span-7 col-span-12 pt-5 pb-0 px-lg-2 px-5 text-start"> --}}
                            <h4 class="text-lg-start font-medium mb-4">Riduci i costi di gestione e massimizza la produttività</h4>
                            <div class="grid grid-cols-12 md:gap-6">
                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box">
                                        <img src="/assets/images/media/ristopilot-home-spesa.png" class="card-img-top" alt="Lista della spesa Risto Pilot">
                                        <div class="box-body">
                                            <h6 class="box-title font-semibold !text-[1rem]">Lista della Spesa automatica e condivisa
                                            </h6>
                                            <p class="card-text mb-4 text-textmuted text-[1rem]">La tua lista della spesa giornaliera inviata in automatico ai fornitori, modificabile prima di ogni invio, e condivisibile
                                                con il tuo manager e i tuoi collaboratori.</p>
                                            {{-- <p class="card-text mb-0"><small>Last updated 3 mins
                                                    ago</small></p> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box">
                                        <img src="/assets/images/media/ristopilot-home-fornitori.png" class="card-img-top" alt="Fornitori Risto Pilot">
                                        <div class="box-body">
                                            <h6 class="box-title font-semibold !text-[1rem]">Prodotti e fornitori facilmente gestibili
                                            </h6>
                                            <p class="card-text mb-4 text-textmuted text-[1rem]">Crea la tua lista di fornitori da quelli nel nostro network. Aggiungi i fornitori alla tua lista privata e passa facilmente
                                                da un fornitore
                                                all’altro con un click</p>
                                            {{-- <p class="card-text mb-0"><small>Last updated 3 mins
                                                    ago</small></p> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box">
                                        <img src="/assets/images/media/ristopilot-home-fidelizza.png" class="card-img-top" alt="Risto Pilot Marketing">
                                        <div class="box-body">
                                            <h6 class="box-title font-semibold !text-[1rem]">Fidelizza i tuoi clienti in autonomia
                                            </h6>
                                            <p class="card-text mb-4 text-textmuted text-[1rem]">Crea e gestisci in maniera intuitiva Coupon e messaggi di Marketing mirando solo ai clienti che ti interessano. Una volta
                                                creata inviala direttamente tramite Risto Pilot.</p>
                                            {{-- <p class="card-text mb-0"><small>Last updated 3 mins
                                                    ago</small></p> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="grid grid-cols-12">
                                <div class="col-span-12 md:col-span-12">
                                    <div class="flex mb-2">
                                        <span>
                                            <i class='bx bxs-badge-check text-primary text-[1.125rem]'></i>
                                        </span>
                                        <div class="ms-2">
                                            <h6 class="font-medium mb-0">Lista della Spesa automatica e condivisa
                                            </h6>
                                            <p class=" text-textmuted mb-3">
                                                La tua lista della spesa giornaliera inviata in automatico ai fornitori, modificabile prima di ogni invio, e condivisibile con il tuo manager e i tuoi collaboratori.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12 md:col-span-12">
                                    <div class="flex mb-2">
                                        <span>
                                            <i class='bx bxs-badge-check text-primary text-[1.125rem]'></i>
                                        </span>
                                        <div class="ms-2">
                                            <h6 class="font-medium mb-0">Prodotti e fornitori facilmente gestibili</h6>
                                            <p class=" text-textmuted mb-3">
                                                Crea la tua lista di fornitori da quelli nel nostro network. Aggiungi i fornitori alla tua lista privata e passa facilmente da un fornitore
                                                all’altro con un click.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12 md:col-span-12">
                                    <div class="flex mb-2">
                                        <span>
                                            <i class='bx bxs-badge-check text-primary text-[1.125rem]'></i>
                                        </span>
                                        <div class="ms-2">
                                            <h6 class="font-medium mb-0">Fidelizza i tuoi clienti in autonomia</h6>
                                            <p class=" text-textmuted">
                                                Crea e gestisci in maniera intuitiva Coupon e messaggi di Marketing mirando solo ai clienti che ti interessa
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12 md:col-span-12">
                                    <div class="flex mb-2">
                                        <span>
                                            <i class='bx bxs-badge-check text-primary text-[1.125rem]'></i>
                                        </span>
                                        <div class="ms-2">
                                            <h6 class="font-medium mb-0">Flessibilità: passa facilmente da un fornitore
                                                all’altro con un click</h6>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
    </section>


    {{-- <section class="section dark:!bg-black/10 section-bg text-defaulttextcolor dark:text-defaulttextcolor/70" id="statistics">
        <div class="container text-center position-relative">
            <p class="text-[0.75rem] font-semibold text-success mb-1"><span class="landing-section-heading">STATISTICHE</span></p>
            <h3 class="font-semibold mb-2 text-defaulttextcolor dark:text-defaulttextcolor/70 ">Oltre 120
                ristoranti già utilizzano Risto Pilot</h3>
            <div class="">
                <div class="xl:col-span-7 col-span-12">
                    <p class="text-[#8c9097] text-[0.9375rem] mb-5 font-normal">
                        Decine di ristoratori hanno già scelto Risto Pilot per digitalizzare il proprio business. Unisciti a loro e scopri come ottimizzare i tuoi profitti.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-x-6 container">
                <div class="xl:col-span-1"></div>
                <div class="xl:col-span-2 lg:col-span-4 md:col-span-6 sm:col-span-12 col-span-12 mb-3">
                    <div class="p-4 text-center !rounded-sm bg-white dark:bg-bodybg border dark:border-defaultborder/10">
                        <span class="mb-4 avatar avatar-lg avatar-rounded bg-primary/10 !text-primary">
                            <i class='text-[1.5rem] bx bx-spreadsheet'></i>
                        </span>
                        <h3 class="font-semibold mb-0 text-dark">120+</h3>
                        <p class="mb-1 text-[0.875rem] opacity-[0.7] text-[#8c9097] ">Ristoranti attivati</p>
                    </div>
                </div>
                <div class="xl:col-span-2 lg:col-span-4 md:col-span-6 sm:col-span-12 col-span-12 mb-3">
                    <div class="p-4 text-center !rounded-sm !bg-white dark:!bg-bodybg border dark:border-defaultborder/10">
                        <span class="mb-4 avatar avatar-lg avatar-rounded bg-primary/10 !text-primary">
                            <i class='text-[1.5rem] bx bx-user-plus'></i>
                        </span>
                        <h3 class="font-semibold mb-0 text-dark">20K+</h3>
                        <p class="mb-1 text-[0.875rem] opacity-[0.7] text-[#8c9097] ">Clienti finali Raggiunti
                        </p>
                    </div>
                </div>
                <div class="xl:col-span-2 lg:col-span-4 md:col-span-6 sm:col-span-12 col-span-12 mb-3">
                    <div class="p-4 text-center !rounded-sm !bg-white dark:!bg-bodybg border dark:border-defaultborder/10">
                        <span class="mb-4 avatar avatar-lg avatar-rounded bg-primary/10 !text-primary">
                            <i class='text-[1.5rem] bx bx-money'></i>
                        </span>
                        <h3 class="font-semibold mb-0 text-dark">854</h3>
                        <p class="mb-1 text-[0.875rem] opacity-[0.7] text-[#8c9097] ">Fornitori Gestiti</p>
                    </div>
                </div>
                <div class="xl:col-span-2 lg:col-span-4 md:col-span-6 sm:col-span-12 col-span-12 mb-3">
                    <div class="p-4 text-center !rounded-sm !bg-white dark:!bg-bodybg border dark:border-defaultborder/10">
                        <span class="mb-4 avatar avatar-lg avatar-rounded bg-primary/10 !text-primary">
                            <i class='text-[1.5rem] bx bx-user-circle'></i>
                        </span>
                        <h3 class="font-semibold mb-0 text-dark">50000+</h3>
                        <p class="mb-1 text-[0.875rem] opacity-[0.7] text-[#8c9097] ">Prenotazioni Gestite</p>
                    </div>
                </div>
                <div class="xl:col-span-2 lg:col-span-4 md:col-span-6 sm:col-span-12 col-span-12 mb-3">
                    <div class="p-4 text-center !rounded-sm bg-white dark:!bg-bodybg border dark:border-defaultborder/10">
                        <span class="mb-4 avatar avatar-lg avatar-rounded bg-primary/10 !text-primary">
                            <i class='text-[1.5rem] bx bx-calendar'></i>
                        </span>
                        <h3 class="font-semibold mb-0 text-dark">5+</h3>
                        <p class="mb-1 text-[0.875rem] opacity-[0.7] text-[#8c9097] ">Anni nel settore</p>
                    </div>
                </div>
                <div class="xl:col-span-1"></div>
            </div>
        </div>
    </section> --}}


    {{-- <section class="section bg-white dark:bg-bodybg text-defaulttextcolor dark:text-defaulttextcolor/70 text-[0.813rem] " id="pricing">
        <div class="container text-center">
            <p class="text-[0.75rem] font-semibold text-success mb-1"><span class="landing-section-heading">PREZZI</span></p>
            <h3 class="font-semibold mb-2">Ristopilot offre abbonamenti con fascie di prezzi accessibili.
            </h3>
            <div class="row justify-center">
                <div class="col-xl-9">
                    <p class="text-[#8c9097] text-[0.9375rem] mb-5 font-normal">
                        I piani di abbonamento sono molto vantaggiosi rispetto ai competitor e sono studiati per ogni categoria del settore.
                    </p>
                </div>
            </div>
            <div class="flex justify-center mb-2">
                <span class="text-primary py-2 px-4 text-xl font-medium text-center rounded-sm hover:text-primary active">
                    Seleziona il piano più adatto a te
                </span>
            </div>
            <div class="row justify-center mb-2">
                <div class="col-xl-9">
                    <p class="text-[#8c9097] text-[0.9375rem] mb-2 font-normal">
                        Decidi qual'è il piano di abbonamento più adatto a te e <u>Iscriviti per un mese</u> per ottenere accesso completo alle funzionalità offerte dai nostri servizi.
                    </p>
                </div>
            </div>

            <div class="box overflow-hidden !shadow-none justify-center">
                <div class="box-body !border-0 !p-0">
                    <div class="tab-content !border-0" id="myTabContent">
                        <div class="tab-pane !border-0 show active !p-0 dark:!border-defaultborder/10" id="pricing-monthly-pane" aria-labelledby="pm-item" role="tabpanel" aria-labelledby="pricing-monthly" tabindex="0">
                            <div class="grid grid-cols-12 justify-center">
                                <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-12 col-span-12 border-e dark:border-e-defaultborder/10">
                                    <div class="p-4">
                                        <h6 class="font-semibold text-center text-[1rem]">MENSILE</h6>
                                        <div class="py-4 flex items-center justify-center">
                                            <div class="pricing-svg1">
                                                <i class="bi bi-tags"></i>
                                            </div>
                                            <div class="text-end ms-[3rem]">
                                                <p class="text-[1.5625rem] font-semibold mb-0">€ 450</p>
                                                <p class="text-[#8c9097] text-[0.6875rem] font-semibold mb-0">al mese
                                                </p>
                                            </div>
                                        </div>
                                        <p>
                                            <a href="#" class="!text-primary hover:underline">Provalo per 1 mese</a>
                                        </p>
                                        <ul class="list-unstyled text-center text-[0.75rem] px-4 pt-4 mb-0">
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Numero di operatori<span
                                                          class="badge bg-light text-default ms-1">4</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Aggiornamenti giornalieri<span
                                                          class="badge bg-light text-default ms-1">Illimitati</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Supporto online<span
                                                          class="badge bg-light text-default ms-1">24/7</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Monitoraggio visitatori<span
                                                          class="badge bg-light text-default ms-1">24/7</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">&nbsp;</span>
                                            </li>
                                        </ul>
                                        <div class="grid">
                                            <button class="ti-btn ti-btn-primary !font-[500]">Inizia ora
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-12 col-span-12 border-e dark:border-e-defaultborder/10-e  dark:border-defaultborder/10">
                                    <div class="p-4">
                                        <h6 class="font-semibold text-center text-[1rem]">SEMESTRALE</h6>
                                        <div class="py-4 flex items-center justify-center">
                                            <div class="pricing-svg1">
                                                <i class="bi bi-hand-thumbs-up"></i>
                                            </div>
                                            <div class="text-end ms-[3rem]">
                                                <p class="text-[1.5625rem] font-semibold mb-0">€ 2400</p>
                                                <p class="text-[#8c9097] text-[0.6875rem] font-semibold mb-0">
                                                    ogni sei mesi</p>
                                            </div>
                                        </div>
                                        <p>
                                            <a href="#" class="!text-primary hover:underline">Provalo per 1 mese</a>
                                        </p>
                                        <ul class="list-unstyled text-center text-[0.75rem] px-4 pt-4 mb-0">
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Numero di operatori<span
                                                          class="badge bg-light text-default ms-1">9</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Aggiornamenti giornalieri<span
                                                          class="badge bg-light text-default ms-1">Illimitati</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Supporto online<span
                                                          class="badge bg-light text-default ms-1">24/7</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Monitoraggio visitatori<span
                                                          class="badge bg-light text-default ms-1">24/7</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">&nbsp;</span>
                                            </li>
                                        </ul>
                                        <div class="grid">
                                            <button class="ti-btn ti-btn-primary !font-[500]">Inizia ora
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-12 col-span-12">
                                    <div class="p-4 pricing-offer overflow-hidden">
                                        <span class="pricing-offer-details shadow">
                                            <span class="font-semibold">10%</span> <span class="text-[0.625rem] op-8 ms-1">Off</span>
                                        </span>
                                        <h6 class="font-semibold text-center text-[1rem]">ANNUALE</h6>
                                        <div class="py-4 flex items-center justify-center">
                                            <div class="pricing-svg1">
                                                <i class="bi bi-gem"></i>
                                            </div>
                                            <div class="text-end ms-[3rem]">
                                                <p class="text-[1.5625rem] font-semibold mb-0 !text-primary">€ 4000
                                                </p>
                                                <p class="text-[#8c9097] text-[0.6875rem] font-semibold mb-0">
                                                    ogni anno</p>
                                            </div>
                                        </div>
                                        <p>
                                            <a href="#" class="!text-primary hover:underline">Provalo per 1 mese</a>
                                        </p>
                                        <ul class="list-unstyled text-center text-[0.75rem] px-4 pt-4 mb-0">
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Numero di operatori<span
                                                          class="badge bg-light text-default ms-1">Illimitato</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Aggiornamenti giornalieri<span
                                                          class="badge bg-light text-default ms-1">Illimitati</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Supporto online</span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Monitoraggio visitatori<span
                                                          class="badge bg-light text-default ms-1">24/7</span></span>
                                            </li>
                                            <li class="mb-4">
                                                <span class="text-[#8c9097]">Hotline (costo al min di
                                                    conversazione)<span
                                                          class="badge bg-light text-default ms-1">24/7</span></span>
                                            </li>
                                        </ul>
                                        <div class="grid">
                                            <button class="ti-btn bg-primary text-white !font-[500] shadow">Inizia ora
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <p>
                    <a href="#" class="ti-btn ti-btn-primary !font-[500]">Provalo per 1 Mese</a>
                </p>
                <p class="mt-3">
                    <span class="text-[#8c9097]">
                        Provalo per <span class="badge bg-light text-default ms-1">1 mese</span>.
                        Potrai decidere di interrompere l'abbonamento al servizio che sceglierai.
                    </span>
                </p>
            </div>
        </div>
    </section>

    <section class="section landing-testimonials " id="testimonials">
        <div class="container text-center">
            <p class="fs-12 font-semibold text-success mb-1"><span class="landing-section-heading">TESTIMONIANZE</span></p>
            <div class="landing-title"></div>
            <h3 class="font-semibold text-defaulttextcolor dark:text-defaulttextcolor/70 mb-2">Cosa dicono di noi.</h3>
            <p class="text-muted fs-15 mb-5 font-normal">Alcune delle recensioni dei nostri clienti che ci ispirano a lavorare per progetti futuri.</p>

            <div class="swiper pagination-dynamic text-start">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="box testimonial-card !shadow-none">
                            <div class="box-body">
                                <div class="testimonia text-center">
                                    <span class="avatar avatar-xl avatar-rounded mb-1">
                                        <img src="../assets/images/faces/11.jpg" alt="">
                                    </span>
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <span class="text-warning d-block">
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-half-fill"></i>
                                        </span>
                                    </div>
                                    <p class="op-8 mb-4">
                                        <i class="fa fa-quote-left fs-22 text-primary op-6 me-2"></i>
                                        Con Risto Pilot siamo riusciti a ridurre gli sprechi e a comunicare meglio con i clienti abituali.
                                        <i class="fa fa-quote-right fs-22 text-primary op-6 me-2"></i>
                                    </p>
                                    <p class="mb-0 font-semibold fs-16">Fabrizio</p>
                                    <p class="mb-0 fs-11 text-muted">Proprietario Spilusi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="box testimonial-card !shadow-none">
                            <div class="box-body">
                                <div class="testimonia text-center">
                                    <span class="avatar avatar-xl avatar-rounded mb-1">
                                        <img src="../assets/images/faces/11.jpg" alt="">
                                    </span>
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <span class="text-warning d-block">
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-half-fill"></i>
                                        </span>
                                    </div>
                                    <p class="op-8 mb-4">
                                        <i class="fa fa-quote-left fs-22 text-primary op-6 me-2"></i>
                                        Finalmente posso gestire ordini e prenotazioni online da un’unica dashboard.
                                        <i class="fa fa-quote-right fs-22 text-primary op-6 me-2"></i>
                                    </p>
                                    <p class="mb-0 font-semibold fs-16">Luigi</p>
                                    <p class="mb-0 fs-11 text-muted">Gestore di Osteria La Ruota</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="box testimonial-card !shadow-none">
                            <div class="box-body">
                                <div class="testimonia text-center">
                                    <span class="avatar avatar-xl avatar-rounded mb-1">
                                        <img src="../assets/images/faces/11.jpg" alt="">
                                    </span>
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <span class="text-warning d-block">
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-half-fill"></i>
                                        </span>
                                    </div>
                                    <p class="op-8 mb-4">
                                        <i class="fa fa-quote-left fs-22 text-primary op-6 me-2"></i>
                                        Finalmente posso gestire ordini e prenotazioni online da un’unica dashboard.
                                        <i class="fa fa-quote-right fs-22 text-primary op-6 me-2"></i>
                                    </p>
                                    <p class="mb-0 font-semibold fs-16">Luigi</p>
                                    <p class="mb-0 fs-11 text-muted">Gestore di Osteria La Ruota</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="swiper-pagination mt-4"></div>
            </div>
        </div>
    </section> --}}

    <section class="section bg-[#f9fafb] section-bg text-defaulttextcolor dark:text-defaulttextcolor/70 text-[0.813rem]" id="faq">
        <div class="container text-center">
            <p class="text-[0.75rem] font-semibold text-success mb-1"><span class="landing-section-heading">DOMANDE FREQUENTI</span></p>
            <h3 class="font-semibold mb-2">Domande frequenti?</h3>
            <div class="row justify-center">
                <div class="">
                    <p class="text-[#8c9097] text-[0.9375rem] mb-5 font-normal text-[1.125rem]">Abbiamo raccolto alcune delle domande più frequenti per aiutarti.</p>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-x-6">
                <div class="xl:col-span-6 col-span-12">
                    <div class="accordion accordion-customicon1 accordion-primary accordions-items-seperate" id="accordionFAQ1">
                        <div class="hs-accordion-group">
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-one">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-one">
                                    È possibile provare Risto Pilot prima di abbonarsi?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-one" class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300" aria-labelledby="faq-one" style="height: 0px;">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Sì. Per testare le funzionalità di Risto Pilot mettiamo a tua disposizione una demo personalizzata e gratuita.
                                            <br>Compila il form di contatto per fissare un incontro online: in circa 45 minuti un nostro consulente ti mostrerà tutte le funzionalità di Risto Pilot, risponderà alle tue
                                            domande e ti farà vedere come il software si adatta ai processi del tuo ristorante.
                                            <br>Al termine potrai decidere con calma se attivarlo per il tuo ristorante, senza alcun impegno né costi anticipati.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border-defaultborder/10-white/10" id="faq-two">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-two">
                                    Il form di prenotazione per i clienti è integrabile sul mio sito esistente?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-two" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-two" style="height: 0px;">
                                    <div class="p-5  text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            <strong>Il tuo form di prenotazione è integrabile dove vorrai</strong>.
                                            <br>Potrai gestire in totale autonomia i campi richiesti nel form di prenotazione, al di là dei campi tecnici necessari per il funzionamento della prenotazione. I form di
                                            prenotazione da te creati, tramite la funzionalità "Gestisci Form Prenotazione", saranno disponibili in un indirizzo pubblico a te dedicato chiaramente visibile nel tuo
                                            pannello di gestione. Un link di esempio potrebbe essere <code class="text-gray-700">https://mio-ristorante.ristopilot.com/form-prenotazione-ristorante</code>.
                                            <br>Potrai integrare il form di prenotazione da te creato sul tuo sito internet aziendale. Potrai pubblicare il link del form di prenotazione su campagne social, pubblicità o
                                            campagne marketing in modo da essere raggiungible dai tuoi clienti.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border-defaultborder/10-white/10" id="faq-twenty">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-three">
                                    Vorrei migliorare la fidelizzazione e la gestione dei clienti abituali, è possibile?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-three" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-twenty" style="height: 0px;">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Grazie a Risto Pilot hai accesso a una scheda dettagliata per ogni cliente, che ti permette di tracciare le sue prenotazioni passate, eventuali no-show e l’utilizzo di coupon
                                            promozionali.
                                            <br>
                                            <br> Una delle funzionalità più innovative è la possibilità di <strong>identificare facilmente i clienti più fedeli</strong>, visualizzando quante volte hanno prenotato e
                                            quanto hanno speso nel tuo ristorante. Questo ti consente di creare offerte personalizzate e premiare la loro fedeltà con promozioni mirate.
                                            <br>
                                            <br> Inoltre, puoi tenere traccia di eventuali <strong>no-show</strong>, riducendo il rischio di tavoli prenotati e poi lasciati vuoti. Se un cliente ha un alto tasso di
                                            mancata presentazione, puoi decidere di applicare regole specifiche per le sue future prenotazioni.
                                            <br>
                                            <br> Il sistema ti permette anche di monitorare i <strong>coupon riscattati e utilizzati</strong>, offrendoti una visione chiara dell’impatto delle tue campagne promozionali.
                                            Questo ti aiuta a capire quali offerte funzionano meglio e come incentivare le visite ripetute.
                                            <br>
                                            <br> Con queste funzionalità avanzate, puoi trasformare ogni prenotazione in un'opportunità per migliorare il servizio e costruire una relazione più solida con i tuoi clienti.

                                            <br>Dalla Gestione Clienti potrai decidere se avviare <strong>campagne di
                                                marketing</strong>, via email o sms, su gruppi di clienti in base a metriche da te stabilite (ad esempio, mandare campagna marketing via email a clienti che sono stati nel
                                            ristorante almeno 2 volte nell'ultimo anno).
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border-defaultborder/10-white/10" id="faq-thirty">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-four">
                                    Devo evitare cancellazioni last-minute e gestire meglio le prenotazioni, come faccio?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-four" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-thiery" style="height: 0px;">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Con Risto Pilot puoi automatizzare diversi aspetti della gestione delle prenotazioni per ottimizzare il servizio e ridurre i no-show.
                                            <br>
                                            <br> Una funzionalità innovativa è la possibilità di impostare <strong>promemoria automatici per i clienti</strong>, inviando notifiche personalizzate prima dell’orario della
                                            prenotazione. Questo ti aiuta a ridurre drasticamente le dimenticanze e le cancellazioni all’ultimo momento.
                                            <br>
                                            <br> Inoltre, puoi personalizzare il <strong>tempo massimo di permanenza al tavolo</strong>, permettendoti di ottimizzare la rotazione dei coperti e massimizzare il numero di
                                            clienti serviti nei momenti di punta.
                                            <br>
                                            <br> Il sistema ti consente anche di <strong>configurare messaggi di rifiuto personalizzati</strong> per le prenotazioni non accettate, sia in italiano che in inglese. Questo
                                            garantisce una comunicazione chiara e professionale con i clienti, evitando fraintendimenti e migliorando l’esperienza utente.
                                            <br>
                                            <br> Con queste impostazioni avanzate, puoi automatizzare la gestione delle prenotazioni, migliorare l'efficienza del servizio e garantire una maggiore organizzazione
                                            all’interno del tuo ristorante.

                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border-defaultborder/10-white/10" id="faq-three">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-five">
                                    Quanto è facile e veloce gestire i prodotti del menu digitale?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-five" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-three" style="height: 0px;">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Risto Pilot offre un sistema innovativo per la gestione del menu digitale, consentendoti di aggiornare e personalizzare i tuoi piatti in pochi clic.
                                            <br>
                                            <br> Grazie alla <strong>gestione centralizzata</strong>, puoi visualizzare, creare e modificare prodotti con un'interfaccia intuitiva che ti permette di organizzare il menu in
                                            modo efficiente, mantenendo sempre aggiornate le informazioni su ingredienti, allergeni e categorie.
                                            <br>
                                            <br> Il sistema integra una <strong>ricerca avanzata</strong> per trovare rapidamente prodotti nel tuo catalogo, evitando sprechi di tempo nella gestione del menu.
                                            <br>
                                            <br> Con l’
                                            <strong’>assegnazione automatica degli ingredienti</strong>, puoi selezionare e aggiornare gli ingredienti importati dai fornitori della tua zona, garantendo precisione nelle
                                                quantità e una gestione più efficace della dispensa.
                                                <br>
                                                <br> Inoltre, l’integrazione di <strong>liste allergeni dettagliate</strong> assicura trasparenza per i clienti, migliorando l’esperienza utente e la conformità alle
                                                normative alimentari.
                                                <br>
                                                <br> Questo sistema ti permette di gestire il menu in modo smart, ottimizzando i tempi, riducendo errori e offrendo ai clienti informazioni sempre precise e aggiornate.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border-defaultborder/10-white/10" id="faq-four">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-six">
                                    Posso evitare di organizzare personalmente le prenotazioni e la gestione dei tavoli?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-six" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-four" style="height: 0px;">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Avrai a disposizione un sistema avanzato che semplifica la gestione delle prenotazioni, migliorando l’organizzazione del servizio e riducendo al minimo il rischio di errori.
                                            <br>
                                            <br> Grazie alla <strong>assegnazione automatica</strong>, il sistema individua e assegna il miglior tavolo disponibile in base all’orario e al numero di clienti, evitando
                                            sovrapposizioni e garantendo la massima efficienza degli spazi.
                                            <br>
                                            <br> La <strong>visualizzazione dinamica</strong> delle prenotazioni ti permette di filtrare e monitorare in tempo reale le richieste in base alla data e all’orario, con
                                            un'interfaccia intuitiva che organizza le prenotazioni in ordine cronologico.
                                            <br>
                                            <br> Inoltre, lo <strong>storico avanzato</strong> delle prenotazioni ti offre strumenti di analisi dettagliati per identificare le abitudini dei clienti, ottimizzare i turni e
                                            migliorare le strategie di fidelizzazione.
                                            <br>
                                            <br> Con queste funzionalità innovative, puoi ridurre il rischio di tavoli inutilizzati, massimizzare i coperti e offrire un servizio più fluido e organizzato, migliorando
                                            l’esperienza complessiva dei tuoi clienti.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="xl:col-span-6 col-span-12">
                    <div class="accordion accordion-customicon1 accordion-primary accordions-items-seperate" id="accordionFAQ2">
                        <div class="hs-accordion-group">
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-five">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-seven">
                                    Come posso gestire i tavoli senza perdere tempo e ottimizzare gli spazi disponibili?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-seven" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-five">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            La gestione della sala è completamente automatizzata: i tavoli vengono assegnati <strong>in tempo reale</strong> sia per le prenotazioni effettuate online, sia per quelle
                                            inserite manualmente tramite l’applicativo. Il sistema occupa automaticamente i tavoli seguendo criteri di <strong>ottimizzazione dei posti disponibili</strong>, evitando
                                            sprechi di spazio e migliorando la rotazione dei coperti.<br><br>

                                            Il personale si occuperà solo di <strong>accettare la prenotazione</strong> o, se necessario, di <strong>spostare un cliente su un altro tavolo</strong> in base alle esigenze
                                            del servizio. Non dovrai più preoccuparti di assegnare manualmente i posti o di gestire sovrapposizioni.<br><br>

                                            Grazie a una <strong>mappa interattiva</strong>, puoi monitorare lo stato della sala con un colpo d’occhio. Il <strong>codice colore intelligente</strong> semplifica
                                            l’identificazione della situazione in tempo reale: i tavoli prenotati sono in rosso, quelli in attesa di occupazione in giallo e quelli disponibili in verde.<br><br>

                                            Inoltre, la <strong>visualizzazione per fasce orarie</strong> ti permette di prevedere la disponibilità futura dei tavoli, così puoi organizzare al meglio il flusso dei clienti
                                            e ridurre i tempi di attesa.<br><br>

                                            Per rendere tutto ancora più immediato, riceverai una notifica per ogni prenotazione <strong>direttamente dal gestionale, via WhatsApp o via Telegram</strong>, così potrai
                                            gestire tutto in modo rapido ed efficace, senza il rischio di perdere richieste.<br><br>

                                            Con questa automazione, la sala sarà sempre organizzata, il servizio più fluido e il lavoro del personale più semplice ed efficiente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-six">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-eight">
                                    Ho bisogno di aumentare le prenotazioni e premiare i clienti abituali senza complicarmi la vita. Qual è la soluzione?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-eight" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-six">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            I <strong>coupon promozionali</strong> possono essere un’ottima strategia per incentivare le prenotazioni e fidelizzare i clienti, ma gestirli manualmente può essere un lavoro
                                            impegnativo. Con un sistema di <strong>creazione e distribuzione automatizzata</strong>, puoi lanciare offerte mirate senza spreco di tempo e senza dover utilizzare strumenti
                                            esterni.<br><br>

                                            Il sistema ti permette di <strong>monitorare in tempo reale</strong> le promozioni attive, riutilizzare coupon già creati, modificarne la quantità disponibile e testarne
                                            l’efficacia prima di pubblicarli. Inoltre, grazie alla <strong>distribuzione automatica</strong>, puoi inviare coupon personalizzati ai clienti che hanno visitato il tuo
                                            locale, integrarli nei <strong>sondaggi automatici</strong> o utilizzarli in <strong>campagne marketing mirate</strong> direttamente dall’applicativo.<br><br>

                                            I coupon possono essere inviati via email, condivisi sui social, pubblicati sul sito web o distribuiti tramite le tue campagne digitali, permettendoti di raggiungere il
                                            pubblico giusto nel momento giusto.<br><br>

                                            Con questa automazione, <strong>aumenti le prenotazioni</strong>, premi la clientela affezionata e crei promozioni efficaci senza dover gestire tutto manualmente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-seven">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-nine">
                                    Voglio ottenere più informazioni utili sui clienti durante le prenotazioni, senza complicazioni. Come posso farlo?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-nine" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-seven">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Creare form personalizzati ti permette di semplificare le prenotazioni e raccogliere dati strategici sui tuoi clienti senza perdite di tempo. Grazie a un <strong>editor
                                                avanzato</strong>, puoi configurare ogni form su misura, scegliendo quali informazioni richiedere e impostando campi obbligatori o facoltativi in base alle tue
                                            esigenze.<br><br>

                                            Non è necessario essere esperti di tecnologia: puoi creare <strong>form preimpostati per le prenotazioni</strong> oppure personalizzarli completamente, ad esempio per
                                            raccogliere preferenze alimentari, richieste speciali o dati di contatto per future promozioni.<br><br>

                                            Una funzionalità innovativa è la <strong>visualizzazione in anteprima</strong>, che ti permette di testare il form prima della pubblicazione, così puoi verificare che tutto sia
                                            chiaro e funzionale prima di renderlo disponibile ai clienti.<br><br>

                                            Una volta pronto, il form può essere <strong>integrato ovunque</strong>: puoi condividere il link via email, sui social media o direttamente sul tuo sito web, aumentando la
                                            visibilità e migliorando la gestione delle prenotazioni.<br><br>

                                            Con questo strumento, automatizzi la raccolta dati, semplifichi il processo di prenotazione e offri ai clienti un’esperienza più fluida e personalizzata, riducendo il lavoro
                                            manuale del personale.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-eight">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-ten">
                                    Devo aggiornare spesso il menu e renderlo più attraente per i clienti. Come posso farlo senza perdere tempo?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-ten" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-eight">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Gestire il menu del ristorante non deve essere un’operazione complicata. Con un sistema digitale flessibile, puoi <strong>creare, aggiornare e personalizzare</strong> il menu
                                            in pochi clic, adattandolo in tempo reale alle stagioni, agli eventi speciali o alle variazioni della disponibilità degli ingredienti.<br><br>

                                            Non devi più modificare manualmente stampe o lavagne: puoi organizzare i piatti per categorie, aggiungere <strong>immagini accattivanti</strong> e descrizioni dettagliate per
                                            rendere il menu più chiaro e invitante per i clienti.<br><br>

                                            Una funzione innovativa è la possibilità di <strong>visualizzare un’anteprima</strong> prima della pubblicazione, permettendoti di verificare che tutto sia perfetto prima di
                                            renderlo visibile ai clienti.<br><br>

                                            Il menu sarà sempre accessibile <strong>tramite un link pubblico</strong>, così i clienti potranno consultarlo facilmente dal loro smartphone, anche prima di arrivare al
                                            ristorante. Questo migliora l’esperienza utente e riduce il tempo che il personale deve dedicare a spiegare le opzioni disponibili.<br><br>

                                            Con questa soluzione, aggiornare il menu diventa rapido e semplice, lasciandoti più tempo per concentrarti sulla qualità del servizio e sull’esperienza dei tuoi clienti.

                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-nine">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-eleven">
                                    Gestire le comunicazioni con i clienti mi porta via troppo tempo e rischio di perdere prenotazioni. Come posso essere più veloce ed efficace?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-eleven" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-nine">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            Grazie alle funzionalità di <strong>messaggistica automatica</strong> puoi configurare notifiche personalizzate per ogni fase della prenotazione, garantendo una comunicazione
                                            chiara e professionale con i clienti, senza dover gestire tutto manualmente.
                                            <br>
                                            <br> Il sistema ti permette di impostare <strong>messaggi automatici per ricordare ai clienti la loro prenotazione</strong>, riducendo il rischio di no-show e migliorando la
                                            gestione dei tavoli.
                                            <br>
                                            <br> Inoltre, puoi personalizzare i messaggi di <strong>accettazione o rifiuto della prenotazione</strong>, assicurando che i clienti ricevano una risposta immediata e
                                            trasparente, migliorando così la loro esperienza con il tuo ristorante.
                                            <br>
                                            <br> Con questa automazione, risparmi tempo prezioso nella gestione delle prenotazioni e puoi concentrarti su ciò che conta di più: offrire un servizio eccellente ai tuoi
                                            clienti.

                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="hs-accordion bg-white dark:bg-bodybg border dark:border-defaultborder/10 mt-[0.5rem] rounded-sm dark:border dark:border-defaultborder/10-white/10" id="faq-ten">
                                <button type="button"
                                        class="hs-accordion-toggle hs-accordion-active:!text-primary hs-accordion-active:border dark:border-defaultborder/10-b hs-accordion-active:bg-primary/10   dark:border-defaultborder/10 dark:hs-accordion-active:border dark:border-defaultborder/10-white/10 justify-between inline-flex items-center w-full font-semibold text-start text-[0.9rem] transition py-3 px-4 dark:hs-accordion-active:!text-primary dark:text-gray-200 dark:hover:text-white/80"
                                        aria-controls="faq-collapse-twelve">
                                    Che soluzione avete per gestire al meglio i turni dei tavoli senza creare confusione nel servizio?
                                    <svg class="hs-accordion-active:hidden hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary block w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path d="M8 15.36L8 2.35999" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    <svg class="hs-accordion-active:block hs-accordion-active:!text-primary hs-accordion-active:group-hover:!text-primary hidden w-3 h-3 text-gray-600 group-hover:text-defaulttextcolor dark:text-defaulttextcolor/70 "
                                         width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.5 8.85999L14.5 8.85998" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                </button>
                                <div id="faq-collapse-twelve" class="hs-accordion-content w-full overflow-hidden hidden transition-[height] duration-300" aria-labelledby="faq-ten">
                                    <div class="p-5 text-start">
                                        <p class="text-defaulttextcolor dark:text-defaulttextcolor/70 ">
                                            La funzione <strong>Tempo Massimo di Permanenza</strong> ti permette di ottimizzare la rotazione dei tavoli senza dover intervenire manualmente su ogni prenotazione. Basta
                                            impostare un limite di tempo per tavolo e il sistema lo applicherà automaticamente a tutte le prenotazioni online.
                                            <br>
                                            <br> Questo significa che puoi organizzare meglio il flusso dei clienti, ridurre le attese e massimizzare i coperti, senza che il personale debba gestire manualmente i tempi di
                                            permanenza. È particolarmente utile nei momenti di maggiore affluenza, come il weekend o gli eventi speciali, evitando che alcuni tavoli restino occupati troppo a lungo mentre
                                            altri clienti aspettano.
                                            <br>
                                            <br> Impostarlo è semplice: vai su <strong>Impostazioni > Tempo Massimo Permanenza</strong>, scegli la durata ideale e salva. Il sistema farà il resto, migliorando l’efficienza
                                            del tuo ristorante in modo automatico e senza stress.
                                            <br>
                                            <br>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section text-defaulttextcolor dark:text-defaulttextcolor/70 text-[0.813rem]" id="contact">
        <div class="container text-center">
            <div class="grid lg:grid-cols-12 grid-cols-1 gap-5">
                <div>
                    <p class="text-[0.75rem] font-semibold text-success mb-1"><span class="landing-section-heading">CONTATTACI</span></p>
                    <div class="landing-title"></div>
                    <h3 class="font-semibold mb-2">Pronto a lanciare la tua attività?</h3>
                    <div class="grid grid-cols-1 gap-x-6">
                        <div class="xl:col-span-9 col-span-12">
                            <p class="text-textmuted fs-15 mb-5 font-normal text-[1.125rem]">Siamo qui per accompagnarti verso il successo, passo dopo passo.</p>
                        </div>
                    </div>
                </div>
                {{-- <div>
                    <div class="flex justify-center mb-5">
                        <img src="/assets/images/media/ristopilot-home-razzo.png" />
                    </div>
                </div> --}}
            </div>
            <div class="text-start grid grid-cols-12 gap-x-6 justify-between">

                <livewire:contacts>

                    <div class="lg:col-span-4 col-span-12">
                        <div class="card bg-white dark:bg-bodybg !shadow-none rounded-sm">
                            <div class="box-body px-[3rem] py-[1.5rem]">
                                {{-- <div class="flex mb-3 mt-3">
                                    <div class="contact-icon contact-icon-1  border !border-primary bg-primary/10 m-0">
                                        <i class="fe fe-map-pin !text-primary text-[1.0625rem]"></i>
                                    </div>
                                    <div class="ms-3 text-start">
                                        <h6 class="mb-1 font-medium">Sede principale</h6>
                                        <p class="mb-4">Brindisi, BR</p>
                                    </div>
                                </div> --}}
                                {{-- <div class="flex mb-3">
                                    <div class="contact-icon contact-icon-2 border !border-danger bg-danger/10">
                                        <i class="fe fe-mail !text-danger text-[1.0625rem]"></i>
                                    </div>
                                    <div class="ms-3 text-start">
                                        <h6 class="mb-1 font-medium">Email</h6>
                                        <p class="mb-4">
                                            <a href="mailto:info@ristopilot.com?subject=Richiesta informazioni da Ristopilot.com&body=Vorrei avere informazioni riguardo....">
                                                info@ristopilot.com
                                            </a>
                                        </p>
                                    </div>
                                </div> --}}
                                {{-- <div class="flex mb-3">
                                    <div class="contact-icon contact-icon-3 border !border-success bg-success/10">
                                        <i class="fe fe-headphones !text-success text-[1.0625rem]"></i>
                                    </div>
                                    <div class="ms-3 text-start">
                                        <h6 class="mb-1 font-medium">Contatto</h6>
                                        <p class="mb-4">+39 392 500 0100</p>
                                    </div>
                                </div> --}}
                                <div class="flex mb-3">
                                    <div class="contact-icon contact-icon-4 border !border-warning bg-warning/10">
                                        <i class="fe fe-airplay !text-warning text-[1.0625rem]"></i>
                                    </div>
                                    <div class="ms-3 text-start">
                                        <h6 class="mb-1 font-medium">Orari di lavoro</h6>
                                        <p class="mb-0">Lun - Ven: 9:00 - 18:00</p>
                                    </div>
                                </div>
                                <div class="flex mb-2">
                                    <img src="/assets/images/media/ristopilot-home-razzo.png" />
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="lg:col-span-8 col-span-12">
                        <div class="card bg-white dark:bg-bodybg !shadow-none rounded-sm">
                            <div class="box-body px-[3rem] py-[2.4rem]">
                                <div class="grid grid-cols-12 gap-x-6 mt-1 mb-3">
                                    <div class="xl:col-span-6 col-span-12">
                                        <div class="form-group">
                                            <label for="cusName" class="form-label">Nome <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="cusName" placeholder="Inserisci il tuo nome">
                                        </div>
                                    </div>
                                    <div class="xl:col-span-6 col-span-12">
                                        <div class="form-group">
                                            <label for="cusEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="cusEmail" placeholder="Inserisci la tua email">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="cusMessage" class="form-label">Messaggio <span class="text-danger">*</span></label>
                                    <textarea rows="4" class="form-control" id="cusMessage" placeholder="Scrivi il tuo messaggio qui..."></textarea>
                                </div>
                                <div class="form-group mb-2 pt-1">
                                    <button class="ti-btn ti-btn-primary-full">Invia messaggio</button>
                                </div>
                            </div>
                        </div>
                    </div> --}}
            </div>
        </div>
    </section>

    {{-- <section class="section landing-footer text-white text-[0.813rem] opacity-[0.87]">
        <div class="container">
            <div class="grid grid-cols-12 gap-x-6">
                <div class="xl:col-span-4 lg:col-span-4 col-span-12">
                    <div class="px-6">
                        <p class="font-semibold mb-4">
                            <a href="/"><img src="../assets/images/brand-logos/desktop-logo.png" alt=""></a>
                        </p>
                        <p class="mb-2 opacity-[0.6] font-normal">
                            Risto Pilot è la soluzione completa per raggiungere facilmente nuovi clienti, automatizzare il marketing, gestione la spesa o gli ordini e semplificare la gestione del tuo ristorante.
                        <p class="mb-0 opacity-[0.6] font-normal">
                            Massimizza le tue vendite con un unico strumento.
                        </p>
                    </div>
                </div>
            <div class="xl:col-span-2 lg:col-span-2 col-span-12">
                <div class="px-6">
                    <h6 class="font-semibold text-[1rem] mb-4">PAGINE</h6>
                    <ul class="list-unstyled opacity-[0.6] font-normal landing-footer-list">
                        <li>
                            <a href="javascript:void(0);" class="text-white">Email</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="text-white">Profilo</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="text-white">Cronologia</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="text-white">Progetti</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="text-white">Contatti</a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="text-white">Portfolio</a>
                        </li>
                    </ul>
                </div>
            </div>
                <div class="xl:col-span-2 lg:col-span-2 col-span-12">
                    <div class="px-6">
                        <h6 class="font-semibold text-[1rem] mb-2">INFORMAZIONI</h6>
                        <ul class="list-unstyled opacity-[0.6] font-normal landing-footer-list">
                            <li>
                                <a href="#contact" class="text-white">Contattaci</a>
                            </li>
                            <li>
                                <a href="#about" class="text-white">Chi siamo</a>
                            </li>
                            <li>
                                <a href="#features" class="text-white">Servizi</a>
                            </li>
                            <li>
                                <a href="#terms-conditions" class="text-white">Termini e condizioni</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="xl:col-span-4 lg:col-span-4 col-span-12">
                    <div class="px-6">
                        <h6 class="font-semibold text-[1rem] mb-2">CONTATTI</h6>
                        <ul class="list-unstyled font-normal landing-footer-list">
                            <li>
                                <a href="javascript:void(0);" class="text-white opacity-[0.6]"><i
                                    class="ri-home-4-line me-1 align-middle"></i> Brindisi, BR 72100, IT</a>
                            </li>

                            <li>
                                <a href="mailto:info@ristopilot.com?subject=Richiesta informazioni da Ristopilot.com&body=Vorrei avere informazioni riguardo...." class="text-white opacity-[0.6]">
                                    <i class="ri-mail-line me-1 align-middle"></i> info@ristopilot.com</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="text-white opacity-[0.6]">
                                    <i class="ri-phone-line me-1 align-middle"></i> +39 392 500 0100</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="text-white opacity-[0.6]">
                                    <i class="ri-phone-line me-1 align-middle"></i> +39 340 100 5259</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}



    <section class="section landing-footer text-white text-[0.813rem] opacity-[0.87]">
        <div class="container">
            <div class="grid grid-cols-12">
                <div class="hidden colspan-0 md:colspan-2 md:block">
                    <div class="px-2">
                        <p class="font-semibold mb-4">
                            <a href="/"><img src="/assets/images/brand-logos/desktop-white.png" alt=""></a>
                        </p>
                    </div>
                </div>
                <div class="col-span-12 md:col-span-10">
                    <div class="px-6">
                        <p class="mb-2 opacity-[0.6] font-normal">
                            Risto Pilot: la soluzione completa per conquistare nuovi clienti, automatizzare il marketing e semplificare l'intera gestione del tuo ristorante.
                        <p class="mb-0 opacity-[0.6] font-normal">
                            Massimizza le tue vendite con un unico strumento.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- <section class="section landing-footer text-white text-[0.813rem] opacity-[0.87]">
        <div class="container">
            <div class="grid grid-cols-12 gap-x-6">
                <div class="col-span-2">
                    <div class="px-6">
                        <p class="font-semibold mb-4">
                            <a href="/"><img src="/assets/images/brand-logos/desktop-white.png" alt=""></a>
                        </p>
                    </div>
                </div>
                <div class="col-span-10">
                    <div class="px-6">
                        <p class="mb-2 opacity-[0.6] font-normal">
                            Risto Pilot: la soluzione completa per conquistare nuovi clienti, automatizzare il marketing e semplificare l'intera gestione del tuo ristorante.
                        <p class="mb-0 opacity-[0.6] font-normal">
                            Massimizza le tue vendite con un unico strumento.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <div class="text-center landing-main-footer py-4 opacity-[0.87]">
        <p class="text-[#8c9097] text-[0.9375rem]">Copyright © <span id="year"></span> <span class="!text-primary font-semibold text-white"><u>Ristopilot</u></span>.</p>
        <p class="text-[#8c9097] text-[0.9375rem] pb-4">Progettato con <span class="fa fa-heart text-danger"></span> da <a href="https://webofficine.com"><b class="text-white">Web Officine</b></a></p>
        <p class="text-[#8c9097] text-[0.9375rem] px-2">
            Webofficine Srls P.Iva: 02613570973 REA: PO-621021 - Viale Guglielmo Marconi 50/15, Prato 59100.
        </p>
    </div>
</div>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17709737813"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'AW-17709737813');
</script>

@endsection