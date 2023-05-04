<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Testing\GivenWhenThen;

final readonly class ThenCommand
{
    public function __construct(public object $command)
    {
    }
}
