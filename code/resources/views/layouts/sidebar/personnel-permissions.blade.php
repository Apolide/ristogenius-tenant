@php($permissionService = app(\App\Services\Personnel\PersonnelPermissionsService::class))

<ul class="main-menu" style="margin-left: 0; margin-right: 0;">
    @if ($permissionService->can(auth()->user(), \App\Services\Personnel\PersonnelPermissionsService::BOOKINGS))
        <li class="slide__category"><span class="category-name">Prenotazioni</span></li>
        <li class="slide">
            <a href="{{ route('bookings.index') }}" class="side-menu__item">
                <i class="ti ti-calendar side-menu__icon"></i>
                <span class="side-menu__label">Prenotazioni</span>
            </a>
        </li>
    @endif

    @if ($permissionService->can(auth()->user(), \App\Services\Personnel\PersonnelPermissionsService::CUSTOMERS))
        <li class="slide__category"><span class="category-name">Gestione</span></li>
        <li class="slide">
            <a href="{{ route('customers.index') }}" class="side-menu__item">
                <i class="ti ti-users side-menu__icon"></i>
                <span class="side-menu__label">Clienti</span>
            </a>
        </li>
    @endif

    @if ($permissionService->can(auth()->user(), \App\Services\Personnel\PersonnelPermissionsService::MARKETING))
        <li class="slide__category"><span class="category-name">Marketing</span></li>
        <li class="slide">
            <a href="/manage/contacts" class="side-menu__item">
                <i class="ti ti-address-book side-menu__icon"></i>
                <span class="side-menu__label">Contatti</span>
            </a>
        </li>
    @endif
</ul>
