@component('layouts.public-service', ['title' => __('gift_cards.public_title'), 'language' => $language, 'languageUrls' => $languageUrls])
    <h1 class="text-2xl font-semibold">{{ __('gift_cards.public_title') }}</h1>
    @include('marketing.errors')

    @if($confirmation)
        <div class="alert alert-success" role="status">
            <p class="font-semibold">{{ __('gift_cards.request_received') }}</p>
            <p>{{ __('gift_cards.cash_instructions', $confirmation) }}</p>
        </div>
        <a class="ti-btn ti-btn-light" href="{{ route('gift-cards.public', $language) }}">{{ __('gift_cards.new_request') }}</a>
    @else
        <p>{{ __('gift_cards.intro') }}</p>
        <form method="post" action="{{ route('gift-cards.store', $language) }}" class="space-y-5">
            @csrf
            <fieldset class="grid grid-cols-1 gap-3 rounded-lg border border-defaultborder p-4 md:grid-cols-2">
                <legend class="px-1 font-semibold">{{ __('gift_cards.sender') }}</legend>
                @foreach(['sender_firstname' => ['text', 'given-name', 100], 'sender_lastname' => ['text', 'family-name', 100], 'sender_email' => ['email', 'email', 254], 'sender_phone' => ['tel', 'tel', 30]] as $field => [$type, $autocomplete, $max])
                    @include('gift-cards.input', ['required' => true])
                @endforeach
            </fieldset>

            <fieldset class="grid grid-cols-1 gap-3 rounded-lg border border-defaultborder p-4 md:grid-cols-2">
                <legend class="px-1 font-semibold">{{ __('gift_cards.recipient') }}</legend>
                @foreach(['recipient_firstname' => ['text', 'off', 100], 'recipient_lastname' => ['text', 'off', 100], 'recipient_phone' => ['tel', 'off', 30]] as $field => [$type, $autocomplete, $max])
                    @include('gift-cards.input', ['required' => $field !== 'recipient_phone'])
                @endforeach
                <fieldset class="space-y-2 md:col-span-2">
                    <legend class="form-label">{{ __('gift_cards.delivery') }}</legend>
                    @foreach([0 => 'handover', 1 => 'direct'] as $value => $choice)
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="radio" class="form-check-input" name="send_to_recipient" value="{{ $value }}" @checked(in_array(old('send_to_recipient', '0'), [$value, (string) $value], true)) required>
                            {{ __("gift_cards.{$choice}") }}
                        </label>
                    @endforeach
                </fieldset>
                <div class="md:col-span-2">
                    @include('gift-cards.input', ['field' => 'recipient_email', 'type' => 'email', 'autocomplete' => 'off', 'max' => 254, 'required' => false])
                    <p class="mt-1 text-sm">{{ __('gift_cards.email_hint') }}</p>
                </div>
                <label class="block md:col-span-2">
                    <span class="form-label">{{ __('gift_cards.dedication') }}</span>
                    <textarea name="dedication" class="form-control" rows="3" maxlength="500">{{ is_string(old('dedication')) ? old('dedication') : '' }}</textarea>
                </label>
            </fieldset>

            <label class="block">
                <span class="form-label">{{ __('gift_cards.amount_minimum') }}</span>
                <input class="form-control" type="number" name="amount" min="20" max="999999.99" step="0.01"
                       value="{{ is_scalar(old('amount', '20')) ? old('amount', '20') : '' }}" required>
            </label>
            <div>
                <label class="inline-flex items-center gap-2">
                    <input class="form-check-input" type="checkbox" name="privacy" value="1" @checked(old('privacy') === '1') required>
                    {{ __('gift_cards.privacy') }}
                </label>
                <a class="text-primary underline" href="{{ route('privacy-policy', ['language' => $language]) }}" target="_blank" rel="noopener">{{ __('marketing_forms.privacy_policy') }}</a>
            </div>
            <button type="submit" class="ti-btn ti-btn-primary-full">{{ __('gift_cards.submit') }}</button>
        </form>
    @endif
@endcomponent
