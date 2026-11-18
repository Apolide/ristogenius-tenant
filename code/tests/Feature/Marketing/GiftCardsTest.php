<?php

namespace Tests\Feature\Marketing;

use App\Jobs\Marketing\SendConfirmation;
use App\Mail\MarketingMail;
use App\Models\GiftCard;
use App\Models\MessageOutbox;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GiftCardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_payment_issues_one_code_and_only_then_queues_the_recipient_email(): void
    {
        Mail::fake();
        config(['tenant.customer_languages' => 'it,en,de']);

        $this->get(route('gift-cards.public'))->assertOk()
            ->assertSee('Regala una Gift Card')
            ->assertSeeInOrder(['<header class="tenant-public-form-header">', '<select', route('gift-cards.public', 'en'), '</header>'], false);
        $this->get(route('gift-cards.public', 'en'))->assertOk()->assertSee('Give a gift card');
        $this->get(route('gift-cards.index'))->assertRedirect();

        $public = route('gift-cards.public', 'en');
        $this->post($public, $this->fields())->assertRedirect($public);
        $card = GiftCard::sole();
        $this->assertSame(2019, $card->amount_cents);
        $this->assertNull($card->code);
        $this->get($public)->assertOk()->assertSee($card->reference);

        $requestEmail = MessageOutbox::where('aggregate_id', $card->id)->sole();
        $this->assertSame($card->sender_email, $requestEmail->payload['email']);
        $this->assertStringContainsString($card->reference, $requestEmail->payload['message']);

        $operator = User::factory()->create();
        $operator->assignRole(Role::findOrCreate('operator', 'web'));
        $this->actingAs($operator)->post(route('gift-cards.pay', $card))->assertForbidden();
        $operator->givePermissionTo(Permission::findOrCreate(PersonnelPermissionsService::MARKETING, 'web'));

        $show = route('gift-cards.show', $card);
        $this->get($show)->assertOk()->assertSee($card->reference);
        $this->from($show)->post(route('gift-cards.redeem', $card))->assertSessionHasErrors('gift_card');
        $this->post(route('gift-cards.pay', $card))->assertRedirect($show);
        $card->refresh();
        $this->assertSame('cash', $card->payment_method);
        $this->assertNotNull($card->code);
        $this->post(route('gift-cards.pay', $card))->assertRedirect($show);
        $this->assertSame($card->code, $card->fresh()->code);

        $messages = MessageOutbox::where('aggregate_id', $card->id)->get()->keyBy('deduplication_key');
        $this->assertCount(3, $messages);
        $giftEmail = $messages['gift-card-gift:'.$card->id];
        $this->assertSame($card->recipient_email, $giftEmail->payload['email']);
        $this->assertStringContainsString($card->code, $giftEmail->payload['message']);
        $this->assertStringContainsString('Enjoy dinner!', $giftEmail->payload['message']);
        $this->assertStringNotContainsString($card->code, $messages['gift-card-paid:'.$card->id]->payload['message']);
        (new SendConfirmation($giftEmail->id))->handle();
        Mail::assertSent(MarketingMail::class, fn ($mail) => $mail->hasTo($card->recipient_email));

        $this->get(route('gift-cards.index', ['search' => $card->code, 'status' => 'available']))
            ->assertOk()->assertSee($card->reference);
        $this->post(route('gift-cards.redeem', $card))->assertRedirect($show);
        $redeemedAt = $card->fresh()->redeemed_at;
        $this->assertNotNull($redeemedAt);
        $this->travel(1)->minute();
        $this->post(route('gift-cards.redeem', $card))->assertRedirect($show);
        $this->assertTrue($redeemedAt->equalTo($card->fresh()->redeemed_at));
        $this->get(route('gift-cards.index'))->assertOk()->assertDontSee($card->reference);
    }

    public function test_recipient_email_is_required_only_for_direct_delivery(): void
    {
        config(['tenant.customer_languages' => 'it']);
        $public = route('gift-cards.public', 'it');

        $this->post($public, array_replace($this->fields(), [
            'recipient_email' => null,
            'amount' => '19.99',
        ]))->assertSessionHasErrors(['recipient_email', 'amount']);

        $this->post($public, array_replace($this->fields(), ['send_to_recipient' => '0']))
            ->assertRedirect($public);
        $card = GiftCard::sole();
        $this->assertNull($card->recipient_email);

        app(\App\Services\GiftCards\GiftCardService::class)->confirmCashPayment($card);
        $card->refresh();
        $message = MessageOutbox::where('deduplication_key', 'gift-card-handover:'.$card->id)->sole();
        $this->assertSame($card->sender_email, $message->payload['email']);
        $this->assertStringContainsString($card->code, $message->payload['message']);
        $this->assertSame(2, MessageOutbox::where('aggregate_id', $card->id)->count());
    }

    private function fields(): array
    {
        return [
            'sender_firstname' => 'Mario',
            'sender_lastname' => 'Rossi',
            'sender_email' => 'sender@example.test',
            'sender_phone' => '+393330001234',
            'recipient_firstname' => 'Anna',
            'recipient_lastname' => 'Verdi',
            'recipient_email' => 'recipient@example.test',
            'send_to_recipient' => '1',
            'dedication' => 'Enjoy dinner!',
            'amount' => '20.19',
            'privacy' => '1',
        ];
    }
}
