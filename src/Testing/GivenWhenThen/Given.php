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

namespace Dflydev\EventSauce\Support\Testing\GivenWhenThen;

final readonly class Given
{
    /**
     * @var array<object>
     */
    public array $events;

    /**
     * @template T of object
     *
     * @param T ...$events
     */
    public function __construct(object ...$events)
    {
        $this->events = $events;
    }
}
