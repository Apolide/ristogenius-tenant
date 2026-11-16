# Ordini pubblici

## Attivazione e percorsi

- Configurazione ritiro/consegna: `/manage/settings/customers-orders`, accessibile anche dal nuovo box in Impostazioni. Il servizio è inizialmente disattivato. Configurare orari e, per la consegna, quartieri prima di attivarlo.
- Il menu consultabile resta `/menu/{lingua}/{slug}`. Quando il servizio è attivo mostra un collegamento a `/order/{lingua}/{slug}`. Anche Menu Studio offre il collegamento alla versione per ordinare.
- Tutti i menu pubblicati e visibili sono utilizzabili. Prodotti inattivi, categorie disabilitate e prodotti nascosti non sono ordinabili. `disallow_takeaway` esclude i prodotti dal menu ritiro/consegna, mantenendoli disponibili al tavolo.
- QR dei tavoli: Sale → pulsante QR, oppure `/manage/settings/rooms/orders-tables-qr-codes`. Si possono filtrare le sale, stampare, copiare link, scaricare SVG e rigenerare i codici. Il precedente URL `printqr` reindirizza alla nuova pagina.
- I QR non dipendono dalle impostazioni asporto/consegna. Aprono il menu pubblicato disponibile, oppure una scelta fra i menu pubblicati per la lingua del link. Sono utilizzabili anche come punti di consegna denominati tramite i tavoli configurati.

## Identificazione del cliente

Nome, email, telefono e accettazione dell’informativa sono obbligatori. Il carrello è abilitato soltanto dopo la verifica email, in tutti i flussi. Il numero è normalizzato con il servizio telefonico esistente.

La richiesta crea un record con identità cifrata e hash del token. Il link è valido 20 minuti. Se la richiesta nasce da un conto tavolo già aperto, resta legata a quel conto: non può essere riscattata per la visita successiva. Il GET mostra una conferma: non consuma il token, così i controlli automatici delle caselle email non eseguono l’accesso. Il POST protetto da CSRF consuma il token in transazione, crea/recupera il cliente e rigenera l’identificativo della sessione.

Il cliente usa una sessione distinta dall’autenticazione del personale, con durata massima di sei ore (restano applicabili anche i limiti della sessione browser dell’app). Un cookie di riconoscimento HttpOnly, cifrato e autenticato dal middleware Laravel, conserva il telefono per trenta giorni esclusivamente per il saluto. Non autorizza ordini e non contiene un link di accesso permanente. Recapiti in conflitto fra clienti vengono segnalati senza fusioni o sovrascritture automatiche. I clienti in blacklist non possono ordinare.

Le richieste email sono limitate per IP/email e per IP. Il comando `customer-orders:prune-access`, pianificato giornalmente, elimina le sessioni scadute da oltre un giorno e le richieste di accesso scadute da oltre sette giorni.

## Carrello e prezzi

Sono supportati quantità, ingredienti aggiunti/rimossi e note. Nessuna variante strutturata o movimentazione di magazzino. I prezzi specifici della categoria/menu prevalgono sul prezzo del prodotto.

Il server ricalcola il preventivo alla conferma e confronta il totale con quello mostrato. Ogni invio ha una chiave univoca che impedisce la duplicazione dell’ordine. Prezzi, IVA, nomi, personalizzazioni, recapiti e indirizzo applicati sono storicizzati. Le righe persistite usano lo stesso servizio prezzi delle comande interne.

Ritiro/consegna non addebitano coperti. La consegna applica minimo prodotti, costo e soglia di gratuità; la soglia zero disabilita la gratuità. L’anticipo effettivo è il massimo fra anticipo minimo e preparazione standard. Gli slot rispettano il fuso dell’app, le fasce per servizio e le chiusure, comprese le fasce che attraversano la mezzanotte. Sono ricontrollati all’invio.

## Flusso fuori locale

`/manage/customers-orders` contiene esclusivamente ritiro e consegna, con ricerca, data, stato, tipo e storico. Le schede riportano numero ordine, cliente, recapiti, indirizzo, importo, modalità prevista e stato del pagamento.

Stati: ricevuto → accettato → in preparazione → pronto → ritirato, oppure in consegna → consegnato. Prima dell’accettazione è possibile rifiutare. Prima della preparazione è possibile annullare. Le transizioni verificano lo stato atteso e bloccano i comandi obsoleti.

L’accettazione non genera ticket. `Avvia preparazione` crea i ticket KDS tramite il servizio condiviso. `Segna pronto` richiede che tutti i reparti abbiano completato il lavoro. Il completamento segna serviti i ticket pronti. Produzione, consegna e incasso sono indipendenti: un ordine pagato può ancora essere preparato.

La cassa ha due schede: Sala e Asporto/consegne. La seconda conserva visibili gli ordini non pagati anche dai giorni precedenti e dopo ritiro/consegna. Si usa lo stesso servizio incassi da entrambe le interfacce, con il permesso cassa. Il pagamento previsto è contanti e non costituisce una conferma di incasso; l’incasso deve essere registrato dal personale. Nessun addebito carta viene eseguito. Lo storico finanziario comprende tutti i tipi, con filtro dedicato.

## Flusso tavolo

La verifica email associa il cliente al conto corrente del tavolo, aprendolo se necessario senza creare una prenotazione. Se il conto esiste, viene riutilizzato con i coperti già impostati. Il personale può aggiornare i coperti dal normale editor comanda.

Ogni invio diventa un’uscita attribuita al cliente, in attesa del personale. Il cameriere può inviarla ai reparti o rifiutarla prima dell’invio. Un’uscita rifiutata conserva le righe nello storico senza essere addebitata. Dopo l’invio restano le regole esistenti: nessuna modifica quando un reparto ha iniziato la lavorazione. I prodotti aggiunti dal personale usano un’uscita distinta da quella pubblica.

