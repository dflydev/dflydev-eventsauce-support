<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Testing\GivenWhenThen;

final readonly class ThenEvent implements Then
{
    public function __construct(public object $event)
    {
    }
}
