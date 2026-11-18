@component('layouts.public-service', ['title' => __('fidelity.confirm_title'), 'language' => $language])
    <h1 class="text-2xl font-semibold">{{ __('fidelity.confirm_title') }}</h1>
    <form method="post" action="{{ $url }}">
        @csrf
        <button class="ti-btn ti-btn-primary-full" type="submit">{{ __('fidelity.activate') }}</button>
    </form>
@endcomponent
