<p>Ciao {{ $entry->customer->firstname }},</p>
<p>il tuo tavolo per {{ $entry->pax }} {{ $entry->pax === 1 ? 'persona è pronto' : 'persone è pronto' }} da {{ $restaurant }}.</p>
<p>Presentati allo staff per essere accompagnato al tavolo.</p>
