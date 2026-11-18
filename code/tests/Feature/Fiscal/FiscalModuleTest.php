<?php

namespace Tests\Feature\Fiscal;

use App\Livewire\CashDesk\FiscalDocuments;
use App\Livewire\Settings\Fiscal\Settings;
use App\Models\FiscalApiCall;
use App\Models\FiscalCashier;
use App\Models\FiscalDocument;
use App\Models\FiscalOperation;
use App\Models\FiscalProfile;
use App\Models\FiscalStore;
use App\Models\Order;
use App\Models\User;
use App\Services\Fiscal\FiscalDocumentService;
use App\Services\Fiscal\FiscalReceiptBuilder;
use App\Services\Fiscal\FiscalSetupService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FiscalModuleTest extends TestCase
{
    use RefreshDatabase;

    private FiscalProfile $profile;

    private FiscalStore $store;

    private User $admin;

    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['fiscal.openapi.token' => 'sandbox-test-secret', 'fiscal.environment' => 'sandbox']);
        Role::findOrCreate('admin', 'web');
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
        app(Permissions::class)->ensurePermissionsExist();
        $this->profile = FiscalProfile::create(['fiscal_id' => '01114601006', 'name' => 'Ristorante Test', 'email' => 'fiscal@example.test',
            'address' => $this->address(), 'enabled' => true, 'remote_id' => 'configuration1']);
        $this->store = FiscalStore::create(['fiscal_profile_id' => $this->profile->id, 'remote_id' => 'store1', 'label' => 'Sede',
            'status' => 'ONLINE', 'active' => true, 'pem_serial' => 'XM0J-000001', 'operational_status' => 'OPEN',
            'registers' => [['cr_id' => 'register1', 'label' => 'Cassa 1', 'status' => 'ONLINE']]]);
        FiscalCashier::create(['fiscal_profile_id' => $this->profile->id, 'user_id' => $this->admin->id, 'name' => $this->admin->name,
            'email' => $this->admin->email, 'remote_id' => 'cashier1', 'status' => 'active']);
    }

    private function address(): array
    {
        return ['street_address' => 'Via Roma', 'street_number' => '1', 'zip_code' => '00187', 'city' => 'Roma', 'province' => 'RM'];
    }

    private function order(int $cents = 1000, string $method = 'cash', int $tip = 0): Order
    {
        $order = Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 1, 'cover_price_cents' => $cents]);
        app(OrderService::class)->pay($order->id, $method, $cents, tip: $tip);

        return $order->fresh();
    }

    private function service(): FiscalDocumentService
    {
        return app(FiscalDocumentService::class);
    }

    private function issue(Order $order, string $manual = '', int $vouchers = 0): FiscalDocument
    {
        return $this->service()->issue($order->id, $this->store->id, 'register1', $manual, $vouchers);
    }

    private function receipt(array $payload, array $overrides = []): array
    {
        return array_replace(['id' => 'receipt1', 'fiscal_id' => $this->profile->fiscal_id, 'store_id' => 'store1', 'cr_id' => 'register1',
            'cashier_id' => 'cashier1', 'type' => 'sale', 'status' => 'SENT', 'document_number' => '0001-0001', 'document_date' => now()->toIso8601String(),
            'total_amount' => 10, 'items' => $payload['items'] ?? [], 'additional_text' => $payload['additional_text'] ?? '',
            'cash_payment_amount' => $payload['cash_payment_amount'] ?? '10.00', 'electronic_payment_amount' => $payload['electronic_payment_amount'] ?? '0.00',
            'ticket_restaurant_payment_amount' => $payload['ticket_restaurant_payment_amount'] ?? '0.00', 'is_voidable' => true, 'is_returnable' => true], $overrides);
    }

    private function fakeSale(array $overrides = []): void
    {
        Http::fake(function ($request) use ($overrides) {
            $this->sent = $request->data();

            return Http::response(['success' => true, 'data' => $this->receipt($request->data(), $overrides)]);
        });
    }

    private function rejected(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected fiscal validation rejection');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    public function test_issue_is_explicit_excludes_tips_and_prevents_a_second_sale(): void
    {
        $order = $this->order(tip: 200);
        Http::assertNothingSent();
        $this->fakeSale();
        $document = $this->issue($order);
        $this->assertSame('issued', $document->status);
        $this->assertSame(1000, $document->total_cents);
        $this->assertSame('10.00', $this->sent['cash_payment_amount']);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request->header('Content-Type') === ['application/json'] && $request->header('Accept') === ['application/json']);
        $this->assertStringContainsString($document->id, $this->sent['additional_text']);
        $this->rejected(fn () => $this->issue($order));
        Http::assertSentCount(1);
        $this->assertDatabaseCount('fiscal_documents', 1);
        $this->assertDatabaseHas('fiscal_api_calls', ['outcome' => 'succeeded', 'estimated_cost_micros' => 0, 'cost_status' => 'sandbox']);
        $this->assertStringNotContainsString('sandbox-test-secret', DB::table('fiscal_operations')->first()->request);
    }

    public function test_placeholder_and_production_configuration_never_send_requests(): void
    {
        $order = $this->order();
        config(['fiscal.openapi.token' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX']);
        $this->rejected(fn () => $this->issue($order));
        config(['fiscal.openapi.token' => 'real-looking-test-token', 'fiscal.environment' => 'production']);
        $this->rejected(fn () => $this->issue($order));
        Http::assertNothingSent();
        $this->assertDatabaseCount('fiscal_operations', 0);
        $this->assertDatabaseCount('fiscal_api_calls', 0);
    }

    public function test_partial_or_unready_accounts_cannot_issue(): void
    {
        $order = Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 1, 'cover_price_cents' => 1000]);
        app(OrderService::class)->pay($order->id, 'cash', 1000, 500);
        $this->rejected(fn () => $this->issue($order));
        $this->store->update(['status' => 'REGISTERED']);
        $this->rejected(fn () => $this->issue($this->order()));
        Http::assertNothingSent();
        $this->assertDatabaseCount('fiscal_operations', 0);
    }

    public function test_missing_cashier_or_wrong_register_cannot_issue(): void
    {
        $order = $this->order();
        $this->rejected(fn () => $this->service()->issue($order->id, $this->store->id, 'another-register'));
        FiscalCashier::query()->update(['status' => 'inactive']);
        $this->rejected(fn () => $this->issue($order));
        Http::assertNothingSent();
    }

    public function test_timeout_is_durable_and_cannot_be_retried(): void
    {
        Http::fake(function ($request) {
            $this->sent = $request->data();
            throw new ConnectionException('Simulated timeout');
        });
        $order = $this->order();
        $this->rejected(fn () => $this->issue($order));
        $this->assertDatabaseHas('fiscal_documents', ['status' => 'unknown']);
        $this->assertDatabaseHas('fiscal_operations', ['status' => 'unknown', 'active_key' => 'store:'.$this->store->id]);
        $this->assertDatabaseHas('fiscal_api_calls', ['outcome' => 'unknown']);
        $this->rejected(fn () => $this->issue($order));
        $this->assertDatabaseCount('fiscal_api_calls', 1);
    }

    public function test_timeout_can_be_reconciled_by_exact_remote_reference_without_posting_again(): void
    {
        $order = $this->order();
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                $this->sent = $request->data();
                throw new ConnectionException('Simulated timeout');
            }

            return Http::response(['success' => true, 'data' => $this->receipt($this->sent)]);
        });
        $this->rejected(fn () => $this->issue($order));
        $document = FiscalDocument::firstOrFail();
        $this->service()->refresh($document->id, 'receipt1');
        $this->assertSame('issued', $document->fresh()->status);
        $this->assertDatabaseHas('fiscal_operations', ['status' => 'reconciled', 'active_key' => null]);
        $this->assertSame(1, FiscalApiCall::whereIn('action', ['issue', 'configure'])->count());
    }

    public function test_uncertain_sale_cannot_adopt_an_unrelated_remote_document(): void
    {
        $order = $this->order();
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                $this->sent = $request->data();
                throw new ConnectionException;
            }

            return Http::response(['success' => true, 'data' => $this->receipt($this->sent, ['additional_text' => 'Another sale'])]);
        });
        $this->rejected(fn () => $this->issue($order));
        $document = FiscalDocument::firstOrFail();
        $this->rejected(fn () => $this->service()->refresh($document->id, 'receipt1'));
        $this->assertSame('unknown', $document->fresh()->status);
        $this->assertNotNull(FiscalOperation::first()->active_key);
    }

    public function test_http_500_and_malformed_success_never_unlock_writes(): void
    {
        Http::fakeSequence()->push(['success' => false], 500)->push(['data' => []], 200);
        $this->rejected(fn () => $this->issue($this->order()));
        $this->assertSame('unknown', FiscalOperation::first()->status);
        // A separate store has an independent mutation lock.
        $this->store = FiscalStore::create(['fiscal_profile_id' => $this->profile->id, 'remote_id' => 'store2', 'label' => 'Seconda', 'status' => 'ONLINE',
            'active' => true, 'pem_serial' => 'XM-2', 'registers' => [['cr_id' => 'register1', 'status' => 'ONLINE']]]);
        $this->rejected(fn () => $this->issue($this->order()));
        $this->assertSame(2, FiscalOperation::where('status', 'unknown')->count());
        $this->assertSame(2, FiscalApiCall::where('outcome', 'unknown')->count());
    }

    public function test_definite_rejection_can_be_corrected_and_retried_on_the_same_local_sale(): void
    {
        Http::fake(function ($request) {
            if (FiscalApiCall::count() === 1) {
                return Http::response(['success' => false], 422);
            }

            return Http::response(['success' => true, 'data' => $this->receipt($request->data())]);
        });
        $order = $this->order();
        $this->rejected(fn () => $this->issue($order));
        $this->assertDatabaseHas('fiscal_documents', ['status' => 'rejected']);
        $this->issue($order);
        $this->assertDatabaseCount('fiscal_documents', 1);
        $this->assertDatabaseCount('fiscal_api_calls', 2);
        $this->assertDatabaseHas('fiscal_documents', ['status' => 'issued']);
    }

    public function test_inconsistent_provider_total_stays_locked_with_a_durable_response(): void
    {
        $this->fakeSale(['total_amount' => 99]);
        $this->rejected(fn () => $this->issue($this->order()));
        $operation = FiscalOperation::firstOrFail();
        $this->assertSame('unknown', $operation->status);
        $this->assertSame(99, $operation->response['total_amount']);
        $this->assertNotNull($operation->active_key);
    }

    public function test_ready_is_pending_until_a_document_number_is_confirmed(): void
    {
        $this->fakeSale(['status' => 'ready', 'document_number' => null]);
        $document = $this->issue($this->order());
        $this->assertSame('pending', $document->status);
        $this->rejected(fn () => $this->service()->close($this->store->id));
        Http::assertSentCount(1);
    }

    public function test_manual_methods_voucher_quantity_and_cash_rounding_are_not_guessed(): void
    {
        $this->rejected(fn () => $this->issue($this->order(method: 'manual')));
        $this->rejected(fn () => $this->issue($this->order(method: 'voucher')));
        $this->rejected(fn () => $this->issue($this->order(1001)));
        Http::assertNothingSent();
        $this->fakeSale();
        $this->issue($this->order(method: 'voucher'), vouchers: 2);
        $this->assertSame(2, $this->sent['ticket_restaurant_quantity']);
        $this->assertSame('10.00', $this->sent['ticket_restaurant_payment_amount']);
    }

    public function test_discounted_mixed_vat_lines_reconcile_to_frozen_totals(): void
    {
        $order = Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 0]);
        $course = $order->courses()->create(['number' => 1]);
        foreach ([10 => 333, 22 => 667] as $vat => $price) {
            $course->items()->create(['product_name' => 'Piatto', 'menu_name' => 'Menu', 'quantity' => 3, 'unit_price_cents' => $price, 'base_price_cents' => $price, 'vat' => $vat]);
        }
        app(OrderService::class)->discount($order->id, 1234, 'Promozione', 3000, true);
        $order->refresh();
        app(OrderService::class)->pay($order->id, 'card', $order->totalCents());
        $snapshot = app(FiscalReceiptBuilder::class)->build($order->fresh(), $this->profile, ['local_id' => 'test'], '', 0);
        $this->assertSame(2630, $snapshot['total_cents']);
        $this->assertSame(2630, array_sum(array_map(fn ($row) => $row['quantity'] * $row['unit_cents'], $snapshot['items'])));
        foreach ($order->fresh()->cash_snapshot['taxes'] as $tax) {
            $sum = collect($snapshot['items'])->where('vat_code', number_format($tax['rate'], 2, '.', ''))->sum(fn ($row) => $row['quantity'] * $row['unit_cents']);
            $this->assertSame($tax['gross_cents'], $sum);
        }
    }

    public function test_zero_vat_requires_explicit_nature(): void
    {
        $order = $this->order();
        $order->update(['auxiliary_vat' => 0, 'cash_snapshot' => ['gross_cents' => 1000, 'discount_cents' => 0, 'total_cents' => 1000,
            'taxes' => [['rate' => 0, 'gross_cents' => 1000, 'net_cents' => 1000, 'vat_cents' => 0]]]]);
        $this->rejected(fn () => $this->issue($order));
        $this->profile->update(['zero_vat_code' => 'N2']);
        $this->fakeSale();
        $this->issue($order);
        $this->assertSame('N2', $this->sent['items'][0]['vat_rate_code']);
    }

    public function test_return_uses_patch_and_does_not_refund_or_change_order_payments(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                $this->sent = $request->data();

                return Http::response(['success' => true, 'data' => $this->receipt($this->sent)]);
            }
            if (str_ends_with($request->url(), '/returnable-items')) {
                return Http::response(['success' => true, 'data' => [['id' => 1, 'quantity' => '1.00', 'returned_quantity' => '0.00', 'description' => 'Coperti', 'unit_price' => '10.00']]]);
            }
            if ($request->method() === 'PATCH') {
                return Http::response(['success' => true, 'data' => $this->receipt($this->sent, ['id' => 'return1', 'type' => 'return', 'parent_receipt_id' => 'receipt1', 'document_number' => '0001-0002', 'items' => $request->data()['items']])]);
            }

            return Http::response(['success' => true, 'data' => $this->receipt($this->sent)]);
        });
        $order = $this->order();
        $sale = $this->issue($order);
        $return = $this->service()->reverse($sale->id, 'return', [['id' => 1, 'quantity' => 1]]);
        $this->assertSame('issued', $return->status);
        $this->assertSame($sale->id, $return->parent_id);
        $this->assertSame(1000, (int) $order->payments()->sum('amount_cents'));
        $this->assertSame('paid', $order->fresh()->status);
        $this->rejected(fn () => $this->service()->reverse($sale->id, 'return', [['id' => 1, 'quantity' => 1]]));
        $this->assertSame(1, Http::recorded(fn ($request) => $request->method() === 'PATCH')->count());
    }

    public function test_void_creates_a_linked_document_and_cannot_be_repeated(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                $this->sent = $request->data();
            }
            $override = $request->method() === 'DELETE' ? ['id' => 'void1', 'type' => 'void', 'parent_receipt_id' => 'receipt1', 'document_number' => '0001-0002'] : [];

            return Http::response(['success' => true, 'data' => $this->receipt($this->sent, $override)]);
        });
        $sale = $this->issue($this->order());
        $void = $this->service()->reverse($sale->id, 'void');
        $this->assertSame('voided', $sale->fresh()->status);
        $this->assertSame('issued', $void->status);
        $this->rejected(fn () => $this->service()->reverse($sale->id, 'void'));
        $this->assertSame(1, Http::recorded(fn ($request) => $request->method() === 'DELETE')->count());
        $this->service()->refresh($sale->id);
        $this->assertSame('voided', $sale->fresh()->status);
    }

    public function test_fiscal_close_is_separate_and_no_duplicate_close_is_sent(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'data' => null, 'event_id' => 'event1'])]);
        $this->service()->close($this->store->id);
        $this->assertNotNull($this->store->fresh()->last_closed_at);
        $this->assertSame('CLOSED', $this->store->fresh()->operational_status);
        $this->assertDatabaseCount('cash_shifts', 0);
        $this->rejected(fn () => $this->service()->close($this->store->id));
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request->url() === 'https://test.invoice.openapi.com/IT-store/store1/close');
    }

    public function test_verifying_an_old_receipt_does_not_reopen_a_closed_session(): void
    {
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/close')) {
                return Http::response(['success' => true, 'data' => null, 'event_id' => 'event1']);
            }
            if ($request->method() === 'POST') {
                $this->sent = $request->data();
            }

            return Http::response(['success' => true, 'data' => $this->receipt($this->sent)]);
        });
        $sale = $this->issue($this->order());
        $this->service()->close($this->store->id);
        $this->service()->refresh($sale->id);
        $this->assertSame('CLOSED', $this->store->fresh()->operational_status);
        $this->rejected(fn () => $this->service()->close($this->store->id));
        $this->assertSame(1, FiscalApiCall::where('action', 'close')->count());
    }

    public function test_local_profile_validation_and_remote_identity_are_enforced(): void
    {
        Livewire::test(Settings::class)->set('company.address.zip_code', '123')->call('save')->assertHasErrors('address.zip_code');
        Livewire::test(Settings::class)->set('company.fiscal_id', '12345678901')->call('save')->assertHasErrors('fiscal');
        Livewire::test(Settings::class)->set('company.zero_vat_code', 'N2')->call('save')->assertHasNoErrors();
        $this->assertSame('N2', $this->profile->fresh()->zero_vat_code);
        Http::assertNothingSent();
    }

    public function test_uncertain_operations_block_local_company_edits(): void
    {
        FiscalOperation::create(['resource_key' => 'profile:sandbox', 'active_key' => 'profile:sandbox', 'action' => 'configure', 'status' => 'unknown', 'request' => []]);
        $this->rejected(fn () => app(FiscalSetupService::class)->save($this->profile->only(['fiscal_id', 'name', 'email', 'address', 'zero_vat_code'])));
        Http::assertNothingSent();
    }

    public function test_uncertain_close_is_recovered_from_a_confirmed_event(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                throw new ConnectionException;
            }

            return Http::response(['success' => true, 'data' => [['id' => 'event1', 'store_id' => 'store1', 'fiscal_id' => $this->profile->fiscal_id, 'action' => 'close', 'created_at' => now()->toIso8601String()]]]);
        });
        $this->rejected(fn () => $this->service()->close($this->store->id));
        $operation = FiscalOperation::firstOrFail();
        $this->service()->reconcileClose($operation->id, 'event1');
        $this->assertSame('reconciled', $operation->fresh()->status);
        $this->assertNull($operation->fresh()->active_key);
        $this->assertNotNull($this->store->fresh()->last_closed_at);
    }

    public function test_pdf_download_uses_the_document_endpoint_and_is_audited(): void
    {
        Http::fake(fn ($request) => $request->method() === 'POST'
            ? Http::response(['success' => true, 'data' => $this->receipt($request->data())])
            : Http::response('%PDF-1.7 test', 200, ['Content-Type' => 'application/pdf']));
        $document = $this->issue($this->order());
        $this->get(route('cash-desk.fiscal-pdf', $document->id))->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertSee('%PDF-1.7 test');
        $this->assertDatabaseHas('fiscal_api_calls', ['action' => 'receipt_pdf', 'outcome' => 'succeeded']);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->header('Content-Type') === ['application/pdf'] && $request->header('Accept') === ['application/pdf']);
    }

    public function test_json_instead_of_pdf_is_explained_without_changing_the_issued_document(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                $this->sent = $request->data();
            }

            return Http::response(['success' => true, 'data' => $this->receipt($this->sent)]);
        });
        $document = $this->issue($this->order());
        $this->get(route('cash-desk.fiscal-pdf', $document->id))->assertRedirect()->assertSessionHasErrors('fiscal');
        $this->assertSame('issued', $document->fresh()->status);
        $this->assertDatabaseHas('fiscal_api_calls', ['action' => 'receipt_pdf', 'outcome' => 'invalid_format', 'error_code' => 'PDF_FORMAT']);
        $this->assertSame(0, FiscalOperation::whereNotNull('active_key')->count());
    }

    public function test_settings_and_document_pages_render_without_network_calls(): void
    {
        $order = $this->order();
        $this->get(route('settings.fiscal'))->assertOk()->assertSee('Configurazione fiscale');
        $this->get(route('cash-desk.fiscal', $order))->assertOk()->assertSee('Documenti commerciali');
        $this->get(route('cash-desk.shifts'))->assertOk()->assertSee('Chiusure fiscali');
        Http::assertNothingSent();
    }

    public function test_settings_require_admin_and_document_actions_require_cash_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Livewire::test(Settings::class)->assertForbidden();
        Livewire::test(FiscalDocuments::class, ['order' => $this->orderForPermissionTest()])->assertForbidden();
        $this->get(route('settings.fiscal'))->assertForbidden();
    }

    private function orderForPermissionTest(): Order
    {
        return Order::create(['service_date' => today()]);
    }

    public function test_document_actions_cannot_target_another_order_from_livewire(): void
    {
        $this->fakeSale();
        $document = $this->issue($this->order());
        Http::assertSentCount(1);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(FiscalDocuments::class, ['order' => $this->order()])->call('voidDocument', $document->id);
    }

    public function test_connection_check_verifies_the_token_without_creating_configuration(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'data' => []])]);
        Livewire::test(Settings::class)->call('checkConnection')->assertHasNoErrors()->assertSee('Token sandbox accettato');
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->url() === 'https://test.invoice.openapi.com/IT-configurations');
        $this->assertDatabaseHas('fiscal_api_calls', ['action' => 'connection', 'outcome' => 'succeeded']);
        $this->assertDatabaseCount('fiscal_operations', 0);
        $this->assertDatabaseCount('fiscal_stores', 1);
    }

    public function test_unregistered_configuration_is_explained_separately_from_authentication(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'message' => 'Fiscal ID not found or not registered', 'error' => 424], 404)]);
        Livewire::test(Settings::class)->call('sync')->assertHasErrors('fiscal')->assertSee('Azienda non trovata o non accessibile');
        $this->assertDatabaseHas('fiscal_api_calls', ['http_status' => 404, 'error_code' => '424', 'error_message' => 'Fiscal ID not found or not registered']);
    }

    public function test_authentication_errors_identify_the_request_without_exposing_the_token(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'message' => 'Invalid token: sandbox-test-secret', 'error' => 401], 401)]);
        Livewire::test(Settings::class)->call('checkConnection')->assertHasErrors('fiscal')->assertSee('Autenticazione non accettata per GET /IT-configurations')
            ->assertDontSee('sandbox-test-secret');
        $call = FiscalApiCall::firstOrFail();
        $this->assertSame('401', $call->error_code);
        $this->assertStringNotContainsString('sandbox-test-secret', $call->error_message);
        $this->assertStringContainsString('[REDACTED]', $call->error_message);
    }

    public function test_validation_details_are_sanitized_and_long_messages_do_not_leave_a_write_locked(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'error' => 422, 'message' => str_repeat('Validation failed. ', 80), 'errors' => [
            'merchant_address.zip_code' => ['ZIP code and province mismatch.'], 'authorization' => ['Bearer sandbox-test-secret'],
        ]], 422)]);
        $this->rejected(fn () => app(FiscalSetupService::class)->provisionStore('Nuova sede', 'Cassa 1', $this->address()));
        $operation = FiscalOperation::firstOrFail();
        $this->assertSame('rejected', $operation->status);
        $this->assertNull($operation->active_key);
        $this->assertLessThanOrEqual(255, mb_strlen($operation->error));
        $call = FiscalApiCall::firstOrFail();
        $this->assertSame('422', $call->error_code);
        $this->assertLessThanOrEqual(1000, mb_strlen($call->error_message));
        $this->assertStringNotContainsString('sandbox-test-secret', $call->error_message);
    }

    public function test_duplicate_fiscal_id_is_distinguished_from_invalid_credentials(): void
    {
        $this->profile->update(['remote_id' => null]);
        Http::fake(['*' => Http::response(['success' => false, 'error' => 112, 'message' => 'This fiscal_id already exists'], 422)]);
        Livewire::test(Settings::class)->call('provision')->assertHasErrors('fiscal')->assertSee('partita IVA di test diversa')->assertSee('This fiscal_id already exists');
        $this->assertDatabaseHas('fiscal_api_calls', ['action' => 'configure', 'http_status' => 422, 'error_code' => '112']);
        $this->assertNull(FiscalOperation::first()->active_key);
        $this->assertNull($this->profile->fresh()->remote_id);
    }

    public function test_field_validation_details_survive_without_retaining_response_data_or_credentials(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'error' => 422, 'message' => 'Invalid merchant address', 'errors' => [
            'merchant_address.zip_code' => ['ZIP code and province mismatch.'], 'password' => ['never-show-this-password'],
        ], 'data' => ['token' => 'never-show-this-token']], 422)]);
        $this->rejected(fn () => app(FiscalSetupService::class)->provisionStore('Nuova sede', 'Cassa 1', $this->address()));
        $message = FiscalApiCall::firstOrFail()->error_message;
        $this->assertStringContainsString('merchant_address.zip_code: ZIP code and province mismatch.', $message);
        $this->assertStringNotContainsString('never-show', $message);
    }

    public function test_onboarding_uses_nested_stores_and_waits_for_online_status(): void
    {
        $this->profile->update(['remote_id' => null, 'enabled' => false]);
        FiscalStore::query()->delete();
        Http::fake(function ($request) {
            $this->sent = $request->data();
            $online = $request->method() === 'GET';

            return Http::response(['success' => true, 'data' => ['id' => 'configuration1', 'fiscal_id' => $this->profile->fiscal_id, 'e_receipts' => true,
                'stores' => [['store_id' => 'store1', 'label' => 'Nuova sede', 'address' => $this->address(), 'active' => true,
                    'status' => $online ? 'ONLINE' : 'REGISTERED', 'pem_serial' => $online ? 'XM-1' : null,
                    'cash_registers' => [['cr_id' => 'register1', 'label' => 'Cassa 1', 'status' => $online ? 'ONLINE' : 'PENDING']]]]]]);
        });
        app(FiscalSetupService::class)->provisionStore('Nuova sede', 'Cassa 1', $this->address());
        $this->assertTrue($this->sent['e_receipts']);
        $this->assertFalse($this->sent['receipts']);
        $this->assertFalse($this->sent['supplier_invoice']);
        $this->assertSame('Nuova sede', $this->sent['stores'][0]['label']);
        $this->assertSame('REGISTERED', FiscalStore::first()->status);
        app(FiscalSetupService::class)->sync();
        $this->assertSame('ONLINE', FiscalStore::first()->status);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request->url() === 'https://test.invoice.openapi.com/IT-configurations');
    }

    public function test_uncertain_provisioning_is_reconciled_without_creating_another_store(): void
    {
        $this->profile->update(['remote_id' => null, 'enabled' => false]);
        FiscalStore::query()->delete();
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                throw new ConnectionException;
            }

            return Http::response(['success' => true, 'data' => ['id' => 'configuration1', 'fiscal_id' => $this->profile->fiscal_id, 'e_receipts' => true,
                'stores' => [['store_id' => 'store1', 'label' => 'Sede', 'address' => $this->address(), 'status' => 'REGISTERED', 'cash_registers' => [['cr_id' => 'register1', 'label' => 'Cassa 1']]]]]]);
        });
        $this->rejected(fn () => app(FiscalSetupService::class)->provisionStore('Sede', 'Cassa 1', $this->address()));
        app(FiscalSetupService::class)->sync();
        $this->assertNull(FiscalOperation::first()->active_key);
        $this->assertSame('reconciled', FiscalOperation::first()->status);
        $this->assertSame(1, FiscalApiCall::whereIn('action', ['issue', 'configure'])->count());
    }

    public function test_cashier_picker_includes_admin_and_cash_staff_but_excludes_unpermitted_users(): void
    {
        $plain = User::factory()->create(['name' => 'Utente senza abilitazione fiscale']);
        $cashStaff = User::factory()->create(['name' => 'Operatore cassa abilitato']);
        $cashStaff->givePermissionTo(Permissions::CASH_REGISTER);
        Livewire::test(Settings::class)->assertSee('Admin (cassa abilitata)')->assertSee($cashStaff->name)->assertDontSee($plain->name)->assertSee('Email cassiere Openapi');
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($plain->id, 'new-cashier@example.test'));
        Http::assertNothingSent();
    }

    public function test_cashier_email_conflict_is_a_definite_rejection_with_no_retry(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'pending']);
        Http::fake(['*' => Http::response(['success' => false, 'error' => 652, 'message' => 'A user with this email already exists in the system'], 409)]);
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id));
        $this->assertDatabaseHas('fiscal_operations', ['action' => 'create_cashier', 'status' => 'rejected', 'active_key' => null]);
        $this->assertDatabaseHas('fiscal_api_calls', ['http_status' => 409, 'error_code' => '652', 'outcome' => 'rejected']);
        $this->assertSame('email_conflict', FiscalCashier::first()->status);
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id));
        Http::assertSentCount(1);
    }

    public function test_admin_can_register_with_a_separate_fiscal_email_without_changing_login(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'pending']);
        $loginEmail = $this->admin->email;
        Http::fake(fn ($request) => Http::response(['success' => true, 'data' => $request->data() + ['id' => 'cashier-new', 'status' => 'active']], 201));
        Livewire::test(Settings::class)->set('userId', $this->admin->id)->set('cashierEmail', 'cashier-sandbox@example.test')->call('createCashier')->assertHasNoErrors();
        $this->assertTrue(app(Permissions::class)->can($this->admin, Permissions::CASH_REGISTER));
        $this->assertSame($loginEmail, $this->admin->fresh()->email);
        $this->assertDatabaseHas('fiscal_cashiers', ['user_id' => $this->admin->id, 'remote_id' => 'cashier-new', 'email' => 'cashier-sandbox@example.test', 'status' => 'active']);
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request->data()['email'] === 'cashier-sandbox@example.test');
    }

    public function test_changing_fiscal_email_after_a_known_conflict_is_an_explicit_new_attempt(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'pending']);
        Http::fake(fn ($request) => FiscalApiCall::where('action', 'create_cashier')->count() === 1
            ? Http::response(['success' => false, 'error' => 652, 'message' => 'Email already exists'], 409)
            : Http::response(['success' => true, 'data' => $request->data() + ['id' => 'cashier-new', 'status' => 'active']], 201));
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id));
        app(FiscalSetupService::class)->createCashier($this->admin->id, 'another-cashier@example.test');
        $this->assertDatabaseCount('fiscal_operations', 2);
        $this->assertSame(1, FiscalOperation::where('status', 'rejected')->count());
        $this->assertSame(1, FiscalOperation::where('status', 'succeeded')->count());
        $this->assertSame('another-cashier@example.test', FiscalCashier::first()->email);
        Http::assertSentCount(2);
    }

    public function test_uncertain_cashier_creation_cannot_change_email_to_bypass_the_lock(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'pending']);
        $email = FiscalCashier::first()->email;
        Http::fake(fn () => throw new ConnectionException('Timeout'));
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id));
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id, 'new-email@example.test'));
        $this->assertSame($email, FiscalCashier::first()->email);
        $this->assertSame('unknown', FiscalOperation::first()->status);
        $this->assertNotNull(FiscalOperation::first()->active_key);
        $this->assertDatabaseCount('fiscal_api_calls', 1);
    }

    public function test_other_cashier_conflicts_and_receipt_409_652_remain_uncertain(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'pending']);
        Http::fake(fn () => Http::response(['success' => false, 'error' => 651], 409));
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id));
        $this->assertSame('unknown', FiscalOperation::first()->status);
        $this->assertSame(0, app(FiscalSetupService::class)->resolveCashierEmailConflicts());
        $this->assertNotNull(FiscalOperation::first()->fresh()->active_key);
    }

    public function test_receipt_conflict_code_652_is_not_mistaken_for_a_cashier_rejection(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'error' => 652], 409)]);
        $this->rejected(fn () => $this->issue($this->order()));
        $this->assertDatabaseHas('fiscal_operations', ['action' => 'issue', 'status' => 'unknown']);
        $this->assertNotNull(FiscalOperation::first()->active_key);
        $this->assertSame(0, app(FiscalSetupService::class)->resolveCashierEmailConflicts());
    }

    public function test_historical_email_conflicts_are_unlocked_only_from_recorded_response_evidence(): void
    {
        $cashier = FiscalCashier::first();
        $cashier->update(['remote_id' => null, 'status' => 'pending']);
        $resource = 'cashier:'.$this->profile->id.':'.$this->admin->id;
        $operation = FiscalOperation::create(['action' => 'create_cashier', 'status' => 'unknown', 'resource_key' => $resource, 'active_key' => $resource,
            'request' => ['fiscal_id' => $this->profile->fiscal_id, 'email' => $cashier->email, 'name' => $cashier->name]]);
        $this->assertSame(0, app(FiscalSetupService::class)->resolveCashierEmailConflicts());
        $this->assertNotNull($operation->fresh()->active_key);
        FiscalApiCall::create(['fiscal_operation_id' => $operation->id, 'provider' => 'openapi', 'environment' => 'sandbox', 'action' => 'create_cashier',
            'request_hash' => str_repeat('a', 64), 'http_status' => 409, 'outcome' => 'unknown', 'error_code' => '652', 'cost_status' => 'sandbox']);
        $this->assertSame(1, app(FiscalSetupService::class)->resolveCashierEmailConflicts());
        $this->assertSame('rejected', $operation->fresh()->status);
        $this->assertNull($operation->fresh()->active_key);
        $this->assertSame('email_conflict', $cashier->fresh()->status);
        $this->assertDatabaseHas('fiscal_api_calls', ['outcome' => 'rejected', 'error_code' => '652']);
        $this->assertSame(0, app(FiscalSetupService::class)->resolveCashierEmailConflicts());
        Http::assertNothingSent();
    }

    public function test_sync_verifies_fiscal_identity_from_detail_when_list_omits_it(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'unknown']);
        Http::fake(fn ($request) => Http::response(['success' => true, 'data' => str_ends_with($request->url(), '/cashier1')
            ? ['id' => 'cashier1', 'email' => $this->admin->email, 'name' => $this->admin->name, 'status' => 'active', 'fiscal_id' => $this->profile->fiscal_id]
            : [['id' => 'cashier1', 'email' => $this->admin->email, 'name' => $this->admin->name, 'status' => 'active']]]));
        $result = app(FiscalSetupService::class)->syncCashiers();
        $this->assertSame(1, $result['linked']);
        $this->assertSame('cashier1', FiscalCashier::first()->remote_id);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && $request->url() === 'https://test.invoice.openapi.com/IT-cashiers/cashier1');
    }

    public function test_sync_does_not_adopt_a_cashier_from_another_fiscal_configuration(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'unknown']);
        Http::fake(fn ($request) => Http::response(['success' => true, 'data' => str_ends_with($request->url(), '/cashier1')
            ? ['id' => 'cashier1', 'email' => $this->admin->email, 'name' => $this->admin->name, 'status' => 'active', 'fiscal_id' => '11122211133']
            : [['id' => 'cashier1', 'email' => $this->admin->email, 'name' => $this->admin->name, 'status' => 'active']]]));
        $result = app(FiscalSetupService::class)->syncCashiers();
        $this->assertSame(0, $result['linked']);
        $this->assertNull(FiscalCashier::first()->remote_id);
        Http::assertSentCount(2);
    }

    public function test_a_fresh_inflight_operation_is_not_reconciled_while_its_post_is_running(): void
    {
        $cashier = FiscalCashier::first();
        $cashier->update(['remote_id' => null, 'status' => 'pending']);
        $resource = 'cashier:'.$this->profile->id.':'.$this->admin->id;
        $operation = FiscalOperation::create(['action' => 'create_cashier', 'status' => 'in_flight', 'resource_key' => $resource, 'active_key' => $resource,
            'request' => ['fiscal_id' => $this->profile->fiscal_id, 'email' => $cashier->email, 'name' => $cashier->name]]);
        Http::fake(['*' => Http::response(['success' => true, 'data' => [['id' => 'cashier1', 'fiscal_id' => $this->profile->fiscal_id,
            'email' => $cashier->email, 'name' => $cashier->name, 'status' => 'active']]])]);
        app(FiscalSetupService::class)->syncCashiers();
        $this->assertNull($cashier->fresh()->remote_id);
        $this->assertNotNull($operation->fresh()->active_key);
    }

    public function test_registered_cashier_email_cannot_be_changed_and_local_aliases_are_unique(): void
    {
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id, 'new-email@example.test'));
        $this->assertSame($this->admin->email, FiscalCashier::first()->email);
        $second = User::factory()->create();
        $second->assignRole('admin');
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($second->id, strtoupper($this->admin->email)));
        Http::assertNothingSent();
    }

    public function test_pending_cashier_is_mapped_to_exact_fiscal_identity_during_sync(): void
    {
        FiscalCashier::query()->update(['remote_id' => null, 'status' => 'pending']);
        Http::fake(function ($request) {
            if ($request->method() === 'POST') {
                throw new ConnectionException;
            }

            return Http::response(['success' => true, 'data' => [['id' => 'cashier1', 'fiscal_id' => $this->profile->fiscal_id, 'name' => $this->admin->name, 'email' => $this->admin->email, 'status' => 'active']]]);
        });
        $this->rejected(fn () => app(FiscalSetupService::class)->createCashier($this->admin->id));
        app(FiscalSetupService::class)->syncCashiers();
        $this->assertSame('cashier1', FiscalCashier::first()->remote_id);
        $this->assertSame('active', FiscalCashier::first()->status);
        $this->assertNull(FiscalOperation::first()->active_key);
    }
}
