<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
<div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('cash-desk.index') }}">← Cassa</a><a class="ti-btn ti-btn-light" href="{{ route('cash-desk.history') }}">Storico incassi</a><h1 class="o-title">Turni di cassa</h1></div>
@include('orders.errors')
<div class="o-panel">
@if($active)
<h2 class="font-semibold">Turno aperto il {{ $active->created_at->format('d/m/Y H:i') }}</h2>
<p>Fondo iniziale: {{ number_format($active->opening_cents/100,2,',','.') }} € · Contanti attesi (mance incluse): {{ number_format($active->expectedCents()/100,2,',','.') }} €</p>
@foreach($breakdown as $row)<p>{{ ['cash'=>'Contanti','card'=>'Carta','manual'=>'Manuale','voucher'=>'Buoni pasto'][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} € · Mance {{ number_format($row->tips/100,2,',','.') }} €</p>@endforeach
<form wire:submit="close('{{ $active->id }}')" wire:confirm="Chiudere il turno con i contanti contati?" class="o-toolbar mt-4"><label>Contanti contati € <input class="o-input" wire:model="counted" inputmode="decimal"></label><label>Note <input class="o-input" wire:model="notes" maxlength="2000"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">Chiudi turno</button></form>
@else
<form wire:submit="open" class="o-toolbar"><label>Fondo iniziale € <input class="o-input" wire:model="opening" inputmode="decimal"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">Apri turno</button></form>
@endif
<p class="o-muted mt-4">Gli incassi effettuati durante un turno sono associati automaticamente. Gli incassi senza turno restano nello storico e sono esclusi dalla riconciliazione del cassetto. Le chiusure sono gestionali.</p>
</div>
<div class="o-panel mt-4"><h2 class="font-semibold">Turni chiusi</h2><div style="overflow:auto"><table class="o-table"><thead><tr><th>Apertura</th><th>Chiusura</th><th>Attesi €</th><th>Contati €</th><th>Differenza €</th><th>Note</th></tr></thead><tbody>
@forelse($shifts as $shift)<tr><td>{{ $shift->created_at->format('d/m/Y H:i') }}</td><td>{{ $shift->closed_at->format('d/m/Y H:i') }}</td><td>{{ number_format($shift->expected_cents/100,2,',','.') }}</td><td>{{ number_format($shift->counted_cents/100,2,',','.') }}</td><td>{{ number_format($shift->difference_cents/100,2,',','.') }}</td><td>{{ $shift->notes }}</td></tr>@empty<tr><td colspan="6">Nessun turno chiuso.</td></tr>@endforelse
</tbody></table></div>{{ $shifts->links() }}</div>
</div></div>
