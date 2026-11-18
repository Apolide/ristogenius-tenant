<?php

namespace Tests\Feature\Marketing;

use App\Jobs\Marketing\SendCampaignEmail;
use App\Jobs\Marketing\SendConfirmation;
use App\Livewire\Marketing\Campaigns\CampaignEdit;
use App\Livewire\Marketing\Campaigns\CampaignSend;
use App\Livewire\Marketing\Coupons\CouponEdit;
use App\Livewire\Marketing\Coupons\PublicCoupon;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\CouponAssignment;
use App\Models\CouponClaim;
use App\Models\Customer;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuProduct;
use App\Models\MarketingCampaign;
use App\Models\MessageOutbox;
use App\Models\Order;
use App\Models\User;
use App\Services\Marketing\CampaignAudience;
use App\Services\Marketing\CampaignService;
use App\Services\Marketing\CouponPricing;
use App\Services\Marketing\CouponService;
use App\Services\Marketing\CustomerIdentity;
use App\Services\Marketing\MarketingContent;
use App\Services\Marketing\MarketingMailer;
use App\Services\MarketingForms\FormBlueprintService;
use App\Services\MarketingForms\PublicFormSubmissionService;
use App\Services\Orders\CashBillPricingService;
use App\Services\Orders\CashTotalsService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CampaignsAndCouponsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenant.customer_languages' => 'it,en,de']);
        Mail::fake();
        Queue::fake();
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create(['lang' => 'en']);
        $user->assignRole('admin');
        $this->actingAs($user);
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
    }

    private function customer(array $values = []): Customer
    {
        return Customer::create($values + ['firstname' => 'Ada', 'display_name' => 'Ada', 'email' => Str::uuid().'@example.com', 'registration_source' => 'manual', 'lang' => 'en', 'consent_marketing' => true, 'blacklisted' => false]);
    }

    private function coupon(array $values = []): Coupon
    {
        $translations = [];
        foreach (['it' => 'Offerta famiglia', 'en' => 'Family offer', 'de' => 'Familienangebot'] as $l => $title) {
            $translations[$l] = ['title' => $title, 'description' => '<p>'.$title.'</p>', 'terms' => '<p>Terms</p>'];
        }

        return Coupon::create($values + ['name' => 'Family', 'slug' => (string) Str::uuid(), 'translations' => $translations, 'enabled_languages' => ['it', 'en', 'de'], 'rules' => ['weekdays' => [], 'min_pax' => 1, 'max_pax' => null, 'max_uses' => 1], 'benefit' => ['type' => 'percent', 'value' => 1000, 'quantity' => 1, 'quantity_basis' => 'fixed', 'product_ids' => []], 'fields' => CouponService::fields(), 'is_active' => true]);
    }

    private function assigned(?Coupon $coupon = null, ?Customer $customer = null): CouponAssignment
    {
        $coupon ??= $this->coupon();
        $customer ??= $this->customer();
        $token = Str::random(64);
        $assignment = CouponAssignment::create(['coupon_id' => $coupon->id, 'customer_id' => $customer->id, 'snapshot' => $coupon->snapshot(), 'language' => $customer->lang, 'token_hash' => hash('sha256', $token), 'access_token' => $token, 'profile' => []]);
        $coupon->increment('assigned_count');

        return $assignment;
    }

    private function booking(Customer $customer, array $values = []): Booking
    {
        return Booking::create($values + ['customer_id' => $customer->id, 'booking_date' => today(), 'booking_time' => '20:00', 'pax' => 3, 'status' => 'accepted', 'source' => 'manual', 'language' => 'en']);
    }

    private function proof(Coupon $coupon, string $email): string
    {
        app(CouponService::class)->request($coupon, ['name' => 'Ada Test', 'email' => $email, 'privacy_consent' => true, 'marketing_consent' => false], 'en');

        return basename(parse_url(MessageOutbox::latest('id')->first()->payload['url'], PHP_URL_PATH));
    }

    private function campaign(array $values = []): MarketingCampaign
    {
        return MarketingCampaign::create($values + ['name' => 'Dinner', 'type' => 'link', 'translations' => ['en' => ['subject' => 'Dinner offer', 'html' => '<p>Come for dinner</p>', 'button' => 'Discover', 'preheader' => 'Tonight']], 'enabled_languages' => ['en'], 'target_url' => 'https://ristogenius.com/', 'image_path' => null]);
    }

    private function campaignRun(?MarketingCampaign $campaign = null): \App\Models\CampaignRun
    {
        $campaign ??= $this->campaign();
        $campaign->recipients()->attach($this->customer());

        return app(CampaignService::class)->schedule($campaign, now()->subMinute());
    }

    #[DataProvider('managementRoutes')]
    public function test_management_pages_render_in_user_language(string $route): void
    {
        $this->get(route($route))->assertOk()->assertDontSee('marketing.create_coupon');
    }

    public static function managementRoutes(): array
    {
        return [['coupons.index'], ['coupons.create'], ['campaigns.index'], ['campaigns.create'], ['settings.marketing']];
    }

    public function test_edit_and_send_pages_render(): void
    {
        $coupon = $this->coupon();
        $campaign = $this->campaign();
        $this->get(route('coupons.edit', $coupon))->assertOk()->assertSee('Usage rules');
        $this->get(route('campaigns.edit', $campaign))->assertOk()->assertSee('Email subject');
        $this->get(route('campaigns.send', $campaign))->assertOk()->assertSee('Send test email');
    }

    public function test_unauthorized_users_cannot_manage_or_call_actions(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('campaigns.index'))->assertForbidden();
        Livewire::test(CouponEdit::class)->assertForbidden();
        $this->get(route('settings.marketing'))->assertForbidden();
    }

    public function test_email_proof_is_required_and_get_does_not_assign(): void
    {
        $coupon = $this->coupon();
        $token = $this->proof($coupon, 'proof@example.com');
        $this->assertDatabaseCount('coupon_assignments', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->get(route('coupons.verify', ['language' => 'en', 'token' => $token]))->assertOk()->assertSee('Confirm and claim coupon');
        $this->assertDatabaseCount('coupon_assignments', 0);
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token]))->assertRedirect();
        $this->assertDatabaseCount('coupon_assignments', 1);
        $this->assertSame(1, $coupon->fresh()->assigned_count);
        $this->assertFalse((bool) Customer::first()->consent_marketing);
        $this->assertStringNotContainsString('proof@example.com', DB::table('coupon_claims')->value('identity'));
        $this->assertStringNotContainsString('proof@example.com', DB::table('coupon_assignments')->value('profile'));
    }

    public function test_confirming_twice_assigns_once_and_keeps_same_access(): void
    {
        $coupon = $this->coupon();
        $token = $this->proof($coupon, 'once@example.com');
        $first = app(CouponService::class)->confirm($token);
        $second = app(CouponService::class)->confirm($token);
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('coupon_assignments', 1);
        $this->assertSame(1, $coupon->fresh()->assigned_count);
    }

    public function test_existing_owner_can_retrieve_coupon_after_pool_is_full(): void
    {
        $coupon = $this->coupon(['max_assignments' => 1]);
        $token = $this->proof($coupon, 'owner@example.com');
        $first = app(CouponService::class)->confirm($token);
        $second = app(CouponService::class)->confirm($this->proof($coupon, 'owner@example.com'));
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, $coupon->fresh()->assigned_count);
        $otherToken = $this->proof($coupon, 'other@example.com');
        try {
            app(CouponService::class)->confirm($otherToken);
            $this->fail('quota');
        } catch (ValidationException) {
            $this->assertDatabaseCount('customers', 1);
        }
    }

    public function test_expired_proof_cannot_assign(): void
    {
        $coupon = $this->coupon();
        $token = $this->proof($coupon, 'late@example.com');
        CouponClaim::query()->update(['expires_at' => now()->subSecond()]);
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token]))->assertStatus(410);
        $this->assertDatabaseCount('coupon_assignments', 0);
    }

    public function test_conflicting_phone_does_not_take_over_an_existing_customer(): void
    {
        $old = $this->customer(['phone' => '+393331234567']);
        $this->expectException(ValidationException::class);
        app(CustomerIdentity::class)->resolve(['email' => 'other@example.com', 'phone' => $old->phone, 'name' => 'Other'], 'en', 'coupon');
    }

    public function test_assignment_uses_immutable_offer_snapshot(): void
    {
        $coupon = $this->coupon();
        $a = $this->assigned($coupon);
        $coupon->update(['benefit' => ['type' => 'amount', 'value' => 9900], 'rules' => ['min_pax' => 100]]);
        $this->assertSame('percent', $a->fresh()->snapshot['benefit']['type']);
        app(CouponService::class)->consume($a, $this->booking($a->customer));
        $this->assertSame(1, $a->fresh()->uses_count);
    }

    public function test_consumption_is_idempotent_per_booking(): void
    {
        $a = $this->assigned();
        $b = $this->booking($a->customer);
        $service = app(CouponService::class);
        $service->consume($a, $b);
        $service->consume($a, $b);
        $this->assertDatabaseCount('coupon_usages', 1);
        $this->assertSame(1, $a->fresh()->uses_count);
        $this->assertSame(1, $a->coupon->fresh()->uses_count);
    }

    #[DataProvider('terminalStatuses')]
    public function test_cancellation_and_no_show_do_not_restore_use(string $status): void
    {
        $a = $this->assigned();
        $b = $this->booking($a->customer);
        app(CouponService::class)->consume($a, $b);
        $b->update(['status' => $status]);
        $this->assertSame(1, $a->fresh()->uses_count);
        $this->expectException(ValidationException::class);
        app(CouponService::class)->consume($a, $this->booking($a->customer));
    }

    public static function terminalStatuses(): array
    {
        return [['canceled'], ['no-show']];
    }

    public function test_multiple_personal_offers_cannot_stack_on_one_booking(): void
    {
        $customer = $this->customer();
        $a = $this->assigned(null, $customer);
        $other = $this->assigned(null, $customer);
        $b = $this->booking($customer);
        app(CouponService::class)->consume($a, $b);
        $this->expectException(ValidationException::class);
        app(CouponService::class)->consume($other, $b);
    }

    public function test_reusable_coupon_and_total_cap(): void
    {
        $c = $this->coupon(['rules' => ['weekdays' => [], 'min_pax' => 1, 'max_uses' => null], 'max_total_uses' => 2]);
        $a = $this->assigned($c);
        $service = app(CouponService::class);
        $service->consume($a, $this->booking($a->customer));
        $service->consume($a, $this->booking($a->customer));
        $this->assertSame(2, $a->fresh()->uses_count);
        $this->expectException(ValidationException::class);
        $service->consume($a, $this->booking($a->customer));
    }

    #[DataProvider('invalidVisits')]
    public function test_visit_rules_reject_invalid_dates_days_and_party_size(array $values, string $date, int $pax): void
    {
        $a = $this->assigned($this->coupon($values));
        $this->expectException(ValidationException::class);
        app(CouponService::class)->eligible($a, $date, $pax);
    }

    public static function invalidVisits(): array
    {
        return [
            [['valid_from' => '2027-01-01'], '2026-12-31', 3],
            [['valid_until' => '2027-01-01'], '2027-01-02', 3],
            [['rules' => ['weekdays' => [1, 2, 3, 4], 'min_pax' => 3]], '2027-01-01', 3],
            [['rules' => ['weekdays' => [], 'min_pax' => 3]], '2027-01-04', 2],
            [['rules' => ['weekdays' => [], 'min_pax' => 1, 'max_pax' => 4]], '2027-01-04', 5],
        ];
    }

    public function test_coupon_booking_cannot_be_changed_to_ineligible_visit(): void
    {
        $a = $this->assigned($this->coupon(['rules' => ['min_pax' => 3, 'weekdays' => [], 'max_uses' => 1]]));
        $b = $this->booking($a->customer);
        app(CouponService::class)->consume($a, $b);
        $this->expectException(ValidationException::class);
        $b->update(['pax' => 2]);
    }

    public function test_public_coupon_card_and_download_use_selected_language_and_no_pii(): void
    {
        $a = $this->assigned();
        $this->get($a->url('de'))->assertOk()->assertSee('Familienangebot')->assertHeader('Referrer-Policy', 'no-referrer');
        $this->get(route('coupons.image', ['language' => 'en', 'token' => $a->access_token, 'download' => 1]))->assertOk()->assertHeader('Content-Type', 'image/svg+xml')->assertSee('Family offer');
        $this->assertStringNotContainsString($a->customer->email, $a->url());
        $this->get(route('coupons.card', ['language' => 'en', 'token' => Str::random(64)]))->assertNotFound();
    }

    public function test_public_claim_component_validates_privacy_and_email(): void
    {
        Livewire::test(PublicCoupon::class, ['coupon' => $this->coupon(), 'language' => 'en'])->set('answers.name', 'Ada')->set('answers.email', 'invalid')->call('submit')->assertHasErrors(['answers.email', 'answers.privacy_consent']);
    }

    public function test_public_booking_with_coupon_is_atomic_and_idempotent(): void
    {
        $form = app(FormBlueprintService::class)->create(['type' => 'booking', 'slug' => 'booking-test', 'translations' => ['en' => ['title' => 'Book']], 'enabled_languages' => ['en'], 'is_active' => true, 'accepts_coupons' => true]);
        $a = $this->assigned($this->coupon(['marketing_form_id' => $form->id]));
        $this->partialMock(\App\Services\BookingService::class)->shouldReceive('ensureCapacity')->once()->andReturnNull();
        $service = app(PublicFormSubmissionService::class);
        $key = (string) Str::uuid();
        $payload = ['name' => 'Untrusted', 'email' => 'untrusted@example.com', 'date' => today()->toDateString(), 'time' => '20:00', 'guests' => 3];
        $first = $service->submit($form, $payload, 'en', $a, $key);
        $again = $service->submit($form, $payload, 'en', $a, $key);
        $this->assertSame($a->customer_id, $first->customer_id);
        $this->assertSame($first->id, $again->id);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame(1, $a->fresh()->uses_count);
        $this->get($a->bookingUrl())->assertOk()->assertSee('Family offer');
    }

    private function priced(array $benefit): array
    {
        $product = DigitalMenuProduct::create(['name' => ['en' => 'Pizza'], 'price' => '10.00', 'vat' => 10, 'is_active' => true]);
        $a = $this->assigned($this->coupon(['benefit' => $benefit + ['quantity_basis' => 'fixed', 'quantity' => 1, 'product_ids' => [$product->id]]]));
        $b = $this->booking($a->customer);
        app(CouponService::class)->consume($a, $b, ['children' => 2]);
        $order = Order::create(['booking_id' => $b->id, 'service_date' => today(), 'fulfillment' => 'dine_in', 'covers' => 3, 'cover_price_cents' => 200]);
        $course = $order->courses()->create(['number' => 1]);
        $item = $course->items()->create(['product_id' => $product->id, 'product_name' => 'Pizza', 'menu_name' => 'Menu', 'base_price_cents' => 1000, 'quantity' => 3, 'unit_price_cents' => 1000, 'vat' => 10]);

        return [$order, $item, $a];
    }

    #[DataProvider('benefits')]
    public function test_benefits_reconcile_cash_splits_and_exclude_covers(array $benefit, int $expected): void
    {
        [$order,$item] = $this->priced($benefit);
        $totals = app(CashTotalsService::class)->snapshot($order);
        $this->assertSame($expected, $totals['coupon_discount_cents']);
        $this->assertSame(3600 - $expected, $order->totalCents());
        $this->assertSame($order->totalCents(), array_sum(array_column($totals['taxes'], 'gross_cents')));
        $units = app(CashBillPricingService::class)->snapshot($order)['units'];
        $this->assertSame($order->totalCents(), array_sum(array_column($units, 'amount_cents')));
        $this->assertSame(1000, $item->fresh()->unit_price_cents);
    }

    public static function benefits(): array
    {
        return [
            [['type' => 'percent', 'value' => 1000], 300],
            [['type' => 'amount', 'value' => 100000], 3000],
            [['type' => 'fixed_price', 'value' => 2000, 'basis' => 'package'], 1000],
            [['type' => 'fixed_price', 'value' => 800, 'basis' => 'person'], 600],
            [['type' => 'fixed_price', 'value' => 900, 'basis' => 'item'], 300],
            [['type' => 'gift', 'quantity' => 1], 1000],
            [['type' => 'gift', 'quantity' => 1, 'quantity_basis' => 'children'], 2000],
            [['type' => 'percent', 'value' => 5000, 'max_discount' => 750], 750],
            [['type' => 'gift', 'quantity' => 2, 'max_discount' => 750], 750],
        ];
    }

    public function test_cash_coupon_can_be_removed_and_cannot_change_after_payment(): void
    {
        [$order] = $this->priced(['type' => 'percent', 'value' => 1000]);
        app(CouponPricing::class)->toggle($order);
        $this->assertSame(3600, $order->fresh()->totalCents());
        app(CouponPricing::class)->toggle($order->fresh());
        $this->assertSame(3300, $order->fresh()->totalCents());
        $order->courses()->update(['sent_at' => now()]);
        app(OrderService::class)->pay($order->id, 'cash', 3300, 100);
        $this->expectException(ValidationException::class);
        app(CouponPricing::class)->toggle($order->fresh());
    }

    public function test_coupon_does_not_stack_with_manual_discount(): void
    {
        [$order] = $this->priced(['type' => 'percent', 'value' => 1000]);
        $this->expectException(ValidationException::class);
        app(OrderService::class)->discount($order->id, 100, 'Extra', 3300);
    }

    public function test_all_matching_recipients_are_added_and_ineligible_customers_excluded(): void
    {
        $campaign = $this->campaign();
        for ($i = 0; $i < 21; $i++) {
            $this->customer();
        }$this->customer(['consent_marketing' => false]);
        $this->customer(['blacklisted' => true]);
        app(CampaignService::class)->attach($campaign, []);
        app(CampaignService::class)->attach($campaign, []);
        $this->assertSame(21, $campaign->recipients()->count());
    }

    public function test_campaign_snapshot_and_recipient_snapshot_do_not_change_after_schedule(): void
    {
        $campaign = $this->campaign();
        $run = $this->campaignRun($campaign);
        $campaign->update(['translations' => ['en' => ['subject' => 'Changed', 'html' => 'Changed']]]);
        $campaign->recipients()->detach();
        $this->assertSame('Dinner offer', $run->fresh()->snapshot['translations']['en']['subject']);
        $this->assertSame(1, $run->deliveries()->count());
    }

    public function test_dispatch_only_due_runs_and_queues_once(): void
    {
        $run = $this->campaignRun();
        $future = $this->campaignRun();
        $future->update(['scheduled_at' => now()->addDay()]);
        $this->assertSame(1, app(CampaignService::class)->dispatchDue());
        $this->assertSame(0, app(CampaignService::class)->dispatchDue());
        Queue::assertPushed(SendCampaignEmail::class, 1);
        $this->assertSame('scheduled', $future->fresh()->status);
    }

    public function test_delivery_is_idempotent_and_includes_unsubscribe(): void
    {
        $run = $this->campaignRun();
        $run->update(['status' => 'running']);
        $delivery = $run->deliveries()->first();
        $job = new SendCampaignEmail($delivery->id);
        $job->handle();
        $job->handle();
        Mail::assertSent(\App\Mail\MarketingMail::class, 1);
        Mail::assertSent(\App\Mail\MarketingMail::class, fn ($mail) => str_contains($mail->content['unsubscribe'], '/marketing/unsubscribe/en/'));
        $this->assertSame('accepted', $delivery->fresh()->status);
    }

    public function test_consent_is_rechecked_at_send_time(): void
    {
        $run = $this->campaignRun();
        $run->update(['status' => 'running']);
        $d = $run->deliveries()->first();
        $d->customer->update(['consent_marketing' => false]);
        (new SendCampaignEmail($d->id))->handle();
        Mail::assertNothingSent();
        $this->assertSame('skipped', $d->fresh()->status);
    }

    public function test_paused_runs_do_not_send_and_can_resume(): void
    {
        $run = $this->campaignRun();
        $run->update(['status' => 'paused']);
        $d = $run->deliveries()->first();
        (new SendCampaignEmail($d->id))->handle();
        Mail::assertNothingSent();
        Livewire::test(CampaignSend::class, ['campaign' => $run->campaign])->call('control', $run->id, 'resume')->assertHasNoErrors();
        $this->assertSame('scheduled', $run->fresh()->status);
    }

    public function test_smtp_failure_is_unknown_and_not_automatically_resent(): void
    {
        $run = $this->campaignRun();
        $run->update(['status' => 'running']);
        $d = $run->deliveries()->first();
        $this->mock(MarketingMailer::class)->shouldReceive('send')->once()->andThrow(new \RuntimeException('Timeout'));
        (new SendCampaignEmail($d->id))->handle();
        (new SendCampaignEmail($d->id))->handle();
        $this->assertSame('unknown', $d->fresh()->status);
        Livewire::test(CampaignSend::class, ['campaign' => $run->campaign])->call('control', $run->id, 'retry')->assertHasNoErrors();
        $this->assertSame('unknown', $d->fresh()->status);
    }

    public function test_unsubscribe_get_does_not_mutate_and_post_blocks_campaigns(): void
    {
        $customer = $this->customer();
        $p = app(CustomerIdentity::class)->preference($customer);
        $url = route('marketing.unsubscribe', ['language' => 'en', 'token' => $p->access_token]);
        $this->get($url)->assertOk();
        $this->assertTrue((bool) $customer->fresh()->consent_marketing);
        $this->post($url)->assertOk()->assertSee('You have unsubscribed');
        $this->assertFalse(app(CampaignAudience::class)->allowed($customer->fresh()));
    }

    public function test_incomplete_recipient_language_blocks_scheduling(): void
    {
        $campaign = $this->campaign();
        $campaign->recipients()->attach($this->customer(['lang' => 'de']));
        $this->expectException(ValidationException::class);
        app(CampaignService::class)->schedule($campaign, now());
    }

    public function test_signed_campaign_click_rejects_tampering(): void
    {
        $run = $this->campaignRun();
        $d = $run->deliveries()->first();
        $content = app(CampaignService::class)->content($run->snapshot, 'en', $d->email, $d);
        $this->get($content['url'])->assertRedirect('https://ristogenius.com/');
        $this->assertNotNull($d->fresh()->clicked_at);
        $this->get(route('campaigns.click', $d))->assertForbidden();
    }

    public function test_confirmation_job_does_not_send_twice(): void
    {
        $this->proof($this->coupon(), 'queue@example.com');
        $outbox = MessageOutbox::first();
        $job = new SendConfirmation($outbox->id);
        $job->handle();
        $job->handle();
        Mail::assertSent(\App\Mail\MarketingMail::class, 1);
        $this->assertSame('accepted', $outbox->fresh()->payload['smtp_status']);
    }

    public function test_editor_removes_active_content_without_losing_rich_text(): void
    {
        $clean = app(MarketingContent::class)->clean('<p><strong>Safe</strong><script>alert(1)</script><img src="javascript:alert(1)" onerror="bad()"><a href="https://ristogenius.com">Link</a></p>');
        $this->assertStringContainsString('<strong>Safe</strong>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('https://ristogenius.com', $clean);
    }

    public function test_campaign_and_coupon_editors_accept_greek_content(): void
    {
        config(['tenant.customer_languages' => 'it,en,gr']);

        Livewire::test(CampaignEdit::class)
            ->assertSee('Ελληνικά')
            ->set('data.name', 'Campagna greca')
            ->set('data.enabled_languages', ['gr'])
            ->set('data.translations.gr.subject', 'Νέα από το εστιατόριο')
            ->set('data.translations.gr.html', '<p>Σας περιμένουμε!</p>')
            ->call('save')->assertHasNoErrors();

        Livewire::test(CouponEdit::class)
            ->assertSee('Ελληνικά')
            ->set('data.name', 'Offerta greca')
            ->set('data.slug', 'offerta-greca')
            ->set('data.enabled_languages', ['gr'])
            ->set('data.translations.gr.title', 'Ειδική προσφορά')
            ->set('data.translations.gr.description', '<p>Κερδίστε έκπτωση.</p>')
            ->call('save')->assertHasNoErrors();

        $this->assertSame('Νέα από το εστιατόριο', MarketingCampaign::sole()->translations['gr']['subject']);
        $this->assertSame('Ειδική προσφορά', Coupon::sole()->translations['gr']['title']);
    }

    public function test_coupon_editor_saves_and_preserves_unlimited_uses(): void
    {
        Livewire::test(CouponEdit::class)->set('data.name', 'New')->set('data.slug', 'new-coupon')->set('data.enabled_languages', ['en'])->set('data.translations.en.title', 'New offer')->set('data.translations.en.description', '<p>Description</p>')->set('usageMode', 'unlimited')->call('save')->assertHasNoErrors();
        $this->assertNull(Coupon::where('slug', 'new-coupon')->firstOrFail()->rules['max_uses']);
    }

    public function test_plain_channel_text_enforces_configured_limit(): void
    {
        Livewire::test(CampaignEdit::class)->set('data.name', 'Test')->set('data.enabled_languages', ['en'])->set('data.translations.en.subject', 'Subject')->set('data.translations.en.html', '<p>Body</p>')->set('data.translations.en.sms', str_repeat('x', 481))->call('save')->assertHasErrors(['data.translations.en.sms']);
    }

    public function test_coupon_menu_visibility_respects_menu_scope_and_language(): void
    {
        $menu = DigitalMenu::create(['name' => ['en' => 'Menu'], 'slug' => 'test-menu', 'enabled_languages' => ['en'], 'is_visible' => true, 'is_published' => true]);
        $other = DigitalMenu::create(['name' => ['en' => 'Other'], 'slug' => 'other', 'enabled_languages' => ['en'], 'is_visible' => true, 'is_published' => true]);
        $coupon = $this->coupon(['show_in_menu' => true]);
        $coupon->menus()->attach($menu);
        $this->get(route('menu.public', ['language' => 'en', 'menu' => $menu->slug]))->assertOk()->assertSee('Family offer');
        $this->get(route('menu.public', ['language' => 'en', 'menu' => $other->slug]))->assertOk()->assertDontSee('Family offer');
    }

    public function test_email_templates_render_for_proof_campaign_and_assignment(): void
    {
        $a = $this->assigned();
        foreach ([
            ['subject' => 'Confirm', 'message' => 'Please confirm', 'url' => 'https://ristogenius.com/confirm'],
            ['subject' => 'Offer', 'html' => '<p>Dinner</p>', 'preheader' => 'Tonight', 'button' => 'Book', 'url' => 'https://ristogenius.com/', 'image' => 'https://ristogenius.com/image.jpg', 'unsubscribe' => 'https://ristogenius.com/unsubscribe'],
            ['subject' => 'Coupon', 'message' => 'Assigned', 'url' => $a->url(), 'assignment_id' => $a->id],
        ] as $content) {
            $content += ['language' => 'en', 'email' => 'test@example.com'];
            $html = (new \App\Mail\MarketingMail($content))->render();
            $this->assertStringContainsString($content['subject'], $html);
            $this->assertNotEmpty(view('marketing.email-text', compact('content'))->render());
        }
    }

    public function test_new_coupon_claim_page_renders_all_supported_languages(): void
    {
        $c = $this->coupon();
        foreach (['it' => 'Richiedi il coupon', 'en' => 'Request coupon', 'de' => 'Gutschein anfordern'] as $l => $text) {
            $this->get(route('coupons.public', ['language' => $l, 'coupon' => $c->slug]))->assertOk()->assertSee($text);
        }
    }

    public function test_dispatcher_routes_coupon_confirmations_to_transactional_queue(): void
    {
        $this->proof($this->coupon(), 'transactional@example.com');
        app(\App\Services\Messaging\MessageDispatcher::class)->dispatch(MessageOutbox::first());
        Queue::assertPushed(SendConfirmation::class, fn ($job) => $job->queue === 'messages-email' && $job->connection === 'redis');
    }

    public function test_coupon_is_not_applied_twice_when_booking_has_two_orders(): void
    {
        [$first] = $this->priced(['type' => 'amount', 'value' => 500]);
        $second = Order::create(['booking_id' => $first->booking_id, 'service_date' => today(), 'fulfillment' => 'dine_in', 'covers' => 3, 'cover_price_cents' => 200]);
        $course = $second->courses()->create(['number' => 1]);
        $course->items()->create(['product_name' => 'Pizza', 'menu_name' => 'Menu', 'quantity' => 1, 'base_price_cents' => 1000, 'unit_price_cents' => 1000, 'vat' => 10]);
        $this->assertSame(500, app(CashTotalsService::class)->snapshot($first)['coupon_discount_cents']);
        $this->assertSame(0, app(CashTotalsService::class)->snapshot($second)['coupon_discount_cents']);
    }

    public function test_checkout_rechecks_actual_covers(): void
    {
        [$order,,$a] = $this->priced(['type' => 'percent', 'value' => 1000]);
        $usage = $a->usages()->first();
        $snapshot = $usage->snapshot;
        $snapshot['rules']['min_pax'] = 3;
        $usage->update(['snapshot' => $snapshot]);
        $order->update(['covers' => 2]);
        $this->assertSame(0, app(CashTotalsService::class)->snapshot($order)['coupon_discount_cents']);
        $this->assertSame('invalid_pax', app(CouponPricing::class)->reason($order));
    }

    public function test_booking_rolls_back_if_coupon_rules_fail(): void
    {
        $form = app(FormBlueprintService::class)->create(['type' => 'booking', 'slug' => 'rollback', 'translations' => ['en' => ['title' => 'Book']], 'enabled_languages' => ['en'], 'is_active' => true, 'accepts_coupons' => true]);
        $a = $this->assigned($this->coupon(['marketing_form_id' => $form->id, 'rules' => ['min_pax' => 4, 'max_uses' => 1]]));
        $this->partialMock(\App\Services\BookingService::class)->shouldReceive('ensureCapacity')->once()->andReturnNull();
        try {
            app(PublicFormSubmissionService::class)->submit($form, ['date' => today()->toDateString(), 'time' => '20:00', 'guests' => 2], 'en', $a);
            $this->fail('rules');
        } catch (ValidationException) {
            $this->assertDatabaseCount('bookings', 0);
            $this->assertDatabaseCount('coupon_usages', 0);
            $this->assertSame(0, $a->fresh()->uses_count);
        }
    }

    public function test_staff_can_associate_owned_coupon_to_manual_booking(): void
    {
        $a = $this->assigned();
        $b = $this->booking($a->customer);
        app()->setLocale('en');
        Livewire::test(\App\Livewire\Bookings\BookingShow::class, ['booking' => $b])->set('couponAssignmentId', $a->id)->call('applyCoupon')->assertHasNoErrors()->assertSee('Family offer');
        $this->assertSame(1, $a->fresh()->uses_count);
    }

    public function test_stale_processing_delivery_becomes_unknown_without_resend(): void
    {
        $run = $this->campaignRun();
        $d = $run->deliveries()->first();
        $d->update(['status' => 'processing', 'started_at' => now()->subMinutes(6)]);
        app(CampaignService::class)->dispatchDue();
        $this->assertSame('unknown', $d->fresh()->status);
        Queue::assertNotPushed(SendCampaignEmail::class);
    }

    public function test_actual_visit_filter_ignores_generic_customer_activity(): void
    {
        $recent = $this->customer(['last_action_at' => now()->subYear()]);
        $inactive = $this->customer(['last_action_at' => now()]);
        $this->booking($recent, ['booking_date' => today()->subDays(2), 'status' => 'finalized']);
        $ids = app(CampaignAudience::class)->query(['lastVisitWithin' => 1])->pluck('customers.id')->all();
        $this->assertContains($recent->id, $ids);
        $this->assertNotContains($inactive->id, $ids);
    }

    public function test_repeated_audience_selection_does_not_duplicate_deliveries(): void
    {
        $c = $this->campaign();
        $customer = $this->customer();
        $c->recipients()->syncWithoutDetaching([$customer->id]);
        $c->recipients()->syncWithoutDetaching([$customer->id]);
        $run = app(CampaignService::class)->schedule($c, now());
        $this->assertSame(1, $run->deliveries()->count());
    }

    public function test_paused_run_cancellation_preserves_already_accepted_delivery(): void
    {
        $run = $this->campaignRun();
        $d = $run->deliveries()->first();
        $d->update(['status' => 'accepted']);
        Livewire::test(CampaignSend::class, ['campaign' => $run->campaign])->call('control', $run->id, 'cancel')->assertHasNoErrors();
        $this->assertSame('accepted', $d->fresh()->status);
        $this->assertSame('cancelled', $run->fresh()->status);
    }

    public function test_confirmation_proof_expiry_starts_when_worker_sends(): void
    {
        $this->proof($this->coupon(), 'delayed@example.com');
        $outbox = MessageOutbox::first();
        CouponClaim::query()->update(['expires_at' => now()->subHour()]);
        (new SendConfirmation($outbox->id))->handle();
        $this->assertTrue(CouponClaim::first()->expires_at->isFuture());
    }

    public function test_settings_are_persisted_and_sms_limit_applies(): void
    {
        Livewire::test(\App\Livewire\Marketing\Settings::class)->set('settings.sms_max', 20)->call('save')->assertHasNoErrors();
        $this->assertSame(20, app(\App\Services\Marketing\MarketingSettings::class)->get()['sms_max']);
        Livewire::test(CampaignEdit::class)->set('data.name', 'Test')->set('data.enabled_languages', ['en'])->set('data.translations.en.subject', 'Subject')->set('data.translations.en.html', '<p>Body</p>')->set('data.translations.en.sms', str_repeat('x', 21))->call('save')->assertHasErrors(['data.translations.en.sms']);
    }

    public function test_settings_reserve_hourly_capacity_for_transactional_messages(): void
    {
        Livewire::test(\App\Livewire\Marketing\Settings::class)->set('settings.smtp_per_hour', 80)->set('settings.marketing_per_hour', 80)->call('save')->assertHasErrors(['marketing_per_hour']);
    }

    public function test_livewire_image_upload_is_published_as_an_actual_image(): void
    {
        config(['tenant.slug' => 'marketing-upload-test']);
        try {
            Livewire::test(CouponEdit::class)->set('data.name', 'Image offer')->set('data.slug', 'image-offer')->set('data.enabled_languages', ['en'])->set('data.translations.en.title', 'Image offer')->set('data.translations.en.description', '<p>Image</p>')->set('image', \Illuminate\Http\UploadedFile::fake()->image('offer.png', 400, 300))->call('save')->assertHasNoErrors();
            $path = public_path(Coupon::where('slug', 'image-offer')->firstOrFail()->image_path);
            $this->assertFileExists($path);
            $this->assertSame([400, 300], array_slice(getimagesize($path), 0, 2));
        } finally {
            \Illuminate\Support\Facades\File::deleteDirectory(public_path('marketing-assets/marketing-upload-test'));
        }
    }

    public function test_campaign_reports_missing_content_on_the_specific_enabled_language_fields(): void
    {
        $edit = Livewire::test(CampaignEdit::class)
            ->set('data.name', 'Translated campaign')
            ->set('data.enabled_languages', ['en'])
            ->set('data.translations.en.html', '<p>&nbsp;<br></p>')
            ->call('save')
            ->assertHasErrors(['data.translations.en.subject', 'data.translations.en.html'])
            ->assertHasNoErrors(['coupon', 'data.translations.it.subject', 'data.translations.de.subject']);

        $edit->set('data.translations.en.subject', 'News')
            ->set('data.translations.en.html', '<p>Our news</p>')
            ->call('save')->assertHasNoErrors()->assertRedirect();
        $this->assertSame(1, MarketingCampaign::where('name', 'Translated campaign')->count());
    }

    public function test_campaign_selection_and_send_list_only_show_authorized_selected_customers(): void
    {
        $campaign = $this->campaign();
        $yes = $this->customer(['display_name' => 'Authorized customer']);
        $no = $this->customer(['display_name' => 'No consent customer', 'consent_marketing' => false]);
        $blocked = $this->customer(['display_name' => 'Blocked customer', 'blacklisted' => true]);
        $unsubscribed = $this->customer(['display_name' => 'Unsubscribed customer']);
        \App\Models\MarketingPreference::create(['customer_id' => $unsubscribed->id, 'token_hash' => hash('sha256', Str::random(64)), 'access_token' => Str::random(64), 'unsubscribed_at' => now()]);
        $notSelected = $this->customer(['display_name' => 'Not selected customer']);
        $campaign->recipients()->attach([$yes->id, $no->id, $blocked->id, $unsubscribed->id]);

        Livewire::test(CampaignEdit::class, ['campaign' => $campaign])
            ->assertSee($yes->display_name)->assertSee($notSelected->display_name)
            ->assertDontSee($no->display_name)->assertDontSee($blocked->display_name)->assertDontSee($unsubscribed->display_name)
            ->assertViewHas('eligibleCount', 2);
        Livewire::test(CampaignSend::class, ['campaign' => $campaign])
            ->assertSee($yes->display_name)->assertDontSee($notSelected->display_name)
            ->assertDontSee($no->display_name)->assertDontSee($blocked->display_name)->assertDontSee($unsubscribed->display_name)
            ->assertViewHas('recipients', fn ($recipients) => $recipients->total() === 1);
    }

    public function test_campaign_send_recipients_are_paginated(): void
    {
        $campaign = $this->campaign();
        for ($i = 0; $i < 17; $i++) {
            $campaign->recipients()->attach($this->customer());
        }
        Livewire::test(CampaignSend::class, ['campaign' => $campaign])
            ->assertViewHas('recipients', fn ($recipients) => $recipients->total() === 17 && $recipients->count() === 15)
            ->call('setPage', 2, 'recipientsPage')
            ->assertViewHas('recipients', fn ($recipients) => $recipients->currentPage() === 2 && $recipients->count() === 2);
    }

    public function test_scheduling_excludes_customers_who_revoked_consent_after_selection(): void
    {
        $campaign = $this->campaign();
        $yes = $this->customer();
        $revoked = $this->customer(['lang' => 'de']);
        $campaign->recipients()->attach([$yes->id, $revoked->id]);
        $revoked->update(['consent_marketing' => false]);
        $run = app(CampaignService::class)->schedule($campaign, now());
        $this->assertSame([$yes->id], $run->deliveries()->pluck('customer_id')->all());
    }

    public function test_individual_recipient_selection_checks_consent_and_can_remove(): void
    {
        $campaign = $this->campaign();
        $yes = $this->customer();
        $no = $this->customer(['consent_marketing' => false]);
        $edit = Livewire::test(CampaignEdit::class, ['campaign' => $campaign]);
        $edit->call('toggleRecipient', $yes->id)->assertHasNoErrors();
        $this->assertSame(1, $campaign->recipients()->count());
        $edit->call('toggleRecipient', $yes->id)->assertHasNoErrors();
        $this->assertSame(0, $campaign->recipients()->count());
        $edit->call('toggleRecipient', $no->id)->assertHasErrors(['coupon']);
        $this->assertSame(0, $campaign->recipients()->count());
    }

    public function test_emoji_length_matches_browser_counter_on_the_server(): void
    {
        Livewire::test(\App\Livewire\Marketing\Settings::class)->set('settings.sms_max', 20)->call('save')->assertHasNoErrors();
        Livewire::test(CampaignEdit::class)->set('data.name', 'Test')->set('data.enabled_languages', ['en'])->set('data.translations.en.subject', 'Subject')->set('data.translations.en.html', '<p>Body</p>')->set('data.translations.en.sms', str_repeat('😀', 11))->call('save')->assertHasErrors(['data.translations.en.sms']);
    }

    public function test_scheduler_does_not_overwrite_a_concurrent_pause(): void
    {
        $run = $this->campaignRun();
        $paused = false;
        DB::listen(function ($query) use ($run, &$paused) {
            if (! $paused && str_starts_with($query->sql, 'select') && str_contains($query->sql, 'from `campaign_runs`') && str_contains($query->sql, '`status` in')) {
                $paused = true;
                \App\Models\CampaignRun::whereKey($run->id)->update(['status' => 'paused']);
            }
        });
        app(CampaignService::class)->dispatchDue();
        $this->assertTrue($paused);
        $this->assertSame('paused', $run->fresh()->status);
        Queue::assertNotPushed(SendCampaignEmail::class);
    }

    public function test_cancelled_queue_jobs_are_discarded_before_smtp_throttling(): void
    {
        $run = $this->campaignRun();
        $run->update(['status' => 'cancelled']);
        $d = $run->deliveries()->first();
        $called = false;
        $job = new SendCampaignEmail($d->id);
        app(\Illuminate\Pipeline\Pipeline::class)->send($job)->through($job->middleware())->then(function () use (&$called) {
            $called = true;
        });
        $this->assertFalse($called);
        Mail::assertNothingSent();
    }
}
