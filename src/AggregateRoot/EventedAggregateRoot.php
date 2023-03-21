<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\EventSourcedAggregate;

/**
 * @template T1 of AggregateRoot
 * @template T2 of AggregateRootId
 *
 * @extends AggregateRoot<T2>
 * @extends AggregateRootIdAware<T2>
 * @extends AggregateRootIdGeneration<T2>
 */
interface EventedAggregateRoot extends AggregateRoot, AggregateRootIdAware, AggregateRootIdGeneration, EventSourcedAggregate
{
}
