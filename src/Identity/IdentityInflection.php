<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Identity;

use EventSauce\EventSourcing\AggregateRootId;

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
}
