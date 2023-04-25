<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRootId;

/**
 * @template T of AggregateRootId
 */
interface AggregateRootIdGeneration
{
    /** @return T */
    public static function generateAggregateRootId(): AggregateRootId;
}
