<?php

namespace Tests\Feature\Messaging;

use App\Jobs\Messaging\DispatchMessageJob;
use App\Jobs\Messaging\SendMessageChannelJob;
use App\Mail\BookingMessageMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MessageOutbox;
use App\Models\TenantProfile;
use App\Services\Messaging\BookingMessageService;
use App\Services\Messaging\Channels\EmailMessageChannel;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Messaging\MessageOutboxPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingMessagePipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_is_published_to_the_configured_async_transport(): void
    {
        Queue::fake();
        $outbox = $this->createOutbox();

        app(MessageOutboxPublisher::class)->publishPending();

        Queue::assertPushed(DispatchMessageJob::class, fn (DispatchMessageJob $job) => $job->outboxId === $outbox->id
            && $job->queue === 'messages-dispatch');
        $this->assertSame(MessageOutbox::STATUS_PUBLISHED, $outbox->fresh()->status);
    }

    public function test_dispatcher_creates_an_independent_job_for_each_configured_channel(): void
    {
        Queue::fake();
        $outbox = $this->createOutbox(['email', 'whatsapp']);

        app(MessageDispatcher::class)->dispatch($outbox);

        Queue::assertPushed(SendMessageChannelJob::class, 2);
        Queue::assertPushed(SendMessageChannelJob::class, fn (SendMessageChannelJob $job) => $job->channel === 'email'
            && $job->queue === 'messages-email');
        Queue::assertPushed(SendMessageChannelJob::class, fn (SendMessageChannelJob $job) => $job->channel === 'whatsapp'
            && $job->queue === 'messages-whatsapp');
    }

    public function test_email_uses_the_language_stored_on_the_booking(): void
    {
        Mail::fake();
        $outbox = $this->createOutbox(['email'], bookingLanguage: 'en', customerLanguage: 'it');
        app(EmailMessageChannel::class)->send($outbox);

        Mail::assertSent(BookingMessageMail::class, function (BookingMessageMail $mail): bool {
            return $mail->hasTo('giulia@example.test')
                && str_contains($mail->delivery['content']['subject'], 'accepted')
                && count($mail->delivery['actions']) === 2;
        });
    }

    public function test_outbox_payload_is_self_contained_and_uses_app_url_for_actions(): void
    {
        config()->set('app.url', 'https://tenant.example.test');
        $outbox = $this->createOutbox();
        $payload = $outbox->payload;

        $this->assertSame('booking_accepted', $payload['template']['key']);
        $this->assertSame('Ristopilot', $payload['tenant']['name']);
        $this->assertSame('giulia@example.test', $payload['deliveries'][0]['recipient']['email']);
        $this->assertNotEmpty($payload['deliveries'][0]['content']['subject']);
        foreach ($payload['deliveries'][0]['actions'] as $action) {
            $this->assertStringStartsWith('https://tenant.example.test/', $action['url']);
        }
    }

    private function createOutbox(
        array $channels = ['email'],
        string $bookingLanguage = 'it',
        string $customerLanguage = 'it',
    ): MessageOutbox {
        config()->set('tenant.customer_languages', 'it,en');
        TenantProfile::create([
            'name' => 'Ristopilot',
            'settings' => [
                'reservations' => ['notification_channels' => $channels],
                'messages' => ['message_channel_cases' => [
                    'booking_accepted' => ['channels' => $channels],
                ]],
            ],
        ]);
        $customer = Customer::create([
            'firstname' => 'Giulia', 'lastname' => 'Bianchi', 'display_name' => 'Giulia Bianchi',
            'email' => 'giulia@example.test', 'phone' => null, 'lang' => $customerLanguage,
            'registration_source' => 'backoffice',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id, 'booking_date' => now()->addDay(), 'booking_time' => '20:00',
            'pax' => 2, 'status' => 'accepted', 'source' => 'backoffice', 'language' => $bookingLanguage,
        ]);

        return app(BookingMessageService::class)->bookingCreated($booking);
    }
}
