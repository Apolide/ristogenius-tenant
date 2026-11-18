<?php

namespace Tests\Feature\Marketing;

use App\Jobs\Marketing\SendConfirmation;
use App\Livewire\Polls\PollEditor;
use App\Mail\MarketingMail;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\MessageOutbox;
use App\Models\Poll;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use App\Services\Polls\PollDelivery;
use Database\Seeders\SurveySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PollsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_operator_can_save_translated_questions_and_reopen_the_editor(): void
    {
        config(['tenant.customer_languages' => 'it,en']);
        $this->seed(SurveySeeder::class);
        $operator = User::factory()->create();
        $operator->assignRole(Role::findOrCreate('operator', 'web'));
        $operator->givePermissionTo(Permission::findOrCreate(PersonnelPermissionsService::MARKETING, 'web'));
        $this->actingAs($operator);
        $coupon = $this->coupon();

        Livewire::test(PollEditor::class)
            ->set('label', 'Visita')
            ->set('enabled', true)
            ->set('rewardCouponId', $coupon->id)
            ->set('translations', ['it' => $this->texts('Visita'), 'en' => $this->texts('Visit')])
            ->set('questions.0.type', 'choice')
            ->set('questions.0.texts', [
                'it' => ['label' => 'Servizio', 'options' => "Pranzo\nCena"],
                'en' => ['label' => 'Service', 'options' => "Lunch\nDinner"],
            ])
            ->call('save')->assertOk()
            ->assertHasNoErrors()
            ->assertRedirect();

        $poll = Poll::where('label', 'Visita')->sole();
        $this->assertNotNull($poll->enabled_at);
        $this->assertSame($coupon->id, $poll->reward_coupon_id);
        $this->assertSame(['Lunch', 'Dinner'], $poll->questions[0]['texts']['en']['choices']);
        $this->get(route('polls.index'))->assertOk()->assertSee('Visita');
        $this->get(route('polls.create'))->assertOk();
        $this->get(route('polls.edit', $poll))->assertOk();
        $this->get(route('polls.responses', $poll))->assertOk();
        Livewire::test(PollEditor::class, ['poll' => $poll])
            ->assertSet('questions.0.texts.en.options', "Lunch\nDinner")
            ->assertSet('rewardCouponId', $coupon->id)
            ->set('rewardCouponId', '')
            ->call('save')
            ->assertHasNoErrors();
        $this->assertNull($poll->fresh()->reward_coupon_id);
    }

    public function test_standard_survey_uses_tenant_languages_and_preserves_customizations(): void
    {
        config(['tenant.customer_languages' => 'de,it,fr', 'tenant.default_backend_language' => 'it']);

        $this->seed(SurveySeeder::class);
        $poll = Poll::findOrFail(SurveySeeder::SURVEY_ID);
        $this->assertNull($poll->enabled_at);
        $this->assertFalse($poll->review_enabled);
        $this->assertSame(23, $poll->send_after_hours);
        $this->assertSame('it', $poll->default_language);
        $this->assertEqualsCanonicalizing(['de', 'it', 'fr'], array_keys($poll->translations));
        $this->assertStringContainsString('@@nomelocale@@', $poll->translations['fr']['title']);
        $this->assertSame(['rating', 'rating', 'yes_no', 'rating', 'choice'], array_column($poll->questions, 'type'));
        $this->assertSame([4, 4, null, 4, null], array_column($poll->questions, 'threshold'));
        $this->assertCount(7, $poll->questions[4]['texts']['de']['choices']);

        $poll->update(['label' => 'Sondaggio personalizzato', 'send_after_hours' => 3]);
        $this->seed(SurveySeeder::class);
        $this->assertSame(1, Poll::count());
        $this->assertSame('Sondaggio personalizzato', $poll->fresh()->label);
        $this->assertSame(3, $poll->fresh()->send_after_hours);
    }

    public function test_email_and_review_are_scheduled_once_and_low_ratings_keep_private_feedback(): void
    {
        $this->freezeTime();
        Mail::fake();
        config(['tenant.name' => 'Ristorante']);
        $first = (string) Str::uuid();
        $second = (string) Str::uuid();
        $coupon = $this->coupon();
        $poll = Poll::create([
            'label' => 'Visita',
            'enabled_at' => now()->subDay(),
            'reward_coupon_id' => $coupon->id,
            'send_after_hours' => 2,
            'review_enabled' => true,
            'review_min_minutes' => 15,
            'review_max_minutes' => 35,
            'default_language' => 'it',
            'translations' => [
                'it' => $this->texts('Visita'),
                'en' => $this->texts('Visit'),
            ],
            'questions' => array_map(fn ($id) => [
                'id' => $id, 'type' => 'rating', 'required' => true, 'threshold' => 4,
                'texts' => [
                    'it' => ['label' => 'Servizio', 'comment' => 'Cosa possiamo migliorare?'],
                    'en' => ['label' => 'Service', 'comment' => 'What can we improve?'],
                ],
            ], [$first, $second]),
        ]);
        $customer = Customer::create(['firstname' => 'Ada', 'display_name' => 'Ada', 'email' => 'ada@example.test', 'registration_source' => 'manual']);
        foreach ([-3, -2, -1, -25] as $hours) {
            Booking::create([
                'customer_id' => $customer->id, 'status' => 'finalized', 'finalized_at' => now()->addHours($hours),
                'language' => 'en', 'source' => 'backoffice', 'pax' => 2,
                'booking_date' => today(), 'booking_time' => '12:00',
            ]);
        }

        $delivery = app(PollDelivery::class);
        $delivery->dispatchDue();
        $delivery->dispatchDue();
        $this->assertSame(2, $poll->invitations()->count());
        $this->assertSame(2, MessageOutbox::where('deduplication_key', 'like', 'poll-invite:%')->count());

        [$positive, $negative] = $poll->invitations()->get();
        $invite = MessageOutbox::where('deduplication_key', 'poll-invite:'.$positive->id)->sole();
        $url = $invite->payload['url'];
        $this->assertSame('Visit Ada at Ristorante', $invite->payload['subject']);
        (new SendConfirmation($invite->id))->handle();
        Mail::assertSent(MarketingMail::class, fn ($mail) => $mail->hasTo($customer->email) && $mail->content['url'] === $url);
        $this->get(route('polls.public', $positive))->assertForbidden();
        $this->get($url)->assertOk()->assertSee('What can we improve?');
        $this->post($url, ['answers' => [$first => 6, $second => 5]])->assertSessionHasErrors("answers.$first");
        $this->assertSame(0, $coupon->assignments()->count());

        $negativeUrl = MessageOutbox::where('deduplication_key', 'poll-invite:'.$negative->id)->sole()->payload['url'];
        $this->post($negativeUrl, [
            'answers' => [$first => 2, $second => 5],
            'comments' => [$first => 'Wait time', $second => 'Outside threshold'],
        ])->assertRedirect($negativeUrl);
        $this->assertSame([$first => 'Wait time'], $negative->fresh()->answers['comments']);
        $this->assertSame('3.50', $negative->fresh()->rating);
        $this->assertSame(0, MessageOutbox::where('deduplication_key', 'like', 'poll-review:%')->count());
        $assignment = $coupon->assignments()->sole();
        $this->assertSame($customer->id, $assignment->customer_id);
        $reward = MessageOutbox::where('deduplication_key', 'coupon-assigned:'.$assignment->id)->sole();
        $this->assertSame($customer->email, $reward->payload['email']);

        $this->post($url, ['answers' => [$first => 5, $second => 5]])->assertRedirect($url);
        $this->post($url, ['answers' => [$first => 1, $second => 1]])->assertRedirect($url);

        $review = MessageOutbox::where('deduplication_key', 'like', 'poll-review:%')->sole();
        $this->assertTrue($review->available_at->betweenIncluded(now()->addMinutes(15), now()->addMinutes(35)));
        $this->assertSame('5.00', $positive->fresh()->rating);
        $this->assertSame(1, $coupon->assignments()->count());
        $this->assertStringContainsString('href="https://example.test/reviews"', view('marketing.email', ['content' => $review->payload])->render());
        $this->get($url)->assertOk()->assertSee('Thank you');
        $this->assertTrue($delivery->canSend($review->payload));

        $poll->update(['review_enabled' => false]);
        $this->assertFalse($delivery->canSend($review->payload));
        (new SendConfirmation($review->id))->handle();
        $this->assertSame('skipped', $review->fresh()->payload['smtp_status']);
        Mail::assertSentCount(1);
        $this->assertTrue($delivery->canSend($invite->payload));

        $poll->update(['enabled_at' => null]);
        $this->assertFalse($delivery->canSend($invite->payload));
    }

    private function coupon(): Coupon
    {
        return Coupon::create([
            'name' => 'Omaggio',
            'slug' => 'omaggio',
            'translations' => ['en' => ['title' => 'Gift', 'description' => 'A gift', 'terms' => 'Valid once']],
            'enabled_languages' => ['en'],
            'rules' => ['max_uses' => 1],
            'benefit' => ['type' => 'percent', 'value' => 1000],
            'fields' => [],
            'is_active' => true,
        ]);
    }

    private function texts(string $title): array
    {
        return [
            'title' => $title, 'subject' => $title.' @@nomecliente@@ at @@nomelocale@@',
            'message' => 'Come è andata?', 'review_subject' => 'Thank you, @@nomecliente@@',
            'review_message' => 'Share your experience at https://example.test/reviews.',
        ];
    }
}
