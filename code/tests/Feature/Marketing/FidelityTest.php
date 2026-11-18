<?php

namespace Tests\Feature\Marketing;

use App\Livewire\CashDesk\Show;
use App\Mail\MarketingMail;
use App\Models\Customer;
use App\Models\DigitalMenuProduct;
use App\Models\FidelityCard;
use App\Models\FidelityProgram;
use App\Models\FidelityReward;
use App\Models\MessageOutbox;
use App\Models\Order;
use App\Models\User;
use App\Services\Customer\CustomerService;
use App\Services\Fidelity\FidelityCheckout;
use App\Services\Orders\CashBillService;
use App\Services\Orders\CashCheckoutService;
use App\Services\Orders\CashRefundService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FidelityTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_enrollment_once_with_private_card_pdf_and_independent_marketing_filter(): void
    {
        $this->withoutVite();
        $this->get(route('fidelity.subscribe'))->assertOk()->assertSee('Il programma Fidelity è sospeso.')
            ->assertSee('<fieldset class="space-y-4" disabled>', false);
        $this->post(route('fidelity.request'))->assertNotFound();
        $this->program();
        $customer = Customer::create([
            'firstname' => 'Ada', 'lastname' => 'Rossi', 'display_name' => 'Ada Rossi',
            'email' => 'ada@example.test', 'registration_source' => 'test', 'consent_marketing' => false,
        ]);
        $this->get(route('fidelity.subscribe'))->assertOk()->assertSee('Richiedi la Fidelity Card');
        $this->post(route('fidelity.request'), [
            'firstname' => 'Ada', 'lastname' => 'Rossi', 'email' => $customer->email, 'privacy' => '1', 'language' => 'it',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('fidelity_cards', 0);
        $url = MessageOutbox::where('deduplication_key', 'like', 'fidelity-confirm:%')->sole()->payload['url'];
        $this->get($url)->assertOk()->assertSee('Attiva la tessera');
        $this->assertDatabaseCount('fidelity_cards', 0);

        $this->post($url)->assertRedirect();
        $card = FidelityCard::sole();
        $this->post($url)->assertRedirect($card->statusUrl());
        $this->assertSame($customer->id, $card->customer_id);
        $this->assertSame(20, $card->points);
        $this->assertDatabaseCount('fidelity_movements', 1);
        $this->assertDatabaseCount('message_outboxes', 2);
        $this->assertFalse($customer->refresh()->consent_marketing);
        $this->get($card->statusUrl())->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSee('Punti Fidelity disponibili: 20')->assertSee('Prenota subito');
        $pdf = $this->get(route('fidelity.pdf', $card->token))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $content = MessageOutbox::get()->pluck('payload')->first(fn ($payload) => isset($payload['fidelity_pdf_card_id']));
        $attachment = (new MarketingMail($content))->build()->rawAttachments[0];
        $this->assertSame('fidelity-card.pdf', $attachment['name']);
        $this->assertSame('application/pdf', $attachment['options']['mime']);
        $this->assertStringStartsWith('%PDF-', $attachment['data']);

        $customers = app(CustomerService::class);
        $this->assertSame(1, $customers->query(['onlyFidelity' => true])->count());
        $this->assertSame(0, $customers->query(['onlyFidelity' => true, 'onlyConsentMarketing' => true])->count());
    }

    public function test_management_saves_channel_switches_and_both_reward_types(): void
    {
        $this->withoutVite();
        $this->admin();
        $this->get(route('fidelity.manage.index'))->assertOk()->assertSee('Configurazione Fidelity');
        $this->post(route('fidelity.manage.update'), [
            'active' => '1', 'starting_points' => '50', 'rate' => '1.25',
            'booking_url' => 'https://example.test/book', 'channels' => ['delivery', 'pickup'],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(125, FidelityProgram::findOrFail(1)->rate);
        $this->assertSame(['delivery', 'pickup'], FidelityProgram::findOrFail(1)->channels);
        $this->post(route('fidelity.manage.rewards.create'), ['name' => 'Cinque euro', 'kind' => 'discount', 'points' => 10, 'amount' => '5.00'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $reward = FidelityReward::sole();
        $this->assertSame(500, $reward->discount_cents);
        $this->put(route('fidelity.manage.rewards.update', $reward), [
            'name' => 'Caffè', 'kind' => 'product', 'points' => 5, 'product_id' => $this->coffee()->id,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNotNull($reward->refresh()->product_id);
        $this->assertNull($reward->discount_cents);
        $this->delete(route('fidelity.manage.rewards.delete', $reward))->assertRedirect();
        $this->assertDatabaseCount('fidelity_rewards', 0);
    }

    public function test_booking_url_is_optional_and_card_links_to_the_protected_primary_form(): void
    {
        $this->withoutVite();
        $this->admin();
        $form = \App\Models\MarketingForm::create([
            'type' => 'booking', 'slug' => 'booking', 'is_active' => true,
            'translations' => ['it' => ['title' => 'Prenota']], 'enabled_languages' => ['it'],
        ]);
        $this->post(route('fidelity.manage.update'), [
            'active' => '1', 'starting_points' => '5', 'rate' => '1.00', 'channels' => ['dine_in'],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $program = FidelityProgram::findOrFail(1);
        $this->assertTrue($program->active);
        $this->assertNull($program->booking_url);

        $card = $this->card();
        $card->update(['language' => 'en']);
        $this->get($card->statusUrl())->assertOk()
            ->assertSee(route('marketing.forms.public', ['language' => 'it', 'form' => 'booking']))
            ->assertSee('Book now');

        Livewire::test(\App\Livewire\Marketing\Forms\FormIndex::class)
            ->call('delete', $form->id)->assertForbidden();
        $this->assertModelExists($form);
    }

    public function test_management_shows_greek_and_saves_its_public_copy(): void
    {
        $this->withoutVite();
        config(['tenant.customer_languages' => 'it,gr', 'tenant.backend_languages' => 'it,gr']);
        $this->admin();
        auth()->user()->update(['lang' => 'gr']);

        $this->get(route('fidelity.manage.index'))->assertOk()
            ->assertSee('id="fidelity-tab-gr"', false)
            ->assertSee('🇬🇷')
            ->assertSee('Ελληνικά');
        $this->assertSame('gr', app()->getLocale());

        $this->post(route('fidelity.manage.update'), [
            'active' => '1', 'starting_points' => 0, 'rate' => '1.00',
            'copy' => ['gr' => ['form' => 'Αποκτήστε τη δική σας κάρτα επιβράβευσης.']],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->get(route('fidelity.subscribe', ['language' => 'gr']))->assertOk()
            ->assertSee('Αποκτήστε τη δική σας κάρτα επιβράβευσης.');
    }

    public function test_language_tabs_configure_public_copy_and_activation_email_in_the_selected_language(): void
    {
        $this->withoutVite();
        config(['tenant.customer_languages' => 'it,de,en', 'tenant.default_backend_language' => 'de']);
        $this->admin();
        $program = FidelityProgram::findOrFail(1);
        $program->update(['copy' => ['balance_body' => 'Legacy balance', 'en' => ['form' => 'Old English copy']]]);
        $this->get(route('fidelity.manage.index'))->assertOk()
            ->assertSee('id="fidelity-tab-de"', false)
            ->assertSee('name="copy[it][activation_subject]"', false)
            ->assertSee('Old English copy')
            ->assertSee('Legacy balance');
        $this->post(route('fidelity.manage.update'), [
            'active' => '1', 'starting_points' => '5', 'rate' => '1.00', 'booking_url' => 'https://example.test/book',
            'copy' => [
                'it' => ['form' => 'Testo italiano'],
                'de' => ['form' => 'Deutscher Starttext'],
                'en' => ['form' => 'English enrollment text', 'activation_subject' => 'Your custom card email', 'balance_body' => null],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Legacy balance', $program->refresh()->copyFor('balance_body', 'it'));
        $this->assertNull($program->copyFor('balance_body', 'en'));
        $this->get(route('fidelity.subscribe'))->assertOk()->assertSee('Deutscher Starttext')
            ->assertSee('id="service-language"', false)
            ->assertSee('name="language" value="de"', false);
        $this->get(route('fidelity.subscribe', ['language' => 'en']))->assertOk()->assertSee('English enrollment text')
            ->assertDontSee('Deutscher Starttext');
        $this->post(route('fidelity.request'), [
            'firstname' => 'Ada', 'lastname' => 'Rossi', 'email' => 'ada@example.test', 'privacy' => '1', 'language' => 'en',
        ])->assertRedirect(route('fidelity.subscribe', ['language' => 'en']));
        $confirmation = MessageOutbox::sole()->payload;
        $this->assertSame('en', $confirmation['language']);
        $this->post($confirmation['url'])->assertRedirect();
        $this->assertSame('en', FidelityCard::sole()->language);
        $this->assertDatabaseHas('message_outboxes', ['payload->subject' => 'Your custom card email']);
    }

    public function test_reward_reservation_cannot_be_double_spent_and_split_checkout_refunds_are_idempotent(): void
    {
        $this->admin();
        $this->program();
        $card = $this->card();
        $reward = FidelityReward::create(['name' => 'Sconto', 'points' => 20, 'discount_cents' => 500]);
        $order = $this->order($card, 'delivery', 3000, 200);
        $checkout = app(FidelityCheckout::class);
        $checkout->reserve($order->id, $reward->id, 3200);
        $this->assertSame(0, $card->refresh()->points);
        $this->assertSame(2700, $order->refresh()->totalCents());

        $second = $this->order($card, 'delivery');
        try {
            $checkout->reserve($second->id, $reward->id, 3000);
            $this->fail('Reserved points were spent twice');
        } catch (ValidationException) {
            $this->assertNull($second->refresh()->fidelity_reward);
        }

        $plan = app(CashBillService::class)->create($order->id, 'equal', 2, 2700, 2700);
        [$first, $last] = $plan->shares()->get()->all();
        $cash = app(CashCheckoutService::class);
        $amounts = ['cash' => 1350, 'card' => 0, 'voucher' => 0];
        $batch = $cash->pay($order->id, Str::uuid()->toString(), $amounts, 2700, 2700, $first->id, $plan->version, tip: 100);
        $this->assertSame(0, $card->refresh()->points);
        $key = Str::uuid()->toString();
        $final = $cash->pay($order->id, $key, $amounts, 2700, 1350, $last->id, $plan->refresh()->version);
        $cash->pay($order->id, $key, $amounts, 2700, 1350, $last->id, $plan->version);
        $this->assertSame(25, $card->refresh()->points);

        $refunds = app(CashRefundService::class);
        $key = Str::uuid()->toString();
        $refunds->record($batch->id, $key, 1450, 'cash', 'Rimborso');
        $refunds->record($batch->id, $key, 1450, 'cash', 'Rimborso');
        $this->assertSame(12, $card->refresh()->points);
        $refunds->record($final->id, Str::uuid()->toString(), 1350, 'cash', 'Rimborso');
        $this->assertSame(20, $card->refresh()->points);
        $this->assertDatabaseCount('message_outboxes', 3);
    }

    public function test_cashier_applies_a_single_free_item_and_disabled_channel_does_not_earn(): void
    {
        $this->admin();
        $this->program();
        $card = $this->card();
        $coffee = $this->coffee();
        $reward = FidelityReward::create(['name' => 'Caffè', 'points' => 10, 'product_id' => $coffee->id]);
        $order = $this->order($card, 'pickup', 0);
        $item = $order->courses()->create(['number' => 1])->items()->create([
            'product_id' => $coffee->id, 'product_name' => 'Caffè', 'menu_name' => 'Menu', 'quantity' => 2,
            'base_price_cents' => 200, 'unit_price_cents' => 200, 'vat' => 10,
        ]);

        Livewire::test(Show::class, ['order' => $order])->assertSee('Punti previsti alla chiusura del conto: 0')
            ->set('selectedFidelityReward', (string) $reward->id)->call('applyFidelityReward')->assertHasNoErrors()->assertSee('Rimuovi premio');
        $order->refresh();
        $this->assertSame(200, $order->totalCents());
        $this->assertSame(200, app(\App\Services\Orders\CashTotalsService::class)->snapshot($order)['line_discounts'][$item->id]);
        $this->assertSame(10, $card->refresh()->points);
        app(OrderService::class)->pay($order->id, 'cash', 200);
        $this->assertSame(10, $card->refresh()->points);
    }

    public function test_table_checkout_finds_the_ordering_customer_and_releases_points_on_cancel(): void
    {
        $this->admin();
        $this->program();
        $card = $this->card();
        $order = Order::create(['service_date' => today(), 'fulfillment' => 'dine_in', 'source' => 'customer']);
        $course = $order->courses()->create(['number' => 1, 'customer_id' => $card->customer_id]);
        $course->items()->create([
            'product_name' => 'Pizza', 'menu_name' => 'Menu', 'quantity' => 1,
            'base_price_cents' => 1200, 'unit_price_cents' => 1200, 'vat' => 10,
        ]);
        $checkout = app(FidelityCheckout::class);
        $this->assertSame($card->id, $checkout->card($order)->id);
        $reward = FidelityReward::create(['name' => 'Sconto', 'points' => 5, 'discount_cents' => 100]);
        $checkout->reserve($order->id, $reward->id, 1200);
        $this->assertSame(15, $card->refresh()->points);
        app(OrderService::class)->cancel($order->id);
        $this->assertSame(20, $card->refresh()->points);
        $this->assertNull($order->refresh()->fidelity_reward);
    }

    private function admin(): void
    {
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
        app(Permissions::class)->ensurePermissionsExist();
    }

    private function program(): void
    {
        FidelityProgram::findOrFail(1)->update([
            'active' => true, 'starting_points' => 20, 'channels' => ['dine_in', 'delivery'], 'booking_url' => 'https://example.test/book',
        ]);
    }

    private function card(): FidelityCard
    {
        $customer = Customer::create([
            'firstname' => 'Ada', 'lastname' => 'Rossi', 'email' => 'ada@example.test', 'registration_source' => 'test',
        ]);

        return $customer->fidelityCard()->create(['token' => Str::random(64), 'points' => 20, 'language' => 'it']);
    }

    private function order(FidelityCard $card, string $channel, int $amount = 3000, int $delivery = 0): Order
    {
        return Order::create([
            'service_date' => today(), 'fulfillment' => $channel, 'fulfillment_status' => 'accepted', 'covers' => 1,
            'cover_price_cents' => $amount, 'delivery_fee_cents' => $delivery, 'customer_id' => $card->customer_id,
        ]);
    }

    private function coffee(): DigitalMenuProduct
    {
        return DigitalMenuProduct::create(['name' => ['it' => 'Caffè'], 'price' => 2, 'vat' => 10]);
    }
}
