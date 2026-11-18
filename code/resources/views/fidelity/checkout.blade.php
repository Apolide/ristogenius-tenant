@if($fidelityCard || $fidelityCanAssociate || $order->fidelity_reward)
    <section class="my-4 space-y-3 rounded border border-defaultborder p-4">
        <h2 class="font-semibold">{{ __('fidelity.title') }}</h2>
        @if($fidelityCard)
            <p>{{ $fidelityCard->customer?->display_name ?: $fidelityCard->customer?->firstname }} · {{ __('fidelity.balance', ['points' => max(0, $fidelityCard->points)]) }}</p>
            @if($fidelityPreview !== null)
                <p>{{ __('fidelity.preview', ['points' => $fidelityPreview]) }}</p>
            @endif
        @endif
        @if($order->fidelity_reward)
            <p>{{ $order->fidelity_reward['name'] }} · {{ __('fidelity.cost', ['points' => $order->fidelity_reward['points']]) }}</p>
            @if($fidelityEditable)
                <button type="button" class="ti-btn ti-btn-light" wire:click="removeFidelityReward" wire:loading.attr="disabled">{{ __('fidelity.remove_reward') }}</button>
            @endif
        @elseif($fidelityRewards->isNotEmpty())
            <form wire:submit="applyFidelityReward" class="flex flex-wrap items-end gap-2">
                <label>
                    <span class="form-label">{{ __('fidelity.choose_reward') }}</span>
                    <select class="form-control" wire:model="selectedFidelityReward" required>
                        <option value="">—</option>
                        @foreach($fidelityRewards as $reward)
                            <option value="{{ $reward->id }}">{{ $reward->name }} · {{ __('fidelity.cost', ['points' => $reward->points]) }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="ti-btn ti-btn-primary-full !mb-0" wire:loading.attr="disabled">{{ __('fidelity.apply_reward') }}</button>
            </form>
            @error('selectedFidelityReward') <p class="text-danger">{{ $message }}</p> @enderror
        @endif
        @if($fidelityCanAssociate)
            @if($order->fidelity_card_id)
                <button type="button" class="ti-btn ti-btn-light" wire:click="clearFidelityCustomer" wire:loading.attr="disabled">{{ __('fidelity.clear_customer') }}</button>
            @endif
            <label class="block">
                <span class="form-label">{{ __('fidelity.find_customer') }}</span>
                <input class="form-control" maxlength="150" type="search" wire:model.live.debounce.350ms="fidelitySearch">
            </label>
            @if(mb_strlen(trim($fidelitySearch)) >= 3)
                <ul class="divide-y divide-defaultborder">
                    @forelse($fidelityMatches as $match)
                        <li class="py-2" wire:key="fidelity-match-{{ $match->id }}">
                            <button type="button" class="ti-btn ti-btn-light !mb-0" wire:click="associateFidelityCustomer('{{ $match->id }}')" wire:loading.attr="disabled">
                                {{ $match->display_name ?: trim($match->firstname.' '.$match->lastname) }} · {{ $match->email ?: $match->phone }}
                            </button>
                        </li>
                    @empty
                        <li>{{ __('fidelity.no_matches') }}</li>
                    @endforelse
                </ul>
            @endif
        @endif
    </section>
@endif
