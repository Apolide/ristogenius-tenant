@component('layouts.customer-booking',['title'=>$assignment->snapshot['translations'][$language]['title'], 'fullWidth'=>true])
@php
    $languageUrls = collect($assignment->snapshot['enabled_languages'])->mapWithKeys(fn ($code) => [$code => $assignment->url($code)])->all();
    $form = \App\Models\MarketingForm::find($assignment->snapshot['marketing_form_id'] ?? null);
@endphp
<x-public.page :language="$language" :language-urls="$languageUrls" :form="$form">
<h1 class="text-2xl font-bold">{{ __('marketing.thanks') }}</h1><p>{{ __('marketing.assigned_text') }}</p>
@if($assignment->revoked_at)<p class="alert alert-danger">{{ __('marketing.revoked') }}</p>@else @if($bookingUrl=$assignment->bookingUrl($language))<a class="ti-btn ti-btn-primary-full" style="color:var(--tenant-form-button-text);background-color:var(--tenant-form-button-background);text-decoration:none" href="{{ $bookingUrl }}">{{ __('marketing.book_coupon') }}</a>@endif @endif
<h2>{{ $assignment->snapshot['translations'][$language]['title'] }}</h2><div class="prose max-w-none">{!! $assignment->snapshot['translations'][$language]['description'] !!}</div><div class="prose max-w-none">{!! $assignment->snapshot['translations'][$language]['terms'] !!}</div>
<p>{{ __('marketing.uses') }}: {{ $assignment->uses_count }} / {{ $assignment->snapshot['rules']['max_uses'] ?? __('marketing.unlimited') }}</p>
<p>{{ __('marketing.valid_from') }}: {{ substr($assignment->snapshot['valid_from'] ?? '',0,10) ?: '—' }} · {{ __('marketing.valid_until') }}: {{ substr($assignment->snapshot['valid_until'] ?? '',0,10) ?: '—' }}</p>
<img class="max-w-full" src="{{ route('coupons.image',['language'=>$language,'token'=>$assignment->access_token]) }}" alt="{{ __('marketing.coupon') }}">
<a class="ti-btn ti-btn-light" href="{{ route('coupons.pdf',['language'=>$language,'token'=>$assignment->access_token]) }}">{{ __('marketing.download') }}</a><p>{{ __('marketing.personal_notice') }}</p>
</x-public.page>
@endcomponent
