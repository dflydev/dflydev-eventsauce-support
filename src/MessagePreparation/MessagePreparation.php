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
