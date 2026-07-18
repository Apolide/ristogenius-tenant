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
</ul>
