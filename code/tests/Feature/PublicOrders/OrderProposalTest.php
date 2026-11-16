<?php

namespace Tests\Feature\PublicOrders;

use App\Livewire\CustomerOrders\Index;
use App\Livewire\CustomerOrders\ProposalEditor;
use App\Mail\CustomerOrderMail;
use App\Models\Customer;
use App\Models\CustomerOrderClosure;
use App\Models\CustomerOrderProposal;
use App\Models\CustomerOrderSchedule;
use App\Models\Department;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\MessageOutbox;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\OrderPricingService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use App\Services\PublicOrders\CustomerOrderWorkflow;
use App\Services\PublicOrders\OrderingSettings;
use App\Services\PublicOrders\OrderProposalService;
use App\Services\PublicOrders\OrderSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderProposalTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    private User $admin;

    private $category;

    private DigitalMenuProduct $product;

    private DigitalMenuProduct $replacement;

    private DigitalMenuIngredient $extra;

    private OrderProposalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 16)->setTime(10, 0));
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('operator', 'web');
        app(Permissions::class)->ensurePermissionsExist();
        $this->admin = User::factory()->create(['enabled' => true]);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
        $customer = Customer::create(['firstname' => 'Mario', 'display_name' => 'Mario Rossi', 'email' => 'mario@example.test', 'phone' => '+393401005264', 'registration_source' => 'customer_order', 'lang' => 'it']);
        $menu = DigitalMenu::create(['name' => ['it' => 'Menu cena'], 'slug' => 'proposte-cena', 'enabled_languages' => ['it'], 'is_published' => true, 'is_visible' => true]);
        $this->category = $menu->categories()->create(['name' => ['it' => 'Pizze'], 'is_enabled' => true]);
        $department = Department::create(['name' => 'Pizzeria', 'production' => true]);
        $this->product = DigitalMenuProduct::create(['name' => ['it' => 'Margherita'], 'price' => '9.00', 'vat' => 10, 'is_active' => true, 'department_id' => $department->id]);
        $this->replacement = DigitalMenuProduct::create(['name' => ['it' => 'Diavola'], 'price' => '14.00', 'vat' => 10, 'is_active' => true, 'department_id' => $department->id]);
        $this->category->products()->attach($this->product, ['menu_price' => '10.00', 'is_visible' => true]);
        $this->category->products()->attach($this->replacement, ['is_visible' => true]);
        $this->extra = DigitalMenuIngredient::create(['name' => ['it' => 'Burrata'], 'add_price' => '2.00']);
        $this->order = Order::create(['source' => 'customer', 'status' => 'open', 'reference' => 'OC-PROVA', 'fulfillment' => 'pickup', 'fulfillment_status' => 'received',
            'customer_id' => $customer->id, 'customer_name' => 'Mario Rossi', 'customer_email' => $customer->email, 'customer_phone' => $customer->phone,
            'service_date' => '2026-11-16', 'requested_at' => '2026-11-16 20:00', 'covers' => 0, 'payment_method' => 'cash', 'customer_notes' => 'Citofono Mario']);
        $course = $this->order->courses()->create(['number' => 1, 'customer_id' => $customer->id, 'customer_name' => 'Mario Rossi', 'submission_key' => 'original-order']);
        app(OrderPricingService::class)->append($course, app(OrderPricingService::class)->quote(['category_id' => $this->category->id, 'product_id' => $this->product->id, 'quantity' => 1, 'notes' => 'Ben cotta', 'added' => [], 'removed' => []], true, 'pickup', 'it'));
        app(OrderingSettings::class)->save(array_replace(app(OrderingSettings::class)->get(), ['enabled' => true, 'delivery_enabled' => true, 'delivery_fee_cents' => 300, 'delivery_minimum_cents' => 1000]));
        CustomerOrderSchedule::create(['weekday' => 1, 'opens_at' => '18:00', 'closes_at' => '23:00', 'pickup' => true, 'delivery' => true]);
        $this->service = app(OrderProposalService::class);
    }

    private function details(array $changes = []): array
    {
        return array_replace(['date' => '2026-11-16', 'time' => '20:30', 'notes' => 'Chiamare all’arrivo'], $changes);
    }

    private function create(?array $lines = null, array $details = []): CustomerOrderProposal
    {
        $lines ??= $this->service->initialLines($this->order);
        $details = $this->details($details);
        $quote = $this->service->quote($this->order, $lines, $details);

        return $this->service->create($this->order->id, $lines, $details, 'Proponiamo un nuovo orario e queste modifiche.', $quote['total'], $this->service->fingerprint($this->order));
    }

    private function token(CustomerOrderProposal $proposal): string
    {
        $delivery = MessageOutbox::where('deduplication_key', 'proposal:'.$proposal->id.':sent')->firstOrFail()->payload['deliveries'][0];

        return basename(parse_url($delivery['accept_url'], PHP_URL_PATH));
    }

    private function newLine(array $changes = []): array
    {
        return array_replace(['original_item_id' => null, 'category_id' => $this->category->id, 'product_id' => $this->replacement->id, 'quantity' => 1, 'notes' => 'Poco piccante', 'added' => [], 'removed' => []], $changes);
    }

    public function test_proposal_is_separate_from_live_order_and_email_has_both_complete_summaries(): void
    {
        $proposal = $this->create([$this->newLine(['added' => [$this->extra->id]])]);
        $this->assertSame('20:00', $this->order->fresh()->requested_at->format('H:i'));
        $this->assertSame(1000, $this->order->totalCents());
        $this->assertSame('Margherita', $this->order->items()->first()->product_name);
        $delivery = MessageOutbox::where('deduplication_key', 'proposal:'.$proposal->id.':sent')->first()->payload['deliveries'][0];
        $html = (new CustomerOrderMail($delivery))->render();
        foreach (['Accetta proposta', 'Rifiuta proposta', 'Ordine originale', 'Ordine proposto', 'Margherita', 'Diavola', 'Burrata', 'Poco piccante', 'Mario Rossi', '20:30', '16,00', 'Contanti', 'Non pagato', 'Chiamare all’arrivo'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertNotSame($this->token($proposal), $proposal->token_hash);
    }

    public function test_email_get_links_do_not_apply_the_proposal_and_post_accepts_it(): void
    {
        $proposal = $this->create();
        $token = $this->token($proposal);
        $url = route('public-orders.proposal', ['token' => $token, 'choice' => 'accept']);
        $this->app['auth']->forgetGuards();
        $response = $this->get($url)->assertOk()->assertSee('Conferma accettazione');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('pending', $proposal->fresh()->status);
        $this->assertSame('20:00', $this->order->fresh()->requested_at->format('H:i'));
        $this->post(route('public-orders.proposal.respond', $token), ['choice' => 'accept'])->assertRedirect();
        $this->assertSame('accepted', $proposal->fresh()->status);
        $this->assertSame('20:30', $this->order->fresh()->requested_at->format('H:i'));
    }

    public function test_customer_acceptance_applies_products_prices_notes_and_notifies_both_sides(): void
    {
        $proposal = $this->create([$this->newLine(['quantity' => 2, 'added' => [$this->extra->id]])]);
        $this->replacement->update(['price' => '99.00']);
        $this->service->respond($this->token($proposal), 'accept');
        $order = $this->order->fresh();
        $item = $order->items()->first();
        $this->assertSame('accepted', $order->fulfillment_status);
        $this->assertSame(3200, $order->totalCents());
        $this->assertSame('Diavola', $item->product_name);
        $this->assertSame('Poco piccante', $item->notes);
        $this->assertSame('Burrata', $item->modifiers()->first()->ingredient_name);
        $this->assertSame('Chiamare all’arrivo', $order->customer_notes);
        $this->assertDatabaseHas('message_outboxes', ['deduplication_key' => 'proposal:'.$proposal->id.':accepted:customer']);
        $this->assertDatabaseHas('message_outboxes', ['deduplication_key' => 'proposal:'.$proposal->id.':accepted:staff:'.$this->admin->id]);
        $this->assertSame(0, \App\Models\ProductionTicket::count());
        app(CustomerOrderWorkflow::class)->transition($order->id, 'prepare', 'accepted');
        $this->assertSame(1, \App\Models\ProductionTicket::count());
    }

    public function test_rejection_preserves_original_order_and_status(): void
    {
        $this->order->update(['fulfillment_status' => 'accepted']);
        $proposal = $this->create([$this->newLine()]);
        $this->service->respond($this->token($proposal), 'reject');
        $this->assertSame('rejected', $proposal->fresh()->status);
        $this->assertSame('accepted', $this->order->fresh()->fulfillment_status);
        $this->assertSame('open', $this->order->fresh()->status);
        $this->assertSame(1000, $this->order->totalCents());
        $this->assertSame('20:00', $this->order->fresh()->requested_at->format('H:i'));
        $this->assertSame('Margherita', $this->order->items()->first()->product_name);
    }

    public function test_repeated_customer_response_is_idempotent_but_cannot_be_reversed(): void
    {
        $proposal = $this->create();
        $token = $this->token($proposal);
        $this->service->respond($token, 'accept');
        $count = MessageOutbox::count();
        $this->service->respond($token, 'accept');
        $this->assertSame($count, MessageOutbox::count());
        $this->assertSame(1, $this->order->events()->where('action', 'proposal_accepted')->count());
        $this->expectException(ValidationException::class);
        $this->service->respond($token, 'reject');
    }

    public function test_expired_and_withdrawn_proposals_cannot_be_accepted(): void
    {
        $proposal = $this->create();
        $token = $this->token($proposal);
        $this->service->withdraw($this->order->id, $proposal->id);
        try {
            $this->service->respond($token, 'accept');
            $this->fail('Withdrawn proposal accepted');
        } catch (ValidationException) {
        }
        $new = $this->create();
        $token = $this->token($new);
        $this->travelTo($new->expires_at->copy()->addMinute());
        $this->get(route('public-orders.proposal', $token))->assertOk()->assertSee('Scaduta')->assertDontSee('Conferma accettazione');
        $this->expectException(ValidationException::class);
        $this->service->respond($token, 'accept');
    }

    public function test_only_one_live_proposal_is_allowed(): void
    {
        $this->create();
        $this->expectException(ValidationException::class);
        $this->create();
    }

    public function test_pending_proposal_blocks_preparation_and_payment(): void
    {
        $this->order->update(['fulfillment_status' => 'accepted']);
        $this->create();
        try {
            app(CustomerOrderWorkflow::class)->transition($this->order->id, 'prepare', 'accepted');
            $this->fail('Preparation allowed');
        } catch (ValidationException) {
        }
        $this->expectException(ValidationException::class);
        app(OrderService::class)->pay($this->order->id, 'cash', 1000);
    }

    public function test_order_cancellation_withdraws_pending_proposal(): void
    {
        $proposal = $this->create();
        app(CustomerOrderWorkflow::class)->transition($this->order->id, 'reject', 'received');
        $this->assertSame('withdrawn', $proposal->fresh()->status);
        $this->expectException(ValidationException::class);
        $this->service->respond($this->token($proposal), 'accept');
    }

    public function test_cash_cancellation_also_invalidates_the_proposal(): void
    {
        $proposal = $this->create();
        app(OrderService::class)->cancel($this->order->id);
        $this->assertSame('withdrawn', $proposal->fresh()->status);
        $this->assertSame('cancelled', $this->order->fresh()->status);
    }

    public function test_paid_and_started_orders_cannot_receive_proposals(): void
    {
        $this->order->update(['status' => 'paid']);
        try {
            $this->create();
            $this->fail('Paid proposal allowed');
        } catch (ValidationException) {
        }
        $this->order->update(['status' => 'open', 'fulfillment_status' => 'accepted']);
        app(CustomerOrderWorkflow::class)->transition($this->order->id, 'prepare', 'accepted');
        $this->expectException(ValidationException::class);
        $this->create();
    }

    public function test_order_edits_and_changed_closures_prevent_acceptance(): void
    {
        $proposal = $this->create();
        $this->order->update(['customer_notes' => 'Modifica concorrente']);
        try {
            $this->service->respond($this->token($proposal), 'accept');
            $this->fail('Stale order accepted');
        } catch (ValidationException) {
        }
        $this->service->withdraw($this->order->id, $proposal->id);
        $new = $this->create();
        CustomerOrderClosure::create(['starts_at' => '2026-11-16 20:00', 'ends_at' => '2026-11-16 21:00', 'type' => 'pickup']);
        $this->expectException(ValidationException::class);
        $this->service->respond($this->token($new), 'accept');
    }

    public function test_delivery_quote_updates_fees_and_email_contains_address(): void
    {
        $this->order->update(['fulfillment' => 'delivery', 'delivery_address' => 'Via Roma 1', 'delivery_neighborhood' => 'Centro', 'delivery_fee_cents' => 300]);
        $settings = app(OrderingSettings::class)->get();
        $settings['delivery_free_over_cents'] = 2000;
        app(OrderingSettings::class)->save($settings);
        $lines = $this->service->initialLines($this->order);
        $lines[0]['quantity'] = 2;
        $proposal = $this->create($lines);
        $this->assertSame(2000, $proposal->payload['total']);
        $this->assertSame(0, $proposal->payload['changes']['delivery_fee_cents']);
        $delivery = MessageOutbox::where('deduplication_key', 'proposal:'.$proposal->id.':sent')->first()->payload['deliveries'][0];
        $html = (new CustomerOrderMail($delivery))->render();
        $this->assertStringContainsString('Via Roma 1', $html);
        $this->assertStringContainsString('Centro', $html);
        $this->assertStringContainsString('Consegna prevista', $html);
        $this->service->respond($this->token($proposal), 'accept');
        $this->assertSame(2000, $this->order->fresh()->totalCents());
        $this->assertSame(0, $this->order->fresh()->delivery_fee_cents);
    }

    public function test_existing_product_base_and_modifiers_keep_original_prices(): void
    {
        $item = $this->order->items()->first();
        $item->modifiers()->create(['ingredient_id' => $this->extra->id, 'ingredient_name' => 'Burrata', 'action' => 'add', 'price_cents' => 200]);
        $item->update(['unit_price_cents' => 1200]);
        $this->extra->update(['add_price' => '99.00']);
        $this->category->products()->updateExistingPivot($this->product->id, ['menu_price' => '99.00']);
        $lines = $this->service->initialLines($this->order);
        $lines[0]['quantity'] = 2;
        $lines[0]['notes'] = 'Nuove note';
        $proposal = $this->create($lines);
        $this->assertSame(2400, $proposal->payload['total']);
        $this->service->respond($this->token($proposal), 'accept');
        $this->assertSame(2400, $this->order->totalCents());
        $this->assertSame($item->id, $this->order->items()->first()->id);
    }

    public function test_livewire_editor_sends_proposal_and_backend_displays_waiting_state(): void
    {
        Livewire::test(ProposalEditor::class, ['order' => $this->order])->set('time', '20:30')->set('message', 'Proponiamo il ritiro alle 20:30.')->set('lines.0.quantity', 2)->call('sendProposal')->assertHasNoErrors()->assertRedirect(route('customers-orders.show', $this->order));
        $this->assertSame(1, CustomerOrderProposal::count());
        $this->get(route('customers-orders.show', $this->order))->assertOk()->assertSee('Ritira proposta')->assertDontSee('Avvia preparazione');
        Livewire::test(Index::class)->set('status', 'awaiting_customer')->assertHasNoErrors()->assertSee('OC-PROVA');
    }

    public function test_other_staff_cannot_propose_without_customer_order_permission(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');
        $operator->givePermissionTo(Permissions::CASH_REGISTER);
        $this->actingAs($operator);
        $this->get(route('customers-orders.proposal', $this->order))->assertForbidden();
        Livewire::test(ProposalEditor::class, ['order' => $this->order])->assertForbidden();
    }

    public function test_acceptance_email_contains_historical_order_data_and_payment_details(): void
    {
        app(CustomerOrderWorkflow::class)->transition($this->order->id, 'accept', 'received');
        $delivery = MessageOutbox::where('deduplication_key', 'order:'.$this->order->id.':accepted')->first()->payload['deliveries'][0];
        $this->product->update(['name' => ['it' => 'Nome cambiato'], 'price' => '99.00']);
        $this->order->update(['customer_notes' => 'Note successive']);
        $html = (new CustomerOrderMail($delivery))->render();
        foreach (['Margherita', 'Ben cotta', 'Citofono Mario', '10,00', 'Contanti', 'Non pagato', '20:00', 'Mario Rossi', 'IVA 10%'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('Note successive', $html);
        $this->assertStringNotContainsString('Nome cambiato', $html);
    }

    public function test_livewire_can_replace_product_and_edit_ingredients_before_sending(): void
    {
        Livewire::test(ProposalEditor::class, ['order' => $this->order])
            ->call('removeLine', 0)
            ->set('selectedProduct', $this->category->id.':'.$this->replacement->id)
            ->call('addProduct')->assertHasNoErrors()
            ->call('editIngredients', 0)->set('added', [$this->extra->id])->call('saveIngredients')->assertHasNoErrors()
            ->set('lines.0.quantity', '2')->set('lines.0.notes', 'Senza sale')
            ->set('message', 'Proponiamo la Diavola con burrata.')
            ->call('sendProposal')->assertHasNoErrors();
        $proposal = CustomerOrderProposal::firstOrFail();
        $this->assertSame(3200, $proposal->payload['total']);
        $this->assertSame('Senza sale', $proposal->payload['lines'][0]['attributes']['notes']);
        $this->assertSame('Burrata', $proposal->payload['lines'][0]['modifiers'][0]['ingredient_name']);
        $this->assertSame('Margherita', $this->order->items()->first()->product_name);
    }

    public function test_stale_draft_and_tampered_total_do_not_create_a_proposal(): void
    {
        $lines = $this->service->initialLines($this->order);
        $fingerprint = $this->service->fingerprint($this->order);
        try {
            $this->service->create($this->order->id, $lines, $this->details(), 'Messaggio', 1, $fingerprint);
            $this->fail('Tampered total accepted');
        } catch (ValidationException) {
        }
        $this->order->update(['customer_notes' => 'Aggiornate dal collega']);
        try {
            $this->service->create($this->order->id, $lines, $this->details(), 'Messaggio', 1000, $fingerprint);
            $this->fail('Stale draft accepted');
        } catch (ValidationException) {
        }
        $this->assertSame(0, CustomerOrderProposal::count());
    }

    public function test_unavailable_replacement_cannot_be_proposed(): void
    {
        $this->replacement->update(['disallow_takeaway' => true]);
        $this->expectException(ValidationException::class);
        $this->create([$this->newLine()]);
    }

    public function test_expired_proposal_does_not_permanently_block_original_order(): void
    {
        $this->order->update(['fulfillment_status' => 'accepted']);
        $proposal = $this->create();
        $this->travelTo($proposal->expires_at->copy()->addMinute());
        app(CustomerOrderWorkflow::class)->transition($this->order->id, 'prepare', 'accepted');
        $this->assertSame('preparing', $this->order->fresh()->fulfillment_status);
        $this->assertSame('20:00', $this->order->fresh()->requested_at->format('H:i'));
    }

    public function test_table_email_summary_only_contains_the_recipient_course(): void
    {
        $this->order->update(['fulfillment' => 'dine_in', 'table_name' => '3', 'room_name' => 'Sala blu', 'covers' => 2, 'cover_price_cents' => 200]);
        $course = $this->order->courses()->first();
        $course->update(['service_percentage_bps' => 1000]);
        $other = $this->order->courses()->create(['number' => 2]);
        app(OrderPricingService::class)->append($other, app(OrderPricingService::class)->quote($this->newLine(), true, 'dine_in', 'it'));
        $summary = app(OrderSummaryService::class)->snapshot($this->order, $course);
        $this->assertSame(1100, $summary['total_cents']);
        $this->assertSame(0, $summary['covers']);
        $this->assertSame(1, count($summary['items']));
        $this->assertSame('Margherita', $summary['items'][0]['name']);
    }

    public function test_proposal_links_do_not_authenticate_customer_or_staff(): void
    {
        $proposal = $this->create();
        $token = $this->token($proposal);
        $this->app['auth']->forgetGuards();
        session()->forget('customer_order_session');
        $this->get(route('public-orders.proposal', $token))->assertOk();
        $this->assertNull(session('customer_order_session'));
        $this->get(route('public-orders.proposal', str_repeat('x', 64)))->assertNotFound();
    }
}
