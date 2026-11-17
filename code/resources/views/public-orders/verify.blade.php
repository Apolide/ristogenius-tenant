@component('layouts.public-service', ['title' => __('Conferma accesso'), 'languageUrls' => $languageUrls])
<h1>{{ __('Inizia il tuo ordine') }}</h1><p>{{ __('Conferma l’accesso per aprire il menu e comporre il tuo ordine.') }}</p>@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif<form method="POST" action="{{ route('public-orders.redeem',$token) }}">@csrf<button type="submit" class="ti-btn ti-btn-primary-full">{{ __('Conferma e apri il menu') }}</button></form>
@endcomponent
