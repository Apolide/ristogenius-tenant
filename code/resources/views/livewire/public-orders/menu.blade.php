@pushOnce('styles', 'public-order-icons')
    <link rel="stylesheet" href="{{ asset('assets/iconfonts/line-awesome/1.3.0/css/line-awesome.min.css') }}">
@endPushOnce
<div class="po-shell">
    @include('public-orders.styles')
    <header class="po-bar">
        <div>
            <strong>{{ $branding['name'] }}</strong>
            @if($access)
                <p class="po-muted">{{ __('public_orders.hello') }} {{ $access->customer->firstname }}! {{ $tableToken ? __('cash_desk.table').$access->order->table_name.' · '.$access->order->room_name : __('public_orders.summary.order_for_pickup_or_delivery') }}</p>
            @elseif($recognizedName)
                <p class="po-muted">{{ __('public_orders.hello') }} {{ $recognizedName }}{{ __('public_orders.menu.verify_your_email_to_start_a_new_order') }}</p>
            @else
                <p class="po-muted">{{ __('public_orders.menu.verify_your_email_to_start_ordering') }}</p>
            @endif
        </div>
        @if($access)
            <div class="po-row"><button class="po-btn po-secondary" wire:click="logout">{{ __('public_orders.menu.log_out') }}</button><button class="po-btn" wire:click="$set('cartOpen',true)">{{ __('public_orders.menu.your_order_label') }} {{ count($cart) }}</button></div>
        @endif
    </header>
    @if(!$access)
        <section class="po-panel" id="order-access">
            <h1 class="po-title">{{ __('public_orders.start_your_order') }}</h1>
            <p class="po-muted">{{ __('public_orders.menu.you_will_receive_an_email_link_after_verification_you_can') }}</p>
            @if($errors->any())<div role="alert" class="po-error">{{ $errors->first() }}</div>@endif
            @if($emailSent)<div role="status" class="po-notice">{{ __('public_orders.menu.we_sent_you_an_access_link_check_your_email') }}</div>@endif
            <form wire:submit="requestAccess">
                <label class="po-field"><span>{{ __('public_orders.menu.name') }}</span><input class="po-input" wire:model="name" autocomplete="given-name" required maxlength="150"></label>
                <div class="po-grid">
                    <label class="po-field"><span>Email</span><input class="po-input" type="email" wire:model="email" autocomplete="email" required maxlength="255"></label>
                    <label class="po-field"><span>{{ __('public_orders.menu.phone') }}</span><input class="po-input" type="tel" wire:model="phone" autocomplete="tel" required placeholder="+39 …"></label>
                </div>
                <label class="po-check"><input type="checkbox" wire:model="privacy" required> {{ __('public_orders.menu.i_have_read_the') }}<a href="{{ route('privacy-policy',['language'=>$language]) }}" target="_blank" rel="noopener">{{ __('public_orders.menu.privacy_policy') }}</a>.</label>
                <button class="po-btn" wire:loading.attr="disabled">{{ __('public_orders.menu.receive_access_link') }}</button>
            </form>
        </section>
    @else
        @if($errors->any() && !$cartOpen && !$productModal)<div class="po-panel po-error" role="alert">{{ $errors->first() }}</div>@endif
        @if($lastCourseId)<div class="po-panel po-notice" role="status">{{ __('public_orders.menu.order_received_find_updates_in_your_orders') }}</div>@endif
    @endif
    @include('livewire.menu.public-menu')
    @if($access)
        <section class="po-history" wire:poll.10s>
            <h2 class="po-title">{{ __('public_orders.menu.your_orders') }}</h2>
            @forelse($submissions as $submission)
                <article class="po-line" wire:key="public-submission-{{ $submission->id }}">
                    <div class="po-row"><strong>{{ $submission->order->reference ?: __('cash_desk.table').$submission->order->table_name.' · '.__('orders.course_number', ['number' => $submission->number]) }}</strong><span>{{ $submission->order->fulfillment === 'dine_in' ? ($submission->rejected_at ? __('public_orders.menu.not_confirmed_by_restaurant') : ($submission->sent_at ? __('public_orders.menu.sent_to_departments') : __('public_orders.menu.received_awaiting_staff'))) : $submission->order->fulfillmentStatusLabel() }}</span></div>
                    <p class="po-muted">{{ $submission->created_at->format('d/m/Y H:i') }}@if($submission->order->requested_at) · {{ $submission->order->fulfillmentLabel() }}: {{ $submission->order->requested_at->format('d/m H:i') }}@endif</p>
                    @if($submission->order->fulfillment !== 'dine_in')<p class="po-muted">{{ $submission->order->status === 'paid' ? __('cash_desk.paid') : ($submission->order->status === 'cancelled' ? __('cash_desk.cancelled') : __('public_orders.summary.unpaid')) }} {{ __('customer_orders.list.cash') }}</p>@endif
                    @if($submission->order->pendingProposal)<p class="po-notice">{{ __('public_orders.menu.the_restaurant_sent_you_a_change_proposal_check_your_email') }}</p>@endif
                    @foreach($submission->items as $item)<p>{{ $item->quantity }} × {{ $item->product_name }} · {{ $item->statusLabel() }}</p>@endforeach
                </article>
            @empty<p class="po-muted">{{ __('public_orders.menu.you_have_not_placed_any_orders_yet') }}</p>@endforelse
        </section>
    @endif
    @if($cartOpen && $access)
        <div class="po-overlay" wire:click.self="$set('cartOpen',false)">
            <section class="po-drawer" role="dialog" aria-modal="true" aria-label="{{ __('public_orders.menu.your_order') }}">
                <div class="po-row"><h2 class="po-title">{{ __('public_orders.menu.your_order') }}</h2><button class="po-btn po-secondary" wire:click="$set('cartOpen',false)">{{ __('customer_orders.proposal_editor.close') }}</button></div>
                @if($errors->any())<div role="alert" class="po-error">{{ $errors->first() }}</div>@endif
                @forelse($cart as $index=>$line)
                    @php($quoted=$quote['lines'][$index] ?? null)
                    <div class="po-line" wire:key="cart-line-{{ $index }}">
                        <div class="po-row"><strong>{{ $line['quantity'] ?? '' }} × {{ $quoted['attributes']['product_name'] ?? __('public_orders.menu.product_unavailable') }}</strong><span>{{ number_format(($quoted ? $quoted['attributes']['unit_price_cents']*$quoted['attributes']['quantity'] : 0)/100,2,',','.') }} €</span></div>
                        @foreach($quoted['modifiers'] ?? [] as $modifier)<p class="po-muted"><i @class(['las', 'la-plus' => $modifier['action']==='add', 'la-minus' => !($modifier['action']==='add')]) role="img" aria-label="{{ $modifier['action']==='add' ? __('public_orders.summary.ingredient_added') : __('public_orders.summary.ingredient_removed') }}"></i> {{ $modifier['ingredient_name'] }} ({{ number_format($modifier['price_cents']/100,2,',','.') }} €)</p>@endforeach
                        @if($line['notes'] ?? '')<p class="po-muted">{{ $line['notes'] }}</p>@endif
                        <div class="po-row" style="margin-top:10px">@if($quoted)<button class="po-btn po-secondary" wire:click="editLine({{ $index }})">{{ __('orders.editor.edit') }}</button>@endif<button class="po-btn po-danger" wire:click="removeLine({{ $index }})">{{ __('orders.editor.remove') }}</button></div>
                    </div>
                @empty<p class="po-muted">{{ __('public_orders.menu.choose_products_from_the_menu') }}</p>@endforelse
                @if($cart)
                    <form wire:submit="submit">
                        @if(!$tableToken)
                            <label class="po-field"><span>{{ __('public_orders.menu.order_type') }}</span><select class="po-input" wire:model.live="type">@if($settings['pickup_enabled'])<option value="pickup">{{ __('public_orders.menu.pickup_at_the_restaurant') }}</option>@endif @if($settings['delivery_enabled'])<option value="delivery">{{ __('public_orders.menu.home_delivery') }}</option>@endif</select></label>
                            <div class="po-grid">
                                <label class="po-field"><span>{{ __('customer_orders.day') }}</span><input class="po-input" type="date" wire:model.live="date" min="{{ today()->toDateString() }}" max="{{ today()->addDays($settings['advance_days'])->toDateString() }}" required></label>
                                <label class="po-field"><span>{{ __('public_orders.menu.time') }}</span><select class="po-input" wire:model="time" required><option value="">{{ __('customer_orders.proposal_editor.select') }}</option>@foreach($slots as $slot)<option value="{{ $slot }}">{{ $slot }}</option>@endforeach</select></label>
                            </div>
                            @if(!$slots)<p class="po-error">{{ __('public_orders.menu.no_times_available_for_the_selected_day_and_service') }}</p>@endif
                            @if($type==='delivery')
                                <label class="po-field"><span>{{ __('public_orders.menu.neighborhood') }}</span><select class="po-input" wire:model="neighborhood" required><option value="">{{ __('public_orders.menu.select_neighborhood') }}</option>@foreach($neighborhoods as $area)<option value="{{ $area->id }}">{{ $area->name }}</option>@endforeach</select></label>
                                <label class="po-field"><span>{{ __('public_orders.menu.full_address_and_house_number') }}</span><input class="po-input" wire:model="address" autocomplete="street-address" maxlength="500" required></label>
                                @if($settings['delivery_minimum_cents'])<p class="po-muted">{{ __('public_orders.menu.minimum_delivery_product_amount') }} {{ number_format($settings['delivery_minimum_cents']/100,2,',','.') }} €</p>@endif
                            @endif
                            <p class="po-muted">{{ __('public_orders.menu.pay_cash_on_pickup_or_delivery') }}</p>
                        @else
                            <p class="po-notice">{{ __('public_orders.menu.order_for_table') }} {{ $access->order->table_name }}{{ __('public_orders.menu.the_cover_charge_is_already_included_in_the_table_bill') }}</p>
                        @endif
                        <label class="po-field"><span>{{ __('public_orders.menu.notes_for_the_restaurant') }}</span><textarea class="po-input" wire:model="orderNotes" maxlength="2000" rows="2"></textarea></label>
                        <div class="po-line"><div class="po-row"><span>{{ __('public_orders.products') }}</span><span>{{ number_format($quote['subtotal']/100,2,',','.') }} €</span></div>@if($tableToken || $type==='delivery')<div class="po-row"><span>{{ $tableToken ? __('public_orders.menu.service').number_format($percentage/100,2,',','.').'%)' : __('cash_desk.delivery') }}</span><span>{{ number_format($fee/100,2,',','.') }} €</span></div>@endif</div>
                        <div class="po-row po-total" style="margin:18px 0"><span>{{ __('public_orders.total') }}</span><span>{{ number_format($displayedTotal/100,2,',','.') }} €</span></div>
                        <button class="po-btn" style="width:100%" wire:loading.attr="disabled">{{ __('public_orders.menu.confirm_order') }}</button>
                    </form>
                @endif
            </section>
        </div>
    @endif
    @if($productModal && $product && $access)
        <div class="po-modal"><form class="po-dialog" wire:submit="addLine" role="dialog" aria-modal="true" aria-label="{{ __('public_orders.menu.customize_product') }}">
            <h2 class="po-title">{{ $product->translatedName($language) }}</h2>
            @if($errors->any())<div class="po-error" role="alert">{{ $errors->first() }}</div>@endif
            <label class="po-field"><span>{{ __('orders.details.quantity') }}</span><input type="number" min="1" max="100" class="po-input" wire:model="quantity" required></label>
            @if($product->ingredients->isNotEmpty())<fieldset><legend><strong>{{ __('customer_orders.proposal_editor.remove_from_recipe') }}</strong></legend>@foreach($product->ingredients as $ingredient)<label class="po-check"><input type="checkbox" wire:model="removed" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName($language) }} · {{ number_format($ingredient->remove_price,2,',','.') }} €</label>@endforeach</fieldset>@endif
            <fieldset style="margin-top:16px"><legend><strong>{{ __('orders.editor.add_ingredients') }}</strong></legend><div style="max-height:190px;overflow:auto">@foreach($ingredients as $ingredient)<label class="po-check"><input type="checkbox" wire:model="added" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName($language) }} · {{ number_format($ingredient->add_price,2,',','.') }} €</label>@endforeach</div></fieldset>
            <label class="po-field"><span>{{ __('public_orders.menu.product_notes') }}</span><textarea class="po-input" wire:model="notes" maxlength="2000" rows="2"></textarea></label>
            <div class="po-row"><button type="button" class="po-btn po-secondary" wire:click="$set('productModal',false)">{{ __('customer_orders.proposal_editor.close') }}</button><button class="po-btn" wire:loading.attr="disabled">@if($editingLine === null)<i class="las la-plus" aria-hidden="true"></i> @endif{{ $editingLine!==null ? __('orders.editor.save') : __('public_orders.menu.add_to_cart') }}</button></div>
        </form></div>
    @endif
</div>
