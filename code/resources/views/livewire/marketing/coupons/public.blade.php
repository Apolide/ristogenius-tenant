@php($languageUrls = collect($coupon->enabled_languages)->mapWithKeys(fn ($code) => [$code => route('coupons.public', ['language' => $code, 'coupon' => $coupon->slug])])->all())
<x-public.page :language="$language" :language-urls="$languageUrls" :form="$coupon->form">
<h1 class="text-2xl font-bold">{{ $coupon->title($language) }}</h1>
@if($coupon->image_path)<img src="{{ asset($coupon->image_path) }}" alt="{{ $coupon->title($language) }}" class="max-w-full rounded">@endif
<div class="prose max-w-none">{!! $coupon->translations[$language]['description'] ?? '' !!}</div><div class="prose max-w-none">{!! $coupon->translations[$language]['terms'] ?? '' !!}</div>
@include('marketing.errors')
@if($sent)<p role="status" class="alert alert-success">{{ __('marketing.check_email') }}</p>@else
<form wire:submit="submit" class="space-y-4">
<p>{{ __('marketing.request_step_help') }}</p>
<label class="block">{{ __('marketing.field_name') }} *<input class="form-control" wire:model="answers.name" required maxlength="255" autocomplete="name"></label>
@error('answers.name')<p class="text-danger">{{ $message }}</p>@enderror
<label class="block">{{ __('marketing.field_email') }} *<input class="form-control" type="email" wire:model="answers.email" required maxlength="254" autocomplete="email"></label>
@error('answers.email')<p class="text-danger">{{ $message }}</p>@enderror
<label class="block">{{ __('marketing.field_phone') }}<input class="form-control" type="tel" wire:model="answers.phone" maxlength="25" autocomplete="tel"></label>
@error('answers.phone')<p class="text-danger">{{ $message }}</p>@enderror
<label class="block"><input type="checkbox" wire:model="answers.privacy_consent" required> {{ __('marketing.field_privacy_consent') }} <a class="underline" href="{{ route('privacy-policy', ['language' => $language]) }}" target="_blank" rel="noopener">{{ __('marketing_forms.privacy_policy') }}</a></label>
@error('answers.privacy_consent')<p class="text-danger">{{ $message }}</p>@enderror
<button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">{{ __('marketing.claim') }}</button>
<p>{{ __('marketing.proof_notice') }}</p>
</form>@endif
</x-public.page>
