@extends('layouts.app')

@section('content')
<div class="content"><div class="main-content">
    <h1 class="page-title mb-5">{{ __('fidelity.title') }}</h1>
    @include('marketing.errors')
    @if(session('fidelity_saved'))
        <p class="alert alert-success" role="status">{{ __('fidelity.saved') }}</p>
    @endif
    <section class="box">
        <div class="box-header"><h2 class="box-title">{{ __('fidelity.configuration') }}</h2></div>
        <div class="box-body space-y-4">
            <a class="text-primary underline" target="_blank" rel="noopener noreferrer" href="{{ route('fidelity.subscribe') }}">{{ route('fidelity.subscribe') }}</a>
            <form method="post" action="{{ route('fidelity.manage.update') }}" class="space-y-4">
                @csrf
                <label class="flex items-center gap-2"><input type="checkbox" class="ti-switch" name="active" value="1" @checked(old('active', $program->active))> {{ __('fidelity.active') }}</label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex flex-col">
                        <span class="form-label">{{ __('fidelity.starting_points') }}</span>
                        <input class="form-control" type="number" name="starting_points" min="0" max="1000000" value="{{ old('starting_points', $program->starting_points) }}" required>
                    </label>
                    <label class="flex flex-col">
                        <span class="form-label">{{ __('fidelity.rate') }}</span>
                        <input class="form-control" type="number" step="0.01" name="rate" min="0" max="1000" value="{{ old('rate', number_format($program->rate / 100, 2, '.', '')) }}" required>
                    </label>
                </div>
                <fieldset class="rounded border border-defaultborder p-4">
                    <legend class="px-1 font-semibold">{{ __('fidelity.channels') }}</legend>
                    <div class="flex flex-wrap gap-4">
                        @foreach(['dine_in', 'pickup', 'delivery'] as $channel)
                            <label class="flex items-center gap-2"><input type="checkbox" class="ti-switch" name="channels[]" value="{{ $channel }}" @checked(in_array($channel, (array) old('channels', $program->channels), true))> {{ __('fidelity.'.$channel) }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <label class="block"><span class="form-label block">{{ __('fidelity.booking_url') }}</span><input class="form-control" type="url" name="booking_url" maxlength="2048" value="{{ old('booking_url', $program->booking_url) }}" aria-describedby="fidelity-booking-url-help"></label>
                <p id="fidelity-booking-url-help" class="text-sm text-textmuted">{{ __('fidelity.booking_url_help') }}</p>
                <details open class="rounded border border-defaultborder p-4">
                    <summary class="font-semibold">{{ __('fidelity.copy') }}</summary>
                    <p class="my-3 text-textmuted">{{ __('fidelity.variables') }} <code>{{ implode(' · ', \App\Services\Fidelity\FidelityMail::PLACEHOLDERS) }}</code></p>
                    @php
                        $languageCodes = array_keys($languages);
                        $activeLanguage = collect($languageCodes)->first(fn ($code) => $errors->has('copy.'.$code) || $errors->has('copy.'.$code.'.*')) ?? $defaultLanguage;
                    @endphp
                    <div class="space-y-4" x-data="{ language: @js($activeLanguage) }">
                        @if(count($languages) > 1)
                            <div class="overflow-x-auto">
                                <div role="tablist" aria-label="{{ __('marketing.language') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                                    @foreach($languages as $code => $meta)
                                        <button type="button" role="tab" id="fidelity-tab-{{ $code }}" aria-controls="fidelity-panel-{{ $code }}"
                                            :aria-selected="language === '{{ $code }}'" :tabindex="language === '{{ $code }}' ? 0 : -1"
                                            @click="language = '{{ $code }}'"
                                            @keydown.arrow-left.prevent="language = '{{ $languageCodes[($loop->index + $loop->count - 1) % $loop->count] }}'; $nextTick(() => document.getElementById('fidelity-tab-' + language).focus())"
                                            @keydown.arrow-right.prevent="language = '{{ $languageCodes[($loop->index + 1) % $loop->count] }}'; $nextTick(() => document.getElementById('fidelity-tab-' + language).focus())"
                                            class="ti-btn !mb-0 inline-flex items-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                            :class="language === '{{ $code }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                            <span aria-hidden="true">{{ $meta['flag'] }}</span>{{ $meta['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @foreach($languages as $code => $meta)
                            <section x-show="language === '{{ $code }}'" @if($code !== $activeLanguage) x-cloak @endif
                                @if(count($languages) > 1) role="tabpanel" id="fidelity-panel-{{ $code }}" aria-labelledby="fidelity-tab-{{ $code }}" @endif
                                class="space-y-4">
                                @foreach($copyFields as $field)
                                    @php
                                        $key = 'copy.'.$code.'.'.$field;
                                        $value = \App\Services\Fidelity\FidelityMail::canonicalPlaceholders((string) old($key, $program->copyFor($field, $code)));
                                        $placeholder = __('fidelity.'.($field === 'form' ? 'intro' : $field), [], $code);
                                    @endphp
                                    <label class="block">
                                        <span class="form-label block">{{ __('fidelity.label_'.$field) }}</span>
                                        @if(str_ends_with($field, '_subject'))
                                            <input class="form-control" name="copy[{{ $code }}][{{ $field }}]" maxlength="160" value="{{ $value }}" placeholder="{{ $placeholder }}">
                                        @else
                                            <textarea class="form-control" name="copy[{{ $code }}][{{ $field }}]" maxlength="4000" rows="3" placeholder="{{ $placeholder }}">{{ $value }}</textarea>
                                        @endif
                                    </label>
                                    @error($key)<p class="text-danger">{{ $message }}</p>@enderror
                                @endforeach
                            </section>
                        @endforeach
                    </div>
                </details>
                <button type="submit" class="ti-btn ti-btn-primary-full">{{ __('fidelity.save') }}</button>
            </form>
        </div>
    </section>
    <section class="box">
        <div class="box-header"><h2 class="box-title">{{ __('fidelity.rewards') }}</h2></div>
        <div class="box-body space-y-4">
            @foreach($rewards as $reward)
                <details class="rounded border border-defaultborder p-4">
                    <summary><strong>{{ $reward->name }}</strong> · {{ __('fidelity.cost', ['points' => $reward->points]) }}</summary>
                    @include('fidelity.reward-fields', ['reward' => $reward, 'products' => $products])
                </details>
            @endforeach
            <details class="rounded border border-defaultborder p-4" @if($errors->has('kind') || $errors->has('points') || $errors->has('name') || $errors->has('amount') || $errors->has('product_id')) open @endif>
                <summary>{{ __('fidelity.add_reward') }}</summary>
                @include('fidelity.reward-fields', ['reward' => null, 'products' => $products])
            </details>
        </div>
    </section>
</div></div>
@endsection
