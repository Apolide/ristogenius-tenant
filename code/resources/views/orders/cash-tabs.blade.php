<nav class="service-tabs" aria-label="{{ __('cash_desk.cash_desk_navigation') }}">
    @foreach([
        ['cash-desk.index', 'la-chair', __('cash_desk.dining_room')],
        ['cash-desk.customer-orders', 'la-shopping-bag', __('cash_desk.pickup_and_deliveries')],
        ['cash-desk.shifts', 'la-business-time', __('cash_desk.cash_shifts')],
        ['cash-desk.history', 'la-history', __('cash_desk.payment_history')],
        ['cash-desk.reconciliation', 'la-balance-scale', __('cash_desk.reconciliation_and_export')],
    ] as [$route, $icon, $label])
        <a class="service-tab {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif><i class="las {{ $icon }}" aria-hidden="true"></i> {{ $label }}</a>
    @endforeach
    @if(auth()->user()->hasRole('admin'))
        <a class="service-tab {{ request()->routeIs('settings.fiscal') ? 'active' : '' }}" href="{{ route('settings.fiscal') }}" @if(request()->routeIs('settings.fiscal')) aria-current="page" @endif><i class="las la-file-invoice-dollar" aria-hidden="true"></i> {{ __('cash_desk.fiscal_configuration') }}</a>
    @endif
</nav>
