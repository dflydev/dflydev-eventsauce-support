<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Testing;

use Dflydev\EventSauce\Support\AggregateRoot\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;

/**
 * @template T1 of AggregateRoot
 * @template T2 of AggregateRootId
 */
trait EventSourcedAggregateRootTestingBehavior
{
    /**
     * @use AggregateRootTestingBehavior<T1,T2>
     */
    use AggregateRootTestingBehavior;

    /**
     * @return AggregateRootRepository<T1>
     */
    protected function eventSourcedAggregateRootRepository(): AggregateRootRepository
    {
        return new EventSourcedAggregateRootRepository(
            $this->aggregateRootType(),
            $this->transaction(),
            $this->messageRepository(),
            $this->messagePreparation()
        );
    }

    public static function configureForEventSourcedAggregateRootType(string $aggregateRootType): void
    {
        self::setAggregateRootType($aggregateRootType);
    }
}
