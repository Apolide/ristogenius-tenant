<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('cash_desk.cash_reconciliation')" icon="la-balance-scale">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('cash_desk.cash_desk') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('cash_desk.reconciliation.management_fiscal_sandbox') }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
@include('orders.errors')
<form wire:submit="$refresh" class="box"><div class="box-body service-form-grid"><label>{{ __('cash_desk.reconciliation.from') }} <input class="form-control" type="date" wire:model="from" required></label><label>{{ __('cash_desk.reconciliation.to') }} <input class="form-control" type="date" wire:model="to" required></label><button class="ti-btn ti-btn-light"><i class="las la-sync" aria-hidden="true"></i> {{ __('cash_desk.reconciliation.refresh') }}</button><button type="button" class="ti-btn ti-btn-light" wire:click="export"><i class="las la-file-csv" aria-hidden="true"></i> {{ __('cash_desk.reconciliation.export_csv') }}</button></div></form>
<p class="o-muted mt-3">{{ __('cash_desk.reconciliation.reconciliation_period_help') }}</p>
<div class="table-responsive"><table class="table ti-custom-table w-full mt-4"><thead><tr><th>{{ __('cash_desk.method') }}</th><th>{{ __('cash_desk.reconciliation.payments') }}</th><th>{{ __('cash_desk.reconciliation.tips') }}</th><th>{{ __('cash_desk.reconciliation.refunds') }}</th><th>{{ __('cash_desk.reconciliation.net_financial_amount') }}</th></tr></thead><tbody>
@foreach($totals as $method=>$values)<tr><td>{{ ['cash'=>__('cash_desk.cash'),'card'=>__('cash_desk.card'),'voucher'=>__('cash_desk.vouchers'),'manual'=>__('cash_desk.reconciliation.manual')][$method] }}</td><td>{{ number_format($values['paid']/100,2,',','.') }}</td><td>{{ number_format($values['tips']/100,2,',','.') }}</td><td>{{ number_format($values['refunded']/100,2,',','.') }}</td><td>{{ number_format(($values['paid']+$values['tips']-$values['refunded'])/100,2,',','.') }}</td></tr>@endforeach
</tbody></table></div>
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.reconciliation.payments_to_document_verify') }}</h2></div><div class="box-body">
@forelse($issues as $issue)<p class="o-line"><a href="{{ route('cash-desk.show',$issue['order']) }}"><i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('cash_desk.reconciliation.open_bill') }}</a> · {{ number_format($issue['amount']/100,2,',','.') }} € · {{ $issue['status'] }} {{ __('cash_desk.reconciliation.payment') }} {{ $issue['batch'] }}</p>@empty<p>{{ __('cash_desk.reconciliation.no_payments_in_this_period_need_verification') }}</p>@endforelse
<p class="o-muted mt-3">{{ __('cash_desk.reconciliation.historical_payments_excluded_from_breakdown') }}</p></div></div>
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.reconciliation.documents_and_adjustments_in_this_period') }}</h2></div><div class="box-body">
@foreach($fiscalTotals as $kind=>$values)<p>{{ $kind === 'receipt' ? __('cash_desk.receipts') : __('cash_desk.invoices') }}{{ __('cash_desk.reconciliation.sales') }} {{ number_format($values['sales']/100,2,',','.') }} {{ __('cash_desk.reconciliation.adjustments') }} {{ number_format($values['corrections']/100,2,',','.') }} {{ __('cash_desk.reconciliation.net') }} {{ number_format(($values['sales']-$values['corrections'])/100,2,',','.') }} €</p>@endforeach
<p class="o-muted">{{ __('cash_desk.reconciliation.fiscal_reconciliation_totals_help') }}</p>
@forelse($documents as $document)<p class="o-line"><a href="{{ route('cash-desk.fiscal',$document->order_id) }}">{{ \App\Support\UiLabels::code($document->document_kind, 'types') }} · {{ \App\Support\UiLabels::code($document->type, 'types') }} · {{ $document->document_number }}</a> · {{ \App\Support\UiLabels::code($document->status, 'statuses') }} · {{ number_format($document->total_cents/100,2,',','.') }} €</p>@empty<p>{{ __('cash_desk.reconciliation.no_documents_in_this_period') }}</p>@endforelse</div></div>
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.reconciliation.refunds_to_compare_with_fiscal_adjustments') }}</h2></div><div class="box-body">
@forelse($refunds as $refund)<p class="o-line">{{ $refund->created_at->format('d/m/Y H:i') }} · {{ number_format($refund->amount_cents/100,2,',','.') }} € · {{ \App\Support\UiLabels::code($refund->method, 'methods') }} · {{ $refund->reason }} · {{ $refund->external_reference }}</p>@empty<p>{{ __('cash_desk.reconciliation.no_refunds_in_this_period') }}</p>@endforelse
<p class="o-muted">{{ __('cash_desk.reconciliation.refund_and_fiscal_adjustment_are_separate') }}</p></div></div>
</x-orders.workspace>
</div></div>
