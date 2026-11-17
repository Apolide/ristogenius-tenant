<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
<div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('cash-desk.index') }}">{{ __('← Cassa') }}</a><a class="ti-btn ti-btn-light" href="{{ route('cash-desk.history') }}">{{ __('Storico incassi') }}</a><h1 class="o-title">{{ __('Turni di cassa') }}</h1></div>
<a class="ti-btn ti-btn-light mb-3" href="{{ route('cash-desk.reconciliation') }}">{{ __('Riconciliazione ed esportazione') }}</a>
@include('orders.errors')
<div class="o-panel">
@if($active)
<h2 class="font-semibold">{{ __('Turno aperto il') }} {{ $active->created_at->format('d/m/Y H:i') }}</h2>
<p>{{ __('Fondo iniziale:') }} {{ number_format($active->opening_cents/100,2,',','.') }} {{ __('€ · Contanti attesi (mance incluse):') }} {{ number_format($active->expectedCents()/100,2,',','.') }} €</p>
@foreach($breakdown as $row)<p>{{ ['cash'=>__('Contanti'),'card'=>__('Carta'),'manual'=>__('Manuale'),'voucher'=>__('Buoni pasto')][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} {{ __('€ · Mance') }} {{ number_format($row->tips/100,2,',','.') }} €</p>@endforeach
<form wire:submit="close('{{ $active->id }}')" wire:confirm="{{ __('Chiudere il turno con i contanti contati?') }}" class="o-toolbar mt-4"><label>{{ __('Contanti contati €') }} <input class="o-input" wire:model="counted" inputmode="decimal"></label><label>{{ __('Note') }} <input class="o-input" wire:model="notes" maxlength="2000"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">{{ __('Chiudi turno') }}</button></form>
@else
<form wire:submit="open" class="o-toolbar"><label>{{ __('Fondo iniziale €') }} <input class="o-input" wire:model="opening" inputmode="decimal"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">{{ __('Apri turno') }}</button></form>
@endif
<p class="o-muted mt-4">{{ __('Gli incassi effettuati durante un turno sono associati automaticamente. Gli incassi senza turno restano nello storico e sono esclusi dalla riconciliazione del cassetto. Le chiusure sono gestionali.') }}</p>
</div>
@if($fiscalStores->count())<div class="o-panel mt-4"><h2 class="font-semibold">{{ __('Chiusure fiscali · Sandbox') }}</h2>
<p class="o-muted">{{ __('Separate dalla chiusura del turno gestionale. Il fornitore richiede una chiusura almeno ogni 24 ore per le sedi ONLINE.') }}</p>
@foreach($fiscalStores as $store)<div class="o-toolbar mt-3"><strong>{{ $store->label }}</strong><span>{{ __('Ultima chiusura registrata:') }} {{ $store->last_closed_at?->format('d/m/Y H:i') ?? __('Nessuna') }}</span><button class="ti-btn ti-btn-light" wire:click="closeFiscal('{{ $store->id }}')" wire:confirm="{{ __('Eseguire la chiusura fiscale sandbox di questa sede?') }}" wire:loading.attr="disabled">{{ __('Chiudi sessione fiscale sandbox') }}</button></div>@endforeach</div>@endif
<div class="o-panel mt-4"><h2 class="font-semibold">{{ __('Turni chiusi') }}</h2><div style="overflow:auto"><table class="o-table"><thead><tr><th>{{ __('Apertura') }}</th><th>{{ __('Chiusura') }}</th><th>{{ __('Attesi €') }}</th><th>{{ __('Contati €') }}</th><th>{{ __('Differenza €') }}</th><th>{{ __('Note') }}</th></tr></thead><tbody>
@forelse($shifts as $shift)<tr><td>{{ $shift->created_at->format('d/m/Y H:i') }}</td><td>{{ $shift->closed_at->format('d/m/Y H:i') }}</td><td>{{ number_format($shift->expected_cents/100,2,',','.') }}</td><td>{{ number_format($shift->counted_cents/100,2,',','.') }}</td><td>{{ number_format($shift->difference_cents/100,2,',','.') }}</td><td>{{ $shift->notes }}</td></tr>@empty<tr><td colspan="6">{{ __('Nessun turno chiuso.') }}</td></tr>@endforelse
</tbody></table></div>{{ $shifts->links() }}</div>
</div></div>
