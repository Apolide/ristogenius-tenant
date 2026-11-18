<?php

return [
    // Each type uses a matching key under notifications.types in the language files.
    'types' => [
        'customer_order.received' => ['icon' => 'la-shopping-cart', 'label' => 'customer_order'],
        'table_order.received' => ['icon' => 'la-utensils', 'label' => 'table_order'],
        'staff_order.sent' => ['icon' => 'la-clipboard-list', 'label' => 'staff_order'],
        'customer_order.proposal_accepted' => ['icon' => 'la-comment-dots', 'label' => 'proposal_accepted'],
        'customer_order.proposal_rejected' => ['icon' => 'la-comment-slash', 'label' => 'proposal_rejected'],
        'general' => ['icon' => 'la-bell', 'label' => 'general'],
    ],
];
