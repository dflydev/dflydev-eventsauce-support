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

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRootId;
use Generator;

/**
 * @template T of AggregateRootId
 *
 * @see AggregateRootBehaviour
 * @see AggregateRoot
 * @see EventSourcedAggregate
 */
trait EventSourcedAggregateRootBehaviour
{
    /**
     * @use EventedAggregateRootBehaviour<T>
     */
    use EventedAggregateRootBehaviour;

    /**
     * @phpstan-param T $aggregateRootId
     */
    final private function __construct(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId;
    }

    /**
     * @phpstan-param T $aggregateRootId
     */
    public static function reconstituteFromEvents(AggregateRootId $aggregateRootId, Generator $events): static
    {
        $aggregateRoot = self::createNewInstance($aggregateRootId);

        /** @var object $event */
        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRootVersion = $events->getReturn();

        $aggregateRoot->aggregateRootVersion = (is_int($aggregateRootVersion) && $aggregateRootVersion >= 0)
            ? $aggregateRootVersion
            : 0;

        return $aggregateRoot;
    }

    /**
     * @phpstan-param T $aggregateRootId
     */
    private static function createNewInstance(AggregateRootId $aggregateRootId): static
    {
        return new static($aggregateRootId);
    }
}
