<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Identity;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;

final class IdentityInflection
{
    /**
     * @template T of AggregateRootId
     *
     * @param class-string<T> $identityClass
     *
     * @return T|null $value
     */
    public static function toObject(string $identityClass, mixed $value): ?AggregateRootId
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof $identityClass) {
            return $value;
        }

        assert(is_string($value), 'Expected $value to be a string if it is not null');

        return $identityClass::fromString($value);
    }

    /**
     * @template T of AggregateRootId
     *
     * @param class-string<T> $identityClass
     *
     * @return T $value
     */
    public static function extractFromMessage(string $identityClass, Message $message): AggregateRootId
    {
        if ($message->aggregateRootType() !== $identityClass) {
            throw new \RuntimeException('Expected $message to have an aggregate root type of '.$identityClass.', had '.$message->aggregateRootType().' instead.');
        }

        $value = self::toObject($identityClass, $message->aggregateRootId());

        if (is_null($value)) {
            throw new \RuntimeException('Expected $message to have an aggregate root id');
        }

        return $value;
    }
}
