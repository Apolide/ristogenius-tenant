@props(['language', 'languageUrls' => [], 'form' => null, 'languageSelectId' => 'service-language'])
@php
    $branding = app(\App\Services\TenantBrandingService::class)->branding();
    $styles = app(\App\Services\MarketingForms\FormStyleService::class);
    $formStyleVariables = $styles->cssVariables($form ? $styles->for($form) : $styles->defaults());
    $backgroundUrl = $form ? app(\App\Services\MarketingForms\FormPublicAssetService::class)->backgroundUrl($form) : null;
    $languageUrls = array_intersect_key($languageUrls, app(\App\Services\CustomerLanguageService::class)->enabled());
@endphp
<div class="tenant-public-form-page" style="{{ $formStyleVariables }}; @if($backgroundUrl) --tenant-form-background-image: url('{{ $backgroundUrl }}'); @endif">
    <div class="tenant-public-form-overlay" aria-hidden="true"></div>
    @include('components.public.header')
    <div class="tenant-public-form-content">
        <section class="tenant-public-form-card space-y-5">{{ $slot }}</section>
    </div>
    @include('components.public.footer')
</div>
