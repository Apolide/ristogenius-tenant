<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('Riconciliazione cassa')" icon="la-balance-scale">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('Cassa') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('Gestionale · Fiscale sandbox') }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
@include('orders.errors')
<form wire:submit="$refresh" class="box"><div class="box-body service-form-grid"><label>{{ __('Dal') }} <input class="form-control" type="date" wire:model="from" required></label><label>{{ __('Al') }} <input class="form-control" type="date" wire:model="to" required></label><button class="ti-btn ti-btn-light"><i class="las la-sync" aria-hidden="true"></i> {{ __('Aggiorna') }}</button><button type="button" class="ti-btn ti-btn-light" wire:click="export"><i class="las la-file-csv" aria-hidden="true"></i> {{ __('Esporta CSV') }}</button></div></form>
<p class="o-muted mt-3">{{ __('Periodo per data di registrazione, fuso orario dell’app. Incassi, mance, rimborsi e documenti sono movimenti distinti. I documenti di prova non certificano incassi reali. Questo riepilogo non verifica accrediti bancari o POS.') }}</p>
<div class="table-responsive"><table class="table ti-custom-table w-full mt-4"><thead><tr><th>{{ __('Metodo') }}</th><th>{{ __('Incassi €') }}</th><th>{{ __('Mance €') }}</th><th>{{ __('Rimborsi €') }}</th><th>{{ __('Netto finanziario €') }}</th></tr></thead><tbody>
@foreach($totals as $method=>$values)<tr><td>{{ ['cash'=>__('Contanti'),'card'=>__('Carta'),'voucher'=>__('Buoni'),'manual'=>__('Manuali')][$method] }}</td><td>{{ number_format($values['paid']/100,2,',','.') }}</td><td>{{ number_format($values['tips']/100,2,',','.') }}</td><td>{{ number_format($values['refunded']/100,2,',','.') }}</td><td>{{ number_format(($values['paid']+$values['tips']-$values['refunded'])/100,2,',','.') }}</td></tr>@endforeach
</tbody></table></div>
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Incassi da documentare / verificare') }}</h2></div><div class="box-body">
@forelse($issues as $issue)<p class="o-line"><a href="{{ route('cash-desk.show',$issue['order']) }}"><i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('Apri conto') }}</a> · {{ number_format($issue['amount']/100,2,',','.') }} € · {{ $issue['status'] }} {{ __('· Incasso') }} {{ $issue['batch'] }}</p>@empty<p>{{ __('Nessun incasso del periodo da verificare.') }}</p>@endforelse
<p class="o-muted mt-3">{{ __('Gli incassi storici senza conticino non entrano in questa lista; i loro movimenti sono inclusi nei totali e nel CSV.') }}</p></div></div>
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Documenti e rettifiche del periodo') }}</h2></div><div class="box-body">
@foreach($fiscalTotals as $kind=>$values)<p>{{ $kind === 'receipt' ? __('Documenti commerciali') : __('Fatture') }}{{ __(': vendite') }} {{ number_format($values['sales']/100,2,',','.') }} {{ __('€ · rettifiche') }} {{ number_format($values['corrections']/100,2,',','.') }} {{ __('€ · netto') }} {{ number_format(($values['sales']-$values['corrections'])/100,2,',','.') }} €</p>@endforeach
<p class="o-muted">{{ __('Totali dei soli documenti con esito finale, per data di creazione locale. Incassi e documenti possono ricadere in giorni diversi; il loro scarto giornaliero non indica da solo un errore. Mance escluse dai documenti.') }}</p>
@forelse($documents as $document)<p class="o-line"><a href="{{ route('cash-desk.fiscal',$document->order_id) }}">{{ \App\Support\UiLabels::code($document->document_kind, 'types') }} · {{ \App\Support\UiLabels::code($document->type, 'types') }} · {{ $document->document_number }}</a> · {{ \App\Support\UiLabels::code($document->status, 'statuses') }} · {{ number_format($document->total_cents/100,2,',','.') }} €</p>@empty<p>{{ __('Nessun documento nel periodo.') }}</p>@endforelse</div></div>
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('Rimborsi da confrontare con le rettifiche fiscali') }}</h2></div><div class="box-body">
@forelse($refunds as $refund)<p class="o-line">{{ $refund->created_at->format('d/m/Y H:i') }} · {{ number_format($refund->amount_cents/100,2,',','.') }} € · {{ \App\Support\UiLabels::code($refund->method, 'methods') }} · {{ $refund->reason }} · {{ $refund->external_reference }}</p>@empty<p>{{ __('Nessun rimborso nel periodo.') }}</p>@endforelse
<p class="o-muted">{{ __('Un rimborso non annulla automaticamente un documento; una rettifica fiscale non restituisce automaticamente denaro.') }}</p></div></div>
</x-orders.workspace>
</div></div>
