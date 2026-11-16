# Comande, KDS e cassa

## Uso

- `/manage/orders`: selezionare sala e tavolo, aprire la comanda con coperti e prenotazione facoltativa. Le prenotazioni proposte sono quelle accettate/sedute del tavolo per oggi; nessun automatismo cambia lo stato della prenotazione.
- Selezionare un menu visibile, una categoria e un prodotto. La finestra prodotto gestisce quantità, note e ingredienti aggiunti/rimossi. I prezzi specifici del menu prevalgono sul prezzo base del prodotto.
- Selezionare un’uscita in bozza per aggiungervi prodotti. `Invia uscita` produce un ticket per ogni reparto produttivo coinvolto. Prodotti senza reparto produttivo restano nel conto senza ticket KDS.
- `/manage/kds`: scegliere il reparto. Avanzamento `Da fare → In preparazione → Pronto → Servito`. I ticket serviti rimangono in vista per due ore.
- Finché tutti i reparti di un’uscita sono ancora `Da fare`, è possibile modificarne o rimuoverne le righe. Non è prevista riapertura: per aggiungere prodotti dopo l’invio creare una nuova uscita. Quando un reparto inizia, l’intera uscita è bloccata.
- `/manage/cash-desk`: dettaglio ordine, preconto stampabile non fiscale e registrazione manuale dell’incasso. Prima di incassare occorre inviare o rimuovere le righe in bozza. Un ordine annullabile viene conservato nello storico, senza eliminazioni definitive.
- `/manage/cash-desk/history`: data di servizio, stato e ricerca per tavolo/sala/cliente; totale incassato relativo ai filtri.

Gli ordini aperti sono sempre visibili nelle mappe, anche dai giorni precedenti. Il pagamento libera il tavolo per una nuova comanda ma non interrompe la produzione del precedente ordine. In cassa il rosso indica un ordine aperto e il verde un ordine pagato nella data selezionata.

## Regole e struttura

`OrderService` è il punto unico per le mutazioni: verifica permessi, convalida gli input e serializza le operazioni con lock sull’ordine. L’apertura blocca il tavolo e usa un indice univoco nullable per l’assegnazione attiva. Invio e pagamento sono idempotenti; il KDS richiede lo stato atteso per impedire che due clic concorrenti saltino uno stato. L’incasso verifica che l’importo mostrato sia ancora attuale.

Il coperto è una fotografia del valore fisso della sala. La percentuale e il coperto del menu non vengono applicati. Gli importi sono interi in centesimi. Prezzi, IVA, nomi, reparto, note e variazioni sono conservati nell’ordine: i cambiamenti al catalogo non riscrivono i conti precedenti. Durante una modifica si mantengono il prezzo base e le variazioni già selezionate; per nuove variazioni si usano i prezzi correnti degli ingredienti.

Comande e cassa condividono la mappa `Orders/RoomMap`; la mappa prenotazioni esistente non è stata modificata. Il polling aggiorna mappa/riepiloghi ogni 5 secondi e KDS ogni 3 secondi. Il modulo in compilazione mantiene le proprie proprietà Livewire.

Permessi: `personnel-manage-orders`, `personnel-manage-cash-register`, `personnel-manage-kds`. Gli amministratori accedono tramite il bypass già esistente. I permessi del personale sono assegnabili nella matrice già presente. Il nuovo permesso non viene assegnato automaticamente ai dipendenti.

`source`, `fulfillment`, associazione facoltativa ai tavoli e righe di pagamento separate consentono l’estensione futura a ordini clienti/asporto e pagamenti multipli. Non ci sono ancora endpoint pubblici, QR code, POS, split payment, emissione fiscale, scarico magazzino, gruppi di opzioni o stampa diretta sui dispositivi dei reparti.

## Installazione e test

Applicare la migrazione `2026_11_16_000000_create_order_management_tables.php`. La migrazione aggiunge solo le nuove tabelle.

```sh
docker compose -f docker-compose-dev.yml exec ristopilot-tenant_php php artisan migrate --path=database/migrations/2026_11_16_000000_create_order_management_tables.php

docker compose -f docker-compose-dev.yml exec \
  -e APP_ENV=testing -e DB_DATABASE=ristopilot_tenant_testing \
  ristopilot-tenant_php php artisan test
```

I test includono prezzi e modifiche ingredienti, conservazione dopo modifiche/eliminazioni del catalogo, separazione reparti, blocchi di lavorazione, invii/pagamenti duplicati, stato KDS obsoleto, permessi nelle richieste Livewire, mappe, stampa e storico.
