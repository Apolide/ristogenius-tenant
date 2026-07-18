<?php

namespace App\Contracts\Messaging;

use App\Data\Messaging\MessageEnvelope;

interface MessageTransport
{
    public function publish(MessageEnvelope $message): void;
}
