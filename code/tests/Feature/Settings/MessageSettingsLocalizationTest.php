<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\MessageTemplatesIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MessageSettingsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        config()->set('tenant.customer_languages', 'it,en,de');
    }

    #[DataProvider('locales')]
    public function test_message_settings_list_and_editor_use_the_authenticated_users_language_file(
        string $locale,
        array $listTexts,
        array $editorTexts,
    ): void {
        $admin = User::factory()->create(['lang'=>$locale, 'enabled'=>true, 'activated_at'=>now()]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('settings.messages'));
        $response->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($listTexts as $text) {
            $response->assertSee($text);
        }

        $editor = Livewire::actingAs($admin)->test(MessageTemplatesIndex::class)
            ->call('edit', 'booking_accepted')
            ->assertSet('editingKey', 'booking_accepted');
        foreach ($editorTexts as $text) {
            $editor->assertSee($text);
        }
    }

    public static function locales(): array
    {
        return [
            'Italian message settings translations' => ['it',
                ['Messaggi automatici','Cerca messaggio','Azioni','Messaggio','Prenotazione accettata'],
                ['MODIFICA MESSAGGIO: Prenotazione accettata','Lingue','Oggetto Italiano','Note aggiuntive Italiano','Testo SMS Italiano (max 160 caratteri)','Salva','Annulla'],
            ],
            'English message settings translations' => ['en',
                ['Automatic messages','Search messages','Actions','Message','Booking accepted'],
                ['EDIT MESSAGE: Booking accepted','Languages','Subject Italiano','Additional notes Italiano','SMS text Italiano (max 160 characters)','Save','Cancel'],
            ],
            'German message settings translations' => ['de',
                ['Automatische Nachrichten','Nachrichten suchen','Aktionen','Nachricht','Reservierung angenommen'],
                ['NACHRICHT BEARBEITEN: Reservierung angenommen','Sprachen','Betreff Italiano','Zusätzliche Hinweise Italiano','SMS-Text Italiano (max. 160 Zeichen)','Speichern','Abbrechen'],
            ],
        ];
    }
}
