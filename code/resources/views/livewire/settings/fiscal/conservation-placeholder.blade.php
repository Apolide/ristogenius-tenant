<section class="box mt-4" aria-labelledby="fiscal-conservation-title"><div class="box-body">
    <div class="o-toolbar">
        <h2 id="fiscal-conservation-title" class="font-semibold">{{ __('fiscal.archiving.invoice_archiving') }}</h2>
        <span class="o-badge">{{ __('fiscal.archiving.to_be_configured_in_ristopilot') }}</span>
    </div>
    <p>{{ __('fiscal.archiving.archiving_service_placeholder') }}</p>
    <ul class="mt-3 list-disc pl-5">
        <li><strong>{{ __('fiscal.archiving.italian_revenue_agency') }}</strong> {{ __('fiscal.archiving.fiscal_portal_enrollment_required') }}</li>
        <li><strong>{{ __('fiscal.archiving.external_provider') }}</strong> {{ __('fiscal.archiving.future_integration_with_a_dedicated_archiving_service') }}</li>
    </ul>
    <p class="o-muted mt-3">{{ __('fiscal.archiving.transmission_and_archiving_scope') }}</p>
    <button type="button" class="ti-btn ti-btn-light mt-3" disabled aria-describedby="fiscal-conservation-placeholder">{{ __('fiscal.archiving.configure_archiving_coming_soon') }}</button>
    <p id="fiscal-conservation-placeholder" class="o-muted mt-3">{{ __('fiscal.archiving.external_archiving_verification_unavailable') }}</p>
</div></section>
