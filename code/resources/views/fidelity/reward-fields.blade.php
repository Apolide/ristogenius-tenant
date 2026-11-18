<form method="post" action="{{ $reward ? route('fidelity.manage.rewards.update', $reward) : route('fidelity.manage.rewards.create') }}" class="mt-4 space-y-3" x-data="{ kind: @js($reward?->product_id ? 'product' : 'discount') }">
    @csrf
    @if($reward) @method('PUT') @endif
    <div class="grid gap-3 md:grid-cols-2">
        <label class="flex flex-col">
            <span class="form-label">{{ __('fidelity.reward_name') }}</span>
            <input class="form-control" name="name" maxlength="100" value="{{ $reward?->name }}" required>
        </label>
        <label class="flex flex-col">
            <span class="form-label">{{ __('fidelity.reward_points') }}</span>
            <input class="form-control" type="number" name="points" min="1" max="1000000" value="{{ $reward?->points }}" required>
        </label>
        <label class="flex flex-col">
            <span class="form-label">{{ __('fidelity.reward_type') }}</span>
            <select name="kind" class="form-control" x-model="kind">
                <option value="discount" @selected(!$reward?->product_id)>{{ __('fidelity.discount') }}</option>
                <option value="product" @selected($reward?->product_id)>{{ __('fidelity.product') }}</option>
            </select>
        </label>
        <label class="flex flex-col" x-show="kind === 'discount'">
            <span class="form-label">{{ __('fidelity.amount') }}</span>
            <input class="form-control" name="amount" type="number" min="0.01" step="0.01" value="{{ $reward?->discount_cents ? number_format($reward->discount_cents / 100, 2, '.', '') : '' }}">
        </label>
        <label class="flex flex-col" x-show="kind === 'product'">
            <span class="form-label">{{ __('fidelity.product') }}</span>
            <select name="product_id" class="form-control">
                <option value="">—</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected($reward?->product_id === $product->id)>{{ $product->translatedName() }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <div class="flex items-center gap-3">
        <button type="submit" class="ti-btn ti-btn-primary-full">{{ __('fidelity.save') }}</button>
        @if($reward)
            <button type="submit" form="delete-fidelity-reward-{{ $reward->id }}" class="ti-btn ti-btn-danger">{{ __('fidelity.delete') }}</button>
        @endif
    </div>
</form>
@if($reward)
    <form id="delete-fidelity-reward-{{ $reward->id }}" method="post" action="{{ route('fidelity.manage.rewards.delete', $reward) }}">
        @csrf
        @method('DELETE')
    </form>
@endif
