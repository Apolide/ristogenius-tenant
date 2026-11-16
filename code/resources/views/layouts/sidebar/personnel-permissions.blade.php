@php($permissionService = app(\App\Services\Personnel\PersonnelPermissionsService::class))

<ul class="main-menu" style="margin-left: 0; margin-right: 0;">
    @if ($permissionService->can(auth()->user(), \App\Services\Personnel\PersonnelPermissionsService::BOOKINGS))
        <li class="slide__category"><span class="category-name">{{ __('sidebar.categories.bookings') }}</span></li>
        <li class="slide">
            <a href="{{ route('bookings.index') }}" class="side-menu__item">
                <i class="ti ti-calendar side-menu__icon"></i>
                <span class="side-menu__label">{{ __('sidebar.bookings') }}</span>
            </a>
        </li>
    @endif

    @if ($permissionService->can(auth()->user(), \App\Services\Personnel\PersonnelPermissionsService::CUSTOMERS))
        <li class="slide__category"><span class="category-name">{{ __('sidebar.categories.management') }}</span></li>
        <li class="slide">
            <a href="{{ route('customers.index') }}" class="side-menu__item">
                <i class="ti ti-users side-menu__icon"></i>
                <span class="side-menu__label">{{ __('sidebar.customers') }}</span>
            </a>
        </li>
    @endif

    @if ($permissionService->can(auth()->user(), \App\Services\Personnel\PersonnelPermissionsService::MARKETING))
        <li class="slide__category"><span class="category-name">{{ __('sidebar.categories.marketing') }}</span></li>
        <li class="slide">
            <a href="/manage/contacts" class="side-menu__item">
                <i class="ti ti-address-book side-menu__icon"></i>
                <span class="side-menu__label">{{ __('sidebar.contacts') }}</span>
            </a>
        </li>
    @endif
    @foreach([
        [\App\Services\Personnel\PersonnelPermissionsService::CUSTOMER_ORDERS, 'customers-orders.index', 'ri-shopping-bag-line', 'personnel.features.customer_orders'],
        [\App\Services\Personnel\PersonnelPermissionsService::ORDERS, 'orders.index', 'ri-file-list-3-line', 'personnel.features.orders'],
        [\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER, 'cash-desk.index', 'ri-wallet-line', 'personnel.features.cash_register'],
        [\App\Services\Personnel\PersonnelPermissionsService::KDS, 'kds.index', 'ri-restaurant-line', 'personnel.features.kds'],
    ] as [$permission, $destination, $icon, $label])
        @if($permissionService->can(auth()->user(), $permission))
        <li class="slide"><a href="{{ route($destination) }}" class="side-menu__item"><i class="{{ $icon }} side-menu__icon"></i><span class="side-menu__label">{{ __($label) }}</span></a></li>
        @endif
    @endforeach
</ul>
