<?php

namespace Tests\Feature\Localization;

use App\Livewire\Bookings\BookingRoomIndex;
use App\Models\Customer;
use App\Models\FidelityCard;
use App\Models\FidelityProgram;
use App\Models\Room;
use App\Models\User;
use App\Services\Fidelity\FidelityMail;
use App\Services\Personnel\PersonnelPermissionsService;
use App\Services\TenantBrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoomSidebarAndFidelityLocalizationTest extends TestCase
{
    use RefreshDatabase;

    private function manager(string $language): void
    {
        $this->withoutVite();
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
        $user = User::factory()->create(['lang' => $language, 'enabled' => true, 'activated_at' => now()]);
        $user->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($user);
        Room::create(['name' => 'Main room', 'active' => true]);
    }

    #[DataProvider('sidebarLanguages')]
    public function test_room_sidebar_labels_follow_manager_language_and_start_closed(string $language, string $showBookings, string $hideBookings, string $showOrders, string $hideOrders): void
    {
        $this->manager($language);
        $bookings = $this->get(route('bookings.rooms'))->assertOk()->assertSee($showBookings)->assertSee($hideBookings);
        $orders = $this->get(route('orders.index'))->assertOk()->assertSee($showOrders)->assertSee($hideOrders)->assertSee('sidebarOpen: false', false);
        if ($language !== 'it') {
            $bookings->assertDontSee('Mostra prenotazioni')->assertDontSee('Nascondi prenotazioni');
            $orders->assertDontSee('Mostra comande')->assertDontSee('Nascondi comande');
        }
        Livewire::test(BookingRoomIndex::class)->assertSet('sidebarOpen', false);
        // The shared workspace keeps its existing cash-desk behaviour.
        $this->get(route('cash-desk.index'))->assertOk()->assertSee('sidebarOpen: true', false);
    }

    public static function sidebarLanguages(): array
    {
        return [
            ['it', 'Mostra prenotazioni', 'Nascondi prenotazioni', 'Mostra comande', 'Nascondi comande'],
            ['en', 'Show bookings', 'Hide bookings', 'Show orders', 'Hide orders'],
            ['de', 'Reservierungen anzeigen', 'Reservierungen ausblenden', 'Bestellungen anzeigen', 'Bestellungen ausblenden'],
        ];
    }

    public function test_fidelity_editor_shows_english_identifiers_even_for_saved_legacy_copy(): void
    {
        $this->manager('en');
        config(['tenant.customer_languages' => 'it,en,de,fr,es']);
        FidelityProgram::findOrFail(1)->update(['copy' => ['en' => ['form' => 'Welcome @@nomecliente@@ to @@nomelocale@@']]]);
        $page = $this->get(route('fidelity.manage.index'))->assertOk();
        foreach (FidelityMail::PLACEHOLDERS as $placeholder) {
            $page->assertSee($placeholder);
        }
        $page->assertSee('Welcome @@customer_name@@ to @@venue_name@@')->assertDontSee('@@nomecliente@@')->assertDontSee('@@nomelocale@@');
        // Opening the editor does not rewrite stored customer content.
        $this->assertSame('Welcome @@nomecliente@@ to @@nomelocale@@', FidelityProgram::findOrFail(1)->copyFor('form', 'en'));
    }

    public function test_fidelity_replaces_both_canonical_and_legacy_placeholders(): void
    {
        $this->mock(TenantBrandingService::class)->shouldReceive('branding')->andReturn(['name' => 'Test Restaurant']);
        $program = new FidelityProgram(['starting_points' => 7]);
        $card = (new FidelityCard(['points' => 33]))->setRelation('customer', new Customer(['firstname' => 'Ada']));
        $mail = app(FidelityMail::class);
        foreach (['@@venue_name@@ / @@customer_name@@ / @@starting_points@@ / @@points@@', '@@nomelocale@@ / @@nomecliente@@ / @@starting_points@@ / @@points@@'] as $text) {
            $this->assertSame('Test Restaurant / Ada / 7 / 33', $mail->copy($text, $program, $card));
        }
        foreach (['it', 'en', 'de', 'fr', 'es'] as $language) {
            $text = __('fidelity.activation_body', [], $language);
            $this->assertStringContainsString('@@venue_name@@', $text);
            $this->assertStringContainsString('@@customer_name@@', $text);
            $this->assertStringNotContainsString('@@', $mail->copy($text, $program, $card));
        }
    }
}
