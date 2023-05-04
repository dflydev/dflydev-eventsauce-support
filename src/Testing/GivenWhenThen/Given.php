<?php

declare(strict_types=1);

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
