<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\MessagePayloadConsumption;

use EventSauce\EventSourcing\Message;

interface MessagePayloadConsumer
{
    public function handleMessagePayload(object $payload, Message $message): void;
}
