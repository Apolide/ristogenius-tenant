@component('layouts.public-service', ['title' => __('fidelity.title'), 'language' => $language])
    <h1 class="text-2xl font-semibold">{{ __('fidelity.title') }}</h1>
    <p class="text-xl">{{ __('fidelity.balance', ['points' => max(0, $card->points)]) }}</p>

    @unless($program->active)
        <p class="alert alert-info">{{ __('fidelity.inactive') }}</p>
    @endunless

    @if($rewards->isNotEmpty())
        <section class="space-y-3" aria-labelledby="fidelity-rewards">
            <h2 id="fidelity-rewards" class="text-lg font-semibold">{{ __('fidelity.rewards') }}</h2>
            <ul class="divide-y divide-defaultborder">
                @foreach($rewards as $reward)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <div>
                            <strong>{{ $reward->name }}</strong>
                            <p>{{ __('fidelity.cost', ['points' => $reward->points]) }}@if($reward->discount_cents) · {{ number_format($reward->discount_cents / 100, 2, ',', '.') }} €@endif</p>
                        </div>
                        @if($card->points >= $reward->points)
                            <span class="badge bg-success/10 text-success">{{ __('fidelity.available') }}</span>
                        @else
                            <span>{{ __('fidelity.remaining', ['points' => $reward->points - $card->points]) }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($bookingUrl)
        <a class="ti-btn ti-btn-primary-full" href="{{ $bookingUrl }}" rel="noreferrer">{{ __('fidelity.book') }}</a>
    @endif
    <a class="ti-btn ti-btn-light" href="{{ route('fidelity.pdf', ['token' => $card->token]) }}">{{ __('fidelity.download') }}</a>
@endcomponent
