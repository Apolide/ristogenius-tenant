@if($assignmentModal)
<div class="o-modal" x-data x-on:keydown.escape.window="$wire.set('assignmentModal', false)">
<div class="o-dialog" role="dialog" aria-modal="true" aria-labelledby="cash-assignment-title" tabindex="-1" x-ref="dialog" x-init="$nextTick(() => $refs.dialog.focus())">
<h2 id="cash-assignment-title" class="o-title">{{ __('Prodotti del pagante') }}</h2>
@if($errors->any())<div class="o-error mt-3" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form wire:submit="saveAssignment">
<label class="o-field"><span>{{ __('Nome pagante') }}</span><input class="form-control" wire:model="shareLabel" maxlength="100" required></label>
<p class="o-muted">{{ __('Scegli le quantità da addebitare. I prodotti già assegnati ad altri sono esclusi; coperti, servizio e consegna sono selezionabili separatamente. Gli importi includono lo sconto del conto.') }}</p>
<table class="table ti-custom-table w-full"><thead><tr><th>{{ __('Prodotto') }}</th><th>{{ __('Disponibili') }}</th><th>{{ __('Quantità') }}</th><th>{{ __('Totale scelto') }}</th></tr></thead><tbody>
@foreach($assignmentLines as $line)
<tr wire:key="assign-line-{{ $line['id'] }}"><td>{{ $line['name'] }}</td><td>{{ $line['available'] }}</td><td><input class="form-control" style="width:85px" type="number" min="0" max="{{ $line['available'] }}" wire:model.live.debounce.250ms="selectedQuantities.{{ $line['id'] }}" aria-label="{{ __('Quantità :product', ['product' => $line['name']]) }}" @disabled($line['available'] === 0)></td><td>{{ number_format($line['amount_cents']/100,2,',','.') }} €</td></tr>
@endforeach
</tbody></table>
<div class="box mt-4"><div class="box-body"><p>{{ __('Totale di questo pagante:') }} <strong>{{ number_format($assignmentPreview/100,2,',','.') }} €</strong></p><p>{{ __('Rimanenza non assegnata dopo il salvataggio:') }} {{ number_format($assignmentRemainder/100,2,',','.') }} €</p></div></div>
<p class="o-muted mt-3">{{ __('Quanto non scegli resta in “Rimanenza / ultimo pagante”. Le unità già incassate non possono essere riassegnate.') }}</p>
<div class="service-form-grid mt-4"><button type="button" class="ti-btn ti-btn-light" wire:click="$set('assignmentModal', false)"><i class="las la-times" aria-hidden="true"></i> {{ __('Annulla') }}</button><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled"><i class="las la-save" aria-hidden="true"></i> {{ __('Salva prodotti del pagante') }}</button></div>
</form>
</div></div>
@endif
