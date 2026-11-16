<div class="content order-workspace"><div class="main-content">
@include('orders.styles')
<div class="o-toolbar"><a class="ti-btn ti-btn-light" href="{{ route('cash-desk.index') }}">← Cassa</a><h1 class="o-title">Configurazione fiscale</h1><span class="o-badge">Sandbox · Italia</span></div>
@include('orders.errors')
<div class="o-toolbar mb-4"><button class="ti-btn ti-btn-light" wire:click="checkConnection" wire:loading.attr="disabled">Verifica connessione sandbox</button></div>
<p class="o-panel mb-4">Questa integrazione usa esclusivamente l’ambiente di test Openapi. I documenti di prova non certificano gli incassi reali. Lo stato delle sedi va sincronizzato dopo l’attivazione; non è richiesto inserire credenziali dell’Agenzia delle Entrate.</p>
<form wire:submit="save" class="o-panel">
<h2 class="font-semibold">Azienda del tenant</h2>
<div class="o-toolbar mt-3">
<label>Partita IVA <input class="o-input" wire:model="company.fiscal_id" maxlength="11" required @readonly($profile?->remote_id)></label>
<label>Ragione sociale <input class="o-input" wire:model="company.name" maxlength="255" required @readonly($profile?->remote_id)></label>
<label>Email azienda <input class="o-input" wire:model="company.email" type="email" required @readonly($profile?->remote_id)></label>
</div>
<h3 class="font-semibold mt-3">Sede legale</h3><div class="o-toolbar mt-3">
@foreach(['street_address'=>'Via','street_number'=>'Civico','zip_code'=>'CAP','city'=>'Comune','province'=>'Provincia (sigla)'] as $field=>$label)
<label>{{ $label }} <input class="o-input" wire:model="company.address.{{ $field }}" required @readonly($profile?->remote_id)></label>
@endforeach
</div>
<div class="o-toolbar mt-3"><label>Natura per righe con IVA zero <select class="o-input" wire:model="company.zero_vat_code"><option value="">Da configurare se necessaria</option>@foreach(['N1','N2','N3','N4','N5','N6'] as $code)<option value="{{ $code }}">{{ $code }}</option>@endforeach</select></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">Salva dati azienda</button></div>
<p class="o-muted">Anche in sandbox CAP, comune e provincia devono essere coerenti fra loro secondo il registro CAP italiano. L’elenco Company Sandbox non è un elenco di aziende già registrate in Invoice.</p>
<p class="o-muted">Il salvataggio è locale. Dopo la registrazione i dati aziendali sono bloccati; la natura IVA va scelta secondo il proprio regime fiscale.</p>
</form>
@if($profile)
<div class="o-panel mt-4"><div class="o-toolbar"><h2 class="font-semibold">Sedi e registratori</h2><button class="ti-btn ti-btn-light" wire:click="sync" wire:loading.attr="disabled">Sincronizza azienda e sedi</button><span class="o-badge">{{ $profile->enabled ? 'E-Receipts abilitato' : 'Da abilitare / verificare' }}</span></div>
<form wire:submit="provision" wire:confirm="Inviare la configurazione della sede alla sandbox Openapi?" class="mt-3">
<div class="o-toolbar"><label>Nome sede <input class="o-input" wire:model="storeLabel" maxlength="50" required></label><label>Nome registratore <input class="o-input" wire:model="registerLabel" maxlength="50" required></label><label><input type="checkbox" wire:model.live="sameAddress"> Indirizzo uguale alla sede legale</label></div>
@if(!$sameAddress)<div class="o-toolbar mt-3">@foreach(['street_address'=>'Via','street_number'=>'Civico','zip_code'=>'CAP','city'=>'Comune','province'=>'Provincia (sigla)'] as $field=>$label)<label>{{ $label }} <input class="o-input" wire:model="storeAddress.{{ $field }}" required></label>@endforeach</div>@endif
<button class="ti-btn ti-btn-primary-full mt-3" wire:loading.attr="disabled">{{ $profile->remote_id ? 'Aggiungi sede sandbox' : 'Attiva azienda e prima sede sandbox' }}</button>
</form>
@foreach($stores as $store)<div class="o-line mt-4"><strong>{{ $store->label }}</strong> · {{ $store->status }} · {{ $store->operational_status }} · {{ $store->active ? 'Attiva' : 'Non attiva' }}<p class="o-muted">Matricola: {{ $store->pem_serial ?: 'In attivazione' }} · Ultima verifica: {{ $store->synced_at?->format('d/m/Y H:i') }}</p>
@foreach($store->registers as $register)<p>{{ $register['label'] }} · {{ $register['status'] ?? 'PENDING' }}</p>@endforeach
<p>Ultima chiusura registrata in app: {{ $store->last_closed_at?->format('d/m/Y H:i') ?? 'Nessuna' }}</p>
<div class="o-toolbar mt-2"><button class="ti-btn ti-btn-light" wire:click="events('{{ $store->id }}')" wire:loading.attr="disabled">Verifica eventi della sede</button><button class="ti-btn ti-btn-light" wire:click="close('{{ $store->id }}')" wire:confirm="Eseguire la chiusura fiscale sandbox? Il turno gestionale resta separato." wire:loading.attr="disabled">Chiusura fiscale sandbox</button></div></div>@endforeach
<p class="o-muted mt-3">L’attivazione può richiedere 60–120 secondi. Per sedi ONLINE il fornitore richiede una chiusura almeno ogni 24 ore; senza chiusura può bloccare le emissioni. Nessuna chiusura viene reinviata automaticamente.</p>
@if($events)<details class="mt-3" open><summary>Eventi remoti della sede</summary>@foreach($events as $event)<p>{{ $event['id'] }} · {{ $event['action'] }} · {{ $event['created_at'] }}</p>@endforeach</details>@endif
</div>
<div class="o-panel mt-4"><h2 class="font-semibold">Cassieri</h2><form wire:submit="createCashier" class="o-toolbar mt-3"><label>Utente RistoPilot <select class="o-input" wire:model.live="userId"><option value="0">Seleziona un utente</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}{{ $user->hasRole('admin') ? ' · Admin (cassa abilitata)' : '' }}</option>@endforeach</select></label><label>Email cassiere Openapi <input class="o-input" type="email" wire:model="cashierEmail" maxlength="255" placeholder="Vuota: email utente RistoPilot"></label><button class="ti-btn ti-btn-primary-full" wire:loading.attr="disabled">Registra cassiere sandbox</button><button type="button" class="ti-btn ti-btn-light" wire:click="syncCashiers" wire:loading.attr="disabled">Sincronizza cassieri</button></form>
@foreach($cashiers as $cashier)<div class="o-line mt-4"><p><strong>{{ $cashier->name }}</strong> · {{ $cashier->email }} · {{ ['pending'=>'In attesa di registrazione / verifica','email_conflict'=>'Email già utilizzata in Openapi: scegline una diversa','unknown'=>'Esito da verificare','active'=>'Attivo','unverified'=>'Associazione da verificare','inactive'=>'Non attivo'][$cashier->status] ?? $cashier->status }} · {{ $cashier->remote_id ?: 'Non ancora collegato' }}</p>
<form wire:submit="saveWorkstation('{{ $cashier->id }}')" class="mt-3"><div class="o-toolbar">
<label>Sede predefinita <select class="o-input" wire:model.live="workstations.{{ $cashier->id }}.store_id"><option value="">Automatica solo se unica</option>@foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->label }} · {{ $store->status }}</option>@endforeach</select></label>
<label>Registratore predefinito <select class="o-input" wire:model="workstations.{{ $cashier->id }}.register_id"><option value="">Automatico solo se unico</option>@foreach(($stores->firstWhere('id', $workstations[$cashier->id]['store_id'] ?? '')?->registers ?? []) as $register)@if(isset($register['cr_id']))<option value="{{ $register['cr_id'] }}">{{ $register['label'] }} · {{ $register['status'] ?? 'PENDING' }}</option>@endif @endforeach</select></label>
<label>Default per incassi manuali <select class="o-input" wire:model="workstations.{{ $cashier->id }}.manual_method"><option value="">Da specificare sul conto</option><option value="cash">Contanti</option><option value="card">Carta</option></select></label>
<label><input type="checkbox" wire:model="workstations.{{ $cashier->id }}.allow_switch"> Consenti all’operatore di cambiare e ricordare la postazione</label>
<button class="ti-btn ti-btn-light" wire:loading.attr="disabled">Salva postazione cassiere</button></div></form></div>@endforeach
<p class="o-muted mt-3">Gli admin possono sempre cambiare postazione. Per gli altri operatori il cambio è bloccato finché non viene consentito qui. Con una sola sede e un solo registratore operativi la selezione è automatica. Il metodo degli incassi manuali è una proposta da verificare sul conto, non modifica i pagamenti già registrati.</p>
<p class="o-muted mt-3">Gli amministratori hanno già il permesso cassa, anche se non compaiono nella matrice Personale. Ogni operatore deve comunque essere associato a un cassiere Openapi attivo. L’email fiscale può essere diversa da quella usata per accedere a RistoPilot: modificarla qui non cambia l’accesso. Se Openapi la segnala già utilizzata e non compare nei cassieri di questa azienda, usa un’email di test diversa.</p></div>
@endif
<div class="o-panel mt-4"><h2 class="font-semibold">Operazioni da verificare</h2>
@forelse($operations as $operation)<div class="o-line mt-3"><strong>{{ $operation->action }}</strong> · {{ $operation->status }} · {{ $operation->created_at->format('d/m/Y H:i:s') }}<p>{{ $operation->error }}</p>
@if($operation->fiscal_document_id)<a class="ti-btn ti-btn-light" href="{{ route('cash-desk.fiscal', \App\Models\FiscalDocument::find($operation->fiscal_document_id)->order_id) }}">Verifica documento del conto</a>
@elseif($operation->action === 'close')<label>ID evento chiusura verificato <input class="o-input" wire:model="eventIds.{{ $operation->id }}"></label><button class="ti-btn ti-btn-light" wire:click="reconcileClose('{{ $operation->id }}')" wire:confirm="Confermi che questo evento remoto corrisponde alla chiusura rimasta in sospeso?" wire:loading.attr="disabled">Riconcilia chiusura</button>
@else<p class="o-muted">Usa la sincronizzazione azienda o cassieri. L’assenza nell’elenco remoto non autorizza un nuovo invio.</p>@endif
</div>@empty<p class="o-muted mt-3">Nessuna operazione in sospeso.</p>@endforelse
</div>
<div class="o-panel mt-4"><h2 class="font-semibold">Utilizzo del servizio · database tenant</h2><p>{{ $callCount }} chiamate · Stima API {{ number_format($estimatedCost/1000000,6,',','.') }} € · Chiamate senza tariffa: {{ $unpricedCalls }}</p><p class="o-muted">In sandbox la stima è zero. Sono registrati anche verifiche, errori e timeout. Questi valori non sono addebiti al portafoglio né un rendiconto del fornitore.</p>
<div style="overflow:auto"><table class="o-table"><thead><tr><th>Data</th><th>Operazione</th><th>Esito</th><th>HTTP</th><th>Dettaglio errore</th><th>Costo stimato €</th></tr></thead><tbody>@foreach($calls as $call)<tr><td>{{ $call->created_at->format('d/m/Y H:i:s') }}</td><td>{{ $call->action }}</td><td>{{ $call->outcome }}</td><td>{{ $call->http_status ?? '—' }}</td><td>{{ $call->error_code }} {{ $call->error_message }}</td><td>{{ $call->estimated_cost_micros === null ? 'Da tariffare' : number_format($call->estimated_cost_micros/1000000,6,',','.') }}</td></tr>@endforeach</tbody></table></div></div>
</div></div>
