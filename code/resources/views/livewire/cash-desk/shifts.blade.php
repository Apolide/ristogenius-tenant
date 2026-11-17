<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('Turni di cassa')" icon="la-business-time">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Cassa') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('Turni di cassa') }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
@include('orders.errors')
<div class="box"><div class="box-body">
@if($active)
<h2 class="font-semibold">{{ __('Turno aperto il') }} {{ $active->created_at->format('d/m/Y H:i') }}</h2>
<p>{{ __('Fondo iniziale:') }} {{ number_format($active->opening_cents/100,2,',','.') }} {{ __('€ · Contanti attesi (mance incluse):') }} {{ number_format($active->expectedCents()/100,2,',','.') }} €</p>
@foreach($breakdown as $row)<p>{{ ['cash'=>__('Contanti'),'card'=>__('Carta'),'manual'=>__('Manuale'),'voucher'=>__('Buoni pasto')][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} {{ __('€ · Mance') }} {{ number_format($row->tips/100,2,',','.') }} €</p>@endforeach
<form wire:submit="close('{{ $active->id }}')" wire:confirm="{{ __('Chiudere il turno con i contanti contati?') }}" class="service-form-grid mt-4"><label>{{ __('Contanti contati €') }} <input class="form-control" wire:model="counted" inputmode="decimal"></label><label>{{ __('Note') }} <input class="form-control" wire:model="notes" maxlength="2000"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-times" aria-hidden="true"></i> {{ __('Chiudi turno') }}</button></form>
@else
<form wire:submit="open" class="o-toolbar"><label>{{ __('Fondo iniziale €') }} <input class="form-control" wire:model="opening" inputmode="decimal"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('Apri turno') }}</button></form>
@endif
<p class="o-muted mt-4">{{ __('Gli incassi effettuati durante un turno sono associati automaticamente. Gli incassi senza turno restano nello storico e sono esclusi dalla riconciliazione del cassetto. Le chiusure sono gestionali.') }}</p>
</div></div>
@if($fiscalStores->count())<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Chiusure fiscali · Sandbox') }}</h2></div><div class="box-body">
<p class="o-muted">{{ __('Separate dalla chiusura del turno gestionale. Il fornitore richiede una chiusura almeno ogni 24 ore per le sedi ONLINE.') }}</p>
@foreach($fiscalStores as $store)<div class="service-form-grid mt-3"><strong>{{ $store->label }}</strong><span>{{ __('Ultima chiusura registrata:') }} {{ $store->last_closed_at?->format('d/m/Y H:i') ?? __('Nessuna') }}</span><button class="ti-btn ti-btn-light" wire:click="closeFiscal('{{ $store->id }}')" wire:confirm="{{ __('Eseguire la chiusura fiscale sandbox di questa sede?') }}" wire:loading.attr="disabled"><i class="las la-file-invoice" aria-hidden="true"></i> {{ __('Chiudi sessione fiscale sandbox') }}</button></div>@endforeach</div></div>@endif
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Turni chiusi') }}</h2></div><div class="box-body"><div class="table-responsive"><table class="table ti-custom-table w-full"><thead><tr><th>{{ __('Apertura') }}</th><th>{{ __('Chiusura') }}</th><th>{{ __('Attesi €') }}</th><th>{{ __('Contati €') }}</th><th>{{ __('Differenza €') }}</th><th>{{ __('Note') }}</th></tr></thead><tbody>
@forelse($shifts as $shift)<tr><td>{{ $shift->created_at->format('d/m/Y H:i') }}</td><td>{{ $shift->closed_at->format('d/m/Y H:i') }}</td><td>{{ number_format($shift->expected_cents/100,2,',','.') }}</td><td>{{ number_format($shift->counted_cents/100,2,',','.') }}</td><td>{{ number_format($shift->difference_cents/100,2,',','.') }}</td><td>{{ $shift->notes }}</td></tr>@empty<tr><td colspan="6">{{ __('Nessun turno chiuso.') }}</td></tr>@endforelse
</tbody></table></div>{{ $shifts->links() }}</div></div>
</x-orders.workspace>
</div></div>
