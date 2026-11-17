<?php

namespace Tests\Feature\Marketing;

use App\Livewire\Marketing\Coupons\CompleteCoupon;
use App\Livewire\Marketing\Coupons\CouponEdit;
use App\Livewire\Marketing\Coupons\PublicCoupon;
use App\Models\Coupon;
use App\Models\CouponAssignment;
use App\Models\CouponClaim;
use App\Models\Customer;
use App\Models\MessageOutbox;
use App\Models\User;
use App\Services\Marketing\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CouponClaimFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenant.customer_languages' => 'it,en,de']);
    }

    private function coupon(): Coupon
    {
        $fields = CouponService::fields();
        $fields[] = ['key' => 'drink', 'type' => 'select', 'label' => ['en' => 'Favourite drink'], 'required' => true, 'visible' => true, 'options' => ['en' => ['Beer', 'Wine'], 'it' => ['Birra', 'Vino']]];

        return Coupon::create([
            'name' => 'Friends', 'slug' => (string) Str::uuid(), 'enabled_languages' => ['it', 'en'],
            'translations' => ['en' => ['title' => 'Friends', 'description' => '<p>Offer</p>', 'terms' => ''], 'it' => ['title' => 'Amici', 'description' => '<p>Offerta</p>', 'terms' => '']],
            'rules' => ['min_pax' => 1, 'max_uses' => 1, 'weekdays' => []],
            'benefit' => ['type' => 'percent', 'value' => 1000], 'fields' => $fields, 'is_active' => true,
        ]);
    }

    private function customer(array $data = []): Customer
    {
        return Customer::create($data + ['firstname' => 'Existing', 'lastname' => 'Customer', 'display_name' => 'Existing Customer', 'email' => 'existing@example.test', 'phone' => '+393331234567', 'lang' => 'en', 'registration_source' => 'test', 'consent_marketing' => true]);
    }

    private function requestCoupon(Coupon $coupon, array $answers = []): array
    {
        $component = Livewire::test(PublicCoupon::class, ['coupon' => $coupon, 'language' => 'en'])
            ->set('answers', $answers + ['name' => 'New Visitor', 'email' => 'new@example.test', 'privacy_consent' => true])
            ->call('submit')->assertHasNoErrors()->assertSet('sent', true);
        $outbox = MessageOutbox::where('deduplication_key', 'like', 'coupon-claim:%')->latest('created_at')->firstOrFail();
        $token = basename(parse_url($outbox->payload['url'], PHP_URL_PATH));

        return [$token, $outbox, $component];
    }

    public function test_initial_request_only_collects_minimal_data_and_final_form_assigns_after_validation(): void
    {
        $coupon = $this->coupon();
        [$token] = $this->requestCoupon($coupon);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('coupon_assignments', 0);
        $this->get(route('coupons.verify', ['language' => 'en', 'token' => $token]))->assertOk()->assertSee('Favourite drink');
        $complete = Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en'])
            ->assertSet('answers.name', 'New Visitor')->assertSet('answers.email', 'new@example.test')
            ->assertSet('answers.drink', '')->call('complete')->assertHasErrors(['answers.drink', 'answers.privacy_consent']);
        $this->assertDatabaseCount('coupon_assignments', 0);
        $complete->set('answers.drink', 'Not an allowed option')->set('answers.privacy_consent', true)
            ->call('complete')->assertHasErrors('answers.drink');
        $complete->set('answers.drink', 'Beer')->set('answers.privacy_consent', true)
            ->set('answers.customer_id', 'forged-customer')->set('answers._profile_required', false)
            ->call('complete')->assertHasNoErrors()->assertRedirect();
        $assignment = CouponAssignment::sole();
        $this->assertSame('Beer', $assignment->profile['drink']);
        $this->assertArrayNotHasKey('customer_id', $assignment->profile);
        $this->assertArrayNotHasKey('_profile_required', $assignment->profile);
        $this->assertSame('new@example.test', $assignment->customer->email);
        app(CouponService::class)->confirm($token);
        $this->assertDatabaseCount('coupon_assignments', 1);
    }

    public function test_known_email_prefills_only_after_token_and_does_not_infer_consent(): void
    {
        $customer = $this->customer();
        $coupon = $this->coupon();
        [$token, , $request] = $this->requestCoupon($coupon, ['name' => 'Submitted name', 'email' => $customer->email]);
        $request->assertDontSee('Existing Customer');
        $this->assertTrue((bool) $customer->fresh()->consent_marketing);
        Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en'])
            ->assertSet('answers.name', 'Existing')->assertSet('answers.lastname', 'Customer')
            ->assertSet('answers.phone', $customer->phone)->assertSet('answers.email', $customer->email)
            ->assertSet('answers.privacy_consent', false)->assertSet('answers.marketing_consent', false);
    }

    public function test_known_customer_custom_fields_are_prefilled_only_when_configured_visible(): void
    {
        $customer = $this->customer();
        $coupon = $this->coupon();
        $fields = $coupon->fields;
        $fields[] = ['key' => 'hidden_note', 'type' => 'text', 'required' => false, 'visible' => false, 'label' => [], 'options' => []];
        $coupon->update(['fields' => $fields]);
        CouponAssignment::create([
            'coupon_id' => $coupon->id, 'customer_id' => $customer->id,
            'token_hash' => hash('sha256', Str::random(64)), 'access_token' => Str::random(64),
            'snapshot' => $coupon->snapshot(), 'language' => 'en',
            'profile' => ['drink' => 'Wine', 'hidden_note' => 'PRIVATE HIDDEN NOTE'],
        ]);
        [$token] = $this->requestCoupon($coupon, ['email' => $customer->email]);
        Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en'])
            ->assertSet('answers.drink', 'Wine')->assertDontSee('PRIVATE HIDDEN NOTE')
            ->assertSet('answers', fn ($answers) => ! array_key_exists('hidden_note', $answers));
    }

    public function test_phone_only_match_sends_token_to_the_existing_email_without_disclosing_profile_to_requester(): void
    {
        $customer = $this->customer();
        [$token, $outbox, $request] = $this->requestCoupon($this->coupon(), ['email' => 'different@example.test', 'phone' => $customer->phone]);
        $this->assertSame($customer->email, $outbox->payload['email']);
        $request->assertDontSee($customer->email)->assertDontSee('Existing Customer');
        $this->assertSame($customer->email, CouponClaim::sole()->identity['email']);
        Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en'])
            ->assertSet('answers.name', 'Existing')->assertSet('answers.email', $customer->email);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_link_parameters_cannot_choose_another_customer_and_email_cannot_be_changed_at_completion(): void
    {
        $victim = $this->customer();
        [$token] = $this->requestCoupon($this->coupon());
        $url = route('coupons.verify', ['language' => 'en', 'token' => $token, 'email' => $victim->email, 'phone' => $victim->phone, 'customer_id' => $victim->id, 'answers' => ['email' => $victim->email, 'name' => 'FORGED']]);
        $this->get($url)->assertOk()->assertDontSee($victim->email)->assertDontSee('FORGED');
        Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en'])
            ->set('answers.drink', 'Beer')->set('answers.privacy_consent', true)
            ->set('answers.email', $victim->email)->call('complete')->assertHasErrors('answers.email');
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token]), ['answers' => ['name' => 'Attacker', 'email' => $victim->email, 'drink' => 'Beer', 'privacy_consent' => true]])
            ->assertSessionHasErrors('answers.email');
        $this->assertDatabaseCount('coupon_assignments', 0);
        $this->assertSame('Existing', $victim->fresh()->firstname);
    }

    public function test_email_identity_wins_over_an_unverified_phone_belonging_to_someone_else(): void
    {
        $victim = $this->customer();
        $owner = $this->customer(['email' => 'owner@example.test', 'phone' => '+393331234568', 'firstname' => 'Owner']);
        [$token, $outbox] = $this->requestCoupon($this->coupon(), ['email' => $owner->email, 'phone' => $victim->phone]);
        $this->assertSame($owner->email, $outbox->payload['email']);
        Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en'])
            ->assertSet('answers.name', 'Owner')->assertSet('answers.phone', $owner->phone);
    }

    public function test_final_post_does_not_accept_answers_from_the_query_string_or_skip_the_profile(): void
    {
        [$token] = $this->requestCoupon($this->coupon());
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token, 'answers' => ['name' => 'Forged', 'email' => 'new@example.test', 'privacy_consent' => true, 'drink' => 'Beer']]))
            ->assertSessionHasErrors(['answers.name', 'answers.privacy_consent']);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('coupon_assignments', 0);
    }

    public function test_invalid_or_expired_tokens_cannot_open_the_profile(): void
    {
        $this->get(route('coupons.verify', ['language' => 'en', 'token' => Str::random(64)]))->assertNotFound();
        [$token] = $this->requestCoupon($this->coupon());
        CouponClaim::query()->update(['expires_at' => now()->subMinute()]);
        $this->get(route('coupons.verify', ['language' => 'en', 'token' => $token]))->assertStatus(410);
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token]))->assertStatus(410);
        $this->assertDatabaseCount('coupon_assignments', 0);
    }

    public function test_completion_token_is_locked_against_livewire_tampering(): void
    {
        [$token] = $this->requestCoupon($this->coupon());
        $component = Livewire::test(CompleteCoupon::class, ['token' => $token, 'language' => 'en']);
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        $component->set('token', Str::random(64));
    }

    public function test_coupon_editor_separates_fields_and_supports_add_edit_remove_without_changing_base_identity(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create(['lang' => 'en']);
        $admin->assignRole('admin');
        app()->setLocale('en');
        $edit = Livewire::actingAs($admin)->test(CouponEdit::class)->assertSee('Base fields')->assertSee('Custom fields')->assertSee('Add custom field');
        $edit->set('newField.key', 'email')->call('addField')->assertHasErrors('newField.key');
        $edit->set('newField.key', 'preference')->set('newField.label.en', 'Preference')->call('addField')->assertHasNoErrors();
        $edit->assertSet('data.fields.9.key', 'preference')->call('startEditingField', 9)
            ->set('editingField.label.en', 'Updated preference')->call('saveEditingField')
            ->assertSet('data.fields.9.label.en', 'Updated preference');
        $edit->call('removeField', 0)->assertSet('data.fields.0.key', 'name');
        $edit->call('removeField', 9)->assertSet('data.fields', CouponService::fields());
    }
}
