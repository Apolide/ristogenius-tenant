<nav class="service-tabs" aria-label="{{ __('Navigazione cassa') }}">
    @foreach([
        ['cash-desk.index', 'la-chair', __('Sala')],
        ['cash-desk.customer-orders', 'la-shopping-bag', __('Asporto e consegne')],
        ['cash-desk.shifts', 'la-business-time', __('Turni di cassa')],
        ['cash-desk.history', 'la-history', __('Storico incassi')],
        ['cash-desk.reconciliation', 'la-balance-scale', __('Riconciliazione ed esportazione')],
    ] as [$route, $icon, $label])
        <a class="service-tab {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif><i class="las {{ $icon }}" aria-hidden="true"></i> {{ $label }}</a>
    @endforeach
    @if(auth()->user()->hasRole('admin'))
        <a class="service-tab {{ request()->routeIs('settings.fiscal') ? 'active' : '' }}" href="{{ route('settings.fiscal') }}" @if(request()->routeIs('settings.fiscal')) aria-current="page" @endif><i class="las la-file-invoice-dollar" aria-hidden="true"></i> {{ __('Configurazione fiscale') }}</a>
    @endif
</nav>
