<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;

/**
 * @template T1 of AggregateRoot
 * @template T2 of AggregateRootId
 *
 * @extends EventedAggregateRoot<T1,T2>
 */
interface EventSourcedAggregateRoot extends EventedAggregateRoot
{
}
