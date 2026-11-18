# Divisione del conto e incassi misti

## Percorso operativo

Dal dettaglio conto `/manage/cash-desk/orders/{order}`:

1. **Conto unico**: `Registra incasso` apre la modal. Contanti, carta e buoni pasto possono essere combinati nello stesso incasso. Importi inferiori al residuo vengono registrati come acconto e lasciano il conto aperto.
2. **Parti uguali**: indicare N paganti e premere `Prepara i paganti`. Sono create N schede persistenti con importo, stato e residuo; non viene proposta soltanto la prima quota. Un conto di 10,01 € diviso in tre produce 3,34 €, 3,34 € e 3,33 €. Ogni scheda apre la propria modal di incasso misto.
3. **Ognuno paga i propri prodotti**: preparare il piano prima del primo incasso. Vengono create N schede e una scheda aggiuntiva `Rimanenza / ultimo pagante`. Con `Scegli prodotti`, assegnare quantità intere di prodotti, coperti, servizio e consegna al pagante. La modal mostra subito il suo totale e la rimanenza dopo l'assegnazione. Le unità assegnate ad altri vengono sottratte dalle disponibilità. Ciò che resta non assegnato compare nella scheda rimanenza e può essere incassato dall'ultimo pagante.

La modal di pagamento permette di inserire contanti, carta e buoni, usare i pulsanti `Tutto …` e completare un metodo con il residuo. Per una quota la somma deve essere esattamente il residuo di quel pagante: il pagamento misto viene registrato insieme, senza registrazioni parziali fra metodi. Per il conto unico sono ammessi acconti espliciti. Le mance sono separate e si attribuiscono a contanti o carta, mai ai buoni. I buoni richiedono emittente e numero, con riferimento facoltativo.

I pagamenti sono soltanto registrazioni di importi già ricevuti: nessun addebito POS viene eseguito. Il tavolo resta occupato fino al saldo completo dell'ordine. Lo storico raggruppa gli importi della stessa operazione e mostra la composizione per metodo. Il numero dei buoni registrato viene proposto nella pagina dei documenti fiscali se è noto per tutti i pagamenti in buoni del conto.

## Modifiche, residui e arrotondamenti

Prima del primo incasso di una quota si può sostituire la divisione o tornare al conto unico. Dopo il primo incasso non si possono eliminare o sostituire le quote. Nella divisione per prodotti si possono modificare ancora le assegnazioni dei paganti non incassati, usando esclusivamente unità non già pagate. Dopo l'incasso della rimanenza le assegnazioni sono bloccate; le altre quote già preparate restano incassabili.

Sconti già applicati sono distribuiti sui prodotti mantenendo esattamente i totali per aliquota IVA. Le differenze di un centesimo vengono ripartite con il metodo dei resti maggiori, anche fra più unità dello stesso prodotto. Le schede mostrano importi scontati; il totale di tutte le schede coincide con il conto o, per le quote uguali create dopo un acconto, con il residuo.

La divisione per prodotti usa quantità intere: non gestisce frazioni di un singolo articolo, come metà di una bottiglia. È possibile usare la divisione per importo per conti con prodotti condivisi. Sono gestite al massimo 100 persone, 1.000 righe e 10.000 unità per piano. I servizi accessori restano righe selezionabili; non sono automaticamente distribuiti fra tutti i paganti.

Se erano stati registrati incassi senza attribuzione dei prodotti, il residuo può ancora essere diviso in parti uguali. Non si può ricostruire retroattivamente quali prodotti siano stati pagati: per questo la divisione per prodotti va iniziata prima del primo incasso.

Una modifica al conto prima dell'incasso (quantità, prezzi o sconti) rende il piano precedente non incassabile: compare un avviso per ricreare la divisione. L'importo del conto rimane bloccato dopo il primo incasso secondo le regole già esistenti.

## Integrità

- Piano, schede e unità attribuite sono conservati nel database, non nella sola sessione Livewire.
- Ogni mutazione serializza l'ordine con `lockForUpdate`. Una versione del piano impedisce di usare modal aperte prima di una modifica da parte di un altro operatore.
- L'incasso misto ha una sola chiave UUID e un hash del payload. I metodi vengono registrati nella stessa transazione: se il secondo metodo fallisce, vengono annullati anche il primo e il batch. Un retry identico non duplica l'incasso; payload diversi o riuso fra vecchio/nuovo percorso vengono rifiutati.
- Quote di altri ordini, quantità già attribuite, quote incassate e importi superiori al residuo vengono rifiutati lato servizio. Il percorso di pagamento precedente non può aggirare un piano attivo.
- Mance e importi sul conto restano separati. La riconciliazione dei contanti include soltanto incassi e mance in contanti. La notifica cliente di saldo viene inviata una sola volta, alla chiusura completa.

Le quote si riferiscono a **un unico ordine**, ma ogni incasso può ora avere il proprio documento commerciale o fattura sandbox, emesso esplicitamente dalla cassa. Non viene emesso un documento al solo salvataggio del piano o automaticamente al pagamento. Rimborsi gestionali e riconciliazione sono descritti in [cash-fiscal-checkout.md](cash-fiscal-checkout.md); la riapertura dei conti resta esclusa.

## Installazione

Migrazione aggiuntiva, dopo quelle del motore cassa e del modulo fiscale:

```sh
docker exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_070000_add_split_bills_and_payment_batches.php
```

Le tabelle nuove sono `cash_bill_plans`, `cash_bill_shares`, `cash_payment_batches`. I pagamenti preesistenti mantengono la propria registrazione; le nuove colonne del pagamento sono nullable/default zero. Non creare automaticamente piani su ordini già incassati e non usare il rollback come procedura di rimborso.

Test dedicati: `tests/Feature/Orders/SplitBillTest.php`, con regressioni in Orders, PublicOrders e Fiscal. Le prove automatiche non registrano incassi nel database applicativo e non effettuano chiamate reali a Openapi.

## Identità dei componenti nel browser

Lo storico incassi mantiene un contenitore stabile e chiavi per gruppo/righe di pagamento. La scheda fiscale annidata usa la chiave assegnata dal genitore: non va aggiunto un `wire:key` differente sulla sua radice, perché il successivo rendering del segnaposto Livewire conserva soltanto `wire:id`. Il cambio di identità durante il confronto del DOM causava `Snapshot missing` dopo il primo incasso, bloccando i pulsanti del modal del secondo pagante.

Verificato in Firefox con configurazione fiscale attiva: apertura consecutiva dei due paganti, scorciatoie contanti/carta/buoni, completamento misto (10 € contanti + 5 € carta), saldo della seconda quota e chiusura dell’ordine nella stessa pagina, senza errori JavaScript. La prova usa solo ordini nel database test e non invia documenti remoti.

PHPUnit compila le viste in `/tmp/ristopilot-tenant-testing-views`, separato dalla cache web, per evitare che i test eseguiti da CLI modifichino proprietà e permessi dei file Blade usati dal server.
