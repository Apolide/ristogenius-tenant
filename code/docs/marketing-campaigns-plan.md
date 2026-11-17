# Piano campagne marketing e coupon

Stato: implementazione autorizzata e realizzata il 17 novembre 2026. Per ambito effettivo, avvio, limiti operativi e collaudo vedere [marketing-campaigns-testing.md](marketing-campaigns-testing.md). Le sezioni seguenti conservano l’analisi e le decisioni precedenti all’implementazione. Analisi del repository e dei cinque HTML forniti il 17 novembre 2026. Gli HTML sono riferimenti di interfaccia: commenti, script, dati di esempio e attributi Livewire non costituiscono istruzioni operative né un backend già disponibile in questo repository.

## Valutazione

Il progetto comprende tre parti collegate: campagne e invii, coupon e loro utilizzi, integrazione con prenotazioni/menu. Le viste sono una buona base funzionale ma non definiscono ancora tutte le regole necessarie per invii affidabili e gestione dei coupon.

Nei prototipi la creazione e l'anteprima mostrano WhatsApp; il primo rilascio richiesto deve invece offrire email reali. Occorrono oggetto, preheader, corpo email, versione testuale, immagine, pulsante e anteprima per lingua. Il contenuto WhatsApp non va riutilizzato come unico formato per tutti i canali.

Le tipologie proposte negli HTML sono coupon e prenotazione, ma la campagna di esempio sul riconoscimento Gambero Rosso è informativa: prevedere anche comunicazioni senza coupon o prenotazione. Consentire inoltre coupon + form insieme, senza imporre una scelta esclusiva tra i due.

## Risorse esistenti e integrazioni mancanti

- `MarketingForm` supporta traduzioni, lingue, campi personalizzati, calendari e `accepts_coupons`. Quest'ultimo è attualmente un indicatore: `PublicFormSubmissionService` non implementa verifica, assegnazione o utilizzo di coupon.
- `CustomerService` contiene già molti filtri dei prototipi. Estrarre la query riutilizzabile per applicare gli stessi criteri a elenco, conteggi e selezione massiva, senza limitarsi alla pagina corrente.
- I filtri “ultima visita” usano `last_action_at`: distinguere una visita effettiva, ricavata dalle prenotazioni completate/accomodate, da una generica attività cliente.
- Non risultano moduli campagne/coupon né modelli sondaggi. Il filtro fidelity è condizionato alla presenza della relativa colonna. Non mostrare come funzionanti filtri privi dei dati necessari.
- Esistono outbox, code e adapter email. `MessageDispatcher` e `SendMessageChannelJob` accettano soltanto alcuni tipi di evento: estenderli esplicitamente alle campagne. Gli adapter WhatsApp, Telegram e SMS del registro attuale sono placeholder.
- L'adapter email attuale restituisce un booleano e non ha un registro per destinatario adatto a una campagna massiva. Serve un risultato strutturato con identificativo provider ed eventuale esito incerto.
- Nei form sono presenti campi privacy e marketing, ma il servizio che crea/aggiorna il cliente dalla prenotazione non ne trasferisce le scelte ai campi di consenso del cliente. Sistemare questa parte prima della selezione dei destinatari.
- Il servizio newsletter legacy fa riferimento a un modello non presente. Non è una disiscrizione marketing riutilizzabile senza ulteriore lavoro.

## Flussi proposti

1. Comunicazione: email multilingue con contenuto e, facoltativamente, una pagina pubblica.
2. Coupon: email o menu pubblico → pagina coupon → identificazione/conferma recapito → assegnazione codice → coupon consultabile/scaricabile con codice e QR → prenotazione, se prevista.
3. Prenotazione: email → form di tipo booking/event già configurato.
4. Prenotazione con coupon: email → form compatibile; verifica delle regole e assegnazione e registrazione automatica dell’utilizzo durante il salvataggio, senza imporre un download intermedio.

Il download di un documento e l'apertura di un link non consumano il beneficio. Le anteprime automatiche delle email non devono poter assegnare o utilizzare coupon tramite una semplice richiesta GET.

## Modello funzionale

### Campagne e invii

