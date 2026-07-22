<?php

namespace Tests\Feature\Marketing;

use App\Livewire\Marketing\Forms\FormCreate;
use App\Livewire\Marketing\Forms\FormEdit;
use App\Livewire\Marketing\Forms\FormIndex;
use App\Livewire\Marketing\Forms\FormStyleEdit;
use App\Livewire\Marketing\Forms\PublicForm;
use App\Livewire\Marketing\Forms\SubmissionIndex;
use App\Models\MarketingForm;
use App\Models\User;
use App\Services\MarketingForms\FormBlueprintService;
use App\Services\MarketingForms\FormPublicAssetService;
use App\Services\TenantBrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MarketingFormsCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('tenant.customer_languages', 'it,en,de');
        config()->set('tenant.slug', 'marketing-coverage-test');
        Permission::findOrCreate('marketing', 'web');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('marketing-assets/marketing-coverage-test'));
        parent::tearDown();
    }

    public function test_backend_create_persists_all_languages_options_and_notification_users(): void
    {
        $user = User::factory()->create(['enabled' => true]);

        Livewire::test(FormCreate::class)
            ->set('type', 'generic')
            ->set('translations.it.title', 'Richiesta catering')
            ->set('translations.en.title', 'Catering request')
            ->set('translations.de.title', 'Catering-Anfrage')
            ->set('translations.it.description', '<p>Descrizione</p>')
            ->set('is_active', false)
            ->set('accepts_coupons', false)
            ->set('notify_user_ids', [$user->id])
            ->call('save')
            ->assertHasNoErrors();

        $form = MarketingForm::query()->firstOrFail();
        $this->assertSame(['it', 'en', 'de'], $form->enabled_languages);
        $this->assertSame('Catering request', $form->translations['en']['title']);
        $this->assertFalse($form->is_active);
        $this->assertFalse($form->accepts_coupons);
        $this->assertTrue($form->notificationUsers->contains($user));
    }

    public function test_backend_custom_field_can_be_created_edited_and_deleted(): void
    {
        $form = $this->form('generic', 'editable-fields');

        $component = Livewire::test(FormEdit::class, ['form' => $form])
            ->set('newField.key', 'menu')
            ->set('newField.type', 'select')
            ->set('newField.label.it', 'Menu')
            ->set('newField.options.it', 'Carne, Pesce')
            ->set('newField.label.en', 'Menu')
            ->set('newField.options.en', 'Meat, Fish')
            ->set('newField.label.de', 'Menü')
            ->set('newField.options.de', 'Fleisch, Fisch')
            ->call('addField')
            ->assertHasNoErrors();

        $field = $form->fields()->where('key', 'menu')->firstOrFail();
        $this->assertSame(['Carne', 'Pesce'], $field->options['it']);

        $component->call('startEditingField', $field->id)
            ->set('editField.type', 'radio')
            ->set('editField.label.it', 'Scelta menu')
            ->set('editField.options.it', 'Vegetariano, Carne')
            ->set('editField.label.en', 'Menu choice')
            ->set('editField.options.en', 'Vegetarian, Meat')
            ->set('editField.label.de', 'Menüauswahl')
            ->set('editField.options.de', 'Vegetarisch, Fleisch')
            ->set('editField.required', false)
            ->set('editField.visible', true)
            ->call('updateField')
            ->assertHasNoErrors();

        $field->refresh();
        $this->assertSame('radio', $field->type);
        $this->assertSame('Scelta menu', $field->label['it']);
        $this->assertSame(['Vegetariano', 'Carne'], $field->options['it']);

        $component->call('deleteField', $field->id)->assertHasNoErrors();
        $this->assertDatabaseMissing('marketing_form_fields', ['id' => $field->id]);
    }

    public function test_required_custom_field_cannot_be_deleted_and_option_values_are_required(): void
    {
        $form = $this->form('generic', 'protected-fields');
        $field = $form->fields()->create([
            'key' => 'choice', 'type' => 'select', 'label' => ['it' => 'Scelta', 'en' => 'Choice', 'de' => 'Auswahl'],
            'options' => ['it' => ['A'], 'en' => ['A'], 'de' => ['A']], 'required' => true, 'visible' => true, 'position' => 10,
        ]);

        Livewire::test(FormEdit::class, ['form' => $form])
            ->call('deleteField', $field->id)
            ->assertHasErrors(['field']);
        $this->assertDatabaseHas('marketing_form_fields', ['id' => $field->id]);

        Livewire::test(FormEdit::class, ['form' => $form])
            ->set('newField.key', 'empty_options')
            ->set('newField.type', 'checkbox')
            ->set('newField.label.it', 'Opzioni')
            ->set('newField.label.en', 'Options')
            ->set('newField.label.de', 'Optionen')
            ->call('addField')
            ->assertHasErrors(['newField.options.it', 'newField.options.en', 'newField.options.de']);
    }

    public function test_background_asset_is_replaced_published_and_removed(): void
    {
        Storage::fake('local');
        $form = $this->form('generic', 'asset-lifecycle');

        Livewire::test(FormStyleEdit::class, ['form' => $form])
            ->set('backgroundImage', UploadedFile::fake()->image('first.jpg', 1920, 1080))
            ->call('save')
            ->assertHasNoErrors();

        $form->refresh();
        $firstStoragePath = $form->image_path;
        Storage::disk('local')->assertExists($firstStoragePath);
        $this->assertFileExists(public_path("marketing-assets/marketing-coverage-test/forms/{$form->id}/background.jpg"));

        Livewire::test(FormStyleEdit::class, ['form' => $form])
            ->set('backgroundImage', UploadedFile::fake()->image('second.png', 1920, 1080))
            ->call('save')
            ->assertHasNoErrors()
            ->call('removeBackground');

        Storage::disk('local')->assertMissing($firstStoragePath);
        $this->assertNull($form->fresh()->image_path);
        $this->assertDirectoryDoesNotExist(public_path("marketing-assets/marketing-coverage-test/forms/{$form->id}"));
    }

    public function test_legacy_public_disk_background_is_mirrored_to_marketing_assets(): void
    {
        Storage::fake('public');
        $form = $this->form('generic', 'legacy-asset');
        $legacyPath = 'marketing-forms/backgrounds/legacy.jpg';
        Storage::disk('public')->put($legacyPath, 'legacy-image');
        $form->update(['image_path' => $legacyPath]);

        $url = app(FormPublicAssetService::class)->backgroundUrl($form);

        $this->assertStringContainsString("/marketing-assets/marketing-coverage-test/forms/{$form->id}/background.jpg", $url);
        $this->assertFileExists(public_path("marketing-assets/marketing-coverage-test/forms/{$form->id}/background.jpg"));
    }

    public function test_style_reset_restores_config_defaults_without_removing_background(): void
    {
        $form = $this->form('generic', 'reset-style');
        $form->update(['style_settings' => ['link' => '#ff0000'], 'image_path' => 'existing/background.jpg']);

        Livewire::test(FormStyleEdit::class, ['form' => $form])
            ->call('resetStyle')
            ->assertSet('style.link', config('marketing_form_style.colors.link'));

        $this->assertNull($form->fresh()->style_settings);
        $this->assertSame('existing/background.jpg', $form->fresh()->image_path);
    }

    public function test_public_layout_renders_constrained_logo_compact_footer_and_rich_lists(): void
    {
        $form = $this->form('generic', 'public-layout', [
            'it' => ['title' => 'Richiesta', 'description' => '<ul><li>Prima voce</li></ul><ol><li>Seconda voce</li></ol>'],
            'en' => ['title' => 'Request', 'description' => ''],
            'de' => ['title' => 'Anfrage', 'description' => ''],
        ]);
        $this->mock(TenantBrandingService::class, function ($mock): void {
            $mock->shouldReceive('branding')->andReturn([
                'name' => 'Ristorante Test', 'logo_url' => 'https://example.test/logo.png', 'address' => 'Via Roma 1',
            ]);
        });

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->assertSeeHtml('class="tenant-public-form-logo"')
            ->assertSeeHtml('tenant-public-form-rich-text my-4')
            ->assertSeeHtml('<ul><li>Prima voce</li></ul>')
            ->assertSeeHtml('<ol><li>Seconda voce</li></ol>')
            ->assertSeeHtml('flex-col items-center justify-center gap-1')
            ->assertSee('Ristorante Test')
            ->assertSee('Via Roma 1');

        $css = File::get(public_path('assets/css/style.css'));
        $this->assertStringContainsString('.tenant-public-form-logo {', $css);
        $this->assertStringContainsString('max-height: calc(var(--tenant-form-header-height) - 1rem) !important', $css);
        $this->assertStringContainsString('.tenant-public-form-rich-text ul { list-style: disc outside;', $css);
        $this->assertStringContainsString('.tenant-public-form-rich-text ol { list-style: decimal outside;', $css);
    }

    public function test_backend_index_exposes_public_language_links_and_edit_exposes_style_page(): void
    {
        $form = $this->form('generic', 'linked-form');

        $index = Livewire::test(FormIndex::class)
            ->assertSeeHtml('target="_blank"')
            ->assertSeeHtml('rel="noopener noreferrer"');

        foreach (['it', 'en', 'de'] as $language) {
            $index->assertSee(route('marketing.forms.public', ['language' => $language, 'form' => $form->slug]));
        }

        Livewire::test(FormEdit::class, ['form' => $form])
            ->assertSee(route('marketing.forms.style', $form));
    }

    public function test_public_file_upload_is_stored_privately_and_snapshotted(): void
    {
        Storage::fake('local');
        $form = $this->form('generic', 'cv-upload');
        $form->fields()->create([
            'key' => 'cv', 'type' => 'file', 'label' => ['it' => 'CV', 'en' => 'CV', 'de' => 'CV'],
            'required' => true, 'visible' => true, 'position' => 10,
        ]);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.name', 'Mario Rossi')
            ->set('answers.email', 'mario@example.test')
            ->set('answers.privacy_consent', true)
            ->set('answers.cv', UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $path = $form->submissions()->firstOrFail()->payload['cv'];
        Storage::disk('local')->assertExists($path);
        $this->assertStringStartsWith("marketing-forms/{$form->id}/", $path);
    }

    public function test_public_multivalue_controls_keep_independent_values_and_reject_unknown_options(): void
    {
        $form = $this->form('generic', 'option-controls');
        $form->fields()->create([
            'key' => 'services', 'type' => 'checkbox', 'label' => ['it' => 'Servizi', 'en' => 'Services', 'de' => 'Dienste'],
            'options' => ['it' => ['A', 'B'], 'en' => ['A', 'B'], 'de' => ['A', 'B']],
            'required' => true, 'visible' => true, 'position' => 10,
        ]);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.services', ['A'])
            ->assertSet('answers.services', ['A'])
            ->set('answers.name', 'Mario Rossi')
            ->set('answers.email', 'mario@example.test')
            ->set('answers.privacy_consent', true)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(['A'], $form->submissions()->firstOrFail()->payload['services']);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.services', ['Valore non configurato'])
            ->call('submit')
            ->assertHasErrors(['answers.services.0']);
    }

    public function test_submission_list_displays_generic_responses_and_excludes_booking_snapshots(): void
    {
        $generic = $this->form('generic', 'generic-response');
        $booking = $this->form('booking', 'booking-response');
        $generic->submissions()->create(['language' => 'it', 'payload' => ['message' => 'Risposta generica']]);
        $booking->submissions()->create(['language' => 'it', 'payload' => ['message' => 'Snapshot prenotazione']]);

        Livewire::test(SubmissionIndex::class)
            ->assertSee('Risposta generica')
            ->assertDontSee('Snapshot prenotazione');
    }

    public function test_public_form_rejects_missing_required_values_inactive_forms_and_disabled_languages(): void
    {
        $form = $this->form('generic', 'validation-cases');

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->call('submit')
            ->assertHasErrors(['answers.name', 'answers.email', 'answers.privacy_consent']);

        $this->get(route('marketing.forms.public', ['language' => 'fr', 'form' => $form->slug]))->assertNotFound();
        $form->update(['is_active' => false]);
        $this->get(route('marketing.forms.public', ['language' => 'it', 'form' => $form->slug]))->assertNotFound();
        $this->get('/form/it/slug-inesistente')->assertNotFound();
    }

    private function form(string $type, string $slug, ?array $translations = null): MarketingForm
    {
        return app(FormBlueprintService::class)->create([
            'type' => $type,
            'slug' => $slug,
            'translations' => $translations ?? [
                'it' => ['title' => 'Form test', 'description' => ''],
                'en' => ['title' => 'Test form', 'description' => ''],
                'de' => ['title' => 'Testformular', 'description' => ''],
            ],
            'enabled_languages' => ['it', 'en', 'de'],
            'is_active' => true,
        ]);
    }
}
