@php($codes = array_keys($languageMeta))
<div class="flex flex-wrap gap-4">
    @foreach($languageMeta as $code => $meta)
        <label class="inline-flex items-center gap-2 font-medium">
            <input type="checkbox" class="form-check-input" data-marketing-language wire:model="data.enabled_languages" value="{{ $code }}">
            <x-language-label :meta="$meta" />
        </label>
    @endforeach
</div>
<div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
    <nav role="tablist" aria-label="{{ __('marketing.language') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
        @foreach($languageMeta as $code => $meta)
            <button type="button" role="tab" id="marketing-tab-{{ $code }}" aria-controls="marketing-panel-{{ $code }}"
                    :aria-selected="language === '{{ $code }}'" @click="language = '{{ $code }}'"
                    @keydown.arrow-right.prevent="language = '{{ $codes[($loop->index + 1) % count($codes)] }}'; $nextTick(() => document.getElementById('marketing-tab-' + language).focus())"
                    @keydown.arrow-left.prevent="language = '{{ $codes[($loop->index + count($codes) - 1) % count($codes)] }}'; $nextTick(() => document.getElementById('marketing-tab-' + language).focus())"
                    class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                    :class="language === '{{ $code }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                <x-language-label :meta="$meta" />
            </button>
        @endforeach
    </nav>
</div>
