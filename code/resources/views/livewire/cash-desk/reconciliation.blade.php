<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
<div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('cash-desk.index') }}">← Cassa</a><h1 class="o-title">Riconciliazione cassa</h1><span class="o-badge">Gestionale · Fiscale sandbox</span></div>
@include('orders.errors')
<form wire:submit="$refresh" class="o-panel o-toolbar"><label>Dal <input class="o-input" type="date" wire:model="from" required></label><label>Al <input class="o-input" type="date" wire:model="to" required></label><button class="ti-btn ti-btn-light">Aggiorna</button><button type="button" class="ti-btn ti-btn-light" wire:click="export">Esporta CSV</button></form>
<p class="o-muted mt-3">Periodo per data di registrazione, fuso orario dell’app. Incassi, mance, rimborsi e documenti sono movimenti distinti. I documenti di prova non certificano incassi reali. Questo riepilogo non verifica accrediti bancari o POS.</p>
<table class="o-table mt-4"><thead><tr><th>Metodo</th><th>Incassi €</th><th>Mance €</th><th>Rimborsi €</th><th>Netto finanziario €</th></tr></thead><tbody>
@foreach($totals as $method=>$values)<tr><td>{{ ['cash'=>'Contanti','card'=>'Carta','voucher'=>'Buoni','manual'=>'Manuali'][$method] }}</td><td>{{ number_format($values['paid']/100,2,',','.') }}</td><td>{{ number_format($values['tips']/100,2,',','.') }}</td><td>{{ number_format($values['refunded']/100,2,',','.') }}</td><td>{{ number_format(($values['paid']+$values['tips']-$values['refunded'])/100,2,',','.') }}</td></tr>@endforeach
</tbody></table>
<div class="o-panel mt-4"><h2 class="font-semibold">Incassi da documentare / verificare</h2>
@forelse($issues as $issue)<p class="o-line"><a href="{{ route('cash-desk.show',$issue['order']) }}">Apri conto</a> · {{ number_format($issue['amount']/100,2,',','.') }} € · {{ $issue['status'] }} · Incasso {{ $issue['batch'] }}</p>@empty<p>Nessun incasso del periodo da verificare.</p>@endforelse
<p class="o-muted mt-3">Gli incassi storici senza conticino non entrano in questa lista; i loro movimenti sono inclusi nei totali e nel CSV.</p></div>
<div class="o-panel mt-4"><h2 class="font-semibold">Documenti e rettifiche del periodo</h2>
@foreach($fiscalTotals as $kind=>$values)<p>{{ $kind === 'receipt' ? 'Documenti commerciali' : 'Fatture' }}: vendite {{ number_format($values['sales']/100,2,',','.') }} € · rettifiche {{ number_format($values['corrections']/100,2,',','.') }} € · netto {{ number_format(($values['sales']-$values['corrections'])/100,2,',','.') }} €</p>@endforeach
<p class="o-muted">Totali dei soli documenti con esito finale, per data di creazione locale. Incassi e documenti possono ricadere in giorni diversi; il loro scarto giornaliero non indica da solo un errore. Mance escluse dai documenti.</p>
@forelse($documents as $document)<p class="o-line"><a href="{{ route('cash-desk.fiscal',$document->order_id) }}">{{ $document->document_kind }} · {{ $document->type }} · {{ $document->document_number }}</a> · {{ $document->status }} · {{ number_format($document->total_cents/100,2,',','.') }} €</p>@empty<p>Nessun documento nel periodo.</p>@endforelse</div>
<div class="o-panel mt-4"><h2 class="font-semibold">Rimborsi da confrontare con le rettifiche fiscali</h2>
@forelse($refunds as $refund)<p class="o-line">{{ $refund->created_at->format('d/m/Y H:i') }} · {{ number_format($refund->amount_cents/100,2,',','.') }} € · {{ $refund->method }} · {{ $refund->reason }} · {{ $refund->external_reference }}</p>@empty<p>Nessun rimborso nel periodo.</p>@endforelse
<p class="o-muted">Un rimborso non annulla automaticamente un documento; una rettifica fiscale non restituisce automaticamente denaro.</p></div>
</div></div>
