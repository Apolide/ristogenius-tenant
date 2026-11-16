<nav class="o-toolbar" aria-label="Tipo di conto">
    <a class="o-chip {{ request()->routeIs('cash-desk.index') ? 'active' : '' }}" href="{{ route('cash-desk.index') }}">Sala</a>
    <a class="o-chip {{ request()->routeIs('cash-desk.customer-orders') ? 'active' : '' }}" href="{{ route('cash-desk.customer-orders') }}">Asporto e consegne</a>
    <a class="ti-btn ti-btn-light" href="{{ route('cash-desk.shifts') }}">Turni di cassa</a>
</nav>
