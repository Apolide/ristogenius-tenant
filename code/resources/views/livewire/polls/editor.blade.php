<div class="content">
    <div class="main-content">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h5 class="page-title">{{ __($pollId ? 'polls.edit' : 'polls.create') }}</h5>
            <a href="{{ route('polls.index') }}" class="ti-btn ti-btn-light">{{ __('polls.back') }}</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit="save">
            <div class="box">
                <div class="box-header"><h6 class="box-title">{{ __('polls.delivery') }}</h6></div>
                <div class="box-body space-y-4">
                    <label class="block">
                        <span class="mb-1 block font-medium">{{ __('polls.label') }}</span>
                        <input wire:model="label" class="form-control" maxlength="255">
                    </label>
                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="flex items-center gap-3">
                            <input wire:model="enabled" type="checkbox" class="ti-switch">
                            <span>{{ __('polls.enable') }}</span>
                        </label>
                        <label class="block">
                            <span class="mb-1 block font-medium">{{ __('polls.send_after') }}</span>
                            <input wire:model="sendAfterHours" type="number" min="1" max="720" class="form-control">
                        </label>
                    </div>
                    <p class="text-sm text-textmuted">{{ __('polls.delivery_hint') }}</p>
                    <div>
                        <label class="block">
                            <span class="mb-1 block font-medium">{{ __('polls.reward_coupon') }}</span>
                            <select wire:model="rewardCouponId" class="form-control">
                                <option value="">{{ __('polls.no_reward_coupon') }}</option>
                                @foreach ($coupons as $coupon)
                                    <option value="{{ $coupon->id }}">
                                        {{ $coupon->name }} @unless ($coupon->claimable()) ({{ __('polls.coupon_unavailable') }}) @endunless
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <p class="mt-1 text-sm text-textmuted">{{ __('polls.reward_coupon_hint') }}</p>
                    </div>
                    <label class="flex items-center gap-3">
                        <input wire:model.live="reviewEnabled" type="checkbox" class="ti-switch">
                        <span>{{ __('polls.review_enable') }}</span>
                    </label>
                    @if ($reviewEnabled)
                        <p class="text-sm text-textmuted">{{ __('polls.review_hint') }}</p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block font-medium">{{ __('polls.review_min') }}</span>
                                <input type="number" min="1" max="10080" wire:model="reviewMinMinutes" class="form-control">
                            </label>
                            <label class="block">
                                <span class="mb-1 block font-medium">{{ __('polls.review_max') }}</span>
                                <input type="number" min="1" max="10080" wire:model="reviewMaxMinutes" class="form-control">
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="box" x-data="{ language: @js(array_key_first($languages)) }">
                <div class="box-header"><h6 class="box-title">{{ __('polls.content') }}</h6></div>
                <div class="box-body space-y-4">
                    @include('livewire.polls.language-tabs')
                    <div class="flex flex-wrap items-baseline gap-2 text-sm">
                        <span>{{ __('polls.placeholders') }}</span>
                        @foreach ($placeholders as $placeholder)
                            <code class="rounded border border-defaultborder px-2 py-1 select-all">{{ $placeholder }}</code>
                        @endforeach
                    </div>
                    @foreach ($languages as $language => $meta)
                        <section role="tabpanel" aria-label="{{ $meta['label'] }}" x-show="language === @js($language)" @style(['display: none' => !$loop->first]) class="space-y-4">
                            @php($fields = ['title', 'description', 'subject', 'message'])
                            @if ($reviewEnabled)
                                @php($fields = [...$fields, 'review_subject', 'review_message'])
                            @endif
                            @foreach ($fields as $field)
                                <label class="block">
                                    <span class="mb-1 block font-medium">{{ __($field === 'title' ? 'polls.title_field' : 'polls.'.$field) }}</span>
                                    @if (in_array($field, ['description', 'message', 'review_message'], true))
                                        <textarea wire:model="translations.{{ $language }}.{{ $field }}" rows="3" class="form-control"></textarea>
                                    @else
                                        <input wire:model="translations.{{ $language }}.{{ $field }}" class="form-control" maxlength="255">
                                    @endif
                                </label>
                                @if ($field === 'review_message')
                                    <p class="text-sm text-textmuted">{{ __('polls.review_link_hint') }}</p>
                                @endif
                                @error("translations.$language.$field") <p class="text-sm text-danger">{{ $message }}</p> @enderror
                            @endforeach
                        </section>
                    @endforeach
                </div>
            </div>

            <div class="box">
                <div class="box-header"><h6 class="box-title">{{ __('polls.questions') }}</h6></div>
                <div class="box-body space-y-3">
                    @foreach ($questions as $index => $question)
                        <details wire:key="question-{{ $question['id'] }}" @if ($loop->last || $errors->has('questions.'.$index.'.*')) open @endif
                                 class="rounded-lg border border-defaultborder" x-data="{ language: @js(array_key_first($languages)) }">
                            <summary class="cursor-pointer p-4 font-semibold">
                                {{ $loop->iteration }}. {{ $question['texts'][array_key_first($languages)]['label'] ?? __('polls.types.'.$question['type']) }}
                            </summary>
                            <div class="space-y-4 border-t border-defaultborder p-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-1 block font-medium">{{ __('polls.type') }}</span>
                                        <select wire:model.live="questions.{{ $index }}.type" class="form-control">
                                            @foreach ($types as $type)
                                                <option value="{{ $type }}">{{ __('polls.types.'.$type) }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="flex items-center gap-3">
                                        <input wire:model="questions.{{ $index }}.required" type="checkbox" class="ti-switch">
                                        {{ __('polls.required') }}
                                    </label>
                                    @if ($question['type'] === 'rating')
                                        <label class="block">
                                            <span class="mb-1 block font-medium">{{ __('polls.threshold') }}</span>
                                            <select wire:model.live="questions.{{ $index }}.threshold" class="form-control">
                                                <option value="">{{ __('polls.no_threshold') }}</option>
                                                @foreach (range(2, 5) as $score)
                                                    <option value="{{ $score }}">{{ __('polls.below', ['score' => $score]) }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                </div>

                                @include('livewire.polls.language-tabs')
                                @foreach ($languages as $language => $meta)
                                    <section role="tabpanel" aria-label="{{ $meta['label'] }}" x-show="language === @js($language)" @style(['display: none' => !$loop->first]) class="space-y-3">
                                        <label class="block">
                                            <span class="mb-1 block font-medium">{{ __('polls.question_label') }}</span>
                                            <input wire:model="questions.{{ $index }}.texts.{{ $language }}.label" class="form-control" maxlength="255">
                                        </label>
                                        @if ($question['type'] === 'rating' && $question['threshold'])
                                            <label class="block">
                                                <span class="mb-1 block font-medium">{{ __('polls.question_comment') }}</span>
                                                <input wire:model="questions.{{ $index }}.texts.{{ $language }}.comment" class="form-control" maxlength="255">
                                            </label>
                                        @endif
                                        @if (in_array($question['type'], ['choice', 'checkbox'], true))
                                            <label class="block">
                                                <span class="mb-1 block font-medium">{{ __('polls.question_options') }}</span>
                                                <textarea wire:model="questions.{{ $index }}.texts.{{ $language }}.options" rows="4" class="form-control"></textarea>
                                            </label>
                                        @endif
                                    </section>
                                @endforeach

                                <div class="flex flex-wrap gap-2">
                                    @unless ($loop->first)
                                        <button type="button" wire:click="moveQuestion({{ $index }}, -1)" class="ti-btn ti-btn-light">{{ __('polls.up') }}</button>
                                    @endunless
                                    @unless ($loop->last)
                                        <button type="button" wire:click="moveQuestion({{ $index }}, 1)" class="ti-btn ti-btn-light">{{ __('polls.down') }}</button>
                                    @endunless
                                    <button type="button" wire:click="removeQuestion({{ $index }})" class="ti-btn ti-btn-danger">{{ __('polls.remove') }}</button>
                                </div>
                            </div>
                        </details>
                    @endforeach
                    @if (count($questions) < 30)
                        <button type="button" wire:click="addQuestion" class="ti-btn ti-btn-light">{{ __('polls.add') }}</button>
                    @endif
                </div>
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="save" class="ti-btn ti-btn-primary-full">{{ __('polls.save') }}</button>
        </form>
    </div>
</div>
