# Campagne e coupon: avvio e collaudo

## Ambito implementato

- `/manage/marketing/campaigns`: elenco, duplicazione/archiviazione, editor TinyMCE IT/EN/DE, immagini, azioni, filtri clienti, selezione di tutti i risultati idonei, anteprima, prova email, programmazione, pausa/ripresa/annullamento, registro per destinatario.
- `/manage/coupons`: offerte e regole multilingue, campi di profilazione anche personalizzati, quote, elenco assegnazioni e revoca personale, pubblicazione nei menu scelti.
- `/manage/settings/marketing`: limiti dei testi WhatsApp/Telegram/SMS, limiti SMTP e campagne, stato delle email transazionali e delle prove.
- Coupon assegnati solo dopo richiesta e verifica email; pagina pubblica, download SVG con QR, email, prenotazione con coupon e identità già riconosciuta. Nessuna generazione preventiva di UUID personali.
- Consumo automatico alla creazione della prenotazione, anche se ancora in attesa di approvazione. Si può associare un coupon già posseduto anche dal dettaglio di una prenotazione manuale. Un coupon per prenotazione; annullamenti e no-show non restituiscono utilizzi.
- Cassa: percentuale, importo, omaggio o prezzo fisso sui prodotti ammessi; coperti, servizio e consegna esclusi. Rimozione/riattivazione prima dei pagamenti. Fotografia degli sconti per riga condivisa con ripartizioni e documenti fiscali.
- WhatsApp, Telegram e SMS hanno editor testuali e contatori; il trasporto di questi canali resta da integrare. Invio reale delle campagne soltanto via email.

## Avvio locale

La migrazione aggiunge le nuove tabelle e colonne; non crea campagne né destinatari e non invia email. Non usare `migrate:fresh` sul database locale.

```bash
docker exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_17_020000_create_campaigns_and_coupons.php
# Dopo un aggiornamento dei worker/configurazioni:
docker exec ristopilot-tenant_php php artisan horizon:terminate
```

Horizon è già sotto Supervisor con riavvio automatico. Il nuovo supervisor `supervisor-marketing-email` legge `marketing-email`; le conferme coupon usano `messages-email`. Lo scheduler esegue ogni minuto `messages:publish-outbox` e `marketing:dispatch-due`. Non occorre aggiungere RabbitMQ o MQTT: il progetto usa già Redis/Horizon.

Se occorre anticipare un ciclo durante il collaudo:

```bash
docker exec ristopilot-tenant_php php artisan messages:publish-outbox
docker exec ristopilot-tenant_php php artisan marketing:dispatch-due
```

Ambiente locale verificato: applicazione `http://localhost:8002`, MailHog `http://localhost:8100`. Migrazione applicata e supervisor `marketing-email` in esecuzione. Il mittente locale è `noreply@ristogenius.com`, con trasporto ancora MailHog. Le prove automatiche usano il database separato `ristopilot_tenant_testing` e mail/queue fake. Non inseriscono clienti o campagne nel database di sviluppo. Le prove manuali creano dati reali nell’ambiente locale, con posta catturata da MailHog.

## Percorso di collaudo consigliato

### 1. Lingue e permessi

1. Accedere come Simone con lingua inglese. Aprire campagne, creazione/modifica, invio, coupon e impostazioni marketing: etichette, azioni e stati devono essere inglesi.
2. Ripetere con italiano e tedesco. I tab IT/EN/DE cambiano il contenuto da scrivere, non la lingua dell’interfaccia manage.
3. Provare un collaboratore con permesso marketing e uno senza: il primo gestisce campagne/coupon, il secondo non accede. Le impostazioni SMTP restano riservate agli admin.
4. Provare TinyMCE nei tab: grassetto, lista, tabella, link e immagine tramite URL; salvare e riaprire. Incollare uno script: non deve comparire nel contenuto pubblico.

### 2. Offerta e quote

1. Creare un’offerta attiva con titolo, descrizione e condizioni nelle tre lingue, immagine e form di prenotazione attivo che accetti coupon e le stesse lingue.
2. Impostare giorni lunedì–giovedì, minimo 3 persone, periodo visita, termine assegnazione, massimo 2 assegnazioni e massimo 3 utilizzi complessivi. Verificare la distinzione tra termine richiesta e data della visita.
3. Provare le tre modalità: un solo utilizzo, massimo N utilizzi per cliente, illimitati fino alla scadenza. I campi lasciati vuoti per le quote complessive indicano assenza di limite.
4. Attivare provincia/comune e data di nascita. Aggiungere un campo personalizzato e un elenco con opzioni tradotte. Nome, email e privacy restano obbligatori; marketing facoltativo.
5. Provare salvataggi non validi: lingue incomplete, percentuale maggiore di 100, omaggio senza prodotti, quota inferiore al conteggio già registrato.

### 3. Richiesta pubblica e assegnazione

