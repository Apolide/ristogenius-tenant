<div class="po-shell">
    @include('public-orders.styles')
    <header class="po-bar">
        <div>
            <strong>{{ $branding['name'] }}</strong>
            @if($access)
                <p class="po-muted">Ciao {{ $access->customer->firstname }}! {{ $tableToken ? 'Tavolo '.$access->order->table_name.' · '.$access->order->room_name : 'Ordina per ritiro o consegna' }}</p>
            @elseif($recognizedName)
                <p class="po-muted">Ciao {{ $recognizedName }}, verifica la tua email per iniziare un nuovo ordine.</p>
            @else
                <p class="po-muted">Verifica la tua email per iniziare a ordinare.</p>
            @endif
        </div>
        @if($access)
            <div class="po-row"><button class="po-btn po-secondary" wire:click="logout">Esci</button><button class="po-btn" wire:click="$set('cartOpen',true)">Il tuo ordine · {{ count($cart) }}</button></div>
        @endif
    </header>
    @if(!$access)
        <section class="po-panel" id="order-access">
            <h1 class="po-title">Inizia il tuo ordine</h1>
            <p class="po-muted">Riceverai un link via email. Dopo la verifica potrai personalizzare i prodotti e comporre il carrello.</p>
            @if($errors->any())<div role="alert" class="po-error">{{ $errors->first() }}</div>@endif
            @if($emailSent)<div role="status" class="po-notice">Ti abbiamo inviato il link di accesso. Controlla la tua email.</div>@endif
            <form wire:submit="requestAccess">
                <label class="po-field"><span>Nome</span><input class="po-input" wire:model="name" autocomplete="given-name" required maxlength="150"></label>
                <div class="po-grid">
                    <label class="po-field"><span>Email</span><input class="po-input" type="email" wire:model="email" autocomplete="email" required maxlength="255"></label>
                    <label class="po-field"><span>Telefono</span><input class="po-input" type="tel" wire:model="phone" autocomplete="tel" required placeholder="+39 …"></label>
                </div>
                <label class="po-check"><input type="checkbox" wire:model="privacy" required> Ho letto l’<a href="{{ route('privacy-policy',['language'=>$language]) }}" target="_blank" rel="noopener">informativa privacy</a>.</label>
                <button class="po-btn" wire:loading.attr="disabled">Ricevi il link di accesso</button>
            </form>
        </section>
    @else
        @if($errors->any() && !$cartOpen && !$productModal)<div class="po-panel po-error" role="alert">{{ $errors->first() }}</div>@endif
        @if($lastCourseId)<div class="po-panel po-notice" role="status">Ordine ricevuto! Trovi gli aggiornamenti nella sezione “I tuoi ordini”.</div>@endif
    @endif
    @include('livewire.menu.public-menu')
    @if($access)
        <section class="po-history" wire:poll.10s>
            <h2 class="po-title">I tuoi ordini</h2>
            @forelse($submissions as $submission)
                <article class="po-line" wire:key="public-submission-{{ $submission->id }}">
                    <div class="po-row"><strong>{{ $submission->order->reference ?: 'Tavolo '.$submission->order->table_name.' · '.$submission->number.'ª uscita' }}</strong><span>{{ $submission->order->fulfillment === 'dine_in' ? ($submission->rejected_at ? 'Non confermato dal ristorante' : ($submission->sent_at ? 'Inviato ai reparti' : 'Ricevuto, in attesa del personale')) : $submission->order->fulfillmentStatusLabel() }}</span></div>
                    <p class="po-muted">{{ $submission->created_at->format('d/m/Y H:i') }}@if($submission->order->requested_at) · {{ $submission->order->fulfillmentLabel() }}: {{ $submission->order->requested_at->format('d/m H:i') }}@endif</p>
                    @if($submission->order->fulfillment !== 'dine_in')<p class="po-muted">{{ $submission->order->status === 'paid' ? 'Pagato' : ($submission->order->status === 'cancelled' ? 'Annullato' : 'Non pagato') }} · Contanti</p>@endif
                    @if($submission->order->pendingProposal)<p class="po-notice">Il ristorante ti ha inviato una proposta di modifica. Controlla la tua email per accettarla o rifiutarla.</p>@endif
                    @foreach($submission->items as $item)<p>{{ $item->quantity }} × {{ $item->product_name }} · {{ $item->statusLabel() }}</p>@endforeach
                </article>
            @empty<p class="po-muted">Non hai ancora inviato ordini.</p>@endforelse
        </section>
    @endif
    @if($cartOpen && $access)
        <div class="po-overlay" wire:click.self="$set('cartOpen',false)">
            <section class="po-drawer" role="dialog" aria-modal="true" aria-label="Il tuo ordine">
                <div class="po-row"><h2 class="po-title">Il tuo ordine</h2><button class="po-btn po-secondary" wire:click="$set('cartOpen',false)">Chiudi</button></div>
                @if($errors->any())<div role="alert" class="po-error">{{ $errors->first() }}</div>@endif
                @forelse($cart as $index=>$line)
                    @php($quoted=$quote['lines'][$index] ?? null)
                    <div class="po-line" wire:key="cart-line-{{ $index }}">
                        <div class="po-row"><strong>{{ $line['quantity'] ?? '' }} × {{ $quoted['attributes']['product_name'] ?? 'Prodotto non disponibile' }}</strong><span>{{ number_format(($quoted ? $quoted['attributes']['unit_price_cents']*$quoted['attributes']['quantity'] : 0)/100,2,',','.') }} €</span></div>
                        @foreach($quoted['modifiers'] ?? [] as $modifier)<p class="po-muted">{{ $modifier['action']==='add' ? '+' : '−' }} {{ $modifier['ingredient_name'] }} ({{ number_format($modifier['price_cents']/100,2,',','.') }} €)</p>@endforeach
                        @if($line['notes'] ?? '')<p class="po-muted">{{ $line['notes'] }}</p>@endif
                        <div class="po-row" style="margin-top:10px">@if($quoted)<button class="po-btn po-secondary" wire:click="editLine({{ $index }})">Modifica</button>@endif<button class="po-btn po-danger" wire:click="removeLine({{ $index }})">Rimuovi</button></div>
                    </div>
                @empty<p class="po-muted">Scegli i prodotti dal menu.</p>@endforelse
                @if($cart)
                    <form wire:submit="submit">
                        @if(!$tableToken)
                            <label class="po-field"><span>Tipo di ordine</span><select class="po-input" wire:model.live="type">@if($settings['pickup_enabled'])<option value="pickup">Ritiro in sede</option>@endif @if($settings['delivery_enabled'])<option value="delivery">Consegna a domicilio</option>@endif</select></label>
                            <div class="po-grid">
                                <label class="po-field"><span>Giorno</span><input class="po-input" type="date" wire:model.live="date" min="{{ today()->toDateString() }}" max="{{ today()->addDays($settings['advance_days'])->toDateString() }}" required></label>
                                <label class="po-field"><span>Orario</span><select class="po-input" wire:model="time" required><option value="">Seleziona</option>@foreach($slots as $slot)<option value="{{ $slot }}">{{ $slot }}</option>@endforeach</select></label>
                            </div>
                            @if(!$slots)<p class="po-error">Nessun orario disponibile per il giorno e il servizio selezionati.</p>@endif
                            @if($type==='delivery')
                                <label class="po-field"><span>Quartiere</span><select class="po-input" wire:model="neighborhood" required><option value="">Seleziona il quartiere</option>@foreach($neighborhoods as $area)<option value="{{ $area->id }}">{{ $area->name }}</option>@endforeach</select></label>
                                <label class="po-field"><span>Indirizzo completo e numero civico</span><input class="po-input" wire:model="address" autocomplete="street-address" maxlength="500" required></label>
                                @if($settings['delivery_minimum_cents'])<p class="po-muted">Minimo prodotti per la consegna: {{ number_format($settings['delivery_minimum_cents']/100,2,',','.') }} €</p>@endif
                            @endif
                            <p class="po-muted">Pagamento in contanti al ritiro o alla consegna.</p>
                        @else
                            <p class="po-notice">Ordine per il tavolo {{ $access->order->table_name }}. Il coperto è già gestito nel conto del tavolo.</p>
                        @endif
                        <label class="po-field"><span>Note per il ristorante</span><textarea class="po-input" wire:model="orderNotes" maxlength="2000" rows="2"></textarea></label>
                        <div class="po-line"><div class="po-row"><span>Prodotti</span><span>{{ number_format($quote['subtotal']/100,2,',','.') }} €</span></div>@if($tableToken || $type==='delivery')<div class="po-row"><span>{{ $tableToken ? 'Servizio ('.number_format($percentage/100,2,',','.').'%)' : 'Consegna' }}</span><span>{{ number_format($fee/100,2,',','.') }} €</span></div>@endif</div>
                        <div class="po-row po-total" style="margin:18px 0"><span>Totale</span><span>{{ number_format($displayedTotal/100,2,',','.') }} €</span></div>
                        <button class="po-btn" style="width:100%" wire:loading.attr="disabled">Conferma ordine</button>
                    </form>
                @endif
            </section>
        </div>
    @endif
    @if($productModal && $product && $access)
        <div class="po-modal"><form class="po-dialog" wire:submit="addLine" role="dialog" aria-modal="true" aria-label="Personalizza prodotto">
            <h2 class="po-title">{{ $product->translatedName($language) }}</h2>
            @if($errors->any())<div class="po-error" role="alert">{{ $errors->first() }}</div>@endif
            <label class="po-field"><span>Quantità</span><input type="number" min="1" max="100" class="po-input" wire:model="quantity" required></label>
            @if($product->ingredients->isNotEmpty())<fieldset><legend><strong>Rimuovi dalla ricetta</strong></legend>@foreach($product->ingredients as $ingredient)<label class="po-check"><input type="checkbox" wire:model="removed" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName($language) }} · {{ number_format($ingredient->remove_price,2,',','.') }} €</label>@endforeach</fieldset>@endif
            <fieldset style="margin-top:16px"><legend><strong>Aggiungi ingredienti</strong></legend><div style="max-height:190px;overflow:auto">@foreach($ingredients as $ingredient)<label class="po-check"><input type="checkbox" wire:model="added" value="{{ $ingredient->id }}"> {{ $ingredient->translatedName($language) }} · {{ number_format($ingredient->add_price,2,',','.') }} €</label>@endforeach</div></fieldset>
            <label class="po-field"><span>Note sul prodotto</span><textarea class="po-input" wire:model="notes" maxlength="2000" rows="2"></textarea></label>
            <div class="po-row"><button type="button" class="po-btn po-secondary" wire:click="$set('productModal',false)">Chiudi</button><button class="po-btn" wire:loading.attr="disabled">{{ $editingLine!==null ? 'Salva' : 'Aggiungi al carrello' }}</button></div>
        </form></div>
    @endif
</div>
