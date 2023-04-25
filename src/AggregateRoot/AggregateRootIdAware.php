<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRootId;

/**
 * @template T of AggregateRootId
 */
interface AggregateRootIdAware
{
    /**
     * @return T
     */
    public function aggregateRootId(): AggregateRootId;

    /** @return class-string<T> */
    public static function aggregateRootIdClassName(): string;
}
