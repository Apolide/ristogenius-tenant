<div class="space-y-4">
    @if($alreadyAssigned)
        <p>{{ __('marketing.assigned_text') }}</p>
        <a class="ti-btn ti-btn-primary-full" style="color:var(--tenant-form-button-text);background-color:var(--tenant-form-button-background);text-decoration:none" href="{{ $alreadyAssigned->url($language) }}">{{ __('marketing.view_coupon') }}</a>
    @else
        <p>{{ __('marketing.profile_greeting', ['name' => $answers['name'] ?? '']) }}</p>
        <p>{{ __('marketing.complete_profile_help') }}</p>
        <form wire:submit="complete" class="space-y-4">
            @include('livewire.marketing.coupons.partials.claim-fields')
            @error('identity')<p class="text-danger">{{ $message }}</p>@enderror
            @error('coupon')<p class="text-danger">{{ $message }}</p>@enderror
            <button type="submit" class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">{{ __('marketing.confirm_claim') }}</button>
        </form>
    @endif
</div>
