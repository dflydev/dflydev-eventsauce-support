<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\MessagePayloadConsumption;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;

final readonly class MessagePayloadConsumerMessageConsumer implements MessageConsumer
{
    public function __construct(private MessagePayloadConsumer $messagePayloadHandler)
    {
    }

    public function handle(Message $message): void
    {
        $this->messagePayloadHandler->handleMessagePayload($message->payload(), $message);
    }
}
