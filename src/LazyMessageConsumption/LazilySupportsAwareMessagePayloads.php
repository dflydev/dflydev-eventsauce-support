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

namespace Dflydev\EventSauce\Support\LazyMessageConsumption;

use Dflydev\EventSauce\Support\MessagePayloadConsumption\MessagePayloadConsumer;
use Dflydev\EventSauce\Support\MessagePayloadConsumption\SupportsAwareMessagePayloadConsumer;
use EventSauce\EventSourcing\Message;

trait LazilySupportsAwareMessagePayloads
{
    public static function supportedMessages(): array
    {
        return [];
    }

    /**
     * @param object|class-string $payloadClassName
     *
     * @see SupportsAwareMessagePayloadConsumer::supportsMessage()
     */
    public static function supportsMessage(string|object $payloadClassName): bool
    {
        $payloadClassName = is_object($payloadClassName) ? get_class($payloadClassName) : $payloadClassName;

        return in_array($payloadClassName, self::supportedMessages()) || isset(self::supportedMessages()[$payloadClassName]);
    }

    /**
     * @see MessagePayloadConsumer::handleMessagePayload()
     */
    public function handleMessagePayload(object $payload, Message $message): void
    {
        $payloadClassName = get_class($payload);

        if (in_array($payloadClassName, self::supportedMessages())) {
            $parts = explode('\\', get_class($payload));
            $method = 'handle'.end($parts);
        } else {
            $method = self::supportedMessages()[$payloadClassName] ?? null;

            if (!$method) {
                return;
            }
        }

        if (!method_exists($this, $method)) {
            return;
        }

        $this->{$method}($payload, $message);
    }
}