1. Aprire il link pubblico EN in finestra anonima. Richiedere il coupon con un indirizzo nuovo, privacy accettata e marketing non selezionato.
2. Prima della conferma email non deve esserci un coupon personale assegnato. Aprire il link ricevuto in MailHog: il solo GET non assegna; premere il pulsante di conferma.
3. Verificare ringraziamento inglese, titolo/condizioni, numero utilizzi, QR, download SVG e pulsante di prenotazione. Verificare la seconda email con dettagli e link al coupon.
4. Ripetere il POST o richiedere nuovamente con la stessa email: deve essere restituita la stessa assegnazione, senza aumentare il conteggio. Provare anche quando le nuove assegnazioni sono esaurite.
5. Usare un altro cliente fino a esaurimento quota, poi un terzo: l’ultima assegnazione deve essere rifiutata. Nessuna tabella viene popolata con coupon personali inutilizzati in anticipo.
6. Provare email non valida, privacy mancante, telefono già appartenente a un’altra identità, token inventato e link scaduto. La verifica scade 30 minuti dopo il tentativo di invio della prova email, così l’attesa nella coda non consuma il tempo utile.
7. Passare EN → DE sulla pagina personale e sul form di prenotazione: il coupon deve restare collegato.

### 4. Prenotazione e utilizzi

1. Dal pulsante del coupon prenotare una visita consentita: identità precompilata, coupon visibile, utilizzo registrato senza conferma dello staff.
2. Provare giorno non consentito, data fuori periodo, meno di 3 persone, e capienza/form evento non disponibili: nessuna prenotazione né consumo deve essere salvato.
3. Per omaggi per bambino, indicare il numero di bambini; non può superare quello delle persone.
4. Ripetere l’invio del form: non deve creare una seconda prenotazione per lo stesso invio né scalare due utilizzi.
5. Possedere due coupon diversi: usarne uno sulla prenotazione; tentare l’altro sulla stessa prenotazione deve fallire. Su una seconda prenotazione si può usare l’altro, rispettando le sue regole.
6. Annullare una prenotazione o marcarla no-show: verificare che il consumo resti registrato.
7. Modificare una prenotazione con coupon: data e persone devono continuare a rispettare le condizioni originali.
8. Creare una prenotazione manuale per un cliente che possiede un coupon: associarlo dal dettaglio e verificare il conteggio. Non è consentito farlo dopo l’avvio dei pagamenti.
9. Modificare l’offerta dopo la prima assegnazione: il vecchio coupon conserva beneficio, periodo, form e regole ricevuti; le nuove assegnazioni hanno le nuove condizioni. Quote complessive e revoche restano controlli operativi globali.

### 5. Cassa

1. Per una prenotazione con coupon percentuale 10%, inserire 3 pizze da €10 e €6 di coperti: totale lordo €36, sconto coupon €3, totale €33.
2. Provare anche sconto importo, prezzo fisso per pacchetto/persona/articolo, tetto massimo sconto e prodotti esclusi. Lo sconto non supera il valore dei prodotti ammessi.
3. Per omaggio bambini, inserire prodotti ammessi sufficienti: la quantità gratuita è quella configurata moltiplicata per i bambini. Si scontano prima gli articoli ammessi meno costosi, senza modificare i prezzi di produzione.
4. Rimuovere il coupon dal conto: il totale ritorna ordinario; riattivarlo prima dei pagamenti: torna il beneficio. L’utilizzo resta consumato anche quando lo sconto viene rimosso dalla cassa.
5. Provare sconto manuale insieme al coupon: rifiutato. Rimuovere il coupon prima di applicare lo sconto manuale.
6. Ridurre i coperti sotto il minimo: il beneficio non viene applicato e appare il motivo. Se una prenotazione ha più ordini, il beneficio spetta soltanto al primo conto non annullato.
7. Dividere il conto per persone e per articoli, controllare IVA e somma delle quote, effettuare un primo pagamento. Da questo momento importi e applicazione coupon sono congelati.
8. Stampare preconto e verificare documento fiscale sandbox: righe, IVA, sconto e incassi devono riconciliarsi. Un conto interamente gratuito mantiene il vincolo preesistente del modulo fiscale sui documenti a zero.

### 6. Menu

1. Pubblicare il coupon in un menu specifico: compare sia nel menu consultabile sia nella vista ordinazioni collegata a quel menu.
2. Non deve comparire in altri menu, in una lingua non prevista, dopo disattivazione/scadenza o esaurimento delle quote.
3. Nessuna selezione dei menu significa tutti i menu pubblici. Il link porta alla richiesta nella lingua del menu.

### 7. Campagne, destinatari e programmazione

