@component('layouts.public-service', ['title' => __('fidelity.title'), 'language' => $language, 'languageUrls' => $languageUrls])
    <h1 class="text-2xl font-semibold">{{ __('fidelity.title') }}</h1>
    @include('marketing.errors')
    @if(session('fidelity_sent'))
        <p class="alert alert-success" role="status">{{ __('fidelity.confirm_body') }}</p>
    @else
        <p class="whitespace-pre-line">{{ $intro }}</p>
        @unless($program->active)
            <p class="alert alert-info" role="status">{{ __('fidelity.inactive') }}</p>
        @endunless
        <form method="post" action="{{ route('fidelity.request') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="language" value="{{ $language }}">
            <fieldset class="space-y-4" @disabled(!$program->active)>
                @foreach(['firstname' => ['text', 'given-name', 100], 'lastname' => ['text', 'family-name', 100], 'email' => ['email', 'email', 254]] as $field => [$type, $autocomplete, $max])
                    <label class="block">
                        <span class="form-label">{{ __('fidelity.'.$field) }}</span>
                        <input class="form-control" type="{{ $type }}" name="{{ $field }}" autocomplete="{{ $autocomplete }}" maxlength="{{ $max }}" value="{{ old($field) }}" required>
                    </label>
                @endforeach
                <label class="block">
                    <input type="checkbox" name="privacy" value="1" @checked(old('privacy')) required>
                    {{ __('fidelity.privacy') }}
                    <a class="text-primary underline" target="_blank" rel="noopener noreferrer" href="{{ route('privacy-policy', ['language' => $language]) }}">{{ __('marketing_forms.privacy_policy') }}</a>
                </label>
                <button class="ti-btn ti-btn-primary-full" type="submit">{{ __('fidelity.subscribe') }}</button>
            </fieldset>
        </form>
    @endif
@endcomponent
