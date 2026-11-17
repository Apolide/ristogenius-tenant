<div class="content">
    <div class="main-content">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h5 class="page-title">{{ __('polls.responses') }} · {{ $poll->label }}</h5>
            <a href="{{ route('polls.index') }}" class="ti-btn ti-btn-light">{{ __('polls.back') }}</a>
        </div>
        <div class="box">
            <div class="box-body space-y-3">
                @forelse ($responses as $response)
                    <details class="rounded-lg border border-defaultborder">
                        <summary class="cursor-pointer p-4 font-medium">
                            {{ $response->submitted_at->locale(app()->getLocale())->isoFormat('L LT') }}
                            · {{ strtoupper($response->language) }}
                            @if ($response->rating !== null) · {{ $response->rating }} / 5 @endif
                        </summary>
                        <dl class="space-y-3 border-t border-defaultborder p-4">
                            @foreach ($response->snapshot['questions'] as $id => $question)
                                @php($answer = $response->answers['values'][$id] ?? null)
                                @php($comment = $response->answers['comments'][$id] ?? null)
                                <div>
                                    <dt class="font-semibold">{{ $question['label'] }}</dt>
                                    <dd class="whitespace-pre-wrap break-words">@if ($answer === null)—@elseif ($question['type'] === 'checkbox'){{ implode(', ', array_map(fn ($option) => $question['choices'][$option], $answer)) }}@elseif ($question['type'] === 'choice'){{ $question['choices'][$answer] }}@elseif ($question['type'] === 'yes_no'){{ __('polls.'.$answer) }}@else{{ $answer }}@endif</dd>
                                    @if ($comment !== null)
                                        <dt class="mt-1 font-semibold">{{ $question['comment'] }}</dt>
                                        <dd class="whitespace-pre-wrap break-words">{{ $comment }}</dd>
                                    @endif
                                </div>
                            @endforeach
                        </dl>
                    </details>
                @empty
                    <p>{{ __('polls.no_responses') }}</p>
                @endforelse
                {{ $responses->links() }}
            </div>
        </div>
    </div>
</div>
