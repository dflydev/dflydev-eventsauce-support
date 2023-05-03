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

interface SupportsAwareMessagePayloadConsumer extends MessagePayloadConsumer
{
    /**
     * @return array<int,class-string>|array<class-string,string>
     */
    public static function supportedMessages(): array;

    /**
     * @param object|class-string $payloadClassName
     */
    public static function supportsMessage(object|string $payloadClassName): bool;
}
