@guest
    @include('layouts.footer.guest')
@else
    @if (auth()->user()->hasRole('admin'))
        @include('layouts.footer.admin')
    @elseif (auth()->user()->hasRole('manager'))
        @include('layouts.footer.manager')
    @elseif (auth()->user()->hasAnyRole(['operator', 'kiosk']))
        @include('layouts.footer.operator')
    @else
        @include('layouts.footer.guest')
    @endif
@endguest
