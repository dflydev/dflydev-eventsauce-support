<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Identity;

use EventSauce\EventSourcing\AggregateRootId;

/**
 * @template-covariant T of AggregateRootId
 */
interface IdentityGeneration
{
    /**
     * @return static(T)
     */
    public static function generate(): static;
}
