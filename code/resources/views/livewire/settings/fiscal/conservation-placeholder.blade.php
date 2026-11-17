<section class="box mt-4" aria-labelledby="fiscal-conservation-title"><div class="box-body">
    <div class="o-toolbar">
        <h2 id="fiscal-conservation-title" class="font-semibold">{{ __('Conservazione fatture') }}</h2>
        <span class="o-badge">{{ __('Da configurare in RistoPilot') }}</span>
    </div>
    <p>{{ __('Sezione predisposta per collegare il servizio di conservazione dell’azienda e consultarne gli esiti.') }}</p>
    <ul class="mt-3 list-disc pl-5">
        <li><strong>{{ __('Agenzia delle Entrate:') }}</strong> {{ __('servizio con adesione nel portale Fatture e Corrispettivi.') }}</li>
        <li><strong>{{ __('Provider esterno:') }}</strong> {{ __('futura integrazione con un servizio di conservazione dedicato.') }}</li>
    </ul>
    <p class="o-muted mt-3">{{ __('La trasmissione tramite SDI e la conservazione sono servizi distinti. La presenza di XML e PDF in app non attesta la conservazione a norma.') }}</p>
    <button type="button" class="ti-btn ti-btn-light mt-3" disabled aria-describedby="fiscal-conservation-placeholder">{{ __('Configura conservazione · Prossimamente') }}</button>
    <p id="fiscal-conservation-placeholder" class="o-muted mt-3">{{ __('Placeholder informativo. Un servizio eventualmente già attivato fuori dall’app non è ancora verificato da RistoPilot.') }}</p>
</div></section>
