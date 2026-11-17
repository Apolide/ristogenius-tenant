<nav class="o-toolbar" aria-label="{{ __('Tipo di conto') }}">
    <a class="o-chip {{ request()->routeIs('cash-desk.index') ? 'active' : '' }}" href="{{ route('cash-desk.index') }}">{{ __('Sala') }}</a>
    <a class="o-chip {{ request()->routeIs('cash-desk.customer-orders') ? 'active' : '' }}" href="{{ route('cash-desk.customer-orders') }}">{{ __('Asporto e consegne') }}</a>
    <a class="ti-btn ti-btn-light" href="{{ route('cash-desk.shifts') }}">{{ __('Turni di cassa') }}</a>
    @if(auth()->user()->hasRole('admin'))<a class="ti-btn ti-btn-light" href="{{ route('settings.fiscal') }}">{{ __('Configurazione fiscale') }}</a>@endif
</nav>