Separare la campagna riutilizzabile dalle esecuzioni di invio. Una campagna contiene nome interno, traduzioni pubbliche, lingue abilitate, immagine, obiettivo e riferimenti facoltativi a coupon/form. Ogni invio conserva versione del contenuto, selezione destinatari, canale, pianificazione, operatore e riepilogo degli esiti.

Ogni destinatario/canale ha una propria consegna: in coda, in corso, accettata dal provider, consegnata se confermata, esclusa, fallita o con esito incerto. Vincolo univoco per invio/destinatario/canale e chiave di idempotenza per evitare invii duplicati nei normali tentativi di ripresa. In caso di timeout dopo una possibile accettazione, riconciliare l'esito prima di rispedire: il solo retry della coda non garantisce l'invio esattamente una volta.

Alla preparazione si congelano contenuto e destinatari. Prima dell'invio effettivo si ricontrollano consenso, disiscrizione, blacklist e recapito. La lingua effettiva usata viene registrata. Un reinvio intenzionale crea un nuovo invio; la ripresa riprocessa soltanto consegne appropriate, senza inviare nuovamente a tutti. Mettere in pausa blocca le consegne ancora da eseguire, non ritira quelle già inviate.

Primo rilascio: un canale per invio, email operativo. Canali futuri esplicitamente non disponibili fino all'attivazione dell'adapter e del provider; nessun fallback automatico verso un altro canale.

### Coupon

Separare definizione dell'offerta, codici assegnati ai clienti, utilizzi sulle prenotazioni e utilizzi effettivi. Un unico insieme di regole e disponibilità serve tutte le traduzioni.

Definire il beneficio: omaggio, sconto percentuale, importo fisso o altra offerta. Il primo rilascio deve gestire omaggi, sconti percentuali, sconti a importo fisso e offerte a prezzo fisso, applicati automaticamente alla cassa e deselezionabili/rimuovibili dal personale. L’integrazione con conti divisi, rimborsi e documenti fiscali fa quindi parte dell’ambito iniziale.

Distinguere almeno:

- Termine entro cui ottenere il coupon e intervallo di visita in cui utilizzarlo. Le tre date degli HTML non hanno ancora una semantica univoca.
- Giorni della settimana e possibili servizi/orari validi, nel fuso del tenant.
- Numero minimo/massimo di persone per prenotazione.
- Numero massimo di codici assegnabili, massimo utilizzi complessivo e limite per cliente.
- Utilizzo singolo o ripetuto, cumulabilità e trasferibilità. Il cliente può possedere contemporaneamente più coupon personali di offerte diverse. La non cumulabilità si applica alla prenotazione e al relativo conto: un solo coupon attivo. Ogni offerta configura utilizzo singolo, massimo N utilizzi o utilizzi illimitati fino a scadenza.
- Visibilità nel menu e ambito: tutti i menu o menu selezionati. Nascondere offerte non più ottenibili, mantenendo consultabili i codici già assegnati secondo le condizioni accettate.

Decisione confermata: generare UUID/codice personale soltanto alla richiesta valida di assegnazione, entro la quota configurata. Nessuna pre-generazione di righe inutilizzate. “Genera codici aggiuntivi” diventa “Aumenta disponibilità assegnazioni”, senza creare anticipatamente codici.

Ciclo concordato: disponibilità → assegnazione al cliente → utilizzo automatico al salvataggio di una prenotazione valida. Nessuna conferma del personale. Il salvataggio fallito o ripetuto della stessa richiesta non consuma altri utilizzi. La modifica della stessa prenotazione non consuma nuovamente; va invece rivalidata rispetto alle regole applicabili.

Annullamento e no-show non ripristinano il numero di utilizzi. Per i coupon riutilizzabili rimangono disponibili soltanto gli utilizzi residui già previsti (oppure quelli illimitati fino a scadenza), senza accredito dell’utilizzo consumato. La rimozione del beneficio dalla cassa non deve cancellare lo storico né ripristinare automaticamente la quota: questa è l’impostazione proposta, da rendere esplicita nell’interfaccia.

Un’offerta riutilizzabile produce una sola assegnazione personale e più registrazioni di utilizzo, evitando di moltiplicare i codici per lo stesso cliente e la stessa offerta. Il blocco di non cumulabilità è per prenotazione/conto, così prenotazioni distinte possono usare coupon diversi.

