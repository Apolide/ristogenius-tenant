@php($languageMeta = ['it' => ['🇮🇹', 'Italiano'], 'en' => ['🇬🇧', 'English'], 'de' => ['🇩🇪', 'Deutsch']])
<div class="flex flex-wrap gap-4">
    @foreach($languageMeta as $code => [$flag, $label])
        <label class="inline-flex items-center gap-2 font-medium">
            <input type="checkbox" class="form-check-input" data-marketing-language wire:model="data.enabled_languages" value="{{ $code }}">
            <span>{{ $label }}</span>
        </label>
    @endforeach
</div>
<div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
    <nav role="tablist" aria-label="{{ __('marketing.language') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
        @foreach($languageMeta as $code => [$flag, $label])
            <button type="button" role="tab" id="marketing-tab-{{ $code }}" aria-controls="marketing-panel-{{ $code }}"
                    :aria-selected="language === '{{ $code }}'" @click="language = '{{ $code }}'"
                    @keydown.arrow-right.prevent="language = '{{ ['it' => 'en', 'en' => 'de', 'de' => 'it'][$code] }}'; $nextTick(() => document.getElementById('marketing-tab-' + language).focus())"
                    @keydown.arrow-left.prevent="language = '{{ ['it' => 'de', 'en' => 'it', 'de' => 'en'][$code] }}'; $nextTick(() => document.getElementById('marketing-tab-' + language).focus())"
                    class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                    :class="language === '{{ $code }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                <span>{{ $flag }}</span><span>{{ $label }}</span>
            </button>
        @endforeach
    </nav>
</div>
