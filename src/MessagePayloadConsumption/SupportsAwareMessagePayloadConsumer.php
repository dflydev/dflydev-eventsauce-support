<?php

declare(strict_types=1);

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