La percentuale della sala viene fotografata per ogni uscita pubblica e applicata al suo subtotale prodotti, con arrotondamento al centesimo per uscita. Non si applica ai coperti né alle uscite del personale. Le variazioni del catalogo e della percentuale sala non riscrivono gli importi delle uscite confermate.

Il cliente vede soltanto i propri invii. Il personale vede il conto completo. Chiusura del conto, scadenza o rigenerazione del QR rendono inutilizzabile l’autorizzazione precedente; non si può aggiungere al conto successivo con una vecchia sessione.

## Email e permessi

Il modulo registra eventi nella outbox esistente. Pubblicazione, retry e coda email rimangono quelli già usati dalle prenotazioni; queste ultime mantengono il comportamento precedente. Gli eventi `customer_order` passano esclusivamente al canale email, senza dipendere dalle preferenze delle prenotazioni. Sono previsti accesso, ricezione, notifica al personale, aggiornamenti operativi e pagamento remoto. Sono estendibili ad altri canali successivamente.

I worker Horizon devono caricare il nuovo codice. Localmente l’email è consultabile in MailHog quando il mailer locale è configurato in tal senso. La outbox viene pubblicata dallo scheduler ogni minuto.

Il nuovo permesso è `personnel-manage-customer-orders`, assegnabile dalla matrice del personale. È distinto da comande, KDS e cassa; non viene assegnato automaticamente agli utenti esistenti. Gli amministratori mantengono l’accesso completo. Per registrare incassi serve sempre `personnel-manage-cash-register`.

## Installazione e verifica

Applicare soltanto la nuova migrazione `2026_11_16_010000_add_public_ordering.php`. Essa estende gli ordini e crea le tabelle dedicate ad accessi, sessioni, quartieri, orari e chiusure; assegna i token ai tavoli esistenti.

```sh
docker compose -f docker-compose-dev.yml exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_010000_add_public_ordering.php

docker compose -f docker-compose-dev.yml exec \
  -e APP_ENV=testing -e DB_DATABASE=ristopilot_tenant_testing \
  ristopilot-tenant_php php artisan test
```

I test comprendono verifica email monouso, cookie senza autorizzazione, permessi, conflitti anagrafici, prezzi alterati, ingredienti, idempotenza, disponibilità, chiusure, mezzanotte, consegna, commissioni, rifiuti, conto corrente del tavolo, QR, incassi e messaggistica. POS, varianti strutturate e magazzino sono esclusi.

## Proposte di modifica e riepiloghi email

Nel dettaglio `/manage/customers-orders/{ordine}` il pulsante **Proponi modifica al cliente** apre un editor dedicato. È disponibile per ordini da ritiro/consegna ancora da approvare o accettati, non pagati e non inviati in produzione. Consente di proporre giorno/orario, prodotti sostitutivi, quantità, aggiunte/rimozioni ingredienti e note. Il messaggio che spiega la proposta è obbligatorio. Non introduce varianti strutturate o pagamenti POS.

I prodotti già ordinati conservano il prezzo base e le personalizzazioni storiche; nuove aggiunte usano il catalogo corrente. La consegna viene ricalcolata per il subtotale proposto. Il server verifica prezzi, disponibilità e corrispondenza con l’ordine su cui il personale ha iniziato a lavorare.

La proposta è separata dall’ordine, con fotografia originale, fotografia proposta, autore, scadenza e risposta. Una sola proposta può essere in attesa per ordine. L’ordine effettivo e i suoi importi restano invariati fino all’accettazione del cliente. Preparazione e incasso sono sospesi mentre la proposta è valida e in attesa. Il personale può ritirarla; rifiuto o annullamento dell’ordine la rendono inutilizzabile.

Il dettaglio backend si aggiorna ogni cinque secondi per mostrare la risposta senza ricaricare manualmente la pagina. Il cliente riceve un’email con entrambi i riepiloghi, messaggio, scadenza e pulsanti **Accetta proposta** / **Rifiuta proposta**. I collegamenti contengono un token casuale il cui hash è conservato nel database. Aprono una pagina di conferma: il GET non modifica dati, mentre il POST protetto da CSRF registra la risposta. Il collegamento autorizza soltanto quella risposta, senza autenticare il destinatario al menu o al backend.

- **Accettazione:** applica atomicamente la proposta, conferma l’ordine nello stato accettato e notifica cliente e personale. La produzione continua ad avviarsi manualmente. Le risposte ripetute non duplicano operazioni o email.
- **Rifiuto:** conserva l’ordine originale e il suo stato; non equivale ad annullarlo. Cliente e personale ricevono la comunicazione e concordano come procedere.
- **Scadenza / ritiro:** nessuna modifica all’ordine. I vecchi collegamenti non possono applicare la proposta. La scadenza è il minore fra 24 ore dall’invio e l’orario proposto meno l’anticipo/preparazione richiesti.

Il riepilogo si conserva nella outbox al momento dell’evento: modifiche successive non riscrivono un’email già accodata. Le email operative, inclusa l’accettazione, riportano prodotti, quantità, ingredienti, note, prezzi, IVA, totale, costi di consegna/servizio, orario, recapiti, indirizzo, modalità e stato pagamento. Le email del tavolo riportano soltanto i prodotti dell’uscita del destinatario, senza esporre quelli degli altri clienti.

La nuova migrazione è `2026_11_16_020000_create_customer_order_proposals.php` e aggiunge soltanto la tabella delle proposte.
