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

use Dflydev\EventSauce\Support\AggregateRoot\AggregateRootAware;
use Dflydev\EventSauce\Support\MessagePreparation\DefaultMessagePreparation;
use Dflydev\EventSauce\Support\MessagePreparation\MessagePreparation;
use Dflydev\EventSauce\Support\Testing\TestObject;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\InMemoryMessageRepository;
use EventSauce\EventSourcing\Message;
use PHPUnit\Framework\Assert;

final class Scenario
{
    /**
     * @var string class-string<AggregateRoot>
     */
    private string $aggregateRootType;
    private AggregateRootId $aggregateRootId;
    private Given $given;
    private When $when;
    private Outcome $outcome;
    private mixed $handler;
    private InMemoryMessageRepository $messageRepository;
    private MessagePreparation $messagePreparation;
    private \Closure $visitMessages;
    private \Closure $visitEvents;

    /**
     * @var \Throwable|class-string<\Throwable>
     */
    private \Throwable|string $expect;

    private function __construct(
    ) {
    }

    public static function new(): self
    {
        return new self();
    }

    public function withAggregateRootId(AggregateRootId $aggregateRootId): self
    {
        $instance = clone $this;
        $instance->aggregateRootId = $aggregateRootId;

        if ($aggregateRootId instanceof AggregateRootAware) {
            $instance->aggregateRootType = $aggregateRootId->aggregateRootClassName();
        }

        return $instance;
    }

    /**
     * @template T of object
     *
     * @param T ...$events
     */
    public function given(object ...$events): self
    {
        $instance = clone $this;
        $instance->given = new Given(...$events);

        return $instance;
    }

    public function when(object $command): self
    {
        $instance = clone $this;
        $instance->when = new When($command);

        return $instance;
    }

    public function then(object $event): self
    {
        $instance = clone $this;
        $instance->outcome = new ThenEvent($event);

        return $instance;
    }

    public function thenNothing(): self
    {
        $instance = clone $this;
        $instance->outcome = new ThenNothing();

        return $instance;
    }

    public function handledBy(mixed $handler): self
    {
        $instance = clone $this;
        $instance->handler = $handler;

        return $instance;
    }

    /**
     * @param \Throwable|class-string<\Throwable> $expect
     */
    public function expect(\Throwable|string $expect): self
    {
        $instance = clone $this;
        $instance->expect = $expect;

        return $instance->thenNothing();
    }

    public function visitMessages(\Closure $visitMessages): self
    {
        $instance = clone $this;
        $instance->visitMessages = $visitMessages;

        return $instance;
    }

    public function visitEvents(\Closure $visitEvents): self
    {
        $instance = clone $this;
        $instance->visitEvents = $visitEvents;

        return $instance;
    }

    public function assert(): self
    {
        $instance = clone $this;

        $messageRepository = $this->messageRepository ?? new InMemoryMessageRepository();

        if (isset($instance->given)) {
            $messagePreparation = $this->messagePreparation ?? new DefaultMessagePreparation();

            $messages = $messagePreparation->prepareMessages(
                $instance->aggregateRootType,
                $instance->aggregateRootId,
                count($instance->given->events),
                ...array_map(fn ($event) => $event instanceof TestObject ? $event->build() : $event, $instance->given->events)
            );

            $messageRepository->persist(...$messages);

            $messageRepository->purgeLastCommit();

            if (isset($instance->visitMessages)) {
                ($instance->visitMessages)($messages);
            }

            if (isset($instance->visitEvents)) {
                ($instance->visitEvents)(...array_map(fn (Message $item) => $item->payload(), $messages));
            }
        }

        $handler = $instance->handler;

        if (!is_callable($handler)) {
            if (!is_object($handler)) {
                throw new \InvalidArgumentException('Handler must be an object');
            }

            if (!method_exists($handler, 'handle')) {
                throw new \InvalidArgumentException('Handler must have a "handle" method');
            }

            $handler = [$handler, 'handle'];
        }

        try {
            ($handler)($instance->when->command);
        } catch (\Throwable $throwable) {
            if (isset($instance->expect)) {
                if (is_string($instance->expect)) {
                    Assert::assertInstanceOf($instance->expect, $throwable);
                } else {
                    Assert::assertEquals($instance->expect, $throwable);
                }
            } else {
                throw $throwable;
            }
        }

        $recordedEvents = $messageRepository->lastCommit();

        if (!isset($instance->outcome)) {
            Assert::fail('Expected an outcome.');
        }

        if ($instance->outcome instanceof ThenNothing) {
            Assert::assertCount(0, $recordedEvents, 'Expected no events.');
        }

        if ($instance->outcome instanceof ThenEvent) {
            Assert::assertCount(1, $recordedEvents, 'Expected exactly one event.');

            $recordedEvent = $recordedEvents[0];
            $expectedEvent = $instance->outcome->event;

            if ($expectedEvent instanceof TestObject) {
                $expectedEvent = $expectedEvent->build();
            }

            Assert::assertEquals($expectedEvent, $recordedEvent);
        }

        return $instance;
    }

    public function usingMessagePreparation(MessagePreparation $messagePreparation): self
    {
        $instance = clone $this;
        $instance->messagePreparation = $messagePreparation;

        return $instance;
    }

    public function usingMessageRepository(InMemoryMessageRepository $messageRepository): self
    {
        $instance = clone $this;
        $instance->messageRepository = $messageRepository;

        return $instance;
    }
}
