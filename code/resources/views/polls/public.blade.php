@php($texts = $invitation->snapshot['texts'])
@component('layouts.public-service', ['title' => $texts['title'], 'language' => $invitation->language])
    <h1 class="text-2xl font-semibold">{{ $texts['title'] }}</h1>
    @if ($invitation->submitted_at)
        <p class="alert alert-success" role="status">{{ __('polls.thanks') }}</p>
    @else
        @if ($texts['description'] ?? '')
            <p class="whitespace-pre-wrap">{{ $texts['description'] }}</p>
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

        <form method="post" action="{{ request()->getRequestUri() }}" class="space-y-6">
            @csrf
            @foreach ($invitation->snapshot['questions'] as $id => $question)
                @php($field = "answers[$id]")
                @php($answer = old("answers.$id"))
                <fieldset class="space-y-3 rounded-lg border border-defaultborder p-4">
                    <legend class="px-1 font-semibold">{{ $question['label'] }} @if ($question['required']) * @endif</legend>
                    @switch($question['type'])
                        @case('text')
                            <textarea name="{{ $field }}" class="form-control" rows="3" maxlength="3000"
                                      aria-label="{{ $question['label'] }}" @required($question['required'])>{{ is_scalar($answer) ? $answer : '' }}</textarea>
                            @break
                        @case('number')
                            <input type="number" name="{{ $field }}" class="form-control" step="any" min="-1000000000" max="1000000000"
                                   aria-label="{{ $question['label'] }}" value="{{ is_scalar($answer) ? $answer : '' }}" @required($question['required'])>
                            @break
                        @default
                            @php($options = match ($question['type']) {
                                'rating' => array_combine(range(1, 5), range(1, 5)),
                                'yes_no' => ['yes' => __('polls.yes'), 'no' => __('polls.no')],
                                default => $question['choices'],
                            })
                            @php($multiple = $question['type'] === 'checkbox')
                            <div class="flex flex-wrap gap-2">
                                @foreach ($options as $value => $caption)
                                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-defaultborder px-3 py-3">
                                        <input type="{{ $multiple ? 'checkbox' : 'radio' }}" name="{{ $field }}{{ $multiple ? '[]' : '' }}"
                                               value="{{ $value }}" @checked(in_array((string) $value, (array) $answer, false))
                                               @required(!$multiple && $question['required'])
                                               @if ($question['threshold']) data-feedback-score @endif>
                                        <span>{{ $caption }}</span>
                                    </label>
                                @endforeach
                            </div>
                    @endswitch
                    @if ($question['threshold'])
                        <label class="block" data-feedback-threshold="{{ $question['threshold'] }}">
                            <span class="mb-1 block">{{ $question['comment'] }}</span>
                            <textarea name="comments[{{ $id }}]" class="form-control" rows="3" maxlength="3000">{{ is_string(old("comments.$id")) ? old("comments.$id") : '' }}</textarea>
                        </label>
                    @endif
                </fieldset>
            @endforeach
            <button class="ti-btn ti-btn-primary-full" type="submit">{{ __('polls.submit') }}</button>
        </form>
    @endif
    @push('scripts')
        <script>
            document.querySelectorAll('[data-feedback-threshold]').forEach((feedback) => {
                const question = feedback.closest('fieldset');
                const update = () => {
                    const score = question.querySelector('[data-feedback-score]:checked');
                    feedback.hidden = !score || Number(score.value) >= Number(feedback.dataset.feedbackThreshold);
                    feedback.classList.toggle('block', !feedback.hidden);
                    feedback.querySelector('textarea').disabled = feedback.hidden;
                };
                question.addEventListener('change', update);
                update();
            });
        </script>
    @endpush
@endcomponent
