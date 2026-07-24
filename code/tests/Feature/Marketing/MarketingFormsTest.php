<?php

namespace Tests\Feature\Marketing;

use App\Livewire\Marketing\Forms\FormCreate;
use App\Livewire\Marketing\Forms\FormEdit;
use App\Livewire\Marketing\Forms\FormStyleEdit;
use App\Livewire\Marketing\Forms\PublicForm;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MarketingForm;
use App\Models\MessageOutbox;
use App\Models\User;
use App\Services\MarketingForms\FormBlueprintService;
use App\Services\Personnel\PersonnelPermissionsService;
use App\Services\Settings\TenantSettingsService;
use Database\Seeders\CateringOrderFormSeeder;
use Database\Seeders\JobApplicationFormSeeder;
use Database\Seeders\MarketingBookingFormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        return [['booking', 9], ['event', 9], ['generic', 4]];
    }

    public function test_create_form_enables_configured_customer_languages_by_default_and_renders_tabbed_panels(): void
    {
        Livewire::test(FormCreate::class)
            ->assertSet('multilingual', true)
            ->assertSet('translations.it', ['title' => '', 'description' => ''])
            ->assertSet('translations.en', ['title' => '', 'description' => ''])
            ->assertSet('translations.de', ['title' => '', 'description' => ''])
            ->assertSeeHtml('role="tablist"')
            ->assertSeeHtml("x-show=\"activeLanguage === 'it'\"")
            ->assertSeeHtml("x-show=\"activeLanguage === 'en'\"")
            ->assertSeeHtml("x-show=\"activeLanguage === 'de'\"");
    }

    public function test_form_style_can_override_tenant_defaults_and_validates_background_dimensions(): void
    {
        Storage::fake('local');
        config()->set('tenant.slug', 'ristorante-test');
        File::deleteDirectory(public_path('marketing-assets/ristorante-test'));
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic', 'slug' => 'styled-form',
            'translations' => ['it' => ['title' => 'Form stilato']],
            'enabled_languages' => ['it'], 'is_active' => true,
        ]);

        Livewire::test(FormStyleEdit::class, ['form' => $form])
            ->set('style.link', '#ff0000')
            ->set('style.background_overlay', 45)
            ->set('backgroundImage', UploadedFile::fake()->image('small.jpg', 800, 600))
            ->call('save')
            ->assertHasErrors(['backgroundImage'])
            ->set('backgroundImage', UploadedFile::fake()->image('background.jpg', 1920, 1080))
            ->call('save')
            ->assertHasNoErrors();

        $form->refresh();
        $this->assertSame('#ff0000', $form->style_settings['link']);
        $this->assertSame(45, $form->style_settings['background_overlay']);
        Storage::disk('local')->assertExists($form->image_path);
        $publicBackground = public_path("marketing-assets/ristorante-test/forms/{$form->id}/background.jpg");
        $this->assertFileExists($publicBackground);

        $this->get(route('marketing.forms.public', ['language' => 'it', 'form' => $form->slug]))
            ->assertOk()
            ->assertSee('--tenant-form-link: #ff0000', false)
            ->assertSee("/marketing-assets/ristorante-test/forms/{$form->id}/background.jpg", false)
            ->assertSee('tenant-public-form-header', false)
            ->assertSee('tenant-public-form-footer', false);

        File::deleteDirectory(public_path('marketing-assets/ristorante-test'));
    }

    public function test_event_form_schedule_can_define_dates_guest_limits_and_slot_capacity(): void
    {
        $date = now()->addWeek()->toDateString();
        $form = app(FormBlueprintService::class)->create([
            'type' => 'event',
            'slug' => 'special-event',
            'translations' => ['it' => ['title' => 'Evento speciale']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);

        Livewire::test(FormEdit::class, ['form' => $form])
            ->set('eventSchedule.mode', 'dates')
            ->set('eventSchedule.slot_mode', 'custom')
            ->set('eventSchedule.dates', [$date])
            ->set('eventSchedule.min_guests', 2)
            ->set('eventSchedule.max_guests', 6)
            ->set('eventSchedule.slots', [['time' => '20:30', 'capacity' => 8]])
            ->call('saveEventSchedule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('marketing_forms', ['id' => $form->id]);
        $schedule = $form->fresh()->schedule;
        $this->assertSame('dates', $schedule['mode']);
        $this->assertSame('custom', $schedule['slot_mode']);
        $this->assertSame([$date], $schedule['dates']);
        $this->assertSame(2, $schedule['min_guests']);
        $this->assertSame(6, $schedule['max_guests']);
        $this->assertSame([['time' => '20:30', 'capacity' => 8]], $schedule['slots']);

        Livewire::test(PublicForm::class, ['form' => $form->fresh(), 'language' => 'it'])
            ->set('answers.date', $date)
            ->set('answers.guests', 2)
            ->assertSeeHtml('<option value="20:30">20:30</option>');
    }

    public function test_event_form_can_use_standard_booking_availability(): void
    {
        $date = now()->addWeek()->toDateString();
        $form = app(FormBlueprintService::class)->create([
            'type' => 'event',
            'slug' => 'standard-event',
            'translations' => ['it' => ['title' => 'Evento con orari standard']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);

        Livewire::test(FormEdit::class, ['form' => $form])
            ->set('eventSchedule.mode', 'standard')
            ->call('saveEventSchedule')
            ->assertHasNoErrors();

        $this->assertSame('standard', $form->fresh()->schedule['mode']);

        $this->mock(\App\Services\BookingService::class, function ($mock): void {
            $mock->shouldReceive('availableSlots')->zeroOrMoreTimes()->andReturn([
                '20:30' => ['label' => '20:30', 'meal' => 'cena'],
            ]);
        });

        Livewire::test(PublicForm::class, ['form' => $form->fresh(), 'language' => 'it'])
            ->set('answers.date', $date)
            ->set('answers.guests', 2)
            ->assertSeeHtml('<option value="20:30">20:30</option>');
    }

    public function test_event_with_custom_dates_uses_standard_slots_by_default(): void
    {
        $date = now()->addWeek()->toDateString();
        $form = app(FormBlueprintService::class)->create([
            'type' => 'event',
            'slug' => 'custom-date-standard-slots',
            'translations' => ['it' => ['title' => 'Evento']],
            'enabled_languages' => ['it'],
            'schedule' => [
                'mode' => 'dates',
                'slot_mode' => 'standard',
                'dates' => [$date],
                'min_guests' => 1,
                'max_guests' => 10,
            ],
            'is_active' => true,
        ]);

        $this->mock(\App\Services\BookingService::class, function ($mock): void {
            $mock->shouldReceive('availableSlots')->zeroOrMoreTimes()->andReturn([
                '19:30' => ['label' => '19:30', 'meal' => 'cena'],
            ]);
        });

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.date', $date)
            ->set('answers.guests', 2)
            ->assertSeeHtml('<option value="19:30">19:30</option>')
            ->assertDontSeeHtml('max="10"')
            ->set('answers.date', now()->addWeeks(2)->toDateString())
            ->assertDontSeeHtml('<option value="19:30">19:30</option>');
    }

    public function test_event_flatpickr_only_enables_the_configured_dates(): void
    {
        $dates = [now()->addWeek()->toDateString(), now()->addWeeks(2)->toDateString()];
        $form = app(FormBlueprintService::class)->create([
            'type' => 'event',
            'slug' => 'event-datepicker',
            'translations' => ['it' => ['title' => 'Evento datepicker']],
            'enabled_languages' => ['it'],
            'schedule' => ['mode' => 'dates', 'slot_mode' => 'standard', 'dates' => $dates],
            'is_active' => true,
        ]);

        $this->get(route('marketing.forms.public', ['language' => 'it', 'form' => $form->slug]))
            ->assertOk()
            ->assertSee('data-marketing-datepicker="flatpickr"', false)
            ->assertSee('/assets/libs/flatpickr/flatpickr.min.js', false)
            ->assertSee('marketing-bookable-day', false)
            ->assertSee($dates[0])
            ->assertSee($dates[1]);
    }

    public function test_public_form_can_fall_back_to_the_native_datepicker(): void
    {
        config()->set('marketing_forms.datepicker', 'native');
        $form = app(FormBlueprintService::class)->create([
            'type' => 'event',
            'slug' => 'native-event-datepicker',
            'translations' => ['it' => ['title' => 'Evento native']],
            'enabled_languages' => ['it'],
            'schedule' => ['mode' => 'single', 'single_date' => now()->addWeek()->toDateString()],
            'is_active' => true,
        ]);

        $this->get(route('marketing.forms.public', ['language' => 'it', 'form' => $form->slug]))
            ->assertOk()
            ->assertSee('type="date"', false)
            ->assertSee('this.showPicker', false)
            ->assertDontSee('data-marketing-datepicker="flatpickr"', false);
    }

    public function test_locked_standard_field_cannot_be_hidden(): void
    {
        $service = app(FormBlueprintService::class);
        $form = $service->create(['type' => 'booking', 'slug' => 'booking', 'translations' => ['it' => ['title' => 'Test']], 'enabled_languages' => ['it']]);
        $this->expectException(\DomainException::class);
        $service->updateField($form, $form->fields->first()->id, ['visible' => false, 'required' => true]);
    }

    public function test_booking_policy_is_editable_as_localized_rich_content(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'booking',
            'slug' => 'booking-policy-form',
            'translations' => ['it' => ['title' => 'Prenota', 'description' => '']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);

        Livewire::test(FormEdit::class, ['form' => $form])
            ->assertSeeHtml('id="booking_policy_it"')
            ->set('translations.it.booking_policy', '<p><strong>Condizioni di prenotazione</strong></p>')
            ->call('saveDetails')
            ->assertHasNoErrors();

        $this->assertSame(
            '<p><strong>Condizioni di prenotazione</strong></p>',
            $form->fresh()->translations['it']['booking_policy'],
        );
    }

    public function test_generic_form_can_have_custom_fields_and_notification_users(): void
    {
        $user = User::factory()->create();
        $service = app(FormBlueprintService::class);
        $form = $service->create(['type' => 'generic', 'slug' => 'jobs', 'translations' => ['it' => ['title' => 'Lavora con noi']], 'enabled_languages' => ['it'], 'notify_user_ids' => [$user->id]]);
        $form->fields()->create(['key' => 'cv', 'type' => 'text', 'label' => ['it' => 'CV'], 'required' => true, 'visible' => true, 'position' => 3]);
        $this->assertCount(5, $form->fields()->get());
        $this->assertTrue($form->notificationUsers->contains($user));
    }

    public function test_public_form_validates_and_stores_submission(): void
    {
        $form = app(FormBlueprintService::class)->create(['type' => 'generic', 'slug' => 'catering', 'translations' => ['it' => ['title' => 'Catering']], 'enabled_languages' => ['it'], 'is_active' => true]);
        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->assertSee(route('privacy-policy', ['language' => 'it']))
            ->set('answers.name', 'Mario Rossi')
            ->set('answers.email', 'mario@example.test')
            ->set('answers.privacy_consent', true)
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertDontSee(route('privacy-policy', ['language' => 'it']));
        $this->assertDatabaseCount('marketing_form_submissions', 1);
    }

    public function test_public_form_hides_privacy_link_when_both_consent_fields_are_hidden(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => 'no-consent-link',
            'translations' => ['it' => ['title' => 'Richiesta']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);
        $form->fields()->whereIn('key', ['privacy_consent', 'marketing_consent'])->update(['visible' => false]);

        $this->get(route('marketing.forms.public', ['language' => 'it', 'form' => $form->slug]))
            ->assertOk()
            ->assertDontSee(route('privacy-policy', ['language' => 'it']));
    }

    public function test_public_phone_field_uses_country_validation_and_stores_e164(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => 'phone-request',
            'translations' => ['it' => ['title' => 'Telefono']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);
        $form->fields()->create([
            'key' => 'phone', 'type' => 'tel', 'label' => ['it' => 'Telefono'],
            'required' => true, 'visible' => true, 'position' => 4,
        ]);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->assertSeeHtml('autocomplete="tel-country-code"')
            ->set('answers.name', 'Mario Rossi')
            ->set('answers.email', 'mario@example.test')
            ->set('answers.privacy_consent', true)
            ->set('answers.phone_region', 'DE')
            ->set('answers.phone', '030 901820')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame('+4930901820', $form->submissions()->firstOrFail()->payload['phone']);

        Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.phone_region', 'IT')
            ->set('answers.phone', 'telefono abc')
            ->call('submit')
            ->assertHasErrors(['answers.phone']);
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
        $bookingPermission = Permission::findOrCreate(PersonnelPermissionsService::BOOKINGS, 'web');
        $operator = User::factory()->create([
            'name' => 'Booking Manager',
            'email' => 'booking-manager@example.test',
            'lang' => 'en',
            'enabled' => true,
        ]);
        $operator->givePermissionTo($bookingPermission);

        $form = app(FormBlueprintService::class)->create([
            'type' => 'booking',
            'slug' => 'booking',
            'translations' => ['it' => ['title' => 'Prenota', 'description' => 'Richiedi un tavolo', 'booking_policy' => '<p><strong>Presentarsi dieci minuti prima.</strong></p>']],
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
            ->set('answers.privacy_consent', true)
            ->set('answers.occasion', 'Compleanno')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertSeeHtml('<p><strong>Presentarsi dieci minuti prima.</strong></p>');

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

        $outboxes = MessageOutbox::query()->get()->keyBy(
            fn (MessageOutbox $outbox): string => $outbox->payload['message_case']
        );
        $this->assertCount(2, $outboxes);

        $customerOutbox = $outboxes->get('booking_sent');
        $this->assertNotNull($customerOutbox);
        $this->assertSame(MessageOutbox::STATUS_PENDING, $customerOutbox->status);
        $this->assertSame('customer', $customerOutbox->payload['audience']);
        $this->assertSame('mario@example.test', $customerOutbox->payload['deliveries'][0]['recipient']['email']);
        $this->assertSame('it', $customerOutbox->payload['deliveries'][0]['language']);

        $staffOutbox = $outboxes->get('booking_received');
        $this->assertNotNull($staffOutbox);
        $this->assertSame(MessageOutbox::STATUS_PENDING, $staffOutbox->status);
        $this->assertSame('staff', $staffOutbox->payload['audience']);
        $this->assertSame('booking-manager@example.test', $staffOutbox->payload['deliveries'][0]['recipient']['email']);
        $this->assertSame('en', $staffOutbox->payload['deliveries'][0]['language']);
    }

    public function test_public_booking_form_only_lists_slots_with_enough_remaining_capacity(): void
    {
        $date = now()->addWeek()->toDateString();
        $day = array_keys(TenantSettingsService::DAYS)[now()->addWeek()->dayOfWeekIso - 1];
        $settingsService = app(TenantSettingsService::class);
        $reservations = $settingsService->settings()['reservations'];
        $reservations['opening_hours']['timerange'] = 30;
        $reservations['opening_hours']['weekly'][$day]['pranzo']['open'] = false;
        $reservations['opening_hours']['weekly'][$day]['cena'] = [
            'open' => true, 'start' => '20:00', 'end' => '21:00',
        ];
        $reservations['pax_capacity']['fallback'] = 4;
        $reservations['pax_capacity']['weekly'][$day]['cena'] = [4, 8];
        $settingsService->updateSection('reservations', $reservations);

        $customer = Customer::create([
            'firstname' => 'Mario', 'display_name' => 'Mario', 'email' => 'full@example.test',
            'registration_source' => 'backoffice', 'lang' => 'it',
        ]);
        Booking::create([
            'customer_id' => $customer->id, 'booking_date' => $date, 'booking_time' => '20:00',
            'pax' => 4, 'status' => 'accepted', 'source' => 'backoffice', 'language' => 'it',
        ]);
        Booking::create([
            'customer_id' => $customer->id, 'booking_date' => $date, 'booking_time' => '20:30',
            'pax' => 4, 'status' => 'accepted', 'source' => 'backoffice', 'language' => 'it',
        ]);
        $form = app(FormBlueprintService::class)->create([
            'type' => 'booking', 'slug' => 'capacity-booking',
            'translations' => ['it' => ['title' => 'Prenota']],
            'enabled_languages' => ['it'], 'is_active' => true,
        ]);

        $component = Livewire::test(PublicForm::class, ['form' => $form, 'language' => 'it'])
            ->set('answers.date', $date)
            ->set('answers.guests', 1)
            ->assertDontSeeHtml('<option value="20:00">')
            ->assertSeeHtml('<option value="20:30">');

        $component
            ->set('answers.guests', 5)
            ->assertDontSeeHtml('<option value="20:30">')
            ->set('answers.guests', 4)
            ->assertSeeHtml('<option value="20:30">');

        try {
            app(\App\Services\BookingService::class)->ensureCapacity($date, '20:30', 5);
            $this->fail('Il controllo server-side avrebbe dovuto rifiutare lo slot.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('pax', $exception->errors());
        }
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
            ->assertSee(route('privacy-policy', ['language' => 'it']))
            ->assertSee('required', false);
    }

    public function test_public_form_privacy_policy_link_uses_the_customer_language(): void
    {
        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => 'multilingual-privacy-request',
            'translations' => [
                'it' => ['title' => 'Richiesta privacy'],
                'en' => ['title' => 'Privacy request'],
                'de' => ['title' => 'Datenschutzanfrage'],
            ],
            'enabled_languages' => ['it', 'en', 'de'],
            'is_active' => true,
        ]);

        foreach (['it', 'en', 'de'] as $language) {
            $this->get(route('marketing.forms.public', ['language' => $language, 'form' => $form->slug]))
                ->assertOk()
                ->assertSee(route('privacy-policy', ['language' => $language]))
                ->assertSee('target="_blank"', false);
        }
    }

    #[DataProvider('privacyPolicyLanguages')]
    public function test_privacy_policy_route_renders_each_available_language(string $language, string $heading): void
    {
        $this->assertFileExists(resource_path("views/legal/privacy-policy-{$language}.blade.php"));
        $this->assertTrue(view()->exists("legal.privacy-policy-{$language}"));

        $this->get(route('privacy-policy', ['language' => $language]))
            ->assertOk()
            ->assertSee($heading)
            ->assertSee('fixed inset-x-0 top-0', false)
            ->assertSee('id="terms-scroll"', false)
            ->assertSee('overflow-y-auto bg-white', false)
            ->assertSee('onclick="window.close()"', false);
    }

    public static function privacyPolicyLanguages(): array
    {
        return [
            'Italian' => ['it', 'INFORMATIVA SUL TRATTAMENTO DEI DATI PERSONALI'],
            'English' => ['en', 'INFORMATION NOTICE ON THE PROCESSING OF PERSONAL DATA'],
            'German' => ['de', 'INFORMATIONSHINWEIS ZUR VERARBEITUNG PERSONENBEZOGENER DATEN'],
        ];
    }

    public function test_privacy_policy_route_falls_back_to_italian_for_an_unsupported_language(): void
    {
        $this->get(route('privacy-policy', ['language' => 'fr']))
            ->assertOk()
            ->assertSee('INFORMATIVA SUL TRATTAMENTO DEI DATI PERSONALI')
            ->assertDontSee('INFORMATION NOTICE ON THE PROCESSING OF PERSONAL DATA');
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
        $form->fields()->create([
            'key' => 'event_date', 'type' => 'date', 'label' => ['de' => 'Datum'],
            'required' => true, 'visible' => true, 'position' => 3,
        ]);
        $form->fields()->create([
            'key' => 'event_time', 'type' => 'time', 'label' => ['de' => 'Uhrzeit'],
            'required' => true, 'visible' => true, 'position' => 4,
        ]);

        $response = $this->get(route('marketing.forms.public', [
            'language' => 'de',
            'form' => $form->slug,
        ]));

        $response
            ->assertOk()
            ->assertSee('id="form-language"', false)
            ->assertSee('selected', false)
            ->assertSee('this.showPicker', false)
            ->assertSee('dark:[color-scheme:dark]', false)
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
        $this->assertCount(9, $form->fields);
        $this->assertTrue($form->fields()->where('key', 'privacy_consent')->where('required', true)->where('visible', true)->where('locked', true)->exists());
        $this->assertTrue($form->fields()->where('key', 'marketing_consent')->where('required', false)->where('visible', true)->where('locked', false)->exists());
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
