<span @class(['badge', 'bg-warning/10 text-warning' => $card->status() === 'pending',
    'bg-success/10 text-success' => $card->status() === 'available',
    'bg-info/10 text-info' => $card->status() === 'redeemed'])>
    {{ __('gift_cards.statuses.'.$card->status()) }}
</span>
