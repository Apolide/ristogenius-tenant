<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    @include('orders.workspace-styles')
<x-orders.workspace :title="__('customer_orders.proposal_editor.propose_change').' '.$order->reference" icon="la-edit">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('customers-orders.show', $order) }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('customer_orders.proposal_editor.order_title') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('customer_orders.proposal_editor.propose_change').' '.$order->reference }}</x-slot:stats>

@include('orders.errors')
    <p class="alert alert-info mb-4">{{ __('customer_orders.proposal_editor.the_customer_will_receive_the_full_summary_by_email_and') }}</p>
    <div class="o-grid">
        <section class="box"><div class="box-header"><h2 class="box-title">{{ __('customer_orders.proposal_editor.time_and_communication') }}</h2></div><div class="box-body">
            <div class="service-form-grid mt-3"><label class="o-field"><span>{{ __('customer_orders.day') }}</span><input type="date" class="form-control" wire:model.live="date" min="{{ today()->toDateString() }}"></label><label class="o-field"><span>{{ $order->fulfillment==='delivery' ? __('customer_orders.proposal_editor.delivery_time') : __('customer_orders.proposal_editor.pickup_time') }}</span><select class="form-control" wire:model.live="time"><option value="">{{ __('customer_orders.proposal_editor.select') }}</option>@foreach($slots as $slot)<option value="{{ $slot }}">{{ $slot }}</option>@endforeach</select></label></div>
            <label class="o-field"><span>{{ __('customer_orders.proposal_editor.proposed_order_notes') }}</span><textarea class="form-control w-full" rows="3" maxlength="2000" wire:model.live.debounce.400ms="notes"></textarea></label>
            <label class="o-field"><span>{{ __('customer_orders.proposal_editor.message_to_customer_reason_for_proposal') }}</span><textarea class="form-control w-full" rows="3" maxlength="4000" wire:model="message" placeholder="{{ __('customer_orders.proposal_editor.for_example_we_suggest_pickup_at_20_30_because') }}"></textarea></label>
            <h2 class="font-semibold text-lg mt-5">{{ __('customer_orders.proposal_editor.proposed_items') }}</h2>
            <p class="o-muted">{{ __('customer_orders.proposal_editor.to_replace_a_product_remove_it_from_the_proposal_and') }}</p>
            @foreach($lines as $index=>$line)
                @php($quoted=$priced['lines'][$index] ?? null)
                <div class="o-line" wire:key="proposal-line-{{ $index }}-{{ $line['original_item_id'] ?? $line['product_id'] }}">
                    <div class="o-row"><strong>{{ $quoted['attributes']['product_name'] ?? __('orders.details.product') }}</strong><button type="button" class="ti-btn ti-btn-danger" wire:click="removeLine({{ $index }})"><i class="las la-trash" aria-hidden="true"></i> {{ __('orders.editor.remove') }}</button></div>
                    <label class="o-field"><span>{{ __('orders.details.quantity') }}</span><input class="form-control" type="number" min="1" max="100" wire:model.live.debounce.400ms="lines.{{ $index }}.quantity"></label>
                    <label class="o-field"><span>{{ __('customer_orders.proposal_editor.product_notes') }}</span><textarea class="form-control w-full" rows="2" maxlength="2000" wire:model.live.debounce.400ms="lines.{{ $index }}.notes"></textarea></label>
                    @foreach($quoted['modifiers'] ?? [] as $modifier)<p class="o-muted"><i @class(['las', 'la-plus' => $modifier['action']==='add', 'la-minus' => !($modifier['action']==='add')]) role="img" aria-label="{{ $modifier['action']==='add' ? __('public_orders.summary.ingredient_added') : __('public_orders.summary.ingredient_removed') }}"></i> {{ $modifier['ingredient_name'] }}</p>@endforeach
                    @if($line['product_id'])<button type="button" class="ti-btn ti-btn-warning-full mt-2" wire:click="editIngredients({{ $index }})"><i class="las la-list" aria-hidden="true"></i> {{ __('customer_orders.proposal_editor.ingredients') }}</button>@endif
                </div>
            @endforeach
            <div class="mt-4"><input class="form-control w-full" type="search" aria-label="{{ __('orders.editor.search_product') }}" placeholder="{{ __('orders.editor.search_for_a_product') }}" wire:model.live.debounce.300ms="search"><select class="form-control w-full mt-2" wire:model="selectedProduct" aria-label="{{ __('customer_orders.proposal_editor.product_to_add') }}"><option value="">{{ __('customer_orders.proposal_editor.select_from_catalog') }}</option>@foreach($choices as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select><button class="ti-btn ti-btn-light mt-2" wire:click="addProduct"><i class="las la-plus" aria-hidden="true"></i> {{ __('customer_orders.proposal_editor.add_product') }}</button></div>
        </div></section>
        <section class="box"><div class="box-header"><h2 class="box-title">{{ __('customer_orders.proposal_editor.customer_summary') }}</h2></div><div class="box-body">
            @if($preview)@include('public-orders.order-summary',['summary'=>$preview])@else<p class="o-error mt-4">{{ $previewError }}</p>@endif
            <button class="ti-btn ti-btn-primary-full w-full mt-4" wire:click="sendProposal" wire:confirm="{{ __('customer_orders.proposal_editor.email_the_proposal_the_order_will_wait_for_the_customers') }}" wire:loading.attr="disabled" @disabled(!$preview)><i class="las la-paper-plane" aria-hidden="true"></i> {{ __('customer_orders.proposal_editor.email_proposal') }}</button>
        </div></section>
    </div>
    @if($ingredientLine!==null && $ingredientProduct)
        <div class="o-modal"><section class="o-dialog"><h2 class="text-xl font-semibold">{{ __('customer_orders.proposal_editor.ingredients_label') }} {{ $ingredientProduct->translatedName() }}</h2>@include('orders.errors')
            <fieldset class="o-field"><legend class="font-semibold">{{ __('customer_orders.proposal_editor.remove_from_recipe') }}</legend>@foreach($ingredientProduct->ingredients as $ingredient)<label class="block my-2"><input class="form-check-input" type="checkbox" wire:model="removed" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName() }}</label>@endforeach</fieldset>
            <fieldset class="o-field"><legend class="font-semibold">{{ __('customer_orders.proposal_editor.add') }}</legend><div style="max-height:230px;overflow:auto">@foreach($ingredients as $ingredient)<label class="block my-2"><input class="form-check-input" type="checkbox" wire:model="added" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName() }} · {{ number_format($ingredient->add_price,2,',','.') }} €</label>@endforeach</div></fieldset>
            <div class="o-row"><button class="ti-btn ti-btn-light" wire:click="$set('ingredientLine',null)"><i class="las la-times" aria-hidden="true"></i> {{ __('customer_orders.proposal_editor.close') }}</button><button class="ti-btn ti-btn-primary-full" wire:click="saveIngredients"><i class="las la-check" aria-hidden="true"></i> {{ __('customer_orders.proposal_editor.save_ingredients') }}</button></div>
        </section></div>
    @endif
</x-orders.workspace>
</div></div>
