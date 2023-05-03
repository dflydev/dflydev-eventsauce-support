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

use Dflydev\EventSauce\Support\Identity\IdentityGeneration;
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
 * @see AggregateRoot
 * @see EventSourcedAggregate
 */
trait EventedAggregateRootBehaviour
{
    /**
     * @phpstan-var T
     */
    private AggregateRootId $aggregateRootId;

    /** @phpstan-var 0|positive-int */
    private int $aggregateRootVersion = 0;

    /** @var object[] */
    private array $recordedEvents = [];

    /**
     * @phpstan-return T
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
        /** @phpstan-var class-string<AggregateRootIdAware> $thisClass */
        $thisClass = static::class;

        assert(in_array(AggregateRootIdAware::class, class_implements($thisClass)), "Aggregate root \"$thisClass\" must implement AggregateRootIdAware.");

        /** @var class-string<IdentityGeneration> $identityClassName */
        $identityClassName = $thisClass::aggregateRootIdClassName();

        assert(in_array(IdentityGeneration::class, class_implements($identityClassName)), "Aggregate root identity \"$identityClassName\" must implement IdentityGeneration.");

        return $identityClassName::generate();
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

    /**
     * @phpstan-param T $aggregateRootId
     */
    public static function reconstituteFromEvents(AggregateRootId $aggregateRootId, Generator $events): static
    {
        throw new \LogicException('Evented aggregate roots cannot be reconstituted from events.');
    }
}