Le modifiche alle condizioni non devono cambiare silenziosamente il beneficio dei codici già emessi: conservare la versione delle regole applicabile e registrare le eventuali revoche esplicite.

### Prenotazioni, identità e menu

Verificare i limiti sul server alla creazione, alla modifica della prenotazione e al calcolo in cassa. Intersecare sempre regole coupon, disponibilità del form/evento e capienza del ristorante. Prenotazione, registrazione di utilizzo coupon e notifiche devono essere salvati in modo coerente nella stessa transazione.

La gestione deve coprire creazione da form, modifiche del cliente e del personale, annullamento, rifiuto, no-show e prenotazioni inserite manualmente. Il personale vede il coupon associato e può deselezionare/rimuovere il beneficio in cassa, con registrazione dell’operazione. L’utilizzo del coupon rimane automatico alla prenotazione.

Per coupon personali, verificare il possesso del recapito; non è sufficiente conoscere nome/email di un cliente già presente. Riutilizzare componenti di accesso verificato dove opportuno, mantenendo distinti link campagna, token coupon, accesso cliente e disiscrizione. Il QR deve identificare il codice senza esporre recapiti o dati personali nell'URL.

Per i campi dei coupon riutilizzare le definizioni multilingue del sistema form tramite componenti condivisi, senza creare una seconda gestione incompatibile dei campi e dei consensi. Il consenso marketing rimane una scelta separata dalla richiesta di coupon/prenotazione.

## Modifiche consigliate alle viste

- Lista campagne: stato della campagna e ultimo invio separati, conteggi destinatari/idonei/inviati/falliti, filtro, duplica e storico invii.
- Editor: nome interno distinto dal titolo/oggetto pubblico; schede lingua; sezioni contenuto, obiettivo, destinatari e anteprima. Coupon e form associabili insieme. Editor email con HTML validato sul server.
- Destinatari: riepilogo dei filtri, totale dei risultati e degli esclusi con motivo; azione esplicita su tutti i risultati, non soltanto sulla pagina visibile; nessun invio a blacklist o disiscritti tramite semplice inclusione manuale.
- Anteprima invio: lingua/canale selezionabili, prova a un indirizzo scelto, copertura traduzioni, verifica coupon/form, invia ora o programma. Distinguere “riprendi falliti” da “nuovo invio”.
- Coupon: stati dei codici e degli utilizzi distinti; regole esplicite e testo delle condizioni tradotto; pagine pubbliche mobile; bottone per prenotare solo quando esiste un form compatibile.
- La generazione AI mostrata negli HTML è opzionale successiva: non è necessaria al funzionamento del primo rilascio.

## Multilingua

Interfaccia manage nella lingua dell'operatore, contenuto pubblico nella lingua del cliente/URL. Per email: oggetto, preheader, corpo, pulsante e versione testuale tradotti; per coupon: titolo, descrizione, condizioni ed etichette dei campi. Non duplicare inventario e regole per lingua.

Prima dell'invio mostrare i clienti senza una traduzione completa e la lingua di ripiego, se ammessa. Verificare che form e coupon collegati supportino il percorso scelto; non inviare link verso una lingua disabilitata. La traduzione automatica non è implicita: testi e condizioni devono poter essere revisionati.

## Misurazione

Separare selezionati, esclusi, accettati dal provider, consegnati, falliti, click, coupon assegnati, prenotazioni e utilizzi. “Inviata” non equivale a consegnata. Le aperture email non sono una misura certa della lettura.

Registrare origine campagna/invio/menu e la catena fino a codice e prenotazione; le aperture automatiche o i click non consumano coupon e non sono conversioni di prenotazione. Riscontri del provider vanno associati alla singola consegna e trattati anche se arrivano fuori ordine o ripetuti.

## Fasi e criteri di completamento

