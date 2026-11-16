# Documenti per incasso, fatture sandbox e riconciliazione

## Operatività dalla cassa

In `/manage/cash-desk/orders/{order}`, sotto ogni incasso registrato con il nuovo checkout, si emette **un documento per conticino / operazione di incasso**. Contanti, carta e buoni della stessa operazione fanno parte dello stesso documento; le mance restano escluse. Non è necessario aspettare il saldo degli altri paganti. È una scelta operativa, non una regola generale che equipara ciascuna transazione POS a un documento fiscale. L'emissione al termine del servizio o in caso di anticipi deve seguire il caso fiscale applicabile.

L'incasso viene confermato indipendentemente dall'emissione: il checkout permette ora la scelta contestuale del documento (vedi sezione finale), mantenendo anche i pulsanti fiscali dopo il pagamento. Un errore fiscale non annulla né ripete l'incasso. La pagina `/fiscal` rimane lo storico e la gestione di resi/annullamenti/verifiche. Il vecchio documento dell'intero ordine è mantenuto soltanto per pagamenti precedenti senza conticini.

La chiave univoca `coverage_key` impedisce di inviare sia fattura sia documento commerciale per lo stesso incasso. Il lock dell'ordine impedisce sovrapposizioni con il documento dell'intero conto. I documenti già emessi sull'intero conto coprono i relativi incassi: non vanno emessi di nuovo dopo la migrazione. Un reso/annullamento non libera questa copertura.

Ogni incasso congela la propria ripartizione IVA in `cash_payment_batches.fiscal_snapshot`. Le quote per prodotti usano le unità assegnate, con il prezzo scontato già distribuito al centesimo. Quote uguali e acconti ripartiscono il lordo proporzionalmente fra i gruppi IVA residui, con resti maggiori. L'ultima quota assorbe il residuo; un incasso già salvato non cambia quando pagano gli altri. Gli incassi antecedenti alla migrazione vengono ripartiti alla prima emissione, tenendo conto di tutti gli importi preesistenti.

Sede/registratore sono proposti dalle preferenze del cassiere o dall'unica scelta operativa. L'operatore può modificarli solo se abilitato; gli admin possono sempre farlo. La postazione usata nell'emissione viene ricordata. Un incasso con rimborsi viene bloccato prima di una nuova emissione, per evitare di certificare un importo non più corrispondente ai fondi trattenuti.

## Fase 3: fatture italiane B2B/B2C sandbox

In `/manage/settings/fiscal` è presente il placeholder **Conservazione fatture**, con stato **Da configurare in RistoPilot**. Distingue il servizio dell’Agenzia delle Entrate da un futuro provider esterno e dalla trasmissione SDI. Il pulsante di configurazione è disabilitato: il riquadro non salva impostazioni, non attiva servizi e non verifica adesioni già effettuate fuori app. Non influisce sul checkout sandbox.

1. In `/manage/settings/fiscal`, salvare il **regime fiscale effettivo** RF01–RF19 e la serie di numerazione. Non è preselezionato un regime fiscale.
2. Premere **Abilita fatture sandbox**. L'azione modifica soltanto `customer_invoice: true` in `PATCH /IT-configurations/{fiscal_id}`; non abilita fatture passive o conservazione.
3. Dalla scheda dell'incasso, scegliere **Richiedi fattura**, compilare cliente italiano (P.IVA o CF, indirizzo, destinatario SDI di 7 caratteri oppure PEC con codice 0000000), quindi inviare.
4. La rubrica destinatari è locale al tenant; i dati della rubrica sono cifrati a riposo. Il documento mantiene lo snapshot originale e l'XML per audit.

XML FPR12 TD01, importi in EUR, imponibile e IVA scorporati per gruppo dal lordo effettivamente incassato. Una riga netta per gruppo evita la moltiplicazione di prezzi netti unitari già arrotondati. Numerazione annuale e per serie, riservata sotto lock. Numero/data vengono mantenuti nei tentativi successivi dopo un rifiuto HTTP definitivo. Un esito incerto non autorizza un secondo invio.

