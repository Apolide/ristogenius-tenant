<div role="tablist" aria-label="{{ __('polls.languages') }}" class="flex gap-2 overflow-x-auto rounded-xl border border-defaultborder bg-defaultbackground p-1">
    @foreach ($languages as $language => $meta)
        <button type="button" role="tab" @click="language = @js($language)" :aria-selected="language === @js($language)"
                :class="language === @js($language) ? 'ti-btn-primary-full' : 'ti-btn-light'"
                class="ti-btn !mb-0 shrink-0">
            <x-language-label :meta="$meta" />
        </button>
    @endforeach
</div>
