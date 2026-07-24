<div class="content booking-room-page"><div wire:poll.30s class="main-content booking-room-main">
    <style>
        [x-cloak]{display:none!important}.rooms-tabs{display:flex;gap:.5rem;overflow-x:auto;border-bottom:1px solid #e5e7eb;padding:0 .25rem}.rooms-tab{padding:.8rem 1.15rem;border-bottom:3px solid transparent;white-space:nowrap;font-weight:600;color:#64748b}.rooms-tab.active{color:#2563eb;border-color:#2563eb}.room-workspace{display:flex;gap:1rem;min-height:560px}.booking-sidebar{width:330px;flex:0 0 330px;transition:width .2s,opacity .2s}.booking-sidebar.hidden-panel{width:0;flex-basis:0;opacity:0;overflow:hidden}.booking-scroll-wrap{position:relative}.booking-scroll-shadow{position:absolute;left:0;right:.25rem;height:36px;z-index:30;display:flex;align-items:center;justify-content:center;color:#475569;pointer-events:none;transition:opacity .15s ease}.booking-scroll-shadow svg{width:18px;height:18px;padding:3px;border-radius:9999px;background:rgba(255,255,255,.92);filter:drop-shadow(0 1px 2px rgba(15,23,42,.2))}.booking-scroll-shadow-top{top:0;align-items:flex-start;background:linear-gradient(to bottom,#fff 0,rgba(255,255,255,.94) 45%,rgba(255,255,255,0) 100%)}.booking-scroll-shadow-bottom{bottom:0;align-items:flex-end;background:linear-gradient(to top,#fff 0,rgba(255,255,255,.94) 45%,rgba(255,255,255,0) 100%)}.booking-card{border:1px solid #e5e7eb;border-radius:.65rem;padding:.75rem;margin-bottom:.6rem;background:#fff;transition:.2s;cursor:grab;touch-action:none;user-select:none}.booking-card:active{cursor:grabbing}.booking-card.is-drag-origin{opacity:.4}.booking-card.highlighted{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.2);background:#eff6ff}.room-layout{position:relative;flex:1;min-width:0;overflow:auto;background:#f1f5f9;border:1px solid #cbd5e1;border-radius:.75rem}.room-inner{position:relative;margin:1rem;min-width:700px;min-height:520px;background-size:20px 20px;background-image:linear-gradient(to right,rgba(148,163,184,.18) 1px,transparent 1px),linear-gradient(to bottom,rgba(148,163,184,.18) 1px,transparent 1px)}.table-item{position:absolute;user-select:none;touch-action:none;border:2px solid #475569;background:#99c08c;cursor:pointer;box-sizing:border-box;overflow:visible;border-radius:.5rem;transition:background-color .2s,border-color .2s,box-shadow .2s}.table-circle{border-radius:9999px}.table-free{background:#99c08c;border-color:#3e4552}.table-arriving{background:#fef08a;border-color:#ca8a04}.table-due{background:#fdba74;border-color:#c2410c}.table-seated{background:#fca5a5;border-color:#b91c1c}.table-drop-hover{box-shadow:0 0 0 4px rgba(59,130,246,.45)}.table-add-mode-target{box-shadow:0 0 0 4px rgba(245,158,11,.45)}.table-header{position:absolute;top:5px;left:7px;right:5px;display:flex;justify-content:center;gap:5px;align-items:center;color:#111;z-index:20;white-space:nowrap}.table-countdown{position:absolute;right:0;padding:2px 4px;border-radius:999px;background:rgba(255,255,255,.82);font-size:10px;font-weight:700}.table-number{font-size:15px}.table-capacity{font-size:11px}.table-bookings{position:absolute;top:28px;left:5px;right:5px;display:flex;flex-direction:column;gap:3px;z-index:10}.table-booking-row{width:100%;padding:3px 4px;border-radius:4px;background:rgba(255,255,255,.95);box-shadow:0 1px 3px rgba(0,0,0,.16);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:grab;touch-action:none}.table-booking-row.is-drag-origin{opacity:.35}.booking-drag-preview{position:fixed;z-index:80;pointer-events:none;padding:6px 9px;border-radius:6px;background:#111827;color:#fff;font-size:12px}.booking-context-menu{position:fixed;z-index:90;transform:translate(-50%,-100%);display:flex;gap:6px;padding:6px;border:1px solid #d1d5db;border-radius:999px;background:#fff;box-shadow:0 10px 24px rgba(0,0,0,.2)}.booking-menu-btn{width:32px;height:32px;border:0;border-radius:999px;font-weight:700;cursor:pointer}.booking-menu-btn-remove{background:#fee2e2;color:#b91c1c}.booking-menu-btn-add{background:#dcfce7;color:#15803d}.room-floating-hint{position:fixed;z-index:85;left:50%;bottom:18px;transform:translateX(-50%);padding:10px 14px;border-radius:999px;background:#111827;color:#fff;box-shadow:0 8px 24px rgba(0,0,0,.25)}.modal-tab{padding:.6rem .8rem;border-bottom:2px solid transparent}.modal-tab.active{color:#2563eb;border-color:#2563eb}@media(max-width:900px){.room-workspace{display:block}.booking-sidebar{width:100%;margin-bottom:1rem}.booking-sidebar.hidden-panel{display:none}.room-layout{min-height:500px}}
        .booking-room-controls-box{margin-bottom:0;border-bottom-right-radius:0;border-bottom-left-radius:0;box-shadow:none!important}
        .booking-room-map-box{border-top:0;border-top-left-radius:0;border-top-right-radius:0}
        .booking-timeslot-scroll{max-height:min(22rem,35dvh);overflow:auto;overscroll-behavior:contain}
        .booking-timeslot-scroll-wrap{position:relative}
        .booking-timeslot-scroll-wrap .booking-scroll-shadow{right:0}
        .booking-timeslot-scroll thead{position:sticky;top:0;z-index:10;background:#fff}
        :is(.dark .booking-timeslot-scroll thead){background:rgb(var(--body-bg))}
        .booking-room-widget:fullscreen,.booking-room-widget:-webkit-full-screen{display:flex;width:100%;height:100%;min-height:0;flex-direction:column;overflow:hidden;background:#f8fafc;padding:1rem}
        :is(.dark .booking-room-widget:fullscreen),:is(.dark .booking-room-widget:-webkit-full-screen){background:rgb(var(--body-bg))}
        .booking-room-widget:fullscreen .booking-room-map-box,.booking-room-widget:-webkit-full-screen .booking-room-map-box{display:flex;min-height:0;flex:1;flex-direction:column;overflow:hidden;margin-bottom:0}
        .booking-room-widget:fullscreen .booking-room-map-body,.booking-room-widget:-webkit-full-screen .booking-room-map-body{display:flex;min-height:0;flex:1;flex-direction:column;overflow:hidden}
        .booking-room-widget:fullscreen .room-workspace,.booking-room-widget:-webkit-full-screen .room-workspace{display:flex;min-height:0;flex:1;align-items:stretch}
        .booking-room-widget:fullscreen .booking-sidebar,.booking-room-widget:-webkit-full-screen .booking-sidebar{display:flex;height:100%;min-height:0;flex-direction:column}
        .booking-room-widget:fullscreen .booking-sidebar.hidden-panel,.booking-room-widget:-webkit-full-screen .booking-sidebar.hidden-panel{display:flex}
        .booking-room-widget:fullscreen .booking-scroll-wrap,.booking-room-widget:-webkit-full-screen .booking-scroll-wrap{min-height:0;flex:1}
        .booking-room-widget:fullscreen .booking-list-scroller,.booking-room-widget:-webkit-full-screen .booking-list-scroller{height:100%;max-height:none}
        .booking-room-widget:fullscreen .room-layout,.booking-room-widget:-webkit-full-screen .room-layout{height:100%;min-height:0}
        @media(min-width:901px){
            .booking-room-page{height:calc(100dvh - 4rem);overflow:hidden}
            .booking-room-main{display:flex;height:100%;min-height:0;flex-direction:column;padding-bottom:1rem}
            .booking-room-widget{display:flex;min-height:0;flex:1;flex-direction:column}
            .booking-room-map-box{display:flex;min-height:0;flex:1;flex-direction:column;overflow:hidden;margin-bottom:0}
            .booking-room-map-body{display:flex;min-height:0;flex:1;flex-direction:column;overflow:hidden}
            .booking-room-map-body>.room-workspace{min-height:0;flex:1;align-items:stretch}
            .booking-room-map-body .booking-sidebar{display:flex;height:100%;min-height:0;flex-direction:column}
            .booking-room-map-body .booking-scroll-wrap{min-height:0;flex:1}
            .booking-room-map-body .booking-list-scroller{height:100%;max-height:none}
            .booking-room-map-body .room-layout{height:100%;min-height:0}
        }
    </style>

    <div class="flex items-center justify-between mb-6 page-header-breadcrumb">
        <h5 class="page-title text-[1.3125rem] font-medium"><i class="las la-chair"></i> Sala e prenotazioni</h5>
        <div class="flex gap-3">
            <a href="{{ route('bookings.index', ['date'=>$date, 'meal'=>$meal]) }}" class="ti-btn ti-btn-primary-full text-white ti-btn-icon" title="Vista elenco"><i class="las text-3xl la-calendar"></i></a>
            <a href="{{ route('bookings.waitlist') }}" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Lista d'attesa"><i class="las text-3xl la-hourglass-half"></i></a>
            <a href="{{ route('bookings.create') }}" class="ti-btn ti-btn-info-full text-white ti-btn-icon" title="Nuova prenotazione"><i class="las text-3xl la-plus"></i></a>
            <button wire:click="$refresh" class="ti-btn ti-btn-warning-full text-white ti-btn-icon" title="Aggiorna"><i class="las text-3xl la-redo-alt"></i></button>
        </div>
    </div>

    <div
        wire:key="booking-room-widget"
        x-ref="fullscreenWidget"
        x-data="{
            controlsOpen: false,
            isFullscreen: false,
            async toggleFullscreen() {
                try {
                    const active = document.fullscreenElement || document.webkitFullscreenElement;
                    if (active) {
                        if (document.exitFullscreen) await document.exitFullscreen();
                        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    } else if (this.$refs.fullscreenWidget.requestFullscreen) {
                        await this.$refs.fullscreenWidget.requestFullscreen();
                    } else if (this.$refs.fullscreenWidget.webkitRequestFullscreen) {
                        this.$refs.fullscreenWidget.webkitRequestFullscreen();
                    }
                } catch (error) {
                    console.warn('Fullscreen non disponibile', error);
                }
            },
            syncFullscreen() {
                this.isFullscreen = (document.fullscreenElement || document.webkitFullscreenElement) === this.$refs.fullscreenWidget;
            }
        }"
        @fullscreenchange.document="syncFullscreen()"
        @webkitfullscreenchange.document="syncFullscreen()"
        class="booking-room-widget"
    >
    <div class="box booking-room-controls-box"><div class="box-body">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-4 py-2 text-sm">
                <strong><i class="las la-users"></i> Pax: {{ $totalPax }}</strong>
                <strong>{{ __('bookings.arriving') }}: {{ $arrivingPax }}</strong>
                <strong>{{ __('bookings.serving') }}: {{ $servingPax }}</strong>
            </div>
            <div class="inline-flex items-center gap-3">
                <button type="button" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-success transition" @click="controlsOpen = !controlsOpen" :aria-expanded="controlsOpen.toString()" aria-controls="booking-room-controls">
                    <span>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                    <span aria-hidden="true">—</span>
                    <span x-text="controlsOpen ? @js(__('bookings.collapse')) : @js(__('bookings.expand'))"></span>
                    <svg x-show="!controlsOpen" class="w-3 h-3" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 5L8.16086 10.6869C8.35239 10.8637 8.64761 10.8637 8.83914 10.6869L15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <svg x-show="controlsOpen" x-cloak class="w-3 h-3" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 11L8.16086 5.31305C8.35239 5.13625 8.64761 5.13625 8.83914 5.31305L15 11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button
                    type="button"
                    class="ti-btn ti-btn-light ti-btn-icon !m-0"
                    @click="toggleFullscreen()"
                    :aria-pressed="isFullscreen.toString()"
                    :title="isFullscreen ? @js(__('bookings.exit_fullscreen')) : @js(__('bookings.fullscreen'))"
                >
                    <i class="las text-xl" :class="isFullscreen ? 'la-compress' : 'la-expand'" aria-hidden="true"></i>
                    <span class="sr-only" x-text="isFullscreen ? @js(__('bookings.exit_fullscreen')) : @js(__('bookings.fullscreen'))"></span>
                </button>
            </div>
        </div>

        <div id="booking-room-controls" x-show="controlsOpen" x-transition x-cloak class="pt-4 mt-2 border-t dark:border-white/10">
            <div class="grid grid-cols-3 gap-3">
                <div><button wire:click="previousDay" class="ti-btn ti-btn-primary"><i class="las la-arrow-left text-3xl"></i><span class="hidden md:block">{{ __('bookings.back') }}</span></button></div>
                <div class="flex justify-center gap-2"><input type="date" wire:model.live="date" class="form-control" style="max-width:190px" onchange="this.blur()"><button wire:click="selectToday" class="ti-btn ti-btn-primary">{{ __('bookings.today') }}</button></div>
                <div class="flex justify-end"><button wire:click="nextDay" class="ti-btn ti-btn-primary"><span class="hidden md:block">{{ __('bookings.next') }}</span><i class="las la-arrow-right text-3xl"></i></button></div>
            </div>
            <div x-data="{ open: $wire.entangle('timeslotAccordionOpen').live }" class="mb-1 mt-5">
                <div class="overflow-hidden bg-white border -mt-px first:rounded-t-sm last:rounded-b-sm dark:bg-bodybg dark:border-white/10">
                    <button type="button" class="bg-primary/10 text-primary group py-4 px-5 inline-flex items-center justify-between gap-x-3 w-full text-start transition hover:text-success dark:text-gray-200 dark:hover:text-white/80" @click="open = !open" :aria-expanded="open.toString()">
                        <span class="inline-flex items-center gap-2"><i class="las la-clock text-xl"></i> {{ __('bookings.timeslots') }}</span>
                        <svg x-show="!open" class="w-3 h-3" viewBox="0 0 16 16" fill="none"><path d="M2 5L8.16086 10.6869C8.35239 10.8637 8.64761 10.8637 8.83914 10.6869L15 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <svg x-show="open" x-cloak class="w-3 h-3" viewBox="0 0 16 16" fill="none"><path d="M2 11L8.16086 5.31305C8.35239 5.13625 8.64761 5.13625 8.83914 5.31305L15 11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                    <div x-show="open" x-transition x-cloak class="w-full overflow-hidden">
                        <div class="box-body">
                            <div
                                class="booking-timeslot-scroll-wrap"
                                x-data="{
                                    hasAbove: false,
                                    hasBelow: false,
                                    headerHeight: 0,
                                    resizeObserver: null,
                                    mutationObserver: null,
                                    update() {
                                        const el = this.$refs.scroller;
                                        if (!el) return;
                                        this.headerHeight = el.querySelector('thead')?.offsetHeight || 0;
                                        const maxScroll = Math.max(0, el.scrollHeight - el.clientHeight);
                                        this.hasAbove = maxScroll > 1 && el.scrollTop > 1;
                                        this.hasBelow = maxScroll > 1 && el.scrollTop < maxScroll - 1;
                                    },
                                    init() {
                                        this.resizeObserver = new ResizeObserver(() => this.update());
                                        this.mutationObserver = new MutationObserver(() => this.update());
                                        this.$nextTick(() => {
                                            this.resizeObserver.observe(this.$refs.scroller);
                                            this.mutationObserver.observe(this.$refs.scroller, { childList: true, subtree: true });
                                            this.update();
                                        });
                                    },
                                    destroy() {
                                        this.resizeObserver?.disconnect();
                                        this.mutationObserver?.disconnect();
                                    }
                                }"
                                x-effect="open; $nextTick(() => update())"
                            >
                                <div x-show="hasAbove" x-cloak class="booking-scroll-shadow booking-scroll-shadow-top" :style="`top:${headerHeight}px`" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="M4 12.5 10 6.5l6 6" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                                <div x-ref="scroller" class="table-responsive booking-timeslot-scroll" @scroll.passive="update()" @resize.window.debounce.100ms="update()"><table class="ti-custom-table w-full"><thead><tr><th>{{ __('bookings.slot') }}</th><th>{{ __('bookings.booked') }}</th><th>{{ __('bookings.title') }}</th></tr></thead><tbody>
                                    @forelse($timeslotStats as $slot)
                                        @if($slot['bookings'] !== 0)<tr><td>{{ $slot['time'] }}</td><td>{{ $slot['pax'] }}</td><td>{{ $slot['bookings'] }}</td></tr>@endif
                                    @empty<tr><td colspan="3" class="text-center">{{ __('bookings.closed') }}</td></tr>@endforelse
                                </tbody></table></div>
                                <div x-show="hasBelow" x-cloak class="booking-scroll-shadow booking-scroll-shadow-bottom" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="m4 7.5 6 6 6-6" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-5 gap-2 mb-1 mt-2">
                <input wire:model.live.debounce.300ms="search" class="form-control col-span-5 md:col-span-1 h-12" placeholder="{{ __('bookings.search') }}">
                <a href="{{ route('bookings.index', ['date' => $date, 'meal' => $meal]) }}" class="ti-btn ti-btn-primary col-span-1 h-12">{{ __('bookings.list') }}</a>
                <button wire:click="selectMeal('all')" class="ti-btn h-12 {{ $meal==='all'?'ti-btn-primary':'ti-btn-light' }}">{{ __('bookings.all_day') }}</button>
                <button wire:click="selectMeal('pranzo')" class="ti-btn h-12 {{ $meal==='pranzo'?'ti-btn-primary':'ti-btn-light' }}">{{ __('bookings.lunch') }}</button>
                <button wire:click="selectMeal('cena')" class="ti-btn h-12 {{ $meal==='cena'?'ti-btn-primary':'ti-btn-light' }}">{{ __('bookings.dinner') }}</button>
            </div>
        </div>
    </div></div>

    <div class="box booking-room-map-box"><div class="rooms-tabs">
        @forelse($rooms as $room)<button wire:key="room-tab-{{ $room->id }}" wire:click="selectRoom('{{ $room->id }}')" class="rooms-tab {{ $roomId===$room->id?'active':'' }}">{{ $room->name }}</button>@empty<span class="p-4 text-textmuted">Configura almeno una sala attiva.</span>@endforelse
    </div>
    <div
        class="box-body booking-room-map-body"
        x-data="roomBookingDnD({tables:@js($roomTables),bookings:@js($roomBookings),sidebarOpen:$wire.entangle('sidebarOpen')})"
        wire:key="room-map-{{ $roomId }}-{{ $date }}-{{ $meal }}"
        @keydown.escape.window="cancelInteractiveModes()"
    >
        <div class="flex flex-col items-start gap-3 mb-3 md:flex-row md:items-center md:justify-between">
            <button type="button" class="ti-btn ti-btn-light" @click="sidebarOpen=!sidebarOpen;$nextTick(()=>updateBookingScrollShadows())"><i class="las" :class="sidebarOpen?'la-chevron-left':'la-list'"></i> <span x-text="sidebarOpen?'Nascondi prenotazioni':'Mostra prenotazioni'"></span></button>
            <div class="flex flex-wrap gap-3 text-xs"><span><i class="inline-block w-3 h-3 rounded bg-[#99c08c]"></i> Libero</span><span><i class="inline-block w-3 h-3 rounded bg-[#fef08a]"></i> Arrivo entro 60 min</span><span><i class="inline-block w-3 h-3 rounded bg-[#fdba74]"></i> Orario raggiunto</span><span><i class="inline-block w-3 h-3 rounded bg-[#fca5a5]"></i> Seduti</span></div>
        </div>
        <div class="room-workspace">
            <aside class="booking-sidebar" :class="{'hidden-panel':!sidebarOpen}" aria-label="Prenotazioni">
                <div class="font-semibold mb-3">Prenotazioni ({{ $bookings->count() }})</div>
                <div class="booking-scroll-wrap">
                    <div x-show="bookingScrollHasAbove" x-cloak class="booking-scroll-shadow booking-scroll-shadow-top" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="M4 12.5 10 6.5l6 6" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                    <div x-ref="bookingScroller" class="booking-list-scroller max-h-[540px] overflow-y-auto pr-1" @scroll.passive="updateBookingScrollShadows()" @resize.window.debounce.100ms="updateBookingScrollShadows()">
                        @forelse($bookings as $booking)
                        @php
                            $name=$booking->customer?->display_name ?: trim(($booking->customer?->firstname??'').' '.($booking->customer?->lastname??'')) ?: 'Walk In';
                            $status=$statuses[$booking->status]??['color'=>'secondary'];
                            $language=app(\App\Services\CustomerLanguageService::class)->meta($booking->language);
                            $progress=$booking->status==='seated' ? $this->progress($booking,$maxSitting) : 0;
                        @endphp
                        <article id="booking-list-{{ $booking->id }}" class="booking-card" :class="{'highlighted':highlightedBookingId==='{{ $booking->id }}','is-drag-origin':dragging?.bookingId==='{{ $booking->id }}'&&dragging?.fromTableId===null}" @pointerdown="onListBookingPointerDown('{{ $booking->id }}',$event)" @click.capture="if(Date.now()<suppressClicksUntil){$event.preventDefault();$event.stopPropagation()}">
                            <div class="flex justify-between items-start gap-2">
                                <div class="min-w-0 flex items-center gap-1"><a class="font-semibold text-primary truncate" href="{{ route('bookings.show',$booking) }}">{{ $booking->customer ? $name : '🚶 Walk In' }}</a><span class="shrink-0" title="{{ $language['label'] }}" aria-label="{{ $language['label'] }}">{{ $language['flag'] }}</span></div>
                                <strong class="whitespace-nowrap"><i class="las la-clock"></i> {{ substr($booking->booking_time,0,5) }}</strong>
                            </div>
                            <div class="flex flex-wrap items-center gap-1 mt-1 text-sm"><span><i class="las la-users"></i> {{ $booking->pax }} pax</span><span class="badge bg-{{ $status['color'] }}/10 text-{{ $status['color'] }}">{{ __('bookings.statuses.'.$booking->status) }}</span></div>
                            <div class="flex flex-wrap gap-1 mt-2 booking-card-actions">
                                <a href="{{ route('bookings.show',$booking) }}" class="ti-btn ti-btn-icon !w-8 !h-8 bg-info text-white" title="Gestisci"><i class="las la-cog text-xl"></i></a>
                                @if(in_array($booking->status,['pending','waiting']))<button wire:click="openAction('accept','{{ $booking->id }}')" class="ti-btn ti-btn-icon !w-8 !h-8 bg-success text-white" title="Accetta"><i class="las la-check text-xl"></i></button><button wire:click="openAction('deny','{{ $booking->id }}')" class="ti-btn ti-btn-icon !w-8 !h-8 bg-danger text-white" title="Rifiuta"><i class="las la-times text-xl"></i></button>@endif
                                @if($booking->status==='accepted')<button type="button" class="ti-btn ti-btn-icon !w-8 !h-8 bg-green-500 text-white" title="Assegna trascinando la card"><i class="las la-chair text-xl"></i></button>@if($booking->booking_date->isToday()||$booking->booking_date->isPast())<button wire:click="openAction('seat','{{ $booking->id }}')" class="ti-btn ti-btn-icon !w-8 !h-8 bg-green-500 text-white" title="Fai accomodare"><i class="las la-user-check text-xl"></i></button><button wire:click="openAction('no-show','{{ $booking->id }}')" class="ti-btn ti-btn-icon !w-8 !h-8 bg-red-500 text-white" title="No show"><i class="las la-user-slash text-xl"></i></button>@endif @endif
                                @if($booking->status==='seated')<button wire:click="openAction('finalize','{{ $booking->id }}')" class="ti-btn ti-btn-icon !w-8 !h-8 bg-green-500 text-white" title="Finalizza"><i class="las la-check-double text-xl"></i></button>@endif
                                @unless($booking->isWalkIn())<a href="{{ route('bookings.edit',$booking) }}" class="ti-btn ti-btn-icon !w-8 !h-8 bg-orange-500 text-white" title="Modifica"><i class="las la-edit text-xl"></i></a>@endunless
                                @if($booking->note)<button wire:click="openAction('note','{{ $booking->id }}')" class="ti-btn ti-btn-icon !w-8 !h-8 bg-info text-white" title="Note"><i class="las la-comment text-xl"></i></button>@endif
                            </div>
                            <div class="border-t mt-2 pt-2"><div class="flex flex-wrap items-center gap-1 text-xs"><i class="las la-chair"></i>@forelse($booking->tables as $table)<span class="badge bg-secondary/10 text-black">{{ $table->name }}</span>@empty<span class="text-danger">Tavolo non assegnato</span>@endforelse</div>
                                @if($booking->status==='seated')<div class="progress mt-2 !h-2"><div class="progress-bar {{ $progress<60?'!bg-success':($progress<85?'!bg-warning':'!bg-danger') }}" style="width:{{ $progress }}%"></div></div><div class="text-right text-[11px] mt-1">Permanenza {{ $progress }}%</div>@endif
                            </div>
                        </article>
                        @empty<div class="text-center text-textmuted p-4">Nessuna prenotazione.</div>@endforelse
                    </div>
                    <div x-show="bookingScrollHasBelow" x-cloak class="booking-scroll-shadow booking-scroll-shadow-bottom" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none"><path d="m4 7.5 6 6 6-6" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                </div>
            </aside>
            <section class="room-layout" aria-label="Mappa sala">
                <div class="room-inner" :style="roomInnerStyle()">
                    <template x-for="table in tables" :key="table.id"><div :class="tableCssClasses(table)" :style="tableStyle(table)" :data-drop-table-id="table.id" @click="onTableClick(table.id,$event)">
                        <div class="table-header" @click.stop="openTableModal(table.id)"><strong class="table-number" x-text="table.number"></strong><span class="table-capacity" x-text="`${table.min}-${table.max}`"></span><span x-show="countdownMinutes(table)!==null" class="table-countdown" x-text="`${countdownMinutes(table)} min`" title="Minuti all'arrivo"></span></div>
                        <div class="table-bookings" x-show="table.bookings.length"><template x-for="booking in table.bookings" :key="`${table.id}-${booking.id}`"><div class="table-booking-row" :class="{'is-drag-origin':isDraggedBooking(table.id,booking.id)}" @pointerdown.stop="onBookingPointerDown(table.id,booking.id,$event)" @click.stop="onBookingRowClick(booking.id)" x-text="`${booking.time} ${booking.customer}`"></div></template></div>
                    </div></template>
                </div>
            </section>
        </div>
        <div x-show="dragPreview.visible" x-cloak class="booking-drag-preview" :style="`left:${dragPreview.x}px;top:${dragPreview.y}px`" x-text="dragPreview.label"></div>
        <div x-show="bookingMenu.open" x-cloak class="booking-context-menu" :style="`left:${bookingMenu.x}px;top:${bookingMenu.y}px`" @click.outside="closeBookingMenu()" @pointerdown.stop><button x-show="bookingMenu.canDetach" class="booking-menu-btn booking-menu-btn-remove" title="Scollega dal tavolo" @click="detachBookingFromMenu()">✕</button><button class="booking-menu-btn booking-menu-btn-add" title="Aggiungi a un altro tavolo" @click="beginAddTableMode()">+</button></div>
        <div x-show="addTableMode.active" x-cloak class="room-floating-hint"><span x-text="`Seleziona un altro tavolo per ${addTableMode.label}`"></span> <button class="ml-2" @click="cancelAddTableMode()">Annulla</button></div>

        <div x-show="tableModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"><div class="absolute inset-0 bg-black/50" @click="closeTableModal()"></div><div class="relative z-10 bg-white dark:bg-bodybg rounded-xl shadow-2xl p-6 w-full max-w-xl" @click.stop>
            <div class="flex justify-between"><h2 class="text-xl font-bold" x-text="selectedTable?`Tavolo ${selectedTable.number}`:'Tavolo'"></h2><button @click="closeTableModal()">✕</button></div>
            <div class="flex border-b mt-4"><button class="modal-tab" :class="{active:tableModalTab==='assign'}" @click="tableModalTab='assign'">Assegna tavoli</button><button class="modal-tab" :class="{active:tableModalTab==='create'}" @click="tableModalTab='create'">Nuova prenotazione</button><button class="modal-tab" :class="{active:tableModalTab==='bookings'}" @click="tableModalTab='bookings'">Prenotazioni del tavolo</button></div>
            <div x-show="selectedTable" class="py-4">
                <div x-show="tableModalTab==='assign'">
                    <input x-model="modalSearch" class="form-control mb-3" placeholder="Cerca per nome cliente…">
                    <div class="max-h-72 overflow-y-auto space-y-2">
                        @php $unassignedRoomBookings = collect($roomBookings)->filter(fn (array $booking): bool => empty($booking['tableIds'])); @endphp
                        @forelse($unassignedRoomBookings as $assignableBooking)
                            <div
                                wire:key="assignable-booking-{{ $assignableBooking['id'] }}"
                                x-data="{ customerName: @js(mb_strtolower($assignableBooking['customer'])) }"
                                x-show="customerName.includes(modalSearch.trim().toLocaleLowerCase())"
                                class="flex items-center justify-between gap-3 border rounded p-2"
                            >
                                <span>{{ $assignableBooking['time'] }} · {{ $assignableBooking['customer'] }} · {{ $assignableBooking['pax'] }} pax</span>
                                <button
                                    type="button"
                                    class="ti-btn ti-btn-primary !m-0 shrink-0"
                                    @click="assignFromModal(@js($assignableBooking))"
                                >{{ __('bookings.actions.assign') }}</button>
                            </div>
                        @empty
                            <p class="text-textmuted">Nessuna prenotazione da assegnare.</p>
                        @endforelse
                    </div>
                </div>
                <div x-show="tableModalTab==='create'"><p class="mb-3">Crea una prenotazione per il {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} e assegnala al tavolo selezionato.</p><a href="{{ route('bookings.create', ['date'=>$date]) }}" class="ti-btn ti-btn-primary">Nuova prenotazione</a></div>
                <div x-show="tableModalTab==='bookings'" class="space-y-2"><template x-for="booking in (selectedTable?.allBookings || [])" :key="booking.id"><div class="flex items-center gap-2 border rounded p-3" :class="booking.isPast?'opacity-70 bg-gray-50':''"><button type="button" class="min-w-0 flex-1 text-left" @click="closeTableModal();onBookingRowClick(booking.id)"><span class="flex items-center justify-between gap-3"><span><strong x-text="booking.time"></strong> · <span x-text="booking.customer"></span> · <span x-text="`${booking.pax} pax`"></span></span><span class="badge bg-secondary/10 text-black" x-text="booking.statusLabel"></span></span></button><button x-show="booking.canDetach" type="button" class="ti-btn ti-btn-icon !m-0 !w-8 !h-8 bg-danger text-white shrink-0" title="{{ __('bookings.actions.remove_from_table') }}" @click="detachBooking(booking.id, selectedTableId)"><i class="las la-times text-xl"></i><span class="sr-only">{{ __('bookings.actions.remove_from_table') }}</span></button></div></template><p x-show="!(selectedTable?.allBookings || []).length" class="text-textmuted">Nessuna prenotazione associata al tavolo per questa data.</p></div>
            </div>
        </div></div>
    </div></div>
    @if($modal)
        @php $modalBooking=$bookings->firstWhere('id',$selectedBookingId); @endphp
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4" wire:click.self="closeModal"><div class="bg-white dark:bg-bodybg rounded-lg shadow-xl p-5 w-full max-w-md">
            @if($modal==='note')
                <h2 class="text-xl font-bold mb-3">Note prenotazione</h2><p class="whitespace-pre-wrap">{{ $modalBooking?->note }}</p>
            @else
                <h2 class="text-xl font-bold mb-3">Conferma operazione</h2><p class="mb-4">Confermi il cambio di stato della prenotazione di <strong>{{ $modalBooking?->customer?->display_name ?: 'Walk In' }}</strong>?</p>
                <button wire:click="changeStatus('{{ ['accept'=>'accepted','deny'=>'denied','no-show'=>'no-show','seat'=>'seated','finalize'=>'finalized'][$modal] }}')" class="ti-btn ti-btn-primary">Conferma</button>
            @endif
            <button wire:click="closeModal" class="ti-btn ti-btn-secondary">Chiudi</button>
        </div></div>
    @endif
    </div>
</div></div>

<script>
window.roomBookingDnD = function(config) { config=config||{}; return {
    tables:config.tables||[], bookings:config.bookings||[], layoutScale:1.35, sidebarOpen:config.sidebarOpen??false, highlightedBookingId:null, modalSearch:'', longPressDelay:450, dragThreshold:8,
    nowTimestamp:Date.now(), clockTimer:null, bookingScrollObserver:null, bookingScrollMutationObserver:null, bookingScrollHasAbove:false, bookingScrollHasBelow:false,
    dragging:null, pointerSession:null, pointerListeners:null, hoverTableId:null, suppressClicksUntil:0, dragPreview:{visible:false,x:0,y:0,label:''},
    bookingMenu:{open:false,x:0,y:0,bookingId:null,tableId:null,label:'',canDetach:false}, addTableMode:{active:false,bookingId:null,fromTableId:null,label:''},
    tableModalOpen:false, tableModalTab:'assign', selectedTableId:null,
    get selectedTable(){return this.tables.find(t=>t.id===this.selectedTableId)||null},
    init(){this.clockTimer=window.setInterval(()=>{this.nowTimestamp=Date.now()},30000);this.$nextTick(()=>this.initBookingScrollShadows())},
    destroy(){if(this.clockTimer)window.clearInterval(this.clockTimer);this.bookingScrollObserver?.disconnect();this.bookingScrollMutationObserver?.disconnect()},
    initBookingScrollShadows(){let el=this.$refs.bookingScroller;if(!el)return;this.bookingScrollObserver=new ResizeObserver(()=>this.updateBookingScrollShadows());this.bookingScrollObserver.observe(el);this.bookingScrollMutationObserver=new MutationObserver(()=>this.updateBookingScrollShadows());this.bookingScrollMutationObserver.observe(el,{childList:true,subtree:true});this.updateBookingScrollShadows()},
    updateBookingScrollShadows(){let el=this.$refs.bookingScroller;if(!el||!this.sidebarOpen){this.bookingScrollHasAbove=false;this.bookingScrollHasBelow=false;return}let maxScroll=Math.max(0,el.scrollHeight-el.clientHeight),tolerance=1;this.bookingScrollHasAbove=maxScroll>tolerance&&el.scrollTop>tolerance;this.bookingScrollHasBelow=maxScroll>tolerance&&el.scrollTop<maxScroll-tolerance},
    onBookingRowClick(id){if(Date.now()<this.suppressClicksUntil)return;this.sidebarOpen=true;this.highlightedBookingId=id;this.$nextTick(()=>document.getElementById(`booking-list-${id}`)?.scrollIntoView({behavior:'smooth',block:'center'}))},
    openTableModal(id){this.selectedTableId=id;this.tableModalTab='assign';this.modalSearch='';this.tableModalOpen=!!this.selectedTable},closeTableModal(){this.tableModalOpen=false;this.selectedTableId=null},
    allBookings(){let found=new Map();this.bookings.forEach(b=>found.set(b.id,b));this.tables.flatMap(t=>t.bookings).forEach(b=>found.set(b.id,b));return Array.from(found.values())},
    async assignFromModal(booking){let table=this.selectedTable,snapshot=JSON.stringify({tables:this.tables,bookings:this.bookings});table.bookings.push({...booking});if(!table.allBookings.some(b=>b.id===booking.id))table.allBookings.push({...booking});booking.tableIds=[...(booking.tableIds||[]),table.id];try{await this.$wire.assignBookingToTable(booking.id,table.id)}catch(e){let state=JSON.parse(snapshot);this.tables=state.tables;this.bookings=state.bookings;throw e}},
    onListBookingPointerDown(bookingId,event){if(!event.isPrimary||this.addTableMode.active||event.target.closest('a,button'))return;let booking=this.bookings.find(b=>b.id===bookingId);if(!booking)return;let rowEl=event.currentTarget;this.pointerSession={pointerId:event.pointerId,fromTableId:null,bookingId,startX:event.clientX,startY:event.clientY,lastX:event.clientX,lastY:event.clientY,dragStarted:false,menuOpened:false,rowEl,label:`${booking.time} ${booking.customer}`,longPressTimer:null};rowEl.setPointerCapture?.(event.pointerId);let move=e=>this.onGlobalPointerMove(e),up=e=>this.onGlobalPointerUp(e),cancel=e=>this.onGlobalPointerCancel(e);this.pointerListeners={move,up,cancel};window.addEventListener('pointermove',move,{passive:false});window.addEventListener('pointerup',up);window.addEventListener('pointercancel',cancel)},
    onBookingPointerDown(fromTableId,bookingId,event){if(!event.isPrimary||this.addTableMode.active)return;let table=this.tables.find(t=>t.id===fromTableId),booking=table?.bookings.find(b=>b.id===bookingId);if(!booking)return;this.closeBookingMenu();let rowEl=event.currentTarget;this.pointerSession={pointerId:event.pointerId,fromTableId,bookingId,startX:event.clientX,startY:event.clientY,lastX:event.clientX,lastY:event.clientY,dragStarted:false,menuOpened:false,rowEl,label:`${booking.time} ${booking.customer}`,canDetach:!!booking.canDetach,longPressTimer:window.setTimeout(()=>{let s=this.pointerSession;if(!s||s.pointerId!==event.pointerId||s.dragStarted)return;s.menuOpened=true;this.openBookingMenu(s,rowEl)},this.longPressDelay)};rowEl.setPointerCapture?.(event.pointerId);let move=e=>this.onGlobalPointerMove(e),up=e=>this.onGlobalPointerUp(e),cancel=e=>this.onGlobalPointerCancel(e);this.pointerListeners={move,up,cancel};window.addEventListener('pointermove',move,{passive:false});window.addEventListener('pointerup',up);window.addEventListener('pointercancel',cancel)},
    onGlobalPointerMove(event){let s=this.pointerSession;if(!s||event.pointerId!==s.pointerId||s.menuOpened)return;s.lastX=event.clientX;s.lastY=event.clientY;let distance=Math.hypot(event.clientX-s.startX,event.clientY-s.startY);if(!s.dragStarted&&distance<this.dragThreshold)return;this.clearLongPressTimer(s);if(!s.dragStarted){s.dragStarted=true;this.dragging={bookingId:s.bookingId,fromTableId:s.fromTableId};this.suppressClicksUntil=Date.now()+250;this.closeBookingMenu();this.cancelAddTableMode()}event.preventDefault();this.dragPreview={visible:true,x:event.clientX+14,y:event.clientY+14,label:s.label};this.hoverTableId=this.findDropTableIdAtPoint(event.clientX,event.clientY)},
    async onGlobalPointerUp(event){let s=this.pointerSession;if(!s||event.pointerId!==s.pointerId)return;this.clearLongPressTimer(s);this.releasePointerCapture(s,event.pointerId);this.removePointerListeners();let target=s.dragStarted?this.findDropTableIdAtPoint(event.clientX,event.clientY):null,dragged=s.dragStarted,menuOpened=s.menuOpened;this.pointerSession=null;this.dragPreview.visible=false;this.hoverTableId=null;if(menuOpened){this.suppressClicksUntil=Date.now()+250;return}if(!dragged){this.dragging=null;return}this.suppressClicksUntil=Date.now()+250;if(!target||target===s.fromTableId){this.dragging=null;return}if(s.fromTableId===null)await this.assignListBooking(s.bookingId,target);else await this.moveBooking(s.bookingId,s.fromTableId,target);this.dragging=null},
    onGlobalPointerCancel(event){let s=this.pointerSession;if(!s||event.pointerId!==s.pointerId)return;this.clearLongPressTimer(s);this.releasePointerCapture(s,event.pointerId);this.removePointerListeners();this.pointerSession=null;this.dragging=null;this.hoverTableId=null;this.dragPreview.visible=false},
    clearLongPressTimer(s){if(s?.longPressTimer){window.clearTimeout(s.longPressTimer);s.longPressTimer=null}},releasePointerCapture(s,id){let el=s?.rowEl;if(el?.releasePointerCapture&&el.hasPointerCapture?.(id))el.releasePointerCapture(id)},removePointerListeners(){if(!this.pointerListeners)return;window.removeEventListener('pointermove',this.pointerListeners.move);window.removeEventListener('pointerup',this.pointerListeners.up);window.removeEventListener('pointercancel',this.pointerListeners.cancel);this.pointerListeners=null},findDropTableIdAtPoint(x,y){return document.elementFromPoint(x,y)?.closest?.('[data-drop-table-id]')?.dataset.dropTableId||null},
    openBookingMenu(state,rowEl){this.clearLongPressTimer(state);let rect=rowEl.getBoundingClientRect();this.suppressClicksUntil=Date.now()+350;this.bookingMenu={open:true,x:rect.left+rect.width/2,y:rect.top-6,bookingId:state.bookingId,tableId:state.fromTableId,label:state.label,canDetach:state.canDetach}},closeBookingMenu(){this.bookingMenu.open=false},
    beginAddTableMode(){this.addTableMode={active:true,bookingId:this.bookingMenu.bookingId,fromTableId:this.bookingMenu.tableId,label:this.bookingMenu.label};this.closeBookingMenu()},cancelAddTableMode(){this.addTableMode={active:false,bookingId:null,fromTableId:null,label:''}},
    async onTableClick(id){if(!this.addTableMode.active)return;if(id===this.addTableMode.fromTableId)return;let m={...this.addTableMode};if(this.hasBookingOnTable(m.bookingId,id))return;this.cancelAddTableMode();await this.attachBooking(m.bookingId,m.fromTableId,id)},
    async moveBooking(id,from,to){let snapshot=JSON.stringify({tables:this.tables,bookings:this.bookings});this.moveLocal(id,from,to);try{await this.$wire.moveBookingToTable(id,from,to)}catch(e){let state=JSON.parse(snapshot);this.tables=state.tables;this.bookings=state.bookings;throw e}},
    async assignListBooking(id,to){if(this.hasBookingOnTable(id,to))return;let snapshot=JSON.stringify({tables:this.tables,bookings:this.bookings}),booking=this.bookings.find(b=>b.id===id),table=this.tables.find(t=>t.id===to);if(!booking||!table)return;table.bookings.push({...booking});if(!table.allBookings.some(b=>b.id===id))table.allBookings.push({...booking});booking.tableIds=[...(booking.tableIds||[]),to];table.bookings.sort((a,b)=>a.time.localeCompare(b.time));try{await this.$wire.assignBookingToTable(id,to)}catch(e){let state=JSON.parse(snapshot);this.tables=state.tables;this.bookings=state.bookings;throw e}},
    async attachBooking(id,from,to){let snapshot=JSON.stringify({tables:this.tables,bookings:this.bookings}),source=this.tables.find(t=>t.id===from),target=this.tables.find(t=>t.id===to),booking=source?.bookings.find(b=>b.id===id),fullBooking=source?.allBookings.find(b=>b.id===id)||booking,canonical=this.bookings.find(b=>b.id===id);if(booking)target.bookings.push({...booking});if(fullBooking&&!target.allBookings.some(b=>b.id===id))target.allBookings.push({...fullBooking});if(canonical&&!(canonical.tableIds||[]).includes(to))canonical.tableIds=[...(canonical.tableIds||[]),to];try{await this.$wire.attachBookingToTable(id,from,to)}catch(e){let state=JSON.parse(snapshot);this.tables=state.tables;this.bookings=state.bookings;throw e}},
    async detachBookingFromMenu(){let {bookingId:id,tableId}=this.bookingMenu;this.closeBookingMenu();await this.detachBooking(id,tableId)},
    async detachBooking(id,tableId){let snapshot=JSON.stringify({tables:this.tables,bookings:this.bookings}),table=this.tables.find(t=>t.id===tableId),canonical=this.bookings.find(b=>b.id===id);if(!table)return;table.bookings=table.bookings.filter(b=>b.id!==id);table.allBookings=table.allBookings.filter(b=>b.id!==id);if(canonical)canonical.tableIds=(canonical.tableIds||[]).filter(value=>value!==tableId);try{await this.$wire.detachBookingFromTable(id,tableId)}catch(e){let state=JSON.parse(snapshot);this.tables=state.tables;this.bookings=state.bookings;throw e}},
    moveLocal(id,from,to){let a=this.tables.find(t=>t.id===from),b=this.tables.find(t=>t.id===to),i=a?.bookings.findIndex(x=>x.id===id),fullBooking=a?.allBookings.find(x=>x.id===id),canonical=this.bookings.find(x=>x.id===id);if(i>=0){let [booking]=a.bookings.splice(i,1);if(!b.bookings.some(x=>x.id===id))b.bookings.push(booking)}if(fullBooking){a.allBookings=a.allBookings.filter(x=>x.id!==id);if(!b.allBookings.some(x=>x.id===id))b.allBookings.push(fullBooking)}if(canonical)canonical.tableIds=[...(canonical.tableIds||[]).filter(value=>value!==from),to]},hasBookingOnTable(id,tableId){return this.tables.find(t=>t.id===tableId)?.bookings.some(b=>b.id===id)},
    isDraggedBooking(table,id){return this.dragging?.fromTableId===table&&this.dragging?.bookingId===id},tableStyle(t){let s=this.layoutScale;return `width:${Math.round((t.w||64)*s)}px;height:${Math.round((t.h||64)*s)}px;transform:translate(${Math.round(t.x*s)}px,${Math.round(t.y*s)}px) rotate(${t.rotation||0}deg)`},
    roomInnerStyle(){let s=this.layoutScale,right=0,bottom=0;this.tables.forEach(t=>{right=Math.max(right,(t.x+(t.w||64))*s);bottom=Math.max(bottom,(t.y+(t.h||64))*s)});return `min-width:${Math.max(760,Math.ceil(right+150))}px;min-height:${Math.max(560,Math.ceil(bottom+100))}px`},
    relevantTableBookings(t){let ignored=['canceled','denied','no-show','finalized'];return (t.allBookings||[]).filter(b=>!ignored.includes(b.status))},
    tableTimingState(t){let active=this.relevantTableBookings(t);if(active.some(b=>b.status==='seated'))return'seated';if(active.some(b=>b.arrivalTimestamp<=this.nowTimestamp))return'due';let next=active.filter(b=>b.arrivalTimestamp>this.nowTimestamp).sort((a,b)=>a.arrivalTimestamp-b.arrivalTimestamp)[0];if(next&&(next.arrivalTimestamp-this.nowTimestamp)<=3600000)return'arriving';return'free'},
    countdownMinutes(t){if(this.tableTimingState(t)!=='arriving')return null;let next=this.relevantTableBookings(t).filter(b=>b.arrivalTimestamp>this.nowTimestamp).sort((a,b)=>a.arrivalTimestamp-b.arrivalTimestamp)[0];return next?Math.max(1,Math.ceil((next.arrivalTimestamp-this.nowTimestamp)/60000)):null},
    tableCssClasses(t){let c=['table-item',`table-${t.shape}`,`table-${this.tableTimingState(t)}`];if(this.hoverTableId===t.id)c.push('table-drop-hover');if(this.addTableMode.active&&!this.hasBookingOnTable(this.addTableMode.bookingId,t.id))c.push('table-add-mode-target');return c.join(' ')},
    cancelInteractiveModes(){this.closeBookingMenu();this.cancelAddTableMode();this.closeTableModal()}
}}
</script>