| Fase | Attività | Risultato verificabile |
| --- | --- | --- |
| 1 | Definizione beneficio, date, quote, ciclo e politica di lingua; controllo consenso e filtri | Regole concordate e nessuna ambiguità su “utilizzato” |
| 2 | Modello coupon, CRUD multilingue, campi condivisi, codici e transizioni | Coupon creabile, assegnabile e verificabile; quote sicure con concorrenza |
| 3 | Pagine pubbliche, codice/QR, identità verificata, form e menu | Download e prenotazione rispettano gli stessi limiti anche dopo modifiche |
| 4 | CRUD campagne, obiettivi, traduzioni, immagini e segmenti clienti | Editor e destinatari coerenti con i dati disponibili |
| 5 | Anteprime, test email, invio/pianificazione, consegne e outbox | Invio riprendibile con registrazione per destinatario e controllo consenso al momento dell'invio |
| 6 | Disiscrizione, soppressioni, riscontri provider e statistiche | Destinatari esclusi correttamente e risultati distinguibili |
| 7 | Test e verifica operativa | Nessun doppio utilizzo, nessun reinvio involontario nei flussi coperti, tre lingue e isolamento tenant verificati |

La forma dei contenuti per altri canali viene prevista nelle fasi 1/4/5; l'integrazione effettiva con WhatsApp, Telegram e SMS è successiva.

Test prioritari: ultima disponibilità contesa; doppio click; job ripetuto; esito provider incerto; disiscrizione dopo programmazione; modifica di data/pax; annullamento/rifiuto/no-show; coupon scaduto; lingua mancante; form disabilitato; link inoltrato; rinnovo dell'immagine o delle regole dopo un invio; accessi tra tenant; selezione di tutti i risultati di un filtro.

## Ambito aggiornato e stima preliminare

Complessità medio-alta, con incremento rispetto alla prima proposta perché la cassa è ora compresa. Ordine di grandezza aggiornato: 28–42 giornate effettive di sviluppo e test per un developer, da affinare sulle regole di calcolo dei benefici e sul provider email. Comprende email programmate, offerte/coupon, utilizzi automatici, integrazione cassa, percorso pubblico, form/menu, report e verifiche. Esclude l’attivazione dei provider WhatsApp/Telegram/SMS, automazioni ricorrenti e generazione AI. Non è un preventivo né una data di consegna.

Restano da formalizzare la semantica delle tre date del prototipo e il trattamento del rifiuto della prenotazione da parte del ristorante; annullamento e no-show sono già definiti come non ripristinabili. Le regole per prezzo fisso e omaggi vanno espresse in campi strutturati configurabili, non ricavate dal testo promozionale.

## Integrazione automatica con la cassa

Ogni applicazione deve conservare coupon, assegnazione, utilizzo, versione delle regole, righe coinvolte e importi calcolati. Il testo promozionale serve alla comunicazione; i calcoli usano esclusivamente regole strutturate.

- Omaggio: prodotti/categorie ammessi, quantità massima o quantità per persona/bambino, eventuale prezzo massimo e criterio di scelta.
- Percentuale: base di calcolo (prodotti selezionati o consumazioni), percentuale e tetto massimo.
- Importo fisso: base di calcolo e limite al valore ammissibile, senza generare un totale negativo.
- Prezzo fisso: distinguere prezzo per persona, per prodotto o per pacchetto; definire cosa comprende e quali extra restano a prezzo ordinario.
- Specificare per ogni offerta l’inclusione o esclusione di coperti, servizio e consegna, oltre al rapporto con sconti manuali. Impostazione proposta: bloccare cumuli impliciti e mostrarne il motivo.

Esempio “pizza omaggio ai bambini”: giorni lunedì–giovedì, almeno 3 persone, prodotto/categoria pizza, numero bambini rilevante e limite di omaggi devono essere dati calcolabili. Il motore non può inferirli dalla sola descrizione. Nessuna riga omaggio fittizia viene aggiunta in cucina: il beneficio opera su prodotti realmente ordinati, salvo un’azione esplicita per aggiungerli.

Il calcolo si aggiorna con le righe del conto prima degli incassi. La rimozione in cassa ripristina i prezzi ordinari, con audit; il coupon rimane marcato come disabilitato per quel conto per impedirne la riattivazione automatica al refresh. Dopo il primo incasso valgono i vincoli del conto congelato: non si riscrivono retroattivamente pagamenti e documenti già emessi. Divisioni per prodotti, quote, IVA, rettifiche e rimborsi devono conservare coerenza con la stessa applicazione del beneficio. L’unione di conti con coupon diversi deve richiedere una risoluzione esplicita della non cumulabilità.

## Code e invii programmati

