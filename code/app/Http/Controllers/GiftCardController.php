<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\MessageOutbox;
use App\Services\GiftCards\GiftCardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftCardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->limit(150)->toString());
        $status = $request->string('status', 'open')->toString();
        $query = GiftCard::query();

        match ($status) {
            'open' => $query->whereNull('redeemed_at'),
            'pending' => $query->whereNull('paid_at'),
            'available' => $query->whereNotNull('paid_at')->whereNull('redeemed_at'),
            'redeemed' => $query->whereNotNull('redeemed_at'),
            default => null,
        };

        if ($search !== '') {
            foreach (preg_split('/\s+/', $search) as $term) {
                $query->where(function (Builder $query) use ($term): void {
                    foreach (['code', 'reference', 'sender_firstname', 'sender_lastname', 'sender_email', 'sender_phone',
                        'recipient_firstname', 'recipient_lastname', 'recipient_email', 'recipient_phone'] as $column) {
                        $query->orWhereLike($column, '%'.$term.'%');
                    }
                });
            }
        }

        return view('gift-cards.index', [
            'cards' => $query->latest()->orderByDesc('id')->paginate(25)->withQueryString(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(GiftCard $giftCard): View
    {
        $messages = MessageOutbox::where('aggregate_type', 'marketing_confirmation')
            ->where('aggregate_id', $giftCard->id)
            ->whereIn('event_type', array_map(
                fn (string $event) => "gift-card-{$event}:{$giftCard->id}",
                ['request', 'paid', 'handover', 'gift'],
            ))->oldest()->get();

        return view('gift-cards.show', ['card' => $giftCard, 'messages' => $messages]);
    }

    public function pay(GiftCard $giftCard, GiftCardService $cards): RedirectResponse
    {
        $cards->confirmCashPayment($giftCard);

        return redirect()->route('gift-cards.show', $giftCard)->with('status', __('gift_cards.payment_confirmed'));
    }

    public function redeem(GiftCard $giftCard, GiftCardService $cards): RedirectResponse
    {
        $cards->redeem($giftCard);

        return redirect()->route('gift-cards.show', $giftCard)->with('status', __('gift_cards.redemption_confirmed'));
    }
}
