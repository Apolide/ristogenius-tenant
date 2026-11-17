@php($languageUrls = collect(app(\App\Services\CustomerLanguageService::class)->enabled())->mapWithKeys(fn ($meta, $code) => [$code => route('public-orders.table', ['language' => $code, 'tableToken' => $tableToken])])->all())
@component('layouts.public-service', ['title' => __('Scegli il menu'), 'language' => $language, 'languageUrls' => $languageUrls])
<h1>{{ __('Scegli il menu') }}</h1>@forelse($menus as $menu)<p><a href="{{ route('public-orders.menu',['language'=>$language,'menu'=>$menu,'table'=>$tableToken]) }}">{{ $menu->translatedName($language) }}</a></p>@empty<p>{{ __('Nessun menu disponibile.') }}</p>@endforelse
@endcomponent
