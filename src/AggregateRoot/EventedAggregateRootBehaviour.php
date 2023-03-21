<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\EventSourcedAggregate;
use Generator;

/**
 * @template T of AggregateRootId
 *
 * @method void apply(object $event)
 *
 * @see AggregateRootBehaviour
 * @see AggregateRoot<T>
 * @see EventSourcedAggregate
 */
trait EventedAggregateRootBehaviour
{
    private AggregateRootId $aggregateRootId;

    /** @phpstan-var 0|positive-int */
    private int $aggregateRootVersion = 0;

    /** @var object[] */
    private array $recordedEvents = [];

    /**
     * @return T
     */
    public function aggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }

    public function aggregateRootVersion(): int
    {
        return $this->aggregateRootVersion;
    }

    protected function recordThat(object $event): void
    {
        $this->apply($event);
        $this->recordedEvents[] = $event;
    }

    /**
     * @phpstan-return T
     */
    public static function generateAggregateRootId(): AggregateRootId
    {
        /** @var AggregateRootIdGeneration $identityClassName */
        $identityClassName = static::aggregateRootIdClassName();

        assert(in_array(AggregateRootIdGeneration::class, class_implements($identityClassName)), 'Aggregate root identity class must implement AggregateRootIdGeneration.');

        return $identityClassName::generateAggregateRootId();
    }

    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $releasedEvents = $this->recordedEvents;
        $this->recordedEvents = [];

        return $releasedEvents;
    }

    public static function reconstituteFromEvents(AggregateRootId $aggregateRootId, Generator $events): static
    {
        throw new \LogicException('Evented aggregate roots cannot be reconstituted from events.');
    }
}
