<div class="content order-workspace"><div class="main-content">
    @include('orders.styles')
    <div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('customers-orders.show',$order) }}">← Ordine</a><h1 class="o-title">Proponi modifica · {{ $order->reference }}</h1></div>
    @include('orders.errors')
    <p class="o-panel mb-4">Il cliente riceverà il riepilogo completo via email e potrà accettare o rifiutare. Nessuna modifica viene applicata all’ordine prima della sua conferma.</p>
    <div class="o-grid">
        <section class="o-panel">
            <h2 class="font-semibold text-lg">Orario e comunicazione</h2>
            <div class="o-toolbar mt-3"><label class="o-field"><span>Giorno</span><input type="date" class="o-input" wire:model.live="date" min="{{ today()->toDateString() }}"></label><label class="o-field"><span>{{ $order->fulfillment==='delivery' ? 'Orario consegna' : 'Orario ritiro' }}</span><select class="o-input" wire:model.live="time"><option value="">Seleziona</option>@foreach($slots as $slot)<option value="{{ $slot }}">{{ $slot }}</option>@endforeach</select></label></div>
            <label class="o-field"><span>Note dell’ordine proposto</span><textarea class="o-input w-full" rows="3" maxlength="2000" wire:model.live.debounce.400ms="notes"></textarea></label>
            <label class="o-field"><span>Messaggio al cliente / motivo della proposta</span><textarea class="o-input w-full" rows="3" maxlength="4000" wire:model="message" placeholder="Ad esempio: proponiamo il ritiro alle 20:30 perché…"></textarea></label>
            <h2 class="font-semibold text-lg mt-5">Prodotti proposti</h2>
            <p class="o-muted">Per sostituire un prodotto, rimuovilo dalla proposta e aggiungi quello nuovo.</p>
            @foreach($lines as $index=>$line)
                @php($quoted=$priced['lines'][$index] ?? null)
                <div class="o-line" wire:key="proposal-line-{{ $index }}-{{ $line['original_item_id'] ?? $line['product_id'] }}">
                    <div class="o-row"><strong>{{ $quoted['attributes']['product_name'] ?? 'Prodotto' }}</strong><button type="button" class="ti-btn ti-btn-danger" wire:click="removeLine({{ $index }})">Rimuovi</button></div>
                    <label class="o-field"><span>Quantità</span><input class="o-input" type="number" min="1" max="100" wire:model.live.debounce.400ms="lines.{{ $index }}.quantity"></label>
                    <label class="o-field"><span>Note prodotto</span><textarea class="o-input w-full" rows="2" maxlength="2000" wire:model.live.debounce.400ms="lines.{{ $index }}.notes"></textarea></label>
                    @foreach($quoted['modifiers'] ?? [] as $modifier)<p class="o-muted">{{ $modifier['action']==='add' ? '+' : '−' }} {{ $modifier['ingredient_name'] }}</p>@endforeach
                    @if($line['product_id'])<button type="button" class="ti-btn ti-btn-light mt-2" wire:click="editIngredients({{ $index }})">Ingredienti</button>@endif
                </div>
            @endforeach
            <div class="mt-4"><input class="o-input w-full" type="search" aria-label="Cerca prodotto" placeholder="Cerca un prodotto…" wire:model.live.debounce.300ms="search"><select class="o-input w-full mt-2" wire:model="selectedProduct" aria-label="Prodotto da aggiungere"><option value="">Seleziona dal catalogo</option>@foreach($choices as $key=>$label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select><button class="ti-btn ti-btn-light mt-2" wire:click="addProduct">Aggiungi prodotto</button></div>
        </section>
        <section class="o-panel">
            <h2 class="font-semibold text-lg">Riepilogo per il cliente</h2>
            @if($preview)@include('public-orders.order-summary',['summary'=>$preview])@else<p class="o-error mt-4">{{ $previewError }}</p>@endif
            <button class="ti-btn ti-btn-primary-full w-full mt-4" wire:click="sendProposal" wire:confirm="Inviare la proposta via email? L’ordine resterà in attesa della risposta del cliente." wire:loading.attr="disabled" @disabled(!$preview)>Invia proposta via email</button>
        </section>
    </div>
    @if($ingredientLine!==null && $ingredientProduct)
        <div class="o-modal"><section class="o-dialog"><h2 class="text-xl font-semibold">Ingredienti · {{ $ingredientProduct->translatedName() }}</h2>@include('orders.errors')
            <fieldset class="o-field"><legend class="font-semibold">Rimuovi dalla ricetta</legend>@foreach($ingredientProduct->ingredients as $ingredient)<label class="block my-2"><input type="checkbox" wire:model="removed" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName() }}</label>@endforeach</fieldset>
            <fieldset class="o-field"><legend class="font-semibold">Aggiungi</legend><div style="max-height:230px;overflow:auto">@foreach($ingredients as $ingredient)<label class="block my-2"><input type="checkbox" wire:model="added" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName() }} · {{ number_format($ingredient->add_price,2,',','.') }} €</label>@endforeach</div></fieldset>
            <div class="o-row"><button class="ti-btn ti-btn-light" wire:click="$set('ingredientLine',null)">Chiudi</button><button class="ti-btn ti-btn-primary-full" wire:click="saveIngredients">Salva ingredienti</button></div>
        </section></div>
    @endif
</div></div>
