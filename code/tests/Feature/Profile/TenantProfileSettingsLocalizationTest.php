<?php

namespace Tests\Feature\Profile;

use App\Livewire\Profile\ProfileEdit;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantProfileSettingsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
    }

    #[DataProvider('locales')]
    public function test_tenant_profile_settings_use_the_authenticated_users_language_file(
        string $locale,
        array $pageTexts,
        string $successText,
    ): void {
        $admin = User::factory()->create(['lang' => $locale, 'enabled' => true, 'activated_at' => now()]);
        $admin->assignRole('admin');
        TenantProfile::create(['name' => 'Ristorante Test']);

        $response = $this->actingAs($admin)->get(route('profile.edit'));
        $response->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($pageTexts as $text) {
            $response->assertSee($text);
        }

        Livewire::actingAs($admin)
            ->test(ProfileEdit::class)
            ->set('company_name', 'Ristopilot GmbH')
            ->set('city', 'Berlin')
            ->set('province', 'BE')
            ->set('postcode', '10115')
            ->set('address', 'Alexanderplatz 1')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSee($successText);

        $this->assertDatabaseHas('tenant_profiles', [
            'name' => 'Ristorante Test',
            'company_name' => 'Ristopilot GmbH',
            'city' => 'Berlin',
            'province' => 'BE',
            'postcode' => '10115',
            'address' => 'Alexanderplatz 1',
        ]);
    }

    public static function locales(): array
    {
        return [
            'Italian tenant profile translations' => ['it', [
                'Profilo Aziendale', 'Profilo', 'Logo', 'Caricamento logo...', 'Nome Attività',
                'Nome Azienda', 'Città', 'Provincia', 'CAP', 'Indirizzo', 'Salva', 'Annulla',
            ], 'Profilo aggiornato correttamente.'],
            'English tenant profile translations' => ['en', [
                'Business Profile', 'Profile', 'Logo', 'Uploading logo...', 'Business Name',
                'Company Name', 'City', 'Province', 'Postcode', 'Address', 'Save', 'Cancel',
            ], 'Profile updated successfully.'],
            'German tenant profile translations' => ['de', [
                'Unternehmensprofil', 'Profil', 'Logo', 'Logo wird hochgeladen...', 'Betriebsname',
                'Firmenname', 'Stadt', 'Provinz', 'Postleitzahl', 'Adresse', 'Speichern', 'Abbrechen',
            ], 'Profil erfolgreich aktualisiert.'],
        ];
    }
}
