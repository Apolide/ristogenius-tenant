<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\MessageChannelCasesIndex;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MessageChannelCasesLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
    }

    #[DataProvider('locales')]
    public function test_message_channel_cases_use_the_authenticated_users_language_file(
        string $locale,
        array $pageTexts,
        string $search,
        string $matchingCase,
        string $nonMatchingCase,
        string $resetMessage,
    ): void {
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole('admin');
        TenantProfile::query()->create(['name' => 'Test']);

        $response = $this->actingAs($admin)->get(route('settings.message-channel-cases'));
        $response->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($pageTexts as $text) {
            $response->assertSee($text);
        }

        Livewire::actingAs($admin)
            ->test(MessageChannelCasesIndex::class)
            ->set('search', $search)
            ->assertSee($matchingCase)
            ->assertDontSee($nonMatchingCase)
            ->call('resetToDefaults')
            ->assertSee($resetMessage);
    }

    public static function locales(): array
    {
        return [
            'Italian message channel cases translations' => ['it',
                ['Casi invio messaggi', 'Cerca per etichetta', 'Azioni', 'Etichetta', 'Obbligatorio', 'Ripristina', 'Sì', 'No', 'Prenotazione accettata', 'Lista d’attesa: tavolo pronto', 'Salva', 'Annulla'],
                'accettata', 'Prenotazione accettata', 'Prenotazione cancellata', 'Casi invio messaggi ripristinati correttamente.'],
            'English message channel cases translations' => ['en',
                ['Message delivery cases', 'Search by label', 'Actions', 'Label', 'Required', 'Reset', 'Yes', 'No', 'Booking accepted', 'Waitlist: table ready', 'Save', 'Cancel'],
                'accepted', 'Booking accepted', 'Booking canceled', 'Message delivery cases reset successfully.'],
            'German message channel cases translations' => ['de',
                ['Nachrichtenversandfälle', 'Nach Bezeichnung suchen', 'Aktionen', 'Bezeichnung', 'Erforderlich', 'Zurücksetzen', 'Ja', 'Nein', 'Reservierung angenommen', 'Warteliste: Tisch bereit', 'Speichern', 'Abbrechen'],
                'angenommen', 'Reservierung angenommen', 'Reservierung storniert', 'Nachrichtenversandfälle erfolgreich zurückgesetzt.'],
        ];
    }
}