Prima del POST fattura viene eseguito `POST /IT-invoices_validate`, in XML, tracciato separatamente; solo con `data.valid=true` segue `POST /IT-invoices`, sempre con body XML grezzo. Nessun reinvio automatico delle scritture. Verifica successiva con `GET /IT-invoices/{id}`; controllati azienda, numero, data, tipo, destinatario e totale. L'XML scaricato è lo snapshot inviato; il PDF è quello restituito da Openapi.

`NEW`/`SENT` rimangono in attesa. `details.sdi_status=DELIVERED` o `NOT_DELIVERED` sono esiti finali emessi; `ERROR` diventa un errore/scarto visibile. Il servizio distingue lo stato macro dallo stato SDI. Gli esiti noti in attesa vengono letti ogni cinque minuti dal comando schedulato `fiscal:sync-invoices` (massimo 25 a esecuzione, richiede scheduler Laravel attivo). Nessun pagamento o documento viene inviato dal comando. La UI aggiorna lo stato locale ogni 30 secondi quando visibile; resta disponibile **Verifica esito**.

Un timeout senza ID remoto richiede verifica e riconciliazione esplicita con ID remoto. Uno scarto SDI con ID remoto non viene reinviato: la correzione e il reinvio di questi documenti non sono automatizzati in questa versione. La nota di credito totale TD04 collega numero/data della fattura originale; una sola nota per fattura, senza rimborso monetario automatico. Esito e XML della nota sono disponibili nello storico.

**Confini espliciti:** destinatari italiani B2B/B2C, aliquote positive ordinarie; IVA zero richiede un modello dedicato di natura/riferimento normativo/bollo e viene bloccata. Non sono implementati PA, estero, fatture passive, note di credito parziali, correzione automatica degli scarti o conservazione a norma. Lo schema ufficiale Invoice descrive `legal_storage` come non disponibile: non viene dichiarata una conservazione conforme. Produzione disabilitata.

## Fase 4: riconciliazione gestionale

`/manage/cash-desk/reconciliation`, protetto dal permesso cassa, presenta incassi/mance/rimborsi per metodo, incassi senza documento o con esito da verificare, vendite e rettifiche fiscali con esito finale, elenco rimborsi e CSV per il commercialista. Il CSV contiene movimenti distinti, non deve essere sommato indiscriminatamente: pagamento e documento possono rappresentare la stessa operazione economica. È neutralizzata l'interpretazione come formula dei campi testuali.

Il periodo filtra la data locale di registrazione, non la data servizio. Incassi e documenti possono ricadere in giornate diverse. Le rettifiche sono separate dalle vendite e i documenti in attesa sono esclusi dai totali finali. Gli incassi storici senza conticino sono nei totali/CSV ma non nell'elenco copertura per conticino.

Sotto ogni incasso si possono registrare rimborsi **già eseguiti esternamente**, con motivo, metodo, riferimento e chiave idempotente. La somma non può superare incasso più mance. I contanti restituiti vengono sottratti dal turno attivo al momento del rimborso, anche se l'incasso apparteneva a un altro turno. Senza turno il movimento resta esplicitamente nello storico; il conto non viene riaperto e il documento non viene modificato. Il rimborso e la rettifica fiscale vanno verificati separatamente.

Non è riconciliazione bancaria: addebiti/rimborsi Stripe, terminali POS, webhook PSP, commissioni e accrediti richiedono una successiva integrazione con credenziali e provider specifici. Il progetto non contiene ora una connessione verificata da attivare per questi flussi. La fase 4 completa rimane aperta per queste parti; anche la fase 3 completa rimane aperta per conservazione e casi esclusi.

## Migrazione e verifica

Migrazione aggiuntiva `2026_11_16_080000_add_payment_fiscal_documents_and_reconciliation.php`, successiva a `.070`. Mantiene documenti e incassi esistenti; non invia richieste remote. Nessuna emissione fiscale viene eseguita automaticamente sui conti preesistenti.

Test: `tests/Feature/Fiscal/BatchFiscalTest.php` più regressioni `tests/Feature/Orders`, `tests/Feature/PublicOrders`, `tests/Feature/Fiscal`, con DB dedicato e tutte le richieste fiscali simulate.

