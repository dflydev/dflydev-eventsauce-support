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
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;

final readonly class DefaultMessagePreparation implements MessagePreparation
{
    private MessageDecorator $messageDecorator;
    private ClassNameInflector $classNameInflector;

    public function __construct(
        ?MessageDecorator $messageDecorator = null,
        ?ClassNameInflector $classNameInflector = null,
    ) {
        $this->messageDecorator = $messageDecorator ?? new DefaultHeadersDecorator();
        $this->classNameInflector = $classNameInflector ?? new DotSeparatedSnakeCaseInflector();
    }

    public function prepareMessages(
        string $aggregateRootClassName,
        AggregateRootId $aggregateRootId,
        int $aggregateRootVersion,
        object ...$events
    ): array {
        if (count($events) === 0) {
            return [];
        }

        // decrease the aggregate root version by the number of raised events
        // so the version of each message represents the version at the time
        // of recording.
        $aggregateRootVersion = $aggregateRootVersion - count($events);
        $metadata = [
            Header::AGGREGATE_ROOT_ID => $aggregateRootId,
            Header::AGGREGATE_ROOT_TYPE => $this->classNameInflector->classNameToType($aggregateRootClassName),
        ];

        return array_map(function (object $event) use ($metadata, &$aggregateRootVersion) {
            return $this->messageDecorator->decorate(new Message(
                $event,
                $metadata + [Header::AGGREGATE_ROOT_VERSION => ++$aggregateRootVersion]
            ));
        }, $events);
    }
}
