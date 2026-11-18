@props(['title', 'icon' => 'la-chair', 'summary' => null, 'sidebarOpen' => true])
<div class="service-workspace" x-ref="serviceWorkspace"
     x-data="{
         controlsOpen: false, isFullscreen: false, sidebarOpen: @js($sidebarOpen),
         syncFullscreen() { this.isFullscreen = (document.fullscreenElement || document.webkitFullscreenElement) === this.$refs.serviceWorkspace; },
         async toggleFullscreen() {
             try {
                 if (document.fullscreenElement || document.webkitFullscreenElement) {
                     if (document.exitFullscreen) await document.exitFullscreen();
                     else document.webkitExitFullscreen?.();
                 } else {
                     const widget = this.$refs.serviceWorkspace;
                     if (widget.requestFullscreen) await widget.requestFullscreen();
                     else widget.webkitRequestFullscreen?.();
                 }
             } catch (error) { console.warn('Fullscreen non disponibile', error); }
         }
     }"
     @fullscreenchange.document="syncFullscreen()" @webkitfullscreenchange.document="syncFullscreen()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 page-header-breadcrumb">
        <h5 class="page-title text-[1.3125rem] font-medium"><i class="las {{ $icon }}" aria-hidden="true"></i> {{ $title }}</h5>
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions ?? '' }}
            <button type="button" wire:click="$refresh" class="ti-btn ti-btn-warning-full ti-btn-icon text-white !m-0" title="{{ __('cash_desk.reconciliation.refresh') }}" aria-label="{{ __('cash_desk.reconciliation.refresh') }}"><i class="las la-redo-alt text-xl" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="box service-controls-box"><div class="box-body">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-4 text-sm font-semibold">{{ $stats ?? '' }}</div>
            <div class="inline-flex items-center gap-3">
                @isset($controls)
                    <button type="button" class="inline-flex items-center gap-2 text-primary font-semibold" @click="controlsOpen = !controlsOpen" :aria-expanded="controlsOpen.toString()" aria-controls="service-controls-{{ md5($title) }}">
                        @if($summary)<span>{{ $summary }}</span><span aria-hidden="true">—</span>@endif
                        <span x-text="controlsOpen ? @js(__('bookings.collapse')) : @js(__('bookings.expand'))"></span>
                        <i class="las" :class="controlsOpen ? 'la-angle-up' : 'la-angle-down'" aria-hidden="true"></i>
                    </button>
                @endisset
                <button type="button" class="ti-btn ti-btn-light ti-btn-icon !m-0" @click="toggleFullscreen()" title="{{ __('bookings.fullscreen') }}" :aria-pressed="isFullscreen.toString()" :title="isFullscreen ? @js(__('bookings.exit_fullscreen')) : @js(__('bookings.fullscreen'))">
                    <i class="las text-xl" :class="isFullscreen ? 'la-compress' : 'la-expand'" aria-hidden="true"></i>
                    <span class="sr-only" x-text="isFullscreen ? @js(__('bookings.exit_fullscreen')) : @js(__('bookings.fullscreen'))">{{ __('bookings.fullscreen') }}</span>
                </button>
            </div>
        </div>
        @isset($controls)
            <div id="service-controls-{{ md5($title) }}" x-show="controlsOpen" x-cloak x-transition class="pt-4 mt-2 border-t dark:border-white/10">{{ $controls }}</div>
        @endisset
    </div></div>
    <div class="box service-content-box">
        {{ $tabs ?? '' }}
        <div class="box-body service-content-body">{{ $slot }}</div>
    </div>
</div>
