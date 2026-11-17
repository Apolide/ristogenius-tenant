<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    @include('orders.workspace-styles')
<x-orders.workspace :title="__('Proponi modifica ·').' '.$order->reference" icon="la-edit">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('customers-orders.show', $order) }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Ordine') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('Proponi modifica ·').' '.$order->reference }}</x-slot:stats>

@include('orders.errors')
    <p class="alert alert-info mb-4">{{ __('Il cliente riceverà il riepilogo completo via email e potrà accettare o rifiutare. Nessuna modifica viene applicata all’ordine prima della sua conferma.') }}</p>
    <div class="o-grid">
        <section class="box"><div class="box-header"><h2 class="box-title">{{ __('Orario e comunicazione') }}</h2></div><div class="box-body">
            <div class="service-form-grid mt-3"><label class="o-field"><span>{{ __('Giorno') }}</span><input type="date" class="form-control" wire:model.live="date" min="{{ today()->toDateString() }}"></label><label class="o-field"><span>{{ $order->fulfillment==='delivery' ? __('Orario consegna') : __('Orario ritiro') }}</span><select class="form-control" wire:model.live="time"><option value="">{{ __('Seleziona') }}</option>@foreach($slots as $slot)<option value="{{ $slot }}">{{ $slot }}</option>@endforeach</select></label></div>
            <label class="o-field"><span>{{ __('Note dell’ordine proposto') }}</span><textarea class="form-control w-full" rows="3" maxlength="2000" wire:model.live.debounce.400ms="notes"></textarea></label>
            <label class="o-field"><span>{{ __('Messaggio al cliente / motivo della proposta') }}</span><textarea class="form-control w-full" rows="3" maxlength="4000" wire:model="message" placeholder="{{ __('Ad esempio: proponiamo il ritiro alle 20:30 perché…') }}"></textarea></label>
            <h2 class="font-semibold text-lg mt-5">{{ __('Prodotti proposti') }}</h2>
            <p class="o-muted">{{ __('Per sostituire un prodotto, rimuovilo dalla proposta e aggiungi quello nuovo.') }}</p>
            @foreach($lines as $index=>$line)
                @php($quoted=$priced['lines'][$index] ?? null)
                <div class="o-line" wire:key="proposal-line-{{ $index }}-{{ $line['original_item_id'] ?? $line['product_id'] }}">
                    <div class="o-row"><strong>{{ $quoted['attributes']['product_name'] ?? __('Prodotto') }}</strong><button type="button" class="ti-btn ti-btn-danger" wire:click="removeLine({{ $index }})"><i class="las la-trash" aria-hidden="true"></i> {{ __('Rimuovi') }}</button></div>
                    <label class="o-field"><span>{{ __('Quantità') }}</span><input class="form-control" type="number" min="1" max="100" wire:model.live.debounce.400ms="lines.{{ $index }}.quantity"></label>
                    <label class="o-field"><span>{{ __('Note prodotto') }}</span><textarea class="form-control w-full" rows="2" maxlength="2000" wire:model.live.debounce.400ms="lines.{{ $index }}.notes"></textarea></label>
                    @foreach($quoted['modifiers'] ?? [] as $modifier)<p class="o-muted">{{ $modifier['action']==='add' ? '+' : '−' }} {{ $modifier['ingredient_name'] }}</p>@endforeach
                    @if($line['product_id'])<button type="button" class="ti-btn ti-btn-warning-full mt-2" wire:click="editIngredients({{ $index }})"><i class="las la-list" aria-hidden="true"></i> {{ __('Ingredienti') }}</button>@endif
                </div>
            @endforeach
            <div class="mt-4"><input class="form-control w-full" type="search" aria-label="{{ __('Cerca prodotto') }}" placeholder="{{ __('Cerca un prodotto…') }}" wire:model.live.debounce.300ms="search"><select class="form-control w-full mt-2" wire:model="selectedProduct" aria-label="{{ __('Prodotto da aggiungere') }}"><option value="">{{ __('Seleziona dal catalogo') }}</option>@foreach($choices as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select><button class="ti-btn ti-btn-light mt-2" wire:click="addProduct"><i class="las la-plus" aria-hidden="true"></i> {{ __('Aggiungi prodotto') }}</button></div>
        </div></section>
        <section class="box"><div class="box-header"><h2 class="box-title">{{ __('Riepilogo per il cliente') }}</h2></div><div class="box-body">
            @if($preview)@include('public-orders.order-summary',['summary'=>$preview])@else<p class="o-error mt-4">{{ $previewError }}</p>@endif
            <button class="ti-btn ti-btn-primary-full w-full mt-4" wire:click="sendProposal" wire:confirm="{{ __('Inviare la proposta via email? L’ordine resterà in attesa della risposta del cliente.') }}" wire:loading.attr="disabled" @disabled(!$preview)><i class="las la-paper-plane" aria-hidden="true"></i> {{ __('Invia proposta via email') }}</button>
        </div></section>
    </div>
    @if($ingredientLine!==null && $ingredientProduct)
        <div class="o-modal"><section class="o-dialog"><h2 class="text-xl font-semibold">{{ __('Ingredienti ·') }} {{ $ingredientProduct->translatedName() }}</h2>@include('orders.errors')
            <fieldset class="o-field"><legend class="font-semibold">{{ __('Rimuovi dalla ricetta') }}</legend>@foreach($ingredientProduct->ingredients as $ingredient)<label class="block my-2"><input class="form-check-input" type="checkbox" wire:model="removed" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName() }}</label>@endforeach</fieldset>
            <fieldset class="o-field"><legend class="font-semibold">{{ __('Aggiungi') }}</legend><div style="max-height:230px;overflow:auto">@foreach($ingredients as $ingredient)<label class="block my-2"><input class="form-check-input" type="checkbox" wire:model="added" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName() }} · {{ number_format($ingredient->add_price,2,',','.') }} €</label>@endforeach</div></fieldset>
            <div class="o-row"><button class="ti-btn ti-btn-light" wire:click="$set('ingredientLine',null)"><i class="las la-times" aria-hidden="true"></i> {{ __('Chiudi') }}</button><button class="ti-btn ti-btn-primary-full" wire:click="saveIngredients"><i class="las la-check" aria-hidden="true"></i> {{ __('Salva ingredienti') }}</button></div>
        </section></div>
    @endif
</x-orders.workspace>
</div></div>
