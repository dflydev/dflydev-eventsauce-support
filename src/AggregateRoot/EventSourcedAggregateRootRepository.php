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

namespace Dflydev\EventSauce\Support\AggregateRoot;

use Dflydev\EventSauce\Support\MessagePreparation\DefaultMessagePreparation;
use Dflydev\EventSauce\Support\MessagePreparation\MessagePreparation;
use Dflydev\EventSauce\Support\Transaction\Transaction;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\UnableToPersistMessages;
use EventSauce\EventSourcing\UnableToReconstituteAggregateRoot;
use EventSauce\MessageOutbox\OutboxRepository;
use Generator;
use Throwable;

use function assert;
use function count;

/**
 * @template T of AggregateRoot
 *
 * @implements AggregateRootRepository<T>
 *
 * @property class-string<T> $aggregateRootClassName
 */
final readonly class EventSourcedAggregateRootRepository implements AggregateRootRepository
{
    private readonly MessagePreparation $messagePreparation;

    /**
     * @param class-string<T> $aggregateRootClassName
     */
    public function __construct(
        private string $aggregateRootClassName,
        private Transaction $transaction,
        private MessageRepository $messageRepository,
        ?MessagePreparation $messagePreparation = null,
        private ?MessageDispatcher $transactionalMessageDispatcher = null,
        private ?MessageDispatcher $synchronousMessageDispatcher = null,
        private ?OutboxRepository $outboxRepository = null,
    ) {
        $this->messagePreparation = $messagePreparation ?? new DefaultMessagePreparation();
    }

    private function retrieveAllEvents(AggregateRootId $aggregateRootId): Generator
    {
        $messages = $this->messageRepository->retrieveAll($aggregateRootId);

        foreach ($messages as $message) {
            yield $message->payload();
        }

        return $messages->getReturn();
    }

    /**
     * @return T
     */
    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        try {
            $className = $this->aggregateRootClassName;
            $events = $this->retrieveAllEvents($aggregateRootId);

            return $className::reconstituteFromEvents($aggregateRootId, $events);
        } catch (Throwable $throwable) {
            throw UnableToReconstituteAggregateRoot::becauseOf($throwable->getMessage(), $throwable);
        }
    }

    public function persist(object $aggregateRoot): void
    {
        assert($aggregateRoot instanceof AggregateRoot, 'Expected $aggregateRoot to be an instance of '.AggregateRoot::class);

        $this->persistEvents(
            $aggregateRoot->aggregateRootId(),
            $aggregateRoot->aggregateRootVersion(),
            ...$aggregateRoot->releaseEvents()
        );
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
    {
        $messages = $this->messagePreparation->prepareMessages(
            $this->aggregateRootClassName,
            $aggregateRootId,
            $aggregateRootVersion,
            ...$events
        );

        if (count($messages) === 0) {
            return;
        }

        try {
            $this->transaction->begin();

            try {
                $this->messageRepository->persist(...$messages);
                $this->outboxRepository?->persist(...$messages);
                $this->transactionalMessageDispatcher?->dispatch(...$messages);

                $this->transaction->commit();
            } catch (Throwable $exception) {
                $this->transaction->rollBack();
                throw $exception;
            }
        } catch (Throwable $exception) {
            throw UnableToPersistMessages::dueTo('', $exception);
        }

        $this->synchronousMessageDispatcher?->dispatch(...$messages);
    }
}
