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

namespace Dflydev\EventSauce\Support\Testing;

use Dflydev\EventSauce\Support\AggregateRoot\AggregateRootIdAware;
use Dflydev\EventSauce\Support\AggregateRoot\AggregateRootIdGeneration;
use Dflydev\EventSauce\Support\MessagePreparation\DefaultMessagePreparation;
use Dflydev\EventSauce\Support\MessagePreparation\MessagePreparation;
use Dflydev\EventSauce\Support\Testing\GivenWhenThen\Scenario;
use Dflydev\EventSauce\Support\Transaction\NoTransaction;
use Dflydev\EventSauce\Support\Transaction\Transaction;
use Dflydev\EventSauce\SupportForLaravel\AggregateRoot\EloquentAggregateRoot;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\InMemoryMessageRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;

/**
 * @template T1 of AggregateRoot
 * @template T2 of AggregateRootId
 */
trait AggregateRootTestingBehavior
{
    /** @var class-string<T1> */
    private static string $aggregateRootType;

    public Scenario $scenario;

    /**
     * @phpstan-var T2
     */
    private AggregateRootId $aggregateRootId;

    private InMemoryMessageRepository $messageRepository;

    private MessagePreparation $messagePreparation;

    private Transaction $transaction;

    private MessageDecorator $messageDecorator;

    private ClassNameInflector $classNameInflector;

    private mixed $scenarioConfiguration;

    /**
     * @return T2
     */
    protected static function newAggregateRootId(): AggregateRootId
    {
        /** @phpstan-var class-string<AggregateRootIdGeneration<T2>> $aggregateRootType */
        $aggregateRootType = self::aggregateRootType();

        if (!in_array(AggregateRootIdGeneration::class, class_implements($aggregateRootType), true)) {
            throw new \RuntimeException(sprintf(
                'Aggregate root type "%s" must implement "%s" or define "%s::newAggregateRootId()" directly.',
                $aggregateRootType,
                AggregateRootIdAware::class,
                static::class,
            ));
        }

        return $aggregateRootType::generateAggregateRootId();
    }

    /**
     * @param class-string<T1> $aggregateRootType
     */
    protected static function setAggregateRootType(string $aggregateRootType): void
    {
        self::$aggregateRootType = $aggregateRootType;
    }

    /**
     * @return class-string<T1>|class-string<EloquentAggregateRoot<T1,T2>>
     */
    protected static function aggregateRootType(): string
    {
        if (!isset(self::$aggregateRootType)) {
            throw new \LogicException('No aggregate root type specified. Did you forget to call self::setAggregateRootType() from setUpScenario()?');
        }

        return self::$aggregateRootType;
    }

    /**
     * @return list<MessageDecorator>
     */
    protected function messageDecorators(): array
    {
        return [];
    }

    /**
     * @return T2
     */
    protected function aggregateRootId(): AggregateRootId
    {
        if (!isset($this->aggregateRootId)) {
            $this->aggregateRootId = self::newAggregateRootId();
        }

        return $this->aggregateRootId;
    }

    protected function messageRepository(): InMemoryMessageRepository
    {
        if (!isset($this->messageRepository)) {
            $this->messageRepository = new InMemoryMessageRepository();
        }

        return $this->messageRepository;
    }

    protected function messagePreparation(): MessagePreparation
    {
        if (!isset($this->messagePreparation)) {
            $this->messagePreparation = new DefaultMessagePreparation(
                $this->messageDecorator(),
                $this->classNameInflector()
            );
        }

        return $this->messagePreparation;
    }

    protected function transaction(): Transaction
    {
        if (!isset($this->transaction)) {
            $this->transaction = new NoTransaction();
        }

        return $this->transaction;
    }

    protected function messageDecorator(): MessageDecorator
    {
        if (!isset($this->messageDecorator)) {
            $messageDecorators = array_merge(
                [new DefaultHeadersDecorator()],
                $this->messageDecorators()
            );

            $this->messageDecorator = new MessageDecoratorChain(...$messageDecorators);
        }

        return $this->messageDecorator;
    }

    protected function classNameInflector(): ClassNameInflector
    {
        if (!isset($this->classNameInflector)) {
            $this->classNameInflector = new DotSeparatedSnakeCaseInflector();
        }

        return $this->classNameInflector;
    }

    abstract protected function setUpScenario(): void;

    /**
     * @before
     */
    protected function setUpScenarioBeforeHook(): void
    {
        $this->setUpScenario();

        $this->scenario = $this->applyScenarioConfiguration(Scenario::new()
            ->usingMessageRepository($this->messageRepository())
            ->usingMessagePreparation($this->messagePreparation())
            ->withAggregateRootId($this->aggregateRootId()));
    }

    protected function applyScenarioConfiguration(Scenario $scenario): Scenario
    {
        if (!isset($this->scenarioConfiguration)) {
            return $scenario;
        }

        $scenarioConfiguration = $this->scenarioConfiguration;

        return $scenarioConfiguration($scenario);
    }
}