Scelta proposta: Laravel Queue su Redis e monitoraggio Horizon, già presenti tra dipendenze/configurazioni del repository. RabbitMQ è un’alternativa tramite il driver già dichiarato, con configurazione oggi commentata e quindi da attivare e verificare separatamente. MQTT è un protocollo pub/sub e non è l’opzione proposta per i job di invio; l’eventuale uso richiederebbe un’integrazione dedicata.

La data programmata viene salvata nel database, nel fuso del tenant e come istante UTC. Uno scheduler seleziona gli invii scaduti con lock, prepara lotti di destinatari e pubblica lavoro tramite outbox; il registro persistente permette il recupero dopo un riavvio. I worker gestiscono consegne per destinatario con limiti di frequenza del provider, retry controllati ed esiti incerti. Non si conserva una campagna programmata per settimane soltanto come job ritardato nel broker.

Prevedere code/lavoratori separati per campagne massive e comunicazioni richieste dal cliente (coupon, conferme prenotazioni), evitando che migliaia di email promozionali ritardino i messaggi operativi. Supportare pause, cancellazione dei job non ancora eseguiti e ripresa. La velocità finale dipende dai limiti del provider, non dal solo numero di worker.

## Editor e contatori

Riutilizzare l’impostazione TinyMCE con tab multilingue presente in `livewire/marketing/forms/edit.blade.php`. Estrarre un componente condiviso con integrazione Livewire e sincronizzazione dei contenuti. Per l’email adottare un profilo HTML più restrittivo rispetto ai form pubblici: elementi non adatti alle email, come iframe, non vengono ereditati; validazione e pulizia HTML anche sul server.

Per ciascuna lingua:

- Campagna email: oggetto, preheader, contenuto TinyMCE, immagine collegata, etichetta/azione del pulsante.
- Coupon pubblico: titolo, descrizione e condizioni con TinyMCE, campi configurabili, immagine e testo di conferma.
- WhatsApp e Telegram: textarea testo/emoticon con limite configurato per canale e contatore.
- SMS: textarea semplice con limite configurato e contatore; mostrare anche la stima dei segmenti tenendo conto di codifica, emoji e link.

La validazione della lunghezza va ripetuta dopo la sostituzione delle variabili e l’inserimento dei link. Evitare troncamenti automatici silenziosi. Le textarea dei canali futuri possono essere compilate anche quando il relativo invio non è ancora attivo.

## Percorso cliente concordato

Link campagna/menu → pagina coupon nella lingua scelta → raccolta dei campi configurati e verifica del recapito dove necessaria → assegnazione idempotente → pagina pubblica di ringraziamento con estremi, immagine/codice, utilizzi residui e pulsante prenotazione.

Inviare una conferma nella lingua del cliente con pulsante “Prenota” in alto, immagine del coupon collegata, testo ed estremi. Nel primo rilascio l’invio è email; altri canali usano gli adapter futuri. Il link apre un form compatibile con coupon già selezionato e dati già disponibili precompilati in modo autorizzato. Un click sul link non crea una prenotazione finché mancano data, orario, persone o conferma della disponibilità.

Email, telefono, data di nascita, provincia/comune e campi personalizzati sono configurabili per visibilità e obbligatorietà. La richiesta di coupon non concede automaticamente il consenso marketing; registrare separatamente le scelte e aggiornare il profilo senza sovrascrivere silenziosamente recapiti appartenenti ad altri clienti.

La campagna email contiene immagine collegata, testo e azione configurabile: prenota, riscatta coupon, riscatta e prenota, oppure altra destinazione consentita. Conservare l’attribuzione all’invio e la lingua lungo il percorso, senza esporre dati personali negli URL.

## Sequenza aggiornata di implementazione

1. Contratti di dominio: offerte, assegnazioni personali, utilizzi, applicazioni sul conto, regole di non cumulabilità e versioni.
2. Motore di calcolo e integrazione cassa, inclusa disattivazione persistente del beneficio e test con divisioni/IVA/rimborsi.
3. CRUD coupon multilingue, campi di profilazione, percorso pubblico, conferme, form e menu.
4. Campagne e editor condivisi TinyMCE/testo, filtri e destinatari.
5. Programmazione, outbox, code Redis separate, invio email per destinatario, consensi/disiscrizioni e report.
6. Verifiche complete: coupon multipli posseduti, utilizzi N/illimitati, nessun ripristino su annullamento/no-show, richieste ripetute, modifica prenotazione, rimozione cassa, confini di pagamento e invii da decine a migliaia di destinatari.


