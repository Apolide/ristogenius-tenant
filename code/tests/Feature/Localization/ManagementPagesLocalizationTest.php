<?php

namespace Tests\Feature\Localization;

use App\Livewire\Menu\IngredientIndex;
use App\Models\CustomerOrderProposal;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Order;
use App\Models\ProductionTicket;
use App\Models\Room;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use App\Services\PublicOrders\OrderNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagementPagesLocalizationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('languages')]
    public function test_internal_pages_and_dialogs_follow_staff_language(string $locale, array $labels): void
    {
        Http::preventStrayRequests();
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        Room::create(['name' => 'Blue room', 'active' => true]);
        $department = Department::create(['name' => 'Kitchen', 'production' => true]);
        $order = Order::create(['service_date' => today(), 'reference' => 'OC-LANG', 'fulfillment' => 'pickup', 'source' => 'customer', 'fulfillment_status' => 'received', 'customer_name' => 'Anna', 'requested_at' => now()]);
        $course = $order->courses()->create(['number' => 1, 'sent_at' => now()]);
        ProductionTicket::create(['order_course_id' => $course->id, 'department_id' => $department->id, 'department_name' => $department->name, 'status' => 'queued', 'sent_at' => now()]);

        foreach (['kds.index', 'bookings.rooms', 'customers-orders.index', 'menu.products', 'menu.ingredients', 'menu.menus', 'cash-desk.index', 'cash-desk.history', 'cash-desk.shifts', 'cash-desk.reconciliation', 'settings.customer-orders', 'marketing.forms.index'] as $route) {
            $response = $this->actingAs($admin)->get(route($route))->assertOk();
            $this->assertSame($locale, app()->getLocale());
            if (in_array($route, ['kds.index', 'customers-orders.index', 'cash-desk.index', 'cash-desk.history', 'cash-desk.shifts', 'cash-desk.reconciliation', 'settings.customer-orders'], true)) {
                $response->assertSee('title="'.__('bookings.fullscreen').'"', false);
                $response->assertSee(__('bookings.fullscreen').'</span>', false);
            }
            foreach ($labels[$route] ?? [] as $label) {
                $response->assertSee($label);
            }
        }
        $this->get(route('customers-orders.show', $order))->assertOk()->assertSee($labels['detail']);
        Livewire::actingAs($admin)->test(IngredientIndex::class)->call('create')
            ->assertSee($labels['ingredient_dialog'])->call('save')->assertHasErrors('nameIt');
    }

    public static function languages(): array
    {
        return [
            ['en', [
                'kds.index' => ['To do', 'Start preparation', 'Open fullscreen', 'Course 1'],
                'bookings.rooms' => ['Dining room and bookings', 'Assign tables', 'Table bookings', 'Arrival within 60 min'],
                'customers-orders.index' => ['Customer orders', 'Awaiting approval', 'Include history', 'Unpaid'],
                'menu.products' => ['New product', 'Price', 'Department'],
                'menu.ingredients' => ['New ingredient', 'Availability'],
                'menu.menus' => ['New menu', 'Create, organize and publish multilingual menus.'],
                'cash-desk.history' => ['Order history', 'All types'],
                'cash-desk.shifts' => ['Cash shifts', 'Open shift'],
                'cash-desk.reconciliation' => ['Cash reconciliation', 'Export CSV'],
                'settings.customer-orders' => ['General configuration', 'Served neighborhoods'],
                'detail' => 'Order management', 'ingredient_dialog' => 'Save ingredient',
            ]],
            ['de', [
                'kds.index' => ['Offen', 'Zubereitung starten', 'Vollbild', 'Gang 1'],
                'bookings.rooms' => ['Gastraum und Reservierungen', 'Tische zuweisen', 'Tischreservierungen', 'Ankunft innerhalb von 60 Min.'],
                'customers-orders.index' => ['Kundenbestellungen', 'Warten auf Freigabe', 'Verlauf einschließen', 'Unbezahlt'],
                'menu.products' => ['Neues Produkt', 'Preis', 'Abteilung'],
                'menu.ingredients' => ['Neue Zutat', 'Verfügbarkeit'],
                'menu.menus' => ['Neue Speisekarte'],
                'cash-desk.history' => ['Bestellverlauf', 'Alle Arten'],
                'cash-desk.shifts' => ['Kassenschichten', 'Schicht öffnen'],
                'cash-desk.reconciliation' => ['Kassenabgleich', 'CSV exportieren'],
                'settings.customer-orders' => ['Allgemeine Konfiguration', 'Belieferte Stadtteile'],
                'detail' => 'Bestellverwaltung', 'ingredient_dialog' => 'Zutat speichern',
            ]],
        ];
    }

    public function test_model_statuses_are_translated_without_changing_status_codes(): void
    {
        $order = new Order(['fulfillment' => 'delivery', 'fulfillment_status' => 'preparing']);
        $order->setRelation('pendingProposal', null);
        $proposal = new CustomerOrderProposal(['status' => 'accepted']);
        app()->setLocale('en');
        $this->assertSame('Delivery', $order->fulfillmentLabel());
        $this->assertSame('Preparing', $order->fulfillmentStatusLabel());
        $this->assertSame('Accepted by customer', $proposal->statusLabel());
        app()->setLocale('de');
        $this->assertSame('Lieferung', $order->fulfillmentLabel());
        $this->assertSame('In Zubereitung', $order->fulfillmentStatusLabel());
        $this->assertSame('Vom Kunden angenommen', $proposal->statusLabel());
        $this->assertSame('delivery', $order->fulfillment);
        $this->assertSame('Consegna', $order->fulfillmentLabel('it'));
    }

    public function test_shared_notifications_translate_for_each_viewer_and_preserve_user_content(): void
    {
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
        $admin = User::factory()->create(['enabled' => true]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $order = Order::create(['service_date' => today(), 'reference' => 'OC-42', 'source' => 'customer', 'fulfillment' => 'delivery', 'customer_name' => 'Anna']);
        app(OrderNotifications::class)->incoming($order);
        $notification = $admin->notifications()->sole();
        app()->setLocale('en');
        $this->assertSame('New order OC-42', $notification->translatedTitle());
        $this->assertSame('Delivery · Anna', $notification->translatedMessage());
        app()->setLocale('de');
        $this->assertSame('Neue Bestellung OC-42', $notification->translatedTitle());
        $this->assertSame('Lieferung · Anna', $notification->translatedMessage());
        $legacy = new Notification(['type' => 'customer_order.received', 'title' => 'Nuovo ordine OC-41', 'message' => 'Asporto · Anna']);
        $this->assertSame('Neue Bestellung OC-41', $legacy->translatedTitle());
        $freeText = new Notification(['type' => 'general', 'title' => 'Salva', 'message' => 'Note del cliente']);
        $this->assertSame('Salva', $freeText->translatedTitle());
        $this->assertSame('Note del cliente', $freeText->translatedMessage());
    }
    public function test_pricing_snapshots_do_not_change_when_staff_language_changes(): void
    {
        $order = Order::create(['service_date' => today(), 'covers' => 2, 'cover_price_cents' => 100]);
        $pricing = app(\App\Services\Orders\CashBillPricingService::class);
        app()->setLocale('it');
        $italian = $pricing->snapshot($order);
        app()->setLocale('de');
        $this->assertSame($italian, $pricing->snapshot($order));
        $this->assertSame('Gedeckpreis', \App\Support\UiLabels::billItemName('covers', 'Coperto'));
        $this->assertSame('Coperto', \App\Support\UiLabels::billItemName('product-id', 'Coperto'));
    }

    public function test_email_uses_order_language_even_when_staff_and_customer_profile_differ(): void
    {
        $customer = \App\Models\Customer::create(['firstname' => 'Anna', 'email' => 'anna@example.test', 'lang' => 'it', 'registration_source' => 'test']);
        $order = Order::create(['service_date' => today(), 'reference' => 'OC-DE', 'fulfillment' => 'delivery',
            'customer_language' => 'de', 'customer_id' => $customer->id, 'customer_email' => $customer->email, 'customer_name' => 'Anna']);
        app()->setLocale('en');
        app(OrderNotifications::class)->status($order, 'ready');
        $delivery = \App\Models\MessageOutbox::where('deduplication_key', 'order:'.$order->id.':ready')->sole()->payload['deliveries'][0];
        $this->assertSame('de', $delivery['language']);
        $this->assertSame('Bestellung OC-DE', $delivery['subject']);
        $this->assertStringContainsString('Ihre Bestellung OC-DE', $delivery['message']);
        $this->assertSame('Consegna', $delivery['summary']['fulfillment']);
        $html = (new \App\Mail\CustomerOrderMail($delivery))->locale('de')->render();
        $this->assertStringContainsString('lang="de"', $html);
        $this->assertStringContainsString('Lieferung', $html);
        $this->assertStringNotContainsString('Consegna prevista', $html);
        $this->assertSame('en', app()->getLocale());
    }

    public function test_public_access_page_uses_token_language_instead_of_staff_language(): void
    {
        $menu = \App\Models\DigitalMenu::create(['name' => ['en' => 'Dinner'], 'slug' => 'language-test',
            'enabled_languages' => ['en'], 'is_published' => true, 'is_visible' => true]);
        $token = str_repeat('a', 64);
        \App\Models\CustomerOrderAccess::create(['token_hash' => hash('sha256', $token), 'menu_id' => $menu->id,
            'identity' => ['name' => 'Anna'], 'language' => 'en', 'expires_at' => now()->addMinutes(20)]);
        $staff = User::factory()->create(['lang' => 'de']);
        $this->actingAs($staff)->get(route('public-orders.verify', $token))->assertOk()
            ->assertSee('lang="en"', false)->assertSee('Confirm access')->assertDontSee('Conferma accesso');
    }

    public function test_validation_and_persisted_system_errors_are_localized(): void
    {
        app()->setLocale('de');
        $error = \Illuminate\Support\Facades\Validator::make([], ['nameIt' => 'required'])->errors()->first();
        $this->assertStringContainsString('erforderlich', $error);
        $this->assertSame('Die Bestellung ist geschlossen.', \App\Support\UiLabels::systemMessage('The order is closed.'));
        $this->assertSame('Provider diagnostic ABC', \App\Support\UiLabels::systemMessage('Provider diagnostic ABC'));
    }

}
