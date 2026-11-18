<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
@include('orders.workspace-styles')
<x-orders.workspace :title="__('cash_desk.cash_shifts')" icon="la-business-time">
    <x-slot:actions><a class="ti-btn ti-btn-light !m-0" href="{{ route('cash-desk.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('cash_desk.cash_desk') }}</a></x-slot:actions>
    <x-slot:stats>{{ __('cash_desk.cash_shifts') }}</x-slot:stats>
    <x-slot:tabs>@include('orders.cash-tabs')</x-slot:tabs>
@include('orders.errors')
<div class="box"><div class="box-body">
@if($active)
<h2 class="font-semibold">{{ __('cash_desk.shifts.shift_opened_on') }} {{ $active->created_at->format('d/m/Y H:i') }}</h2>
<p>{{ __('cash_desk.shifts.opening_float') }} {{ number_format($active->opening_cents/100,2,',','.') }} {{ __('cash_desk.shifts.expected_cash_including_tips') }} {{ number_format($active->expectedCents()/100,2,',','.') }} €</p>
@foreach($breakdown as $row)<p>{{ ['cash'=>__('cash_desk.cash'),'card'=>__('cash_desk.card'),'manual'=>__('cash_desk.manual'),'voucher'=>__('cash_desk.meal_vouchers')][$row->method] ?? $row->method }}: {{ number_format($row->amount/100,2,',','.') }} {{ __('cash_desk.tips') }} {{ number_format($row->tips/100,2,',','.') }} €</p>@endforeach
<form wire:submit="close('{{ $active->id }}')" wire:confirm="{{ __('cash_desk.shifts.close_the_shift_with_the_counted_cash_amount') }}" class="service-form-grid mt-4"><label>{{ __('cash_desk.shifts.counted_cash') }} <input class="form-control" wire:model="counted" inputmode="decimal"></label><label>{{ __('cash_desk.shifts.notes') }} <input class="form-control" wire:model="notes" maxlength="2000"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-times" aria-hidden="true"></i> {{ __('cash_desk.shifts.close_shift') }}</button></form>
@else
<form wire:submit="open" class="o-toolbar"><label>{{ __('cash_desk.shifts.opening_float_euros') }} <input class="form-control" wire:model="opening" inputmode="decimal"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-external-link-alt" aria-hidden="true"></i> {{ __('cash_desk.shifts.open_shift') }}</button></form>
@endif
<p class="o-muted mt-4">{{ __('cash_desk.shifts.automatic_payment_shift_assignment') }}</p>
</div></div>
@if($fiscalStores->count())<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.shifts.fiscal_closings_sandbox') }}</h2></div><div class="box-body">
<p class="o-muted">{{ __('cash_desk.shifts.fiscal_closing_interval_help') }}</p>
@foreach($fiscalStores as $store)<div class="service-form-grid mt-3"><strong>{{ $store->label }}</strong><span>{{ __('cash_desk.shifts.last_recorded_closing') }} {{ $store->last_closed_at?->format('d/m/Y H:i') ?? __('cash_desk.shifts.none') }}</span><button class="ti-btn ti-btn-light" wire:click="closeFiscal('{{ $store->id }}')" wire:confirm="{{ __('cash_desk.shifts.run_the_sandbox_fiscal_closing_for_this_location') }}" wire:loading.attr="disabled"><i class="las la-file-invoice" aria-hidden="true"></i> {{ __('cash_desk.shifts.close_sandbox_fiscal_session') }}</button></div>@endforeach</div></div>@endif
<div class="box mt-4"><div class="box-header"><h2 class="box-title">{{ __('cash_desk.shifts.closed_shifts') }}</h2></div><div class="box-body"><div class="table-responsive"><table class="table ti-custom-table w-full"><thead><tr><th>{{ __('cash_desk.shifts.opening') }}</th><th>{{ __('cash_desk.shifts.closing') }}</th><th>{{ __('cash_desk.shifts.expected') }}</th><th>{{ __('cash_desk.shifts.counted') }}</th><th>{{ __('cash_desk.shifts.difference') }}</th><th>{{ __('cash_desk.shifts.notes') }}</th></tr></thead><tbody>
@forelse($shifts as $shift)<tr><td>{{ $shift->created_at->format('d/m/Y H:i') }}</td><td>{{ $shift->closed_at->format('d/m/Y H:i') }}</td><td>{{ number_format($shift->expected_cents/100,2,',','.') }}</td><td>{{ number_format($shift->counted_cents/100,2,',','.') }}</td><td>{{ number_format($shift->difference_cents/100,2,',','.') }}</td><td>{{ $shift->notes }}</td></tr>@empty<tr><td colspan="6">{{ __('cash_desk.shifts.no_closed_shifts') }}</td></tr>@endforelse
</tbody></table></div>{{ $shifts->links() }}</div></div>
</x-orders.workspace>
</div></div>
