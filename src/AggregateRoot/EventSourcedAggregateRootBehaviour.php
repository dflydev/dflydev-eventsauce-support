<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRootId;
use Generator;

/**
 * @template T of AggregateRootId
 *
 * @see AggregateRootBehaviour
 * @see AggregateRoot<T>
 * @see EventSourcedAggregate
 */
trait EventSourcedAggregateRootBehaviour
{
    /**
     * @uses EventedAggregateRootBehaviour<T>
     */
    use EventedAggregateRootBehaviour;

    /**
     * @phpstan-param T $aggregateRootId
     */
    final private function __construct(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId;
    }

    public static function reconstituteFromEvents(AggregateRootId $aggregateRootId, Generator $events): static
    {
        $aggregateRoot = self::createNewInstance($aggregateRootId);

        /** @var object $event */
        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRootVersion = $events->getReturn();

        // assert(is_int($aggregateRootVersion) && $aggregateRootVersion >= 0, 'Aggregate root version must be a non-negative integer.');

        $aggregateRoot->aggregateRootVersion = (is_int($aggregateRootVersion) && $aggregateRootVersion >= 0)
            ? $aggregateRootVersion
            : 0;

        return $aggregateRoot;
    }

    private static function createNewInstance(AggregateRootId $aggregateRootId): static
    {
        return new static($aggregateRootId);
    }
}
