<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\LazyMessageConsumption;

use Dflydev\EventSauce\Support\MessagePayloadConsumption\MessagePayloadConsumer;
use Dflydev\EventSauce\Support\MessagePayloadConsumption\SupportsAwareMessagePayloadConsumer;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template-covariant T of MessageConsumer
 * @template-covariant T of MessagePayloadConsumer
 * @template-covariant T of SupportsAwareMessagePayloadConsumer
 */
final readonly class LazyMessageConsumer implements MessageConsumer
{
    /**
     * @param class-string<T> $actualMessageConsumerClass
     */
    public function __construct(
        private ContainerInterface $container,
        private string $actualMessageConsumerClass
    ) {
    }

    public function actualConsumerClassName(): string
    {
        return $this->actualMessageConsumerClass;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(Message $message): void
    {
        $actualMessageConsumerClass = $this->actualMessageConsumerClass;
        $payload = $message->payload();

        if (in_array(SupportsAwareMessagePayloadConsumer::class, class_implements($actualMessageConsumerClass))) {
            /** @var SupportsAwareMessagePayloadConsumer $actualMessageConsumerClass */
            if (!$actualMessageConsumerClass::supportsMessage($payload)) {
                return;
            }
        }

        if (in_array(MessagePayloadConsumer::class, class_implements($actualMessageConsumerClass))) {
            /** @var MessagePayloadConsumer $actualConsumer */
            $actualConsumer = $this->container->get($this->actualMessageConsumerClass);

            $actualConsumer->handleMessagePayload($payload, $message);

            return;
        }

        if (in_array(MessageConsumer::class, class_implements($actualMessageConsumerClass))) {
            /** @var MessageConsumer $actualConsumer */
            $actualConsumer = $this->container->get($this->actualMessageConsumerClass);

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