1. Preparare clienti con email e consenso, uno senza consenso e uno bloccato. Usare i filtri su nome, località, compleanno, prenotazioni e ultima visita.
2. “Aggiungi tutti i risultati idonei” deve selezionare anche le pagine successive; ripeterlo non duplica. Ultima visita considera prenotazioni effettivamente accomodate/completate, non l’ultima modifica dell’anagrafica. Il filtro fidelity compare solo quando esiste il dato.
3. Completare oggetto, preheader, HTML, pulsante e testi futuri nelle lingue dei destinatari. Provare il limite SMS e il contatore, anche con emoji. Una traduzione destinatario assente blocca la programmazione.
4. Provare tutti i tipi: informativa, coupon, prenotazione, coupon + prenotazione e link esterno. Per coupon + prenotazione il form viene scelto sul coupon.
5. Inviare una prova alla propria email locale: controllare in MailHog oggetto, HTML/testo, immagine collegata e pulsante. Le prove non aggiungono destinatari né aggiornano statistiche dei veri invii.
6. Programmare tra qualche minuto: nessun invio anticipato; entro il ciclo scheduler iniziano gli invii. Provare anche “Invia ora”.
7. Modificare contenuti e lista dopo la programmazione: la versione già programmata conserva contenuti e destinatari salvati.
8. Pausare/riprendere e annullare: non partono nuovi messaggi mentre è in pausa; quelli già in elaborazione possono arrivare. L’annullamento lascia lo storico degli accettati.
9. Revocare il consenso o cambiare email a un cliente dopo la programmazione: il worker lo esclude, senza inviare al precedente indirizzo.
10. Aprire il link nell’email: viene tracciato il clic; il percorso coupon/prenotazione mantiene l’attribuzione all’invio. Il conteggio clic non prova la lettura umana: anche scanner email possono aprire link.
11. Aprire disiscrizione: il solo GET mostra conferma; il POST revoca il consenso. Le email transazionali richieste continuano a funzionare.
12. Impostare limiti SMTP bassi e verificare avanzamento graduale. “Accettato da SMTP” non equivale a consegna in casella. Gli esiti incerti non vengono ritentati automaticamente; “Riprova” considera solo i messaggi certamente non inviati.

## Produzione e limiti operativi

Il codice non configura DNS, una casella Zimbra/OVH o credenziali di produzione. Configurare sul server il mittente autorizzato `@ristogenius.com`, SMTP autenticato, URL HTTPS corretto, SPF/DKIM/DMARC e quote effettive del provider. Non copiare credenziali del progetto Tour.Puglia.

Esempio senza segreti:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=ssl0.ovh.net
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=<casella-autorizzata@ristogenius.com>
MAIL_PASSWORD=<segreto-sul-server>
MAIL_FROM_ADDRESS=noreply@ristogenius.com
MAIL_FROM_NAME=RistoGenius
MARKETING_MAIL_QUEUE=marketing-email
MARKETING_CONFIRMATION_QUEUE=messages-email
MARKETING_TIMEZONE=Europe/Rome
```

I default 20/minuto, 100/ora complessivi e 80/ora campagne sono prudenziali e configurabili, non quote dichiarate da OVH. Una coda Redis gestisce migliaia di job; il tempo effettivo di invio resta determinato dalle quote SMTP. Il limite campagne deve essere inferiore al complessivo per lasciare capacità transazionale. Timeout SMTP 30s, job 45s e riacquisizione Redis 90s evitano sovrapposizioni ordinarie. Ogni consegna ha anche un lock di unicità e una transizione persistente prima del contatto SMTP.

Con più host usare una cache condivisa. Se più tenant condividono lo stesso account SMTP, devono condividere anche il namespace effettivo dei limiter (backend, database Redis e prefissi): condividere soltanto l’host Redis non basta. In alternativa assegnare un account SMTP distinto a ogni tenant o centralizzare il dispatcher. Le impostazioni delle quote dello stesso account devono essere coerenti tra i tenant.

Gli esiti SMTP incerti possono derivare da timeout dopo l’accettazione. Verificare i log del server e lo storico prima di autorizzare un nuovo invio: SMTP non offre una garanzia generale di consegna esattamente una volta. Non sono simulati bounce, consegna in casella o aperture email; servirebbe un provider con API/webhook per tali metriche.

Il download del coupon è SVG con QR, non un wallet pass o un PDF. Le immagini dell’offerta sono pubbliche; i link del coupon personale sono credenziali bearer da conservare. Le conferme non richiedono autenticazione manage. Per la produzione definire anche una politica di conservazione/cancellazione dei dati di profilazione e delle richieste email scadute.

## Verifiche automatiche eseguite

Suite completa: 529 test, 3.105 asserzioni, nessun errore o fallimento, 21 test saltati dalle condizioni della suite. Ultimi controlli mirati sulle code: 3 test e 8 asserzioni superati, inclusi pausa concorrente dello scheduler, scarto dei job annullati prima del limiter SMTP e invio idempotente. I test coprono anche upload Livewire delle immagini, contatore con emoji e tutti i benefici in cassa. Build Vite completata; restano warning preesistenti Sass/CSS/Browserslist. Nessun invio SMTP remoto è stato effettuato.
