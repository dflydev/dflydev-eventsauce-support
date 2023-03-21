<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\LazyMessageConsumption;

use Dflydev\EventSauce\Support\MessagePayloadConsumption\MessagePayloadHandler;
use Dflydev\EventSauce\Support\MessagePayloadConsumption\SupportsAwareMessagePayloadHandler;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template-covariant T of MessageConsumer
 * @template-covariant T of MessagePayloadHandler
 * @template-covariant T of SupportsAwareMessagePayloadHandler
 */
final readonly class LazyMessageConsumer implements MessageConsumer
{
    /**
     * @param class-string<T> $actualConsumerClassName
     */
    public function __construct(
        private ContainerInterface $container,
        private string $actualConsumerClassName
    ) {
    }

    public function actualConsumerClassName(): string
    {
        return $this->actualConsumerClassName;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(Message $message): void
    {
        $actualConsumerClassName = $this->actualConsumerClassName;
        $payload = $message->payload();

        if (in_array(SupportsAwareMessagePayloadHandler::class, class_implements($actualConsumerClassName))) {
            /** @var SupportsAwareMessagePayloadHandler $actualConsumerClassName */
            if (!$actualConsumerClassName::supportsMessage($payload)) {
                return;
            }
        }

        if (in_array(MessagePayloadHandler::class, class_implements($actualConsumerClassName))) {
            /** @var MessagePayloadHandler $actualConsumer */
            $actualConsumer = $this->container->get($this->actualConsumerClassName);

            $actualConsumer->handleMessagePayload($payload, $message);

            return;
        }

        if (in_array(MessageConsumer::class, class_implements($actualConsumerClassName))) {
            /** @var MessageConsumer $actualConsumer */
            $actualConsumer = $this->container->get($this->actualConsumerClassName);

            $actualConsumer->handle($message);

            return;
        }

        $parts = explode('\\', get_class($payload));
        $method = 'handle'.end($parts);

        if (method_exists($this, $method)) {
            $this->{$method}($payload, $message);
        }
    }
}
