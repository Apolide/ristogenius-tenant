# Localizzazione delle interfacce — 17 novembre 2026

Le interfacce sono state collegate ai cataloghi italiano, inglese e tedesco. Nel gestionale viene usata la lingua dell'utente autenticato; nelle pagine pubbliche prevale la lingua selezionata nel menu, modulo o link cliente.

La verifica precedente aveva individuato testi letterali nelle viste, etichette restituite dai modelli e messaggi prodotti dai servizi. L'intervento ora comprende anche questi livelli.

## Ambito implementato

- Sidebar, header e notifiche.
- Sala e prenotazioni: legenda, mappa, tooltip, assegnazione dei tavoli, finestre modali e conferme.
- Comande e KDS: stati, uscite, ingredienti, azioni e messaggi operativi.
- Ordini cliente: elenco, filtri, dettaglio, transizioni, proposte di modifica e relativi stati.
- Cassa: pagamenti, conti divisi, paganti, assegnazione dei prodotti, rimborsi, turni, storico e riconciliazione.
- Fiscale: configurazione, documenti, richieste, stati operativi, messaggi di errore e copie via email.
- Catalogo: prodotti, ingredienti, menu, categorie, editor, conferme, anteprime e allergeni. I contenuti multilingue disponibili vengono visualizzati nella lingua dell'operatore, mantenendo i campi separati per modificarne le traduzioni.
- Marketing: creazione e modifica dei moduli, campi, calendario evento, stile, risposte e modulo pubblico.
- Contatti, utenti e testi residui nelle viste già localizzate di clienti e prenotazioni.
- Ordini pubblici: accesso, scelta menu, carrello, personalizzazione, conferma, riepilogo e risposta alle proposte.
- Messaggi di validazione, attributi principali e paginazione.

Le traduzioni dei testi letterali utilizzano i cataloghi JSON Laravel `lang/{it,en,de}.json`; restano validi i cataloghi PHP organizzati per modulo già presenti. I nomi di prodotti, ingredienti, sale, tavoli, reparti, paganti personalizzati e i messaggi inseriti dagli utenti sono contenuti: vengono preservati. Le etichette controllate dell'interfaccia vengono tradotte senza cambiare i codici salvati.

## Notifiche, email e lingua del cliente

Le nuove notifiche condivise memorizzano chiavi e parametri in `notifications.translation_data`, risolti nella lingua di ciascun destinatario alla visualizzazione. Per lo storico vengono riconosciuti soltanto i formati esatti delle notifiche generate dal sistema; gli altri testi restano intatti.

Gli oggetti e i messaggi automatici delle email vengono composti nella lingua del destinatario. Le proposte scritte dal personale restano testo libero. Le email al personale rispettano la lingua del singolo utente. Le copie fiscali usano la lingua del cliente associato o dell'ordine.

`orders.customer_language` e `order_courses.customer_language` conservano la lingua selezionata durante l'ordine, anche quando differisce dalla lingua salvata nel profilo cliente. Per i record precedenti privi di questo dato resta il ripiego sulla lingua del cliente. I componenti pubblici mantengono la lingua anche nelle richieste Livewire successive; accesso e proposte pubbliche impostano la lingua dal contesto del link.

## Integrità dei dati operativi

Le traduzioni non modificano identificativi, valori di stato, codici del protocollo fiscale, contenuti XML, riferimenti di riconciliazione o prezzi. Le fotografie dei conti e delle ripartizioni mantengono etichette canoniche, tradotte soltanto quando vengono mostrate: il cambio lingua non invalida una divisione del conto. Anche i riepiloghi memorizzati nelle proposte restano leggibili nella lingua di chi li consulta.

I messaggi di errore applicativi vengono tradotti. Per gli errori di sistema già salvati viene riconosciuta anche la versione italiana, inglese o tedesca; i dettagli originali restituiti da un fornitore esterno restano disponibili nel testo ricevuto.

## Migrazione e verifica

La migrazione `2026_11_17_000000_add_localization_metadata.php` aggiunge i metadati delle notifiche e la lingua degli ordini. Deve essere applicata insieme alla distribuzione del codice. Non è stata eseguita sul database operativo durante questa attività.

Verifiche automatizzate: sintassi PHP e compilazione delle viste modificate; pagine interne in inglese e tedesco; modali e validazione; stati dei modelli; notifiche condivise e storico; lingua delle email e dei link pubblici; stabilità delle fotografie dei conti al cambio lingua; regressione dei moduli interessati. Le fixture storiche che verificano testi italiani usano esplicitamente l'italiano come lingua predefinita in `phpunit.xml`, mentre i test multilingue selezionano la lingua da verificare.

I controlli sono eseguiti nel container PHP con il database dedicato `ristopilot_tenant_testing`. Non è stata effettuata una verifica visuale tramite l'account Simone e non è stata eseguita una distribuzione in produzione.

Esito finale: **403 test superati, 2.841 assertion** nei moduli interessati, incluse le verifiche mirate finali sulle email. Controlli PHP/Blade e `git diff --check` superati. Cataloghi JSON: **1.488 voci per lingua**, con parità delle chiavi tra italiano, inglese e tedesco e nessun valore vuoto.


## Nomi dei reparti

La pagina `/manage/settimgs/departments` permette di aggiungere e modificare i nomi inglese e tedesco di ogni reparto. Il nome esistente resta quello italiano e il ripiego quando manca una traduzione. Elenco, ricerca, conferma di eliminazione, KDS, collegamenti dalla sala e selettori del catalogo prodotti usano il nome nella lingua dell’operatore. I nomi canonici salvati nelle comande restano stabili.

La migrazione `2026_11_17_010000_add_department_name_translations.php` aggiunge il campo JSON facoltativo e traduce i quattro reparti predefiniti. I nomi personalizzati possono essere tradotti dal modulo di modifica. Il seeder completa le traduzioni predefinite senza sovrascrivere quelle personalizzate.
