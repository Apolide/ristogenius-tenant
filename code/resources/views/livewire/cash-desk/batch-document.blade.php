<div class="o-panel mt-3" wire:poll.30s.visible>
<strong>{{ __('Documento di questo incasso · Sandbox') }}</strong>
@foreach($errors->all() as $error)<p class="o-error">{{ $error }}</p>@endforeach
@if($wholeDocument)
<p class="o-muted">{{ __('Incasso incluso nel documento dell’intero ordine:') }} {{ $wholeDocument->document_number ?: $wholeDocument->status }}.</p>
@elseif($document && !in_array($document->status,['draft','rejected']))
<p>{{ $document->document_kind === 'invoice' ? __('Fattura elettronica') : __('Documento commerciale') }} {{ $document->document_number }} · {{ ['issued'=>__('Emesso'),'pending'=>__('In elaborazione: verifica esito'),'unknown'=>__('Esito da verificare: non reinviare'),'submitting'=>__('Invio in corso'),'sdi_rejected'=>'Errore / scarto: verifica dettaglio SDI','voided'=>__('Annullato')][$document->status] ?? $document->status }}</p>
@if($document->document_kind === 'invoice')<p class="o-muted">{{ $document->remote_data['details']['sdi_message'] ?? '' }}</p>@endif
<div class="o-toolbar mt-2">
@if(!$document->remote_id)<label>{{ __('ID remoto verificato') }} <input class="o-input" wire:model="remoteId"></label>@endif
<button class="ti-btn ti-btn-light" wire:click="verify" wire:loading.attr="disabled">{{ __('Verifica esito') }}</button>
@if($document->remote_id)<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.fiscal-pdf',$document) }}">{{ __('Scarica PDF sandbox') }}</a>@endif
@if($document->document_kind === 'invoice')<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.fiscal-xml',$document) }}">{{ __('Scarica XML') }}</a>@endif
@if($document->document_kind === 'invoice' && $document->status === 'issued' && $corrections->whereNotIn('status',['draft','rejected'])->isEmpty())<button class="ti-btn ti-btn-light" wire:click="credit" wire:confirm="{{ __('Emettere una nota di credito totale sandbox? Il rimborso del denaro va registrato separatamente.') }}" wire:loading.attr="disabled">{{ __('Nota di credito totale') }}</button>@endif
</div>
@foreach($corrections as $correction)<p>{{ __('Nota di credito') }} {{ $correction->document_number }} · {{ \App\Support\UiLabels::code($correction->status, 'statuses') }} · <a href="{{ route('cash-desk.fiscal-xml',$correction) }}">XML</a> · <a href="{{ route('cash-desk.fiscal',$document->order_id) }}">{{ __('Verifica esito') }}</a></p>@endforeach
@elseif($profile?->enabled)
@if($document)<p class="o-error">{{ __('Invio rifiutato dal servizio. Correggi i dati prima di riprovare.') }}</p>@endif
<div class="o-toolbar mt-3">
@if($canSwitch)
<label>{{ __('Sede') }} <select class="o-input" wire:model.live="storeId"><option value="">{{ __('Seleziona sede') }}</option>@foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->label }}</option>@endforeach</select></label>
<label>{{ __('Registratore') }} <select class="o-input" wire:model="registerId"><option value="">{{ __('Seleziona registratore') }}</option>@foreach(($stores->firstWhere('id',$storeId)?->registers ?? []) as $register)@if(($register['status'] ?? '') === 'ONLINE')<option value="{{ $register['cr_id'] }}">{{ $register['label'] }}</option>@endif @endforeach</select></label>
@else<p>{{ __('Postazione assegnata:') }} {{ $stores->firstWhere('id',$storeId)?->label ?: __('Da configurare') }} · {{ $registerId }}</p>@endif
@if(!$document || $document->document_kind === 'receipt')<button class="ti-btn ti-btn-primary-full" wire:click="receipt" wire:loading.attr="disabled" wire:confirm="{{ __('Emettere il documento commerciale sandbox per questo singolo incasso?') }}">{{ __('Emetti documento commerciale') }}</button>@endif
@if($profile->invoice_enabled && (!$document || $document->document_kind === 'invoice'))<button class="ti-btn ti-btn-light" wire:click="$toggle('invoiceForm')">{{ __('Richiedi fattura') }}</button>@endif
</div>
@if(!$profile->invoice_enabled)<p class="o-muted">{{ __('Per emettere fatture abilita la fatturazione elettronica nelle impostazioni fiscali.') }}</p>@endif
@if($invoiceForm)
<form wire:submit="invoice" class="mt-3">
<label>{{ __('Cliente salvato') }} <select class="o-input" wire:model.live="recipientId"><option value="">{{ __('Nuovo destinatario') }}</option>@foreach($recipients as $recipient)<option value="{{ $recipient->id }}">{{ $recipient->name }}</option>@endforeach</select></label>
<div class="o-toolbar mt-3">
@foreach(['name'=>'Nome / Ragione sociale','vat_id'=>__('Partita IVA'),'tax_code'=>__('Codice fiscale'),'address'=>__('Indirizzo'),'zip'=>'CAP','city'=>__('Comune'),'province'=>__('Provincia'),'sdi'=>__('Codice destinatario (7 caratteri)'),'pec'=>'PEC (facoltativa)'] as $field=>$label)<label>{{ $label }} <input class="o-input" wire:model="buyer.{{ $field }}" maxlength="{{ $field === 'name' ? 80 : ($field === 'address' || $field === 'city' ? 60 : 256) }}"></label>@endforeach
</div><p class="o-muted">{{ __('Destinatari italiani B2B/B2C. Per privati o recapito PEC usa 0000000. Il destinatario sarà salvato nella rubrica. La sandbox non emette fatture valide né fornisce conservazione a norma.') }}</p>
<button class="ti-btn ti-btn-primary-full mt-3" wire:loading.attr="disabled" wire:confirm="{{ __('Inviare questa fattura elettronica alla sandbox Openapi?') }}">{{ __('Invia fattura sandbox') }}</button>
</form>
@endif
@else<p class="o-muted">{{ __('Configura il servizio fiscale sandbox per emettere documenti di prova.') }}</p>@endif
<details class="mt-3"><summary>{{ __('Rimborsi del pagamento') }}</summary>
@foreach($refunds as $refund)<p>{{ $refund->created_at->format('d/m/Y H:i') }} · {{ number_format($refund->amount_cents/100,2,',','.') }} € · {{ \App\Support\UiLabels::code($refund->method, 'methods') }} · {{ $refund->reason }}</p>@endforeach
<p class="o-muted">{{ __('Registra denaro già restituito. La registrazione non esegue storni POS e non modifica il documento fiscale: emetti separatamente il reso / annullamento o la nota di credito.') }}</p>
<form wire:submit="refund" wire:confirm="{{ __('Confermi di aver già restituito questo importo al cliente?') }}" class="o-toolbar mt-3">
<label>{{ __('Importo €') }} <input class="o-input" wire:model="refundAmount" inputmode="decimal" required></label>
<label>{{ __('Metodo') }} <select class="o-input" wire:model="refundMethod"><option value="cash">{{ __('Contanti') }}</option><option value="card">{{ __('Carta') }}</option><option value="voucher">{{ __('Buoni') }}</option></select></label>
<label>{{ __('Motivo') }} <input class="o-input" wire:model="refundReason" maxlength="255" required></label>
<label>{{ __('Riferimento esterno') }} <input class="o-input" wire:model="refundReference" maxlength="255"></label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">{{ __('Registra rimborso eseguito') }}</button></form></details>
<details class="mt-3"><summary>{{ __('Copia al cliente via email') }}</summary>
@if($copyRequest)<p>{{ __('Destinatario:') }} {{ $copyRequest->email }} · {{ $copyRequest->queued_at ? __('Copia accodata al servizio email') : __('In attesa di emissione confermata') }}.</p><p class="o-muted">{{ __('L’accodamento non conferma la ricezione dell’email. Il documento fiscale non viene emesso di nuovo.') }}</p>
@else
<form wire:submit="requestCopy" class="mt-3"><label>Email <input class="o-input" type="email" wire:model="copyEmail" maxlength="254" required></label><label class="o-field"><input type="checkbox" wire:model="copyAgreed" required> {{ __('Il cliente ha richiesto / accettato la consegna elettronica a questo indirizzo.') }}</label><p class="o-muted">{{ __('Dopo l’emissione confermata verrà accodata una copia sandbox tramite link PDF valido per 7 giorni. Per la fattura si tratta della copia di cortesia. I consensi marketing rimangono separati.') }}</p><button class="ti-btn ti-btn-light" wire:loading.attr="disabled">{{ __('Richiedi copia via email') }}</button></form>
@endif
</details>
</div>
