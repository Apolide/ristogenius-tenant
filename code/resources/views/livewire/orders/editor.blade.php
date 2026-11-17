<div class="content order-workspace">
<div class="main-content">
@include('orders.styles')
<div class="o-fullscreen" x-data="{ async fullscreen() { if(document.fullscreenElement) await document.exitFullscreen(); else await $el.requestFullscreen(); } }">
<div class="o-toolbar">
<a class="ti-btn ti-btn-light" href="{{ route('orders.index') }}">{{ __('← Sala') }}</a>
<div class="o-title">{{ __('Tavolo') }} {{ $order->table_name }}<p class="o-muted">{{ $order->room_name }} · {{ $order->service_date->format('d/m/Y') }} · {{ ['open'=>__('Aperto'),'paid'=>__('Pagato'),'cancelled'=>__('Annullato')][$order->status] }}</p>
</div>
<button class="ti-btn ti-btn-light" @click="fullscreen()">{{ __('⛶ Schermo intero') }}</button>@if(auth()->user()->can(\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER))<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.show',$order) }}">{{ __('Vai alla cassa') }}</a>@endif
</div>
@include('orders.errors')
<div class="o-grid">
<section class="o-panel">
<div class="o-toolbar">
<select aria-label="{{ __('Menu') }}" class="o-input" wire:model.live="menuId">@forelse($menus as $menu)<option value="{{ $menu->id }}">{{ $menu->translatedName() }}</option>@empty
<option>{{ __('Nessun menu visibile') }}</option>@endforelse
</select>
<input class="o-input" type="search" placeholder="{{ __('Cerca un prodotto…') }}" aria-label="{{ __('Cerca prodotto') }}" wire:model.live.debounce.300ms="search">
</div>
<div class="o-categories">@foreach($categories as $category)<button class="o-chip {{ $categoryId === $category->id ? 'active' : '' }}" wire:click="$set('categoryId','{{ $category->id }}')">{{ $category->translatedName() }}</button>@endforeach
</div>
<div class="o-products">@forelse($products as $productRow)<button class="o-product" wire:click="chooseProduct('{{ $productRow->id }}')" @disabled($order->status !== 'open')>
<strong>{{ $productRow->translatedName() }}</strong>
<span class="text-primary font-semibold">{{ number_format($productRow->pivot->menu_price ?? $productRow->price,2,',','.') }} € <span class="float-end">＋</span>
</span>
</button>@empty
<p class="o-empty">{{ __('Nessun prodotto disponibile.') }}</p>@endforelse
</div>
</section>
<section class="o-panel">
<div class="o-row">
<h2 class="font-semibold text-lg">{{ __('Comanda') }}</h2>@if($order->status === 'open')<button class="ti-btn ti-btn-primary-full" wire:click="addCourse" wire:loading.attr="disabled">{{ __('＋ Uscita') }}</button>@endif
</div>
<form wire:submit="updateCovers" class="o-toolbar mt-3">
<label>{{ __('Coperti') }} <input aria-label="{{ __('Coperti') }}" type="number" min="1" max="1000" style="width:80px" class="o-input" wire:model="covers" @disabled($order->status !== 'open')>
</label>@if($order->status === 'open')<button class="ti-btn ti-btn-light">{{ __('Aggiorna') }}</button>@endif
<span class="o-muted">{{ number_format($order->cover_price_cents/100,2,',','.') }} {{ __('€ / persona') }}</span>
</form>
<div wire:poll.5s>
@foreach($order->courses as $course)
@php($editable = $order->status === 'open' && !$course->rejected_at && !$course->tickets->contains(fn($ticket) => !in_array($ticket->status,['queued','cancelled'])))
<div class="o-line" wire:key="course-{{ $course->id }}">
<div class="o-row">
<div class="flex items-center gap-2">@if(!$course->sent_at && !$course->submission_key && $order->status === 'open')<input type="radio" aria-label="{{ __('Seleziona uscita :number', ['number' => $course->number]) }}" wire:model.live="courseId" value="{{ $course->id }}">@endif
<strong>{{ __('Uscita :number', ['number' => $course->number]) }}</strong>@if($course->customer_name)<span class="o-badge">{{ __('Cliente:') }} {{ $course->customer_name }}</span>@endif
</div>@if($course->rejected_at)<span class="o-badge">{{ __('Rifiutata') }}</span>@elseif($course->sent_at)<span class="o-badge">{{ __('Inviata') }} {{ $course->sent_at->format('H:i') }}</span>@elseif($order->status === 'open')<button class="ti-btn ti-btn-primary-full" wire:click="sendCourse('{{ $course->id }}')" wire:loading.attr="disabled" @disabled($course->items->isEmpty())>{{ __('Invia uscita') }}</button>@if($course->submission_key)<button class="ti-btn ti-btn-danger" wire:click="rejectCourse('{{ $course->id }}')" wire:confirm="{{ __('Rifiutare questa uscita cliente? Rimarrà nello storico senza essere addebitata.') }}">{{ __('Rifiuta') }}</button>@endif @endif
</div>
@if($course->customer_notes)<p class="o-muted">{{ __('Note cliente:') }} {{ $course->customer_notes }}</p>@endif
@forelse($course->items as $item)<div class="o-line" wire:key="item-{{ $item->id }}">
<div class="o-row">
<strong>{{ $item->quantity }} × {{ $item->product_name }}</strong>
<span>{{ number_format($item->unit_price_cents*$item->quantity/100,2,',','.') }} €</span>
</div>
@foreach($item->modifiers as $modifier)<p class="o-muted">{{ $modifier->action === 'add' ? '+' : '−' }} {{ $modifier->ingredient_name }} ({{ number_format($modifier->price_cents/100,2,',','.') }} €)</p>@endforeach
@if($item->notes)<p class="o-muted">{{ __('Note:') }} {{ $item->notes }}</p>@endif
<div class="o-row mt-2">
<span class="o-badge">{{ $item->statusLabel() }}</span>@if($editable)<div>
<button class="ti-btn ti-btn-light !p-1" wire:click="editItem('{{ $item->id }}')">{{ __('Modifica') }}</button>
<button class="ti-btn ti-btn-danger !p-1" wire:click="removeItem('{{ $item->id }}')" wire:confirm="{{ __('Rimuovere questo prodotto dalla comanda?') }}">{{ __('Rimuovi') }}</button>
</div>@endif
</div>
</div>@empty
<p class="o-muted mt-3">{{ __('Seleziona questa uscita e aggiungi prodotti dal menu.') }}</p>@endforelse
</div>
@endforeach
<div class="o-row mt-5">
<strong>{{ __('Totale, coperti inclusi') }}</strong>
<strong class="text-xl">{{ number_format($order->totalCents()/100,2,',','.') }} €</strong>
</div>
</div>
</section>
</div>
@if($productModal)
<div class="o-modal">
<form wire:submit="saveItem" class="o-dialog">
<h2 class="text-xl font-semibold">{{ $editingItemId ? __('Modifica prodotto') : $product?->translatedName() }}</h2>
@include('orders.errors')
<label class="o-field">
<span>{{ __('Quantità') }}</span>
<input type="number" min="1" max="1000" wire:model="quantity" class="o-input">
</label>
<label class="o-field">
<span>{{ __('Note per il reparto') }}</span>
<textarea wire:model="notes" maxlength="2000" class="o-input w-full" rows="3">
</textarea>
</label>
@if($product)
@if($product->ingredients->isNotEmpty())<fieldset class="o-field">
<legend class="font-semibold mb-2">{{ __('Rimuovi ingredienti della ricetta') }}</legend>@foreach($product->ingredients as $ingredient)<label class="block mb-2">
<input type="checkbox" value="{{ $ingredient->id }}" wire:model="removed"> {{ $ingredient->translatedName() }} <span class="o-muted">{{ number_format($ingredient->remove_price,2,',','.') }} €</span>
</label>@endforeach
</fieldset>@endif
<fieldset class="o-field">
<legend class="font-semibold mb-2">{{ __('Aggiungi ingredienti') }}</legend>
<div style="max-height:200px;overflow:auto">@foreach($ingredients as $ingredient)<label class="block mb-2">
<input type="checkbox" value="{{ $ingredient->id }}" wire:model="added"> {{ $ingredient->translatedName() }} <span class="o-muted">+{{ number_format($ingredient->add_price,2,',','.') }} €</span>
</label>@endforeach
</div>
</fieldset>
@endif
<div class="o-row mt-4">
<button type="button" class="ti-btn ti-btn-light" wire:click="$set('productModal',false)">{{ __('Chiudi') }}</button>
<button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">{{ $editingItemId ? __('Salva') : __('Aggiungi all’uscita selezionata') }}</button>
</div>
</form>
</div>
@endif
</div>
</div>
</div>
