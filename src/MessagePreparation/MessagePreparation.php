<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\MessagePreparation;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;

interface MessagePreparation
{
    /** @return Message[] */
    public function prepareMessages(
        string $aggregateRootClassName,
        AggregateRootId $aggregateRootId,
        int $aggregateRootVersion,
        object ...$events
    ): array;
}
