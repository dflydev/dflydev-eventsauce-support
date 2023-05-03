<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023 Dragonfly Development Inc
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/dflydev/dflydev-eventsauce-support
 */

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
