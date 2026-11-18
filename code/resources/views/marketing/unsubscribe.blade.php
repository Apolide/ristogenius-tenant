@php($languageUrls = collect(app(\App\Services\CustomerLanguageService::class)->enabled())->mapWithKeys(fn ($meta, $code) => [$code => route('marketing.unsubscribe', ['language' => $code, 'token' => $token])])->all())
@component('layouts.public-service', ['title' => __('marketing.unsubscribe'), 'language' => $language, 'languageUrls' => $languageUrls])
<div class="space-y-4"><h1>{{ __('marketing.unsubscribe') }}</h1>@if($done)<p role="status">{{ __('marketing.unsubscribed') }}</p>@else<form method="post" action="{{ route('marketing.unsubscribe',compact('language','token')) }}">@csrf<button class="ti-btn ti-btn-primary-full">{{ __('marketing.unsubscribe') }}</button></form>@endif</div>
@endcomponent
