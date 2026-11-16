# Fase 2 — Invoice / E-Receipts in sandbox

## Stato e fonti verificate

Implementazione basata sullo schema ufficiale scaricato il 16 novembre 2026:

- [Documentazione Invoice](https://console.openapi.com/it/apis/invoice/documentation/).
- [OpenAPI ufficiale in italiano](https://console.openapi.com/oas/it/invoice.openapi.json), schemi `IT-configuration`, `Store`, `CashRegister`, `POSTCashier`, `POSTEReceipt`, `POSTEReceiptReturn`, `ITStoreActionResponse`.

Lo schema attuale dichiara **E-Receipts abilitabile solo nell'ambiente TEST**. La sintesi HTML della documentazione e gli elenchi degli scope non descrivono sempre tutti i campi più recenti: per payload e percorsi è stato utilizzato il file OpenAPI. La creazione di store e registratori avviene in `POST /IT-configurations` oppure `PATCH /IT-configurations/{fiscal_id}`, non tramite un presunto `POST /IT-store`.

L'integrazione è intenzionalmente vincolata a `https://test.invoice.openapi.com`: nessun interruttore di produzione né host configurabile da browser. I documenti sandbox **non certificano gli incassi reali**. I test automatici usano HTTP simulato. Con il token sandbox configurato dall’utente sono state verificate anche letture reali e il download PDF di un documento già emesso nella sandbox; nessuna verifica è stata eseguita in produzione.

## Attivazione locale

Applicare prima le migrazioni della cassa; poi:

```sh
docker exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_040000_create_fiscal_module.php
```

Nel file di ambiente dell'applicazione PHP (`code/.env`, oppure variabile di ambiente del servizio) impostare il **bearer token sandbox**, non una credenziale dell'Agenzia delle Entrate:

```dotenv
OPENAPI_INVOICE_SANDBOX_TOKEN=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

Il placeholder viene riconosciuto e blocca le chiamate prima dell'invio. Inserire il token effettivo esclusivamente sul server, senza commetterlo. Se Laravel usa configurazione in cache, rigenerarla con la procedura abituale del deployment dopo la modifica. Le nuove rotte richiedono anche l'aggiornamento di eventuali cache delle rotte.

Le nuove tabelle non modificano i dati degli incassi esistenti. Il rollback cancella il registro fiscale locale: non eseguirlo su dati da conservare e non usarlo come procedura di annullamento remoto.

## Diagnosi token e attivazione

Nella configurazione fiscale, **Verifica connessione sandbox** esegue soltanto `GET /IT-configurations`. Una risposta positiva conferma l'autenticazione per quella lettura, senza creare aziende o sedi; gli scope POST/PATCH restano verificati dal fornitore durante le rispettive operazioni. La lettura diagnostica è registrata nel registro chiamate.

- **HTTP 401**: l'autenticazione non è accettata per la specifica rotta. Controllare token sandbox, scadenza, scope e caricamento della variabile `OPENAPI_INVOICE_SANDBOX_TOKEN` nel processo PHP.
- **HTTP 404 su sincronizzazione, errore 424**: la configurazione richiesta non è trovata/accessibile al token. Non significa automaticamente che il token sia errato: un account appena creato può avere l'elenco configurazioni vuoto.
- **HTTP 422, errore 112 (`This fiscal_id already exists`)**: il fornitore rifiuta la creazione perché la partita IVA risulta già presente. Se la stessa partita IVA restituisce 404 in lettura e non compare nell'elenco dell'account, non è possibile importarla o modificarla con quel token. Occorre una partita IVA adatta ai propri test oppure la verifica di Openapi sull'accessibilità della configurazione. Non tentare di sostituire l'azienda esistente tramite PATCH.
- Gli altri **422** riportano ora il messaggio e gli eventuali errori di campo restituiti dal fornitore. Il codice e il dettaglio sanitizzato sono visibili nel registro chiamate. Non vengono memorizzati il corpo completo della risposta, header o credenziali.

L'[elenco Company Sandbox](https://docs.openapi.it/company-sandbox-examples.html) è usato per provare le ricerche del servizio Company: non predispone configurazioni Invoice nel proprio account. Anche in sandbox Invoice, CAP, comune e provincia dell'indirizzo devono essere coerenti secondo il registro CAP italiano. Il codice 112 riguarda la duplicazione del fiscal_id, non dimostra un errore nell'indirizzo.

Per gli aggiornamenti da un'installazione precedente applicare anche la migrazione aggiuntiva:

```sh
docker exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_050000_add_fiscal_api_error_details.php
```

## Flusso amministratore

1. Aprire **Impostazioni → Configurazione fiscale · Sandbox**, oppure `/manage/settings/fiscal`.
2. Salvare partita IVA italiana, ragione sociale, email e sede legale. 
Vedi https://docs.openapi.it/company-sandbox-examples.html#1 con ESEMPI!
Il salvataggio è locale. Dopo la registrazione remota questi dati sono bloccati in questa versione; la natura IVA zero resta configurabile. Non vengono abilitate fatture passive, fatture attive, Smart Receipts o conservazione.
3. Inserire sede e nome del registratore. Attivare azienda e prima sede sandbox. Per sedi successive viene usato il PATCH additivo per label. Ogni sede nasce con un registratore; i registratori già presenti nella configurazione vengono importati e sono selezionabili.
4. Attendere il provisioning asincrono, indicativamente 60–120 secondi secondo Openapi, e utilizzare **Sincronizza azienda e sedi**. L'emissione richiede sede attiva, stato `ONLINE`, matricola `pem_serial`, abilitazione E-Receipts e registratore `ONLINE`.
5. Registrare un cassiere per ogni utente che emette documenti, incluso l'amministratore. Gli admin hanno già il permesso cassa e sono volutamente esclusi dalla matrice Personale; il selettore fiscale li include insieme agli altri utenti abilitati. Il campo **Email cassiere Openapi** può contenere un indirizzo distinto da `User.email`: non modifica il login RistoPilot. Vuoto significa usare l'email dell'utente. Un cassiere già collegato non può cambiare email da questa schermata. **Sincronizza cassieri** verifica lo stato e permette di recuperare una creazione con esito incerto solo quando c'è una corrispondenza univoca per azienda e identità del cassiere. Lo schema della lista può omettere `fiscal_id`: in quel caso viene letto `GET /IT-cashiers/{id}` per provare l'appartenenza alla configurazione prima di collegarlo. Un indirizzo già presente nei sistemi Openapi non implica che il relativo cassiere sia disponibile per questa azienda.

Si segue l'isolamento esistente del progetto: **un database per tenant**. Il bearer token rimane una credenziale gestita sul server. Prima di un futuro uso produttivo con token condiviso fra tenant serviranno anche un vincolo centrale sull'associazione tenant/partita IVA e la verifica dell'identità dell'esercente; un amministratore tenant non deve poter rivendicare liberamente configurazioni appartenenti ad altri esercenti. Il sandbox di questa fase non costituisce tale verifica.

## Postazione e preferenze per operatore

Nella scheda di ogni cassiere in `/manage/settings/fiscal`, l'amministratore può salvare:

- sede e registratore predefiniti;
- una proposta per il metodo degli incassi storici registrati come `manual` (`cash`, `card` oppure da specificare sul conto);
- il permesso di cambiare e ricordare la postazione, inizialmente disabilitato per gli operatori.

Gli admin possono sempre cambiare postazione. Gli altri operatori usano l'assegnazione amministrativa e non possono aggirarla modificando i valori Livewire: i servizi verificano la scelta prima di emissioni, rettifiche e chiusure fiscali. Per cambiare sede/cassa devono avere il relativo permesso configurato dall'admin. Con una sola sede operativa e un solo registratore operativo la scelta è automatica anche senza un'assegnazione esplicita. Con più alternative, il sistema non sceglie arbitrariamente. Una postazione già assegnata ma divenuta inattiva non viene sostituita silenziosamente con un'altra.

La pagina documenti carica le preferenze dal cassiere associato all'utente corrente. **Ricorda questa postazione** salva sede e cassa e, se il conto contiene incassi manuali, il relativo default scelto. Ogni nuova emissione ricorda inoltre la postazione selezionata dagli utenti autorizzati al cambio. Le preferenze sono persistenti per utente nel database del tenant, non soltanto nella sessione del browser, e le modifiche sono registrate come operazioni locali `workstation_preferences` senza chiamate API.

I metodi già registrati come contanti, carta o buoni pasto vengono letti dai pagamenti, senza reinterpretarli. Il selettore **Incassi registrati come manuali** compare soltanto se esiste un incasso manuale positivo; il default resta una proposta da verificare per il conto e non modifica gli incassi originali. Anche il numero dei buoni pasto viene richiesto soltanto sui conti che li contengono.

Applicare la migrazione aggiuntiva alle installazioni precedenti:

```sh
docker exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_060000_add_cashier_workstation_defaults.php
```

## Documenti commerciali

Dal dettaglio conto selezionare **Documenti commerciali · Sandbox**, percorso `/manage/cash-desk/orders/{order}/fiscal`.

L'incasso non emette automaticamente un documento. L'emissione esplicita richiede:

- conto interamente saldato, con fotografia del motore cassa e righe ancora coerenti con essa;
- sede e registratore selezionati appartenenti alla configurazione;
- cassiere attivo associato all'utente corrente;
- metodo effettivo esplicito per eventuali pagamenti registrati come `manual`;
- numero effettivo dei buoni pasto se presenti;
- natura `N1`–`N6` esplicitamente configurata se ci sono righe con aliquota zero.

Si emette **una vendita per intero conto**, anche con più incassi o paganti. Le N quote del motore cassa non generano N documenti fiscali. Le mance restano separate e non sono incluse nei corrispettivi inviati da questo modulo.

Gli sconti già fotografati vengono ripartiti fra i prodotti rispettando i totali per aliquota. Si inviano prezzi unitari già scontati e il totale dello sconto nel testo aggiuntivo, senza riapplicare uno sconto globale. Quando la divisione non è esatta al centesimo, uno stesso prodotto viene rappresentato da al massimo due righe con prezzi unitari differenti di un centesimo. I resi si riferiscono agli ID delle righe fiscali risultanti.

La vendita usa `POST /IT-e-receipts`. La risposta è considerata emessa solo quando lo stato è `sent` (senza distinzione maiuscole) e c'è un `document_number`; `ready` senza numero resta in attesa. **Verifica stato** interroga `GET /IT-e-receipts/{id}`. Il PDF viene scaricato dal provider con lo stesso endpoint e intestazioni uniche `Accept: application/pdf` e `Content-Type: application/pdf`, senza intestazioni JSON concorrenti e senza rigenerare localmente un documento presentato come fiscale. È stato verificato un file PDF reale di sandbox. Se il fornitore risponde invece con JSON o un formato non PDF, il download viene rifiutato con un messaggio specifico: il documento già emesso non cambia stato.

### Resi e annullamenti

**Verifica articoli restituibili** usa `GET /IT-e-receipts/{id}/returnable-items`. L'interfaccia permette un articolo con quantità intera per operazione; il servizio supporta un insieme di articoli. Prima dell'invio si ricontrollano disponibilità remota, quantità già rese localmente e stato della vendita. Il reso usa `PATCH /IT-e-receipts/{id}`; l'annullamento usa `DELETE /IT-e-receipts/{id}`. Ogni rettifica ha un record separato, collegato alla vendita originale.

Non vengono cancellati né modificati gli incassi originali. Reso e annullamento fiscale **non eseguono un rimborso monetario**, non riaprono il conto e non liberano il vincolo sull'unica vendita già inviata. La riconciliazione dei rimborsi appartiene alla fase 4.

### Chiusure

Disponibili nella configurazione fiscale e nella pagina **Turni di cassa**. Usano `POST /IT-store/{id}/close` con oggetto JSON vuoto. La chiusura del cassetto gestionale e quella fiscale sono azioni distinte. La chiusura fiscale viene registrata solo con `event_id` valido e blocca una ripetizione immediata finché non risulta una nuova apertura della sessione. Verificare un vecchio documento non riapre localmente la sessione.

Lo schema richiede la chiusura almeno ogni 24 ore mentre lo store è `ONLINE`, altrimenti nuove emissioni possono essere rifiutate. Il modulo mostra l'ultima chiusura conosciuta e offre la verifica degli eventi remoti. Non esegue chiusure o retry in background. Documenti pending/unknown vanno verificati prima di chiudere.

## Duplicati, timeout e ripristino

L'API non offre una chiave di idempotenza E-Receipts. RistoPilot usa quindi:

- un vincolo univoco sulla vendita del conto;
- una prenotazione persistente dell'operazione **prima** della chiamata HTTP;
- un lock logico univoco per sede, che serializza emissioni, resi, annullamenti e chiusure; configurazione e cassieri hanno lock separati;
- richiesta e risposta dell'operazione cifrate nel database; nessun bearer token nei payload o nel registro chiamate;
- nessun retry HTTP e nessun reinvio su errori di rete, risposte 5xx, 409, 408, redirect o risposte 2xx non interpretabili.

Gli esiti incerti restano `unknown`; un processo interrotto può lasciare `in_flight`/`submitting`. Anche una risposta ricevuta ma non applicata localmente mantiene il lock. Una risposta 4xx conclusiva di rifiuto libera il lock e permette un invio esplicito corretto, conservando gli eventi precedenti. Un 409 non viene considerato una prova di mancata emissione.

Per una vendita senza ID remoto noto, la ricerca remota consente di consultare 100 documenti alla volta e indicare l'ID del documento da riconciliare. Il sistema controlla riferimento univoco RistoPilot, righe, pagamenti, totale, azienda, sede, registratore e tipo. Per resi/annullamenti controlla anche vendita padre, data dell'operazione e quantità rese. Non sbloccare un'operazione soltanto perché il documento non compare ancora nella lista remota.

**Eccezione verificata per i cassieri:** `POST /IT-cashiers` con **HTTP 409 / codice 652** indica esplicitamente che l'email esiste già e che la creazione è stata rifiutata. Questo caso è registrato come `rejected`, con stato locale `email_conflict`, anziché `unknown`. Richiede un indirizzo fiscale diverso, oppure la sincronizzazione di un cassiere già accessibile e appartenente alla stessa azienda. Non viene effettuato alcun retry automatico; ripetere la stessa email già rifiutata viene bloccato prima della chiamata. Gli altri 409, i timeout e i conflitti delle emissioni fiscali restano incerti.

Gli eventuali record precedenti `unknown` per questo preciso conflitto vengono riclassificati soltanto se il registro chiamate collegato prova azione `create_cashier`, HTTP 409 e codice 652 per quell'operazione. Non basta il testo del messaggio né l'assenza nell'elenco remoto. La riconciliazione avviene prima di una registrazione o sincronizzazione dei cassieri e conserva lo storico. Non è necessaria una nuova migrazione.

Le verifiche di processi ancora in corso non possono liberare il lock prima del timeout configurato più un margine. Per configurazioni e cassieri si usano le rispettive sincronizzazioni. Per una chiusura incerta, un amministratore può scegliere l'evento remoto corrispondente fra gli eventi della sede; vengono verificati azione, azienda, sede e data. Se non esiste una prova conclusiva, il lock resta attivo e serve la verifica con il fornitore: nessun pulsante forza un reinvio.

## Registro chiamate e costi

`fiscal_api_calls` registra prima dell'invio ogni tentativo HTTP, inclusi GET, PDF, provisioning, errori e timeout: provider, ambiente, azione, hash della richiesta, operatore, operazione associata, esito, stato HTTP, durata e tariffa stimata al momento della chiamata. I dati sono nel database del tenant.

I costi sono interi in **micro-euro** (1 € = 1.000.000), per poter rappresentare tariffe inferiori a un centesimo. In sandbox la stima è zero. Il contratto/configurazione prevede tariffe per azione; una futura tariffa non configurata deve risultare `unpriced`, mai zero per assenza di informazioni. Stime, anche per tentativi falliti, non sono importi fatturati dal fornitore. Canoni sede, rinnovi, abbonamenti e verifiche del rendiconto richiedono una modellazione dedicata prima di produzione. Nessun addebito al futuro portafoglio tenant viene effettuato ora.

## Limiti espliciti

Non sono implementati in questa fase: produzione E-Receipts, certificazione reale senza RT, fatturazione/SDI, conservazione, accrediti o rimborsi, wallet/Stripe, documenti per singolo pagante, riapertura conti, procedure automatiche per outage/inattività, rinnovi/disattivazioni store, modifica dati aziendali dopo registrazione e creazione aggiuntiva di registratori in una sede già esistente. I registratori esistenti vengono comunque letti e selezionati.

I conti omaggio e gli incassi esclusivamente in contanti con totale non multiplo di 5 centesimi vengono bloccati all'emissione. Il motore di fase 1 non registra ancora l'arrotondamento obbligatorio del contante: il modulo fiscale non inventa un incasso differente da quello registrato. I pagamenti misti quadrano esattamente senza aggiungere arrotondamenti. Questi casi vanno completati prima dell'uso produttivo.

## Verifica automatica

```sh
docker exec \
  -e APP_CONFIG_CACHE=/tmp/ristopilot-cash-test-config.php \
  -e APP_ENV=testing -e DB_CONNECTION=mysql \
  -e DB_HOST=ristopilot-tenant_database \
  -e DB_DATABASE=ristopilot_tenant_testing \
  -e DB_USERNAME=test -e DB_PASSWORD=test \
  ristopilot-tenant_php php artisan test --compact \
  tests/Feature/Fiscal tests/Feature/Orders tests/Feature/PublicOrders
```

I test fiscali bloccano le richieste HTTP non simulate e non consumano credito API. Coprono percorsi e payload, provisioning asincrono, autorizzazioni, importi/IVA, mance escluse, vincoli di unicità, errori certi e incerti, recupero esiti, resi/annullamenti, chiusure, PDF, registro chiamate e rendering delle pagine.
