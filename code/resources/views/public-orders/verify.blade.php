@component('layouts.public-service', ['title' => __('public_orders.access.confirm_access'), 'languageUrls' => $languageUrls])
<h1>{{ __('public_orders.start_your_order') }}</h1><p>{{ __('public_orders.access.confirm_access_to_open_the_menu_and_build_your_order') }}</p>@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif<form method="POST" action="{{ route('public-orders.redeem',$token) }}">@csrf<button type="submit" class="ti-btn ti-btn-primary-full">{{ __('public_orders.access.confirm_and_open_menu') }}</button></form>
@endcomponent
