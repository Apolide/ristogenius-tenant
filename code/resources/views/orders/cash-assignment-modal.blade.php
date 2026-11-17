@if($assignmentModal)
<div class="o-modal" x-data x-on:keydown.escape.window="$wire.set('assignmentModal', false)">
<div class="o-dialog" role="dialog" aria-modal="true" aria-labelledby="cash-assignment-title" tabindex="-1" x-ref="dialog" x-init="$nextTick(() => $refs.dialog.focus())">
<h2 id="cash-assignment-title" class="o-title">{{ __('cash_desk.assign_items.payers_items') }}</h2>
@if($errors->any())<div class="o-error mt-3" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form wire:submit="saveAssignment">
<label class="o-field"><span>{{ __('cash_desk.assign_items.payer_name') }}</span><input class="form-control" wire:model="shareLabel" maxlength="100" required></label>
<p class="o-muted">{{ __('cash_desk.assign_items.assignable_items_and_quantities') }}</p>
<table class="table ti-custom-table w-full"><thead><tr><th>{{ __('cash_desk.assign_items.product') }}</th><th>{{ __('cash_desk.assign_items.available') }}</th><th>{{ __('cash_desk.assign_items.quantity') }}</th><th>{{ __('cash_desk.assign_items.selected_total') }}</th></tr></thead><tbody>
@foreach($assignmentLines as $line)
<tr wire:key="assign-line-{{ $line['id'] }}"><td>{{ $line['name'] }}</td><td>{{ $line['available'] }}</td><td><input class="form-control" style="width:85px" type="number" min="0" max="{{ $line['available'] }}" wire:model.live.debounce.250ms="selectedQuantities.{{ $line['id'] }}" aria-label="{{ __('cash_desk.assign_items.quantity_for_product', ['product' => $line['name']]) }}" @disabled($line['available'] === 0)></td><td>{{ number_format($line['amount_cents']/100,2,',','.') }} €</td></tr>
@endforeach
</tbody></table>
<div class="box mt-4"><div class="box-body"><p>{{ __('cash_desk.assign_items.total_for_this_payer') }} <strong>{{ number_format($assignmentPreview/100,2,',','.') }} €</strong></p><p>{{ __('cash_desk.assign_items.unassigned_balance_after_saving') }} {{ number_format($assignmentRemainder/100,2,',','.') }} €</p></div></div>
<p class="o-muted mt-3">{{ __('cash_desk.assign_items.unselected_items_and_paid_units_help') }}</p>
<div class="service-form-grid mt-4"><button type="button" class="ti-btn ti-btn-light" wire:click="$set('assignmentModal', false)"><i class="las la-times" aria-hidden="true"></i> {{ __('cash_desk.assign_items.cancel') }}</button><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-save" aria-hidden="true"></i> {{ __('cash_desk.assign_items.save_payers_items') }}</button></div>
</form>
</div></div>
@endif
