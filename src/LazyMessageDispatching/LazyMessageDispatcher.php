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

namespace Dflydev\EventSauce\Support\LazyMessageDispatching;

use Dflydev\EventSauce\Support\LazyMessageConsumption\LazyMessageConsumer;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Psr\Container\ContainerInterface;

final class LazyMessageDispatcher implements MessageDispatcher
{
    private array $messageConsumerClassNames;
    private MessageDispatcher $messageDispatcher;

    public function __construct(private ContainerInterface $container, string ...$messageConsumerClassNames)
    {
        $this->messageConsumerClassNames = $messageConsumerClassNames;
    }

    public function dispatch(Message ...$messages): void
    {
        if (!isset($this->messageDispatcher)) {
            $messageConsumers = array_map(fn ($className) => new LazyMessageConsumer($this->container, $className), $this->messageConsumerClassNames);

            $this->messageDispatcher = new SynchronousMessageDispatcher(...$messageConsumers);
        }

        $this->messageDispatcher->dispatch(...$messages);
    }
}
