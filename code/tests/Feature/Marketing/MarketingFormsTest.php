<?php

namespace Tests\Feature\Marketing;

use App\Livewire\Marketing\Forms\PublicForm;
use App\Models\MarketingForm;
use App\Models\User;
use App\Services\MarketingForms\FormBlueprintService;
use Database\Seeders\CateringOrderFormSeeder;
use Database\Seeders\JobApplicationFormSeeder;
use Database\Seeders\MarketingBookingFormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MarketingFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('tenant.customer_languages', 'it,en,de');
        Permission::findOrCreate('marketing', 'web');
    }

    #[DataProvider('types')]
    public function test_blueprints_create_expected_standard_fields(string $type, int $count): void
    {
        $form = app(FormBlueprintService::class)->create(['type' => $type, 'slug' => $type, 'translations' => ['it' => ['title' => 'Test', 'description' => '']], 'enabled_languages' => ['it'], 'is_active' => true, 'accepts_coupons' => true]);
        $this->assertCount($count, $form->fields);
        $this->assertTrue($form->fields->first()->required);
    }

    public static function types(): array
    {
        return [['booking', 7], ['event', 7], ['generic', 2]];
    }

    public function test_locked_standard_field_cannot_be_hidden(): void
    {
        $service = app(FormBlueprintService::class);
        $form = $service->create(['type' => 'booking', 'slug' => 'booking', 'translations' => ['it' => ['title' => 'Test']], 'enabled_languages' => ['it']]);
        $this->expectException(\DomainException::class);
        $service->updateField($form, $form->fields->first()->id, ['visible' => false, 'required' => true]);
    }

    public function test_generic_form_can_have_custom_fields_and_notification_users(): void
    {
        $user = User::factory()->create();
        $service = app(FormBlueprintService::class);
        $form = $service->create(['type' => 'generic', 'slug' => 'jobs', 'translations' => ['it' => ['title' => 'Lavora con noi']], 'enabled_languages' => ['it'], 'notify_user_ids' => [$user->id]]);
        $form->fields()->create(['key' => 'cv', 'type' => 'text', 'label' => ['it' => 'CV'], 'required' => true, 'visible' => true, 'position' => 3]);
        $this->assertCount(3, $form->fields()->get());
        $this->assertTrue($form->notificationUsers->contains($user));
    }

    public function test_public_form_validates_and_stores_submission(): void
    {
        $form = app(FormBlueprintService::class)->create(['type' => 'generic', 'slug' => 'catering', 'translations' => ['it' => ['title' => 'Catering']], 'enabled_languages' => ['it'], 'is_active' => true]);
        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])->set('answers.name', 'Mario Rossi')->set('answers.email', 'mario@example.test')->call('submit')->assertSet('submitted', true);
        $this->assertDatabaseCount('marketing_form_submissions', 1);
    }

    public function test_public_multi_value_checkbox_is_initialized_as_an_array(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => 'checkbox-form',
            'translations' => ['it' => ['title' => 'Checkbox']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);
        $form->fields()->create([
            'key' => 'preferences',
            'type' => 'checkbox',
            'label' => ['it' => 'Preferenze'],
            'options' => ['it' => ['check1', 'check2']],
            'required' => false,
            'visible' => true,
            'position' => 3,
        ]);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->assertSet('answers.preferences', []);
    }

    public function test_public_booking_form_creates_pending_booking_instead_of_generic_submission(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'booking',
            'slug' => 'booking',
            'translations' => ['it' => ['title' => 'Prenota', 'description' => 'Richiedi un tavolo']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);
        $form->fields()->create([
            'key' => 'occasion',
            'type' => 'text',
            'label' => ['it' => 'Occasione'],
            'required' => false,
            'visible' => true,
            'position' => 8,
        ]);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.name', 'Mario Rossi')
            ->set('answers.email', 'mario@example.test')
            ->set('answers.phone', '+393331234567')
            ->set('answers.date', now()->addDay()->toDateString())
            ->set('answers.time', '20:00')
            ->set('answers.guests', 4)
            ->set('answers.occasion', 'Compleanno')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('bookings', [
            'status' => 'pending',
            'source' => 'public-form',
            'pax' => 4,
            'language' => 'it',
        ]);
        $this->assertDatabaseHas('customers', ['email' => 'mario@example.test']);
        $booking = \App\Models\Booking::query()->firstOrFail();
        $this->assertDatabaseHas('marketing_form_submissions', [
            'marketing_form_id' => $form->id,
            'booking_id' => $booking->id,
            'language' => 'it',
        ]);
        $submission = $form->submissions()->firstOrFail();
        $this->assertSame('Compleanno', $submission->payload['occasion']);
        $this->assertContains('occasion', collect($submission->field_snapshot)->pluck('key'));
    }

    public function test_public_form_is_available_to_guests_and_marks_required_fields_in_html(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => 'public-request',
            'translations' => ['it' => ['title' => 'Richiesta pubblica', 'description' => 'Descrizione pubblica']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);

        $this->get(route('marketing.forms.public', ['language' => 'it', 'form' => $form->slug]))
            ->assertOk()
            ->assertSee('Richiesta pubblica')
            ->assertSee('Descrizione pubblica')
            ->assertSee('required', false);
    }

    public function test_public_form_language_select_links_to_each_enabled_language(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => 'multilingual-request',
            'translations' => [
                'it' => ['title' => 'Richiesta'],
                'en' => ['title' => 'Request'],
                'de' => ['title' => 'Anfrage'],
            ],
            'enabled_languages' => ['it', 'en', 'de'],
            'is_active' => true,
        ]);

        $response = $this->get(route('marketing.forms.public', [
            'language' => 'de',
            'form' => $form->slug,
        ]));

        $response
            ->assertOk()
            ->assertSee('id="form-language"', false)
            ->assertSee('selected', false)
            ->assertSee('Anfrage');

        foreach (['it', 'en', 'de'] as $language) {
            $response->assertSee(route('marketing.forms.public', [
                'language' => $language,
                'form' => $form->slug,
            ]));
        }
    }

    public function test_default_booking_form_seeder_is_idempotent(): void
    {
        $this->seed(MarketingBookingFormSeeder::class);
        $this->seed(MarketingBookingFormSeeder::class);

        $form = MarketingForm::query()->where('slug', 'booking')->firstOrFail();

        $this->assertSame('booking', $form->type);
        $this->assertSame(['it', 'en', 'de'], $form->enabled_languages);
        $this->assertSame('Prenota un tavolo', $form->translations['it']['title']);
        $this->assertTrue($form->is_active);
        $this->assertCount(7, $form->fields);
        $this->assertSame(1, MarketingForm::query()->where('slug', 'booking')->count());
        $this->assertStringEndsWith('/form/it/booking', route('marketing.forms.public', [
            'language' => 'it',
            'form' => $form->slug,
        ]));
    }

    public function test_job_application_form_seeder_creates_cv_form_once(): void
    {
        $this->seed(JobApplicationFormSeeder::class);
        $this->seed(JobApplicationFormSeeder::class);

        $form = MarketingForm::query()->where('slug', 'job-application')->firstOrFail();

        $this->assertSame('generic', $form->type);
        $this->assertTrue($form->fields()->where('key', 'cv')->where('type', 'file')->where('required', true)->exists());
        $this->assertTrue($form->fields()->where('key', 'privacy_consent')->where('required', true)->exists());
        $this->assertSame(1, MarketingForm::query()->where('slug', 'job-application')->count());
    }

    public function test_catering_order_form_seeder_creates_request_fields_once(): void
    {
        $this->seed(CateringOrderFormSeeder::class);
        $this->seed(CateringOrderFormSeeder::class);

        $form = MarketingForm::query()->where('slug', 'catering-order')->firstOrFail();

        $this->assertSame('generic', $form->type);
        $this->assertTrue($form->fields()->where('key', 'event_date')->where('required', true)->exists());
        $this->assertTrue($form->fields()->where('key', 'guests')->where('type', 'number')->exists());
        $this->assertSame(['Consegna', 'Buffet', 'Servizio al tavolo'], $form->fields()->where('key', 'service_type')->firstOrFail()->options['it']);
        $this->assertSame(1, MarketingForm::query()->where('slug', 'catering-order')->count());
    }
}
