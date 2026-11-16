<?php

namespace Tests\Feature\Fiscal;

use App\Livewire\CashDesk\BatchDocument;
use App\Livewire\CashDesk\Reconciliation;
use App\Models\CashPaymentBatch;
use App\Models\CashRefund;
use App\Models\FiscalCashier;
use App\Models\FiscalDocument;
use App\Models\FiscalOperation;
use App\Models\FiscalProfile;
use App\Models\FiscalStore;
use App\Models\Order;
use App\Models\User;
use App\Services\Fiscal\FiscalDocumentService;
use App\Services\Fiscal\FiscalInvoiceService;
use App\Services\Orders\CashBillService;
use App\Services\Orders\CashCheckoutService;
use App\Services\Orders\CashReconciliationService;
use App\Services\Orders\CashRefundService;
use App\Services\Orders\CashShiftService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BatchFiscalTest extends TestCase
{
    use RefreshDatabase;

    private FiscalProfile $profile;

    private FiscalStore $store;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['fiscal.openapi.token' => 'sandbox-test', 'fiscal.environment' => 'sandbox']);
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
        app(Permissions::class)->ensurePermissionsExist();
        $this->profile = FiscalProfile::create(['fiscal_id' => '01114601006', 'name' => 'Test & Ristorante', 'email' => 'fiscal@example.test', 'enabled' => true, 'invoice_enabled' => true, 'tax_regime' => 'RF01',
            'address' => ['street_address' => 'Via Roma', 'street_number' => '1', 'zip_code' => '00187', 'city' => 'Roma', 'province' => 'RM']]);
        $this->store = FiscalStore::create(['fiscal_profile_id' => $this->profile->id, 'remote_id' => 'store1', 'label' => 'Sede', 'status' => 'ONLINE', 'active' => true, 'pem_serial' => 'XM0J-000001',
            'registers' => [['cr_id' => 'register1', 'label' => 'Cassa', 'status' => 'ONLINE']]]);
        FiscalCashier::create(['fiscal_profile_id' => $this->profile->id, 'user_id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'status' => 'active', 'remote_id' => 'cashier1']);
    }

    private function order(int $amount = 3000): Order
    {
        return Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 1, 'cover_price_cents' => $amount]);
    }

    private function pay(Order $order, array $amounts, int $tip = 0): CashPaymentBatch
    {
        $order->refresh();
        $amounts = array_replace(['cash' => 0, 'card' => 0, 'voucher' => 0], $amounts);

        return app(CashCheckoutService::class)->pay($order->id, (string) Str::uuid(), $amounts, $order->totalCents(), $order->balanceCents(), tip: $tip);
    }

    private function buyer(): array
    {
        return ['name' => 'Cliente & Figli', 'vat_id' => '01234567890', 'tax_code' => '', 'address' => 'Via Roma 1', 'zip' => '00187', 'city' => 'Roma', 'province' => 'RM', 'sdi' => '0000000', 'pec' => ''];
    }

    private function reject(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected validation exception');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    private function receipts(): void
    {
        Http::fake(['test.invoice.openapi.com/IT-e-receipts' => function ($request) {
            $body = $request->data();

            return Http::response(['success' => true, 'data' => ['id' => 'receipt'.Str::random(8), 'fiscal_id' => $this->profile->fiscal_id, 'store_id' => 'store1', 'cr_id' => 'register1', 'type' => 'sale', 'status' => 'sent',
                'document_number' => '0001-0001', 'document_date' => today()->toDateString(), 'total_amount' => ($body['cash_payment_amount'] ?? 0) + ($body['electronic_payment_amount'] ?? 0) + ($body['ticket_restaurant_payment_amount'] ?? 0)]]);
        }]);
    }

    private function resetHttp(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
    }

    private function invoices(string $sdi = 'SENT'): void
    {
        $this->resetHttp();
        Http::fake(['test.invoice.openapi.com/IT-invoices*' => function ($request) use ($sdi) {
            if (str_contains($request->url(), 'IT-invoices_validate')) {
                return Http::response(['data' => ['valid' => true, 'message' => 'XML structure is valid.']]);
            }
            $dom = new \DOMDocument;
            $dom->loadXML($request->body());
            $xpath = new \DOMXPath($dom);
            $text = fn ($name) => $xpath->evaluate('string(//*[local-name()="'.$name.'"])');

            return Http::response(['data' => ['id' => 'invoice'.Str::random(8), 'fiscal_id' => $this->profile->fiscal_id, 'direction' => 'outgoing', 'type' => $text('TipoDocumento'),
                'document_number' => $text('Numero'), 'issue_date' => $text('Data'), 'total_gross_amount' => (float) $text('ImportoTotaleDocumento'),
                'recipient' => ['vat_id' => $this->buyer()['vat_id']], 'state' => 'SENT', 'details' => ['sdi_status' => $sdi]]]);
        }]);
    }

    public function test_a_partial_mixed_incasso_can_issue_one_receipt_without_waiting_for_order_balance(): void
    {
        $order = $this->order();
        $batch = $this->pay($order, ['cash' => 500, 'card' => 1000], 200);
        $this->receipts();
        $document = app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1');
        $this->assertSame('issued', $document->status);
        $this->assertSame(1500, $document->total_cents);
        $this->assertSame(1500, $order->fresh()->balanceCents());
        $this->assertEquals(['cash' => 500, 'card' => 1000, 'voucher' => 0], $document->snapshot['payments']);
        $this->reject(fn () => app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1'));
        Http::assertSentCount(1);
        Livewire::test(BatchDocument::class, ['batchId' => $batch->id])->assertSee('Emesso')->assertSee('Scarica PDF sandbox');
    }

    public function test_two_payers_have_independent_receipts_and_whole_order_is_blocked(): void
    {
        $order = $this->order();
        $a = $this->pay($order, ['card' => 1500]);
        $b = $this->pay($order, ['cash' => 1500]);
        $this->receipts();
        $service = app(FiscalDocumentService::class);
        $first = $service->issueBatch($a->id, $this->store->id, 'register1');
        $second = $service->issueBatch($b->id, $this->store->id, 'register1');
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame(3000, (int) FiscalDocument::sum('total_cents'));
        $this->reject(fn () => $service->issue($order->id, $this->store->id, 'register1'));
        Http::assertSentCount(2);
    }

    public function test_receipt_timeout_keeps_money_and_blocks_invoice_or_receipt_retry(): void
    {
        $order = $this->order();
        $batch = $this->pay($order, ['card' => 3000]);
        Http::fake(['*' => Http::failedConnection()]);
        $this->reject(fn () => app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1'));
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(3000, (int) $batch->payments()->sum('amount_cents'));
        $this->assertSame('unknown', FiscalDocument::first()->status);
        $this->reject(fn () => app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1'));
        $this->assertSame(1, FiscalOperation::where('action', 'issue')->count());
    }

    public function test_xml_and_invoice_lifecycle_do_not_mark_sent_as_issued(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        $this->invoices();
        $doc = app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1');
        $this->assertSame('pending', $doc->status);
        $this->assertSame('F/'.now()->year.'/1', $doc->document_number);
        $dom = new \DOMDocument;
        $this->assertTrue($dom->loadXML($doc->snapshot['xml']));
        $this->assertSame('FPR12', $dom->documentElement->getAttribute('versione'));
        $this->assertStringContainsString('Test &amp; Ristorante', $doc->snapshot['xml']);
        Http::assertSent(fn ($r) => $r->hasHeader('Content-Type', 'application/xml') && str_starts_with($r->body(), '<?xml') && ! str_contains($r->body(), '"xml":'));
        $this->get(route('cash-desk.fiscal-xml', $doc))->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->reject(fn () => app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1'));
    }

    public function test_sdi_delivered_is_issued_even_when_macro_state_is_sent_and_credit_is_unique(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        $this->invoices('DELIVERED');
        $doc = app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1');
        $this->assertSame('issued', $doc->status);
        $credit = app(FiscalInvoiceService::class)->credit($doc->id);
        $this->get(route('cash-desk.fiscal', $batch->order_id))->assertOk()->assertSee('Nota di credito');
        $this->assertSame('credit', $credit->type);
        $this->assertStringContainsString('<TipoDocumento>TD04</TipoDocumento>', $credit->snapshot['xml']);
        $this->assertStringContainsString('<DatiFattureCollegate>', $credit->snapshot['xml']);
        $this->reject(fn () => app(FiscalInvoiceService::class)->credit($doc->id));
        $this->assertSame(0, CashRefund::count());
        Http::assertSentCount(4);
    }

    public function test_invalid_invoice_recipient_never_reserves_number_or_sends_request(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        $this->reject(fn () => app(FiscalInvoiceService::class)->issue($batch->id, array_replace($this->buyer(), ['vat_id' => '']), $this->store->id, 'register1'));
        $this->assertSame(0, FiscalDocument::count());
        Http::assertNothingSent();
    }

    public function test_refund_is_idempotent_bounded_and_deducts_cash_from_its_actual_shift(): void
    {
        $shift = app(CashShiftService::class)->open(1000);
        $batch = $this->pay($this->order(), ['cash' => 3000], 200);
        $service = app(CashRefundService::class);
        $key = (string) Str::uuid();
        $a = $service->record($batch->id, $key, 500, 'cash', 'Reso');
        $b = $service->record($batch->id, $key, 500, 'cash', 'Reso');
        $this->assertSame($a->id, $b->id);
        $this->assertSame(3700, $shift->expectedCents());
        $this->reject(fn () => $service->record($batch->id, (string) Str::uuid(), 3000, 'cash', 'Troppo'));
        $this->reject(fn () => $service->record($batch->id, $key, 400, 'cash', 'Reso'));
        $this->assertSame(1, CashRefund::count());
        Http::assertNothingSent();
    }

    public function test_reconciliation_distinguishes_money_tips_refunds_and_documents_and_escapes_csv(): void
    {
        $batch = $this->pay($this->order(), ['cash' => 1000, 'card' => 2000], 200);
        $batch->payments()->update(['payer' => '=HYPERLINK("evil")']);
        app(CashRefundService::class)->record($batch->id, (string) Str::uuid(), 500, 'cash', 'Reso', '+external');
        $service = app(CashReconciliationService::class);
        $report = $service->report(today()->toDateString(), today()->toDateString());
        $this->assertSame(['paid' => 1000, 'tips' => 200, 'refunded' => 500], $report['totals']['cash']);
        $this->assertCount(1, $report['issues']);
        $csv = $service->csv($report);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+external", $csv);
        Livewire::test(Reconciliation::class)->assertSee('Riconciliazione cassa')->assertSee('senza_documento');
    }

    public function test_vat_allocation_is_frozen_and_sums_to_full_order_for_equal_shares(): void
    {
        $order = $this->order(100);
        $course = $order->courses()->create(['number' => 1]);
        foreach ([[1001, 10], [502, 22]] as [$amount,$vat]) {
            $course->items()->create(['product_name' => 'Prodotto', 'menu_name' => 'Menu', 'quantity' => 1, 'base_price_cents' => $amount, 'unit_price_cents' => $amount, 'vat' => $vat]);
        }
        $plan = app(CashBillService::class)->create($order->id, 'equal', 2, 1603, 1603);
        $shares = $plan->shares()->orderBy('position')->get();
        $first = app(CashCheckoutService::class)->pay($order->id, (string) Str::uuid(), ['cash' => 0, 'card' => $shares[0]->amount_cents, 'voucher' => 0], 1603, 1603, $shares[0]->id, $plan->version);
        $frozen = $first->fiscal_snapshot;
        $second = app(CashCheckoutService::class)->pay($order->id, (string) Str::uuid(), ['cash' => 0, 'card' => $shares[1]->amount_cents, 'voucher' => 0], 1603, $shares[1]->amount_cents, $shares[1]->id, $plan->fresh()->version);
        $this->assertSame($frozen, $first->fresh()->fiscal_snapshot);
        $this->assertSame(1101, $frozen['groups'][10] + $second->fiscal_snapshot['groups'][10]);
        $this->assertSame(502, $frozen['groups'][22] + $second->fiscal_snapshot['groups'][22]);
    }

    public function test_invalid_xml_is_rejected_before_the_invoice_post_and_preserves_number_for_correction(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        Http::fake(['test.invoice.openapi.com/IT-invoices_validate' => Http::response(['success' => false, 'message' => 'XML Validation Failed', 'error' => 422], 422)]);
        $this->reject(fn () => app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1'));
        $document = FiscalDocument::firstOrFail();
        $this->assertSame('rejected', $document->status);
        Http::assertNotSent(fn ($r) => parse_url($r->url(), PHP_URL_PATH) === '/IT-invoices');
        $number = $document->document_number;
        $this->invoices();
        $retry = app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1');
        $this->assertSame($document->id, $retry->id);
        $this->assertSame($number, $retry->document_number);
        $this->assertSame(1, FiscalDocument::count());
    }

    public function test_periodic_sdi_sync_only_reads_known_pending_invoices_and_exposes_scarts(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        $this->invoices();
        $document = app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1');
        $data = $document->remote_data;
        $data['state'] = 'ERROR';
        $data['details'] = ['sdi_status' => 'ERROR', 'sdi_message' => 'Codice fiscale non valido'];
        $this->resetHttp();
        Http::fake(['test.invoice.openapi.com/IT-invoices/'.$document->remote_id => Http::response(['data' => $data])]);
        $this->artisan('fiscal:sync-invoices')->assertSuccessful();
        $this->assertSame('sdi_rejected', $document->fresh()->status);
        Livewire::test(BatchDocument::class, ['batchId' => $batch->id])->assertSee('Codice fiscale non valido')->assertDontSee('Emetti documento commerciale');
        $this->assertSame(1, FiscalDocument::count());
    }

    public function test_invoice_recovery_rejects_a_remote_document_for_another_customer(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        $this->invoices();
        $document = app(FiscalInvoiceService::class)->issue($batch->id, $this->buyer(), $this->store->id, 'register1');
        $data = $document->remote_data;
        $data['recipient']['vat_id'] = '99999999999';
        $this->resetHttp();
        Http::fake(['test.invoice.openapi.com/IT-invoices/'.$document->remote_id => Http::response(['data' => $data])]);
        $this->reject(fn () => app(FiscalInvoiceService::class)->refresh($document->id));
        $this->assertSame('pending', $document->fresh()->status);
    }

    public function test_an_existing_whole_order_receipt_prevents_duplicate_batch_certification(): void
    {
        $order = $this->order();
        $batch = $this->pay($order, ['card' => 3000]);
        FiscalDocument::create(['order_id' => $order->id, 'sale_order_id' => $order->id, 'coverage_key' => 'order:'.$order->id, 'fiscal_store_id' => $this->store->id,
            'provider' => 'openapi', 'environment' => 'sandbox', 'fiscal_id' => $this->profile->fiscal_id, 'type' => 'sale', 'status' => 'issued', 'document_kind' => 'receipt', 'total_cents' => 3000, 'snapshot' => []]);
        $this->reject(fn () => app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1'));
        Livewire::test(BatchDocument::class, ['batchId' => $batch->id])->assertSee('incluso nel documento')->assertDontSee('Emetti documento commerciale');
        Http::assertNothingSent();
    }

    public function test_refund_using_comma_amount_is_recorded_exactly_once_from_the_payment_panel(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        Livewire::test(BatchDocument::class, ['batchId' => $batch->id])->set('refundAmount', '5,50')->set('refundReason', 'Reso')->call('refund')->assertHasNoErrors();
        $this->assertSame(550, CashRefund::firstOrFail()->amount_cents);
        $this->reject(fn () => app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1'));
    }

    public function test_financial_reports_require_cash_permission(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('cash-desk.reconciliation'))->assertForbidden();
        $this->get(route('cash-desk.fiscal-xml', 'nonexistent'))->assertForbidden();
    }

    public function test_enabling_invoices_changes_only_the_invoice_flag_and_preserves_receipts(): void
    {
        $this->profile->update(['remote_id' => 'configuration1', 'invoice_enabled' => false]);
        Http::fake(['test.invoice.openapi.com/IT-configurations/'.$this->profile->fiscal_id => Http::response(['success' => true, 'data' => [
            'id' => 'configuration1', 'fiscal_id' => $this->profile->fiscal_id, 'e_receipts' => true, 'customer_invoice' => true,
            'stores' => [['store_id' => 'store1', 'label' => 'Sede', 'status' => 'ONLINE', 'active' => true, 'pem_serial' => 'XM0J-000001', 'cash_registers' => $this->store->registers]]]])]);
        app(\App\Services\Fiscal\FiscalSetupService::class)->enableInvoices();
        $this->assertTrue($this->profile->fresh()->invoice_enabled);
        $this->assertTrue($this->profile->fresh()->enabled);
        Http::assertSent(fn ($r) => $r->method() === 'PATCH' && $r->data() === ['customer_invoice' => true]);
    }

    public function test_refund_is_deducted_from_the_refund_shift_not_the_original_closed_shift(): void
    {
        $service = app(CashShiftService::class);
        $old = $service->open(0);
        $batch = $this->pay($this->order(), ['cash' => 3000]);
        $service->close($old->id, 3000);
        $current = $service->open(1000);
        $refund = app(CashRefundService::class)->record($batch->id, (string) Str::uuid(), 500, 'cash', 'Reso');
        $this->assertSame($current->id, $refund->cash_shift_id);
        $this->assertSame(500, $current->expectedCents());
        $this->assertSame(3000, $old->fresh()->expected_cents);
    }

    public function test_checkout_can_receive_money_and_issue_receipt_in_one_action_without_replaying_either(): void
    {
        $order = $this->order();
        $this->receipts();
        $screen = Livewire::test(\App\Livewire\CashDesk\Show::class, ['order' => $order])->call('openPayment')->assertSet('checkoutDocument', 'receipt')->call('fillPayment', 'card')->call('checkout')->assertHasNoErrors()->assertSet('paymentModal', false)->assertSee('documento sandbox emesso');
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame(1, FiscalDocument::count());
        $screen->call('checkout')->assertHasNoErrors();
        Http::assertSentCount(1);
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_combined_checkout_timeout_preserves_payment_and_copy_request_without_sending_a_copy(): void
    {
        $order = $this->order();
        Http::fake(['*' => Http::failedConnection()]);
        $screen = Livewire::test(\App\Livewire\CashDesk\Show::class, ['order' => $order])->call('openPayment')->call('fillPayment', 'card')->set('checkoutEmailCopy', true)->set('checkoutEmail', 'copy@example.test')->set('checkoutAgreement', true)->call('checkout')->assertHasErrors('fiscal')->assertSet('paymentModal', false)->assertSee('non ripetere il pagamento');
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('unknown', FiscalDocument::firstOrFail()->status);
        $this->assertSame(1, \App\Models\FiscalCopyRequest::count());
        $this->assertSame(0, \App\Models\MessageOutbox::where('aggregate_type', 'fiscal_copy')->count());
        $screen->call('checkout');
        $this->assertSame(1, $order->payments()->count());
        Http::assertSentCount(1);
    }

    public function test_combined_checkout_rejects_incomplete_invoice_and_unagreed_email_before_recording_money(): void
    {
        $order = $this->order();
        $screen = Livewire::test(\App\Livewire\CashDesk\Show::class, ['order' => $order])->call('openPayment')->set('checkoutDocument', 'invoice')->call('checkout')->assertHasErrors('name');
        $this->assertSame(0, $order->payments()->count());
        $screen->set('checkoutDocument', 'receipt')->set('checkoutEmailCopy', true)->set('checkoutEmail', 'copy@example.test')->call('checkout')->assertHasErrors('agreed');
        $this->assertSame(0, $order->payments()->count());
        Http::assertNothingSent();
    }

    public function test_invoice_copy_waits_for_final_sdi_result_and_queueing_is_idempotent(): void
    {
        $order = $this->order();
        $this->invoices();
        Livewire::test(\App\Livewire\CashDesk\Show::class, ['order' => $order])->call('openPayment')->call('fillPayment', 'card')->set('checkoutDocument', 'invoice')->set('checkoutBuyer', $this->buyer())->set('checkoutEmailCopy', true)->set('checkoutEmail', 'copy@example.test')->set('checkoutAgreement', true)->call('checkout')->assertHasNoErrors();
        $doc = FiscalDocument::firstOrFail();
        $this->assertSame('pending', $doc->status);
        $copies = app(\App\Services\Fiscal\FiscalCopyService::class);
        $this->assertSame(0, $copies->queueReady());
        $data = $doc->remote_data;
        $data['details']['sdi_status'] = 'DELIVERED';
        $this->resetHttp();
        Http::fake(['test.invoice.openapi.com/IT-invoices/'.$doc->remote_id => Http::response(['data' => $data])]);
        app(FiscalInvoiceService::class)->refresh($doc->id);
        $this->assertSame(1, $copies->queueReady());
        $this->assertSame(0, $copies->queueReady());
        $outbox = \App\Models\MessageOutbox::where('aggregate_type', 'fiscal_copy')->firstOrFail();
        $this->assertSame('copy@example.test', $outbox->payload['deliveries'][0]['recipient']['email']);
        $this->assertStringContainsString('Copia di cortesia', $outbox->payload['deliveries'][0]['subject']);
        \Illuminate\Support\Facades\Mail::fake();
        (new \App\Jobs\Messaging\SendMessageChannelJob($outbox->id, 'email'))->handle(app(\App\Services\Messaging\MessageChannelRegistry::class));
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\CustomerOrderMail::class, fn ($mail) => $mail->hasTo('copy@example.test'));
    }

    public function test_copy_download_requires_unexpired_signature_and_does_not_require_staff_login(): void
    {
        $batch = $this->pay($this->order(), ['card' => 3000]);
        $this->receipts();
        $doc = app(FiscalDocumentService::class)->issueBatch($batch->id, $this->store->id, 'register1');
        $service = app(\App\Services\Fiscal\FiscalCopyService::class);
        $copy = $service->request($batch->id, 'copy@example.test', true);
        $service->queueReady();
        $outbox = \App\Models\MessageOutbox::findOrFail($copy->fresh()->message_outbox_id);
        $url = $outbox->payload['deliveries'][0]['url'];
        $this->resetHttp();
        Http::fake(['test.invoice.openapi.com/IT-e-receipts/'.$doc->remote_id => Http::response('%PDF-1.4 sandbox', 200, ['Content-Type' => 'application/pdf'])]);
        auth()->logout();
        $this->get(route('fiscal-copy.download', ['copy' => $copy->id, 'document' => $doc->id]))->assertForbidden();
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->travel(8)->days();
        $this->get($url)->assertForbidden();
        Http::assertSentCount(1);
    }

    public function test_checkout_customer_lookup_uses_email_or_phone_without_changing_customer_consent(): void
    {
        $customer = \App\Models\Customer::create(['registration_source'=>'test','display_name' => 'Cliente Prova', 'email' => 'copy@example.test', 'phone' => '+393331234567', 'consent_marketing' => false]);
        $screen = Livewire::test(\App\Livewire\CashDesk\Show::class, ['order' => $this->order()])->call('openPayment')->set('checkoutCustomerSearch', '3331234')->assertSee('Cliente Prova')->call('selectCheckoutCustomer', $customer->id)->assertSet('checkoutEmail', 'copy@example.test')->assertSet('checkoutAgreement', false)->assertSet('checkoutCustomerId', $customer->id);
        $this->assertFalse($customer->fresh()->consent_marketing);
        $this->assertSame(0, \App\Models\FiscalCopyRequest::count());
    }
}
