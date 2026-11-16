# Motore cassa RistoPilot — fase 1

Il conto continua a coincidere con l'ordine: sala, QR, asporto e consegna utilizzano `OrderService`. Il modulo è gestionale e non emette documenti fiscali, non chiama Openapi e non addebita carte. Non occorre una chiave API per questa fase.

## Uso

Nel dettaglio cassa sono disponibili:

- Incassi parziali o saldo residuo, con contanti, carta incassata esternamente, registrazione manuale o buoni pasto. L'importo esclude la mancia, che va indicata a parte. I buoni pasto richiedono un emittente e non coprono mance; riferimento facoltativo.
- Divisione per importo con indicazione del pagante e calcolo di una quota del residuo corrente. Per tre quote di un conto da 10,01 € si possono incassare 3,34 €, 3,34 € e il saldo di 3,33 €. Il pulsante ricalcola sul residuo attuale: non genera sottoconti persistenti né assegna singoli prodotti ai paganti.
- Sconto fisso in euro o percentuale con motivazione obbligatoria. Una nuova applicazione sostituisce lo sconto precedente. Se il conto viene ridotto prima dell’incasso, lo sconto effettivo è limitato al lordo corrente. Zero rimuove lo sconto; 100% permette un conto omaggio, chiuso con registrazione a zero.
- Riepilogo di imponibile e IVA inclusa per aliquota. I prodotti usano l'IVA fotografata nelle righe. Coperti, servizio e consegna usano un'aliquota del conto, inizialmente 10%, modificabile prima dell'incasso nel modulo sconto/IVA (sconto zero per aggiornare solo l'aliquota). Il riepilogo non è una validazione fiscale delle aliquote o delle esenzioni.

Al primo incasso il totale e il riepilogo IVA vengono fotografati: non sono più consentiti sconti, variazioni ai coperti/prodotti, nuove comande QR o annullamento. Il KDS e l'avanzamento della consegna continuano a funzionare. Solo il saldo completo libera il tavolo. Rimborsi e riaperture non fanno parte di questa fase.

## Turni e riconciliazione

`/manage/cash-desk/shifts`, accessibile dalle pagine cassa, gestisce un solo cassetto attivo per database tenant. Apertura con fondo iniziale, riepilogo per metodo, chiusura con contanti contati e note. La differenza è `contati − (fondo + contanti incassati + mance in contanti)`. Carta, manuale e buoni pasto sono esclusi dal contante atteso. Le mance in contanti si presumono lasciate nel cassetto fino alla chiusura: non sono previsti prelievi o versamenti durante il turno.

Gli incassi vengono associati automaticamente al turno attivo. Per compatibilità con il flusso esistente si possono registrare incassi senza turno: sono esplicitamente indicati e non entrano nella riconciliazione. Un conto può avere incassi in turni differenti. La chiusura è immutabile e una ripetizione non cambia i valori già salvati; non equivale alla chiusura fiscale giornaliera.

Lo storico distingue gli incassi della giornata (data effettiva di registrazione, tutti gli ordini) dai pagamenti relativi agli ordini filtrati per data di servizio, stato e cliente. Le mance sono mostrate separatamente.

## Integrità e confini

Importi interi in centesimi. Lo sconto proporzionale viene distribuito sulle aliquote con il metodo dei resti maggiori, quindi scorporato con arrotondamento al centesimo. La somma dei gruppi coincide con il totale scontato. Mance separate dalla vendita.

Gli incassi serializzano l'ordine con `lockForUpdate`, verificano totale e residuo visualizzati e rifiutano sovraincassi. La UI invia una chiave UUID univoca per ogni incasso: un retry identico restituisce l'esito già registrato, un riutilizzo con dati diversi viene respinto. Le firme precedenti del servizio restano compatibili con il pagamento a saldo. I turni usano un indice univoco per il cassetto attivo e un lock che serializza incasso/chiusura. Eventi di ordine conservano operatore, importi e motivazioni.

L'isolamento segue il modello esistente del progetto: un database per tenant. Tutte le operazioni richiedono `personnel-manage-cash-register`. Non introdurre un database condiviso senza aggiungere lo scope tenant a conti, pagamenti e turni.

Le fasi successive potranno consumare lo snapshot del conto da un servizio fiscale separato. Non sono ancora implementati documenti commerciali, fatture/SDI, conservazione, costi API/portafoglio tenant, gateway Stripe o riconciliazione bancaria/POS. Nessuna chiamata esterna o costo API viene generato in questa fase.

## Migrazione e verifica

Applicare la migrazione aggiuntiva, dopo le migrazioni degli ordini già presenti:

```sh
docker exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_030000_add_cash_engine.php
```

Non viene eseguita automaticamente sul database applicativo durante lo sviluppo. I dati preesistenti mantengono sconto e mancia a zero; i vecchi incassi rimangono senza turno. Il rollback rimuove i nuovi dati gestionali.

Test con configurazione isolata dalla cache applicativa e database dedicato:

```sh
docker exec \
  -e APP_CONFIG_CACHE=/tmp/ristopilot-cash-test-config.php \
  -e APP_ENV=testing -e DB_CONNECTION=mysql \
  -e DB_HOST=ristopilot-tenant_database \
  -e DB_DATABASE=ristopilot_tenant_testing \
  -e DB_USERNAME=test -e DB_PASSWORD=test \
  ristopilot-tenant_php php artisan test --compact tests/Feature/Orders tests/Feature/PublicOrders
```
