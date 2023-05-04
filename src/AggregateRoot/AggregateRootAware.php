<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\AggregateRoot;

use EventSauce\EventSourcing\AggregateRoot;

/**
 * @template T of AggregateRoot
 */
interface AggregateRootAware
{
    /**
     * @return class-string<T>
     */
    public static function aggregateRootClassName(): string;
}
