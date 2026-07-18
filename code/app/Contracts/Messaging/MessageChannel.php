<?php

namespace App\Contracts\Messaging;

use App\Models\MessageOutbox;

interface MessageChannel
{
    public function send(MessageOutbox $outbox): bool;
}
