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

namespace Dflydev\EventSauce\Support\Messages;

use Dflydev\EventSauce\Support\AggregateRoot\AggregateRootIdAware;
use Dflydev\EventSauce\Support\Identity\IdentityInflection;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\Message;

final readonly class MessageInflection
{
    public function __construct(private ClassNameInflector $classNameInflector)
    {
    }

    /**
     * @template T of AggregateRootId
     *
     * @param class-string<T>|null $identityType
     *
     * @phpstan-return T|AggregateRootId $value
     */
    public function extractAggregateRootId(Message $message, ?string $identityType = null): AggregateRootId
    {
        /** @var class-string<AggregateRootIdAware>|null $aggregateRootTypeString */
        $aggregateRootTypeString = $message->aggregateRootType();

        !is_null($aggregateRootTypeString) || throw new \LogicException('Expected $message to have an aggregate root type.');

        /** @phpstan-var class-string<AggregateRootIdAware> $aggregateRootType */
        $aggregateRootType = $this->classNameInflector->typeToClassName($aggregateRootTypeString);

        if (!in_array(AggregateRootIdAware::class, class_implements($aggregateRootType) ?: [])) {
            throw new \RuntimeException("Cannot extract Aggregate Root ID from \"$aggregateRootType\" because it does not implement AggregateRootIdAware.");
        }

        $aggregateRootIdType = $aggregateRootType::aggregateRootIdClassName();

        $value = IdentityInflection::toObject(
            $identityType ?? $aggregateRootIdType,
            $message->aggregateRootId()
        );

        if (is_null($value)) {
            throw new \RuntimeException('Expected $message to have an aggregate root id');
        }

        return $value;
    }
}