Riferimenti tecnici verificati per la scelta delle code: [Laravel Queue 11](https://laravel.com/framework/docs/11.x/queues), [Laravel Horizon 11](https://laravel.com/framework/docs/11.x/horizon), [Laravel Scheduler 11](https://laravel.com/framework/docs/11.x/scheduling), [RabbitMQ MQTT Plugin](https://www.rabbitmq.com/docs/mqtt).


## Assegnazione su richiesta e contatori — decisioni confermate

Separare tre entità persistenti:

1. `coupons`: definizione dell’offerta, traduzioni, regole e quote.
2. `coupon_assignments`: una riga personale e il relativo UUID/codice soltanto quando un cliente richiede e ottiene l’assegnazione. La stessa assegnazione viene riutilizzata per richieste/download successivi e per gli eventuali utilizzi multipli.
3. `coupon_usages`: una riga per ciascun utilizzo effettivo registrato dalla prenotazione, con riferimento alla prenotazione e storico delle operazioni.

Il termine pubblico “riscatta” significa ottenere l’assegnazione; “utilizza” significa consumare un utilizzo nella prenotazione. Evitare un unico contatore “riscattati” che confonda questi eventi.

| Valore | Significato e gestione |
| --- | --- |
| `max_assignments` | Quota massima di assegnazioni personali; nullo indica nessun limite |
| `assigned_count` | Assegnazioni effettivamente create; in questo flusso coincide con i codici generati |
| Assegnazioni residue | Derivate da quota meno assegnazioni, oppure “illimitate”; il codice non viene pre-generato |
| `max_uses_per_assignment` | 1, N o nullo per utilizzi illimitati secondo la configurazione |
| `uses_count` sull’assegnazione | Numero di utilizzi consumati da quel cliente per quell’offerta |
| Utilizzi residui personali | Derivati dal limite personale meno utilizzi; nessun accredito per annullamento/no-show |
| `total_uses_count` sull’offerta | Totale degli utilizzi di tutte le assegnazioni |
| `max_total_uses` facoltativo | Eventuale tetto complessivo degli utilizzi dell’offerta, distinto dal numero di clienti assegnatari |
| Scaduti/revocati/utilizzati almeno una volta | Conteggi di report ricavati dagli stati e dallo storico, senza confonderli con la disponibilità numerica |

La capienza di assegnazione e quella di utilizzo sono indipendenti: esaurire le nuove assegnazioni non invalida i coupon personali già ottenuti, salvo le loro condizioni e l’eventuale tetto complessivo di utilizzi. Un’offerta scaduta o disattivata non è ottenibile anche se la quota numerica non è esaurita; esporre quindi anche l’effettiva ottenibilità/validità corrente.

Vincolo univoco offerta/cliente per evitare doppie assegnazioni; transazioni e lock/aggiornamenti condizionati MySQL per prenotare la quota, creare l’assegnazione e incrementare il contatore in modo atomico. Una richiesta ripetuta restituisce il codice già assegnato senza scalare ancora la disponibilità. La registrazione di utilizzo è idempotente per prenotazione e mantiene il vincolo di non cumulabilità.

I contatori numerici servono per prestazioni e controllo delle quote, ma devono essere riconciliabili con assegnazioni/utilizzi persistenti. Redis gestisce i job e i limiti di invio, non sostituisce MySQL come fonte dello stato dei coupon. Non memorizzare un altro contatore mutabile “disponibili” quando è calcolabile: si evita di disallineare generati, assegnati e residui.

## Regole base dell’editor coupon

Le regole confermate dal prototipo sono:

- Giorni della settimana consentiti, con limitazione attivabile/disattivabile.
- Utilizzi multipli disabilitati: un utilizzo per assegnazione personale.
- Utilizzi multipli abilitati: massimo N utilizzi oppure illimitati fino alla scadenza.
- Numero minimo di persone, con regola opzionale.
- Numero massimo di persone, con regola opzionale e controllo che non sia inferiore al minimo.

Queste regole si riferiscono alla data della visita e alle persone della prenotazione, non al giorno in cui si scarica il coupon. Quota totale delle assegnazioni, scadenza e regole del beneficio economico restano impostazioni distinte. Le limitazioni al beneficio in cassa vengono espresse nei relativi campi strutturati e non aggiunte arbitrariamente alle regole base del prototipo.

## Worker dedicati e riferimento Tour.Puglia

Verificato il riferimento `/home/toni/Projects/tour-puglia/docker-compose-laravel/docker-compose-dev.yml`: `queue-background` esegue `queue:work` sulle code route-imports/offline-packages/privacy/analytics. Il file dichiara che Redis non è usato; l’ambiente di produzione citato imposta `QUEUE_CONNECTION=database`. La separazione dei servizi è riutilizzabile, ma quel worker non dimostra l’uso di Redis.

Il tenant RistoPilot configura invece `QUEUE_CONNECTION=redis` e `MESSAGE_QUEUE_CONNECTION=redis`. Prevedere un worker/supervisor dedicato a `marketing-email`, distinto da `messages-email` e da eventuali code di calcolo lente. Una coda promozionale con invii brevi, limitati e riprendibili non necessita di ereditare il timeout da 600 secondi del worker background dell’altro progetto.

L’invio rimane Laravel Mail sul mailer SMTP configurato. Lo scheduler attiva campagne programmate, il dispatcher alimenta la coda dedicata e il worker esegue le consegne. Configurare timeout SMTP finito, timeout del worker e intervallo di riacquisizione dei job coerenti, oltre a tentativi ed esiti incerti. L’accettazione SMTP è un esito diverso dalla consegna nella casella del cliente; non presumere webhook o metriche di consegna disponibili con una semplice casella SMTP.

Riservare capacità ai messaggi richiesti dal cliente. I limiti sono per account SMTP e, quando applicabile, per IP: se più tenant condividono una casella, il limite deve essere coordinato tra tenant mediante un limiter condiviso o un dispatcher centrale. Aumentare i worker non aumenta la quota del provider.

## Ambienti email e dominio mittente

- Sviluppo: SMTP verso MailHog, senza credenziali; usare il nome del servizio Docker raggiungibile dal worker. Nel file del tenant verificato è `ristopilot-tenant_mailhog:1025`.
- Produzione: SMTP autenticato della casella OVH/Zimbra predisposta per `ristogenius.com`, con hostname, porta e sicurezza verificati per l’offerta effettiva. L’esempio Tour.Puglia usa `ssl0.ovh.net:587` e TLS, ma non va copiato con il suo mittente o le sue credenziali.
- `MAIL_FROM_ADDRESS`: casella o alias autorizzato su `ristogenius.com`; `MAIL_FROM_NAME`: identità pubblica configurata del prodotto/ristorante. Eventuale `Reply-To` distinto e configurabile.
- `CONTACT_MAIL_TO` riguarda il recapito dei messaggi di contatto; non è la lista destinatari delle campagne. Analogamente `CONTACT_MAIL_QUEUE=contact` non seleziona la nuova coda promozionale.
- Il file locale del tenant letto durante la verifica riporta `hello@example.com` come mittente, quindi differisce dall’esempio incollato dall’utente. Al rilascio va sostituito con l’indirizzo autorizzato del dominio scelto.
- `config/mail.php` imposta attualmente lo schema SMTP in modo fisso. Prevedere una configurazione coerente con le variabili dell’ambiente, insieme a timeout SMTP esplicito e verifica della connessione sicura in produzione.
- Verificare autorizzazione del mittente, configurazione SPF/DKIM/DMARC e limiti della casella prima degli invii reali. Nessuna quota oraria viene dedotta automaticamente dall’esempio di un’altra casella o da un altro piano OVH.

Non sono state modificate configurazioni `.env`, credenziali, account SMTP o DNS durante la pianificazione.

Riferimenti OVH consultati: [configurazione Zimbra nei client](https://github.com/ovh/docs/blob/develop/pages/web_cloud/email_and_collaborative_solutions/zimbra/zimbra_mail_apps/guide.en-gb.md), [diagnostica invio/ricezione](https://docs.ovhcloud.com/en/guides/web-cloud/email-and-collaborative-solutions/troubleshooting/diagnostic-advanced), [avvio Zimbra e configurazione dominio](https://docs.ovhcloud.com/en/guides/web-cloud/email-and-collaborative-solutions/zimbra/getting-started-zimbra).