Fonti API: [schema ufficiale Invoice](https://console.openapi.com/oas/it/invoice.openapi.json). Tempistica corrispettivi: [FiscoOggi, chiarimento su pagamento totale/parziale e ultimazione del servizio](https://www.fiscooggi.it/print/pdf/node/100219).

## Checkout contestuale e copie al cliente

Nel modal di incasso si può scegliere **Incassa ed emetti documento commerciale**, **Incassa e richiedi fattura** oppure **Registra solo incasso · documento dopo**. Il documento commerciale è proposto quando il profilo E-Receipts è abilitato; la fattura compare solo dopo l'abilitazione dedicata. La postazione è proposta dalle preferenze del cassiere; il cambio resta soggetto agli stessi permessi.

I dati locali essenziali (postazione, destinatario fattura, accordo/email se richiesta) vengono controllati prima della registrazione. Il pagamento e l'eventuale richiesta di copia vengono poi salvati nella stessa transazione; l'emissione remota avviene **dopo il commit**. Rifiuti e timeout fiscali non eliminano il pagamento. Un replay del checkout riconosce l'incasso già registrato e non ripete l'emissione. Il recupero resta nella scheda dell'incasso, con i vincoli già previsti per gli esiti incerti.

Il cliente si cerca per nome, telefono o email (almeno tre caratteri, massimo otto risultati). La selezione propone email e denominazione, senza presumere P.IVA/CF o ricavare dati fiscali dal telefono. La rubrica fiscale resta selezionabile separatamente. Non si modifica l'anagrafica cliente né il consenso marketing.

La copia email è **facoltativa** e richiede conferma esplicita che il cliente abbia richiesto/accettato la consegna elettronica a quel recapito. `fiscal_copy_requests` registra destinatario cifrato, eventuale cliente associato, operatore e data dell'accordo, per singolo incasso. L'email può essere inserita anche per un cliente non censito. Una richiesta già registrata non può cambiare destinatario durante un retry. La richiesta può essere aggiunta anche dalla scheda di un incasso precedente.

La copia viene accodata soltanto dopo `issued` con ID remoto: mai per stati pending, unknown o scarto SDI. La scheda può accodarla immediatamente dopo la conferma; il comando schedulato `fiscal:queue-copies` recupera ogni minuto quelle diventate emesse in seguito. Una chiave univoca impedisce di creare due messaggi per la stessa richiesta. Il messaggio usa il flusso outbox/dispatch/email già presente, con tipo separato `fiscal_copy`; non passa dai canali o dalle preferenze marketing/prenotazioni.

L'email contiene un **link firmato con scadenza di 7 giorni** per scaricare il PDF originale Openapi. Il link lega richiesta e documento, è limitato dal throttling e non richiede login del cliente. Senza firma, con firma scaduta o per un documento non collegato alla richiesta, l'accesso è negato. Non contiene recapiti nella URL e non espone le rotte gestionali. Il collegamento permette l'accesso a chi lo possiede: va trattato come un documento riservato.

La UI distingue copia in attesa di emissione e copia accodata; l'accodamento non prova la ricezione dell'email. Servono scheduler, publisher outbox, worker delle code e mailer correttamente configurati. I retry di consegna non emettono nuovi documenti né ripetono l'incasso. Come per la messaggistica esistente, la consegna SMTP non garantisce un invio esattamente una volta in caso di arresto fra invio e conferma del job.

Per le fatture l'email è una **copia di cortesia**, separata dal recapito SDI/PEC. Tutti i messaggi e i PDF rimangono sandbox; non sono documenti validi per incassi reali. SMS/WhatsApp non sono attivati: il telefono serve soltanto alla ricerca del cliente.

Migrazione aggiuntiva `2026_11_16_090000_add_fiscal_copy_requests.php`, successiva a `.080`. Nessuna richiesta o emissione viene creata per gli ordini esistenti applicando la migrazione.

Riferimenti: [MEF, documento commerciale e previo accordo per la forma elettronica](https://def.finanze.it/DocTribFrontend/getAttoNormativoDetail.do?ACTION=getArticolo&codiceOrdinamento=200000100000000&id=%7B323C1EA3-8E9A-4AB8-B669-E899939EF582%7D), [Agenzia Entrate, fattura elettronica e copia al cliente](https://www1.agenziaentrate.gov.it/web_app_entrate/fatturazione_elettronica.html/guide-gallery.html).
