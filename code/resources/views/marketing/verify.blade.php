@component('layouts.customer-booking', ['title' => __('marketing.verify_subject'), 'fullWidth' => true])
@php($languageUrls = collect($claim->coupon->enabled_languages)->mapWithKeys(fn ($code) => [$code => route('coupons.verify', ['language' => $code, 'token' => $token])])->all())
<x-public.page :language="$language" :language-urls="$languageUrls" :form="$claim->coupon->form">
    <h1 class="text-2xl font-semibold">{{ __('marketing.verify_subject') }}</h1>
    <h2 class="text-xl font-semibold">{{ $claim->coupon->title($language) }}</h2>
    <p>{{ __('marketing.verify_page_intro', ['restaurant' => $branding['name']]) }}</p>
    <dl class="rounded border border-defaultborder p-4 space-y-2">
        @foreach(app(\App\Services\Marketing\CouponPresentation::class)->summary($claim->coupon->snapshot(), $language) as $label => $value)
            <div><dt class="font-semibold">{{ $label }}</dt><dd>{{ $value }}</dd></div>
        @endforeach
    </dl>
    @include('marketing.errors')
    <livewire:marketing.coupons.complete-coupon :token="$token" :language="$language" />
</x-public.page>
@endcomponent
