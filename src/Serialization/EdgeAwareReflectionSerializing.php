<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Serialization;

use DateTimeInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionObject;

trait EdgeAwareReflectionSerializing
{
    public function toPayload(): array
    {
        $payload = [];

        $object = new ReflectionObject($this);
        foreach ($object->getProperties() as $property) {
            $key = $property->getName();
            $value = $property->getValue($this);

            $type = $property->getType();

            if (!is_null($value) && $type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                /** @phpstan-var class-string<object>|object $value */
                $targetClass = new ReflectionClass($value);

                if ($targetClass->implementsInterface(SerializablePayloadEdgeKey::class)) {
                    /** @phpstan-var class-string<SerializablePayloadEdgeKey> $value */
                    $key = $value::getPayloadKey();
                }

                if ($targetClass->implementsInterface(SerializablePayloadEdgeValue::class)) {
                    /** @var SerializablePayloadEdgeValue $value */
                    $convertedValue = $value->toPayloadValue();

                    $value = $convertedValue;
                }

                if ($targetClass->implementsInterface(DateTimeInterface::class) && $value) {
                    /** @var DateTimeInterface $value */
                    $convertedValue = $value->format('Y-m-d\TH:i:s.uP');

                    $value = $convertedValue;
                }
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    /**
     * @throws \ReflectionException
     */
    public static function fromPayload(array $payload): static
    {
        $class = new ReflectionClass(static::class);

        if (!$class->getConstructor()) {
            return $class->newInstance();
        }

        $args = [];

        foreach ($class->getConstructor()->getParameters() as $parameter) {
            $constructorKey = $key = $parameter->getName();

            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $targetClassName = $type->getName();

                $targetClass = new ReflectionClass($targetClassName);

                if ($targetClass->implementsInterface(SerializablePayloadEdgeKey::class)) {
                    /** @var SerializablePayloadEdgeKey $targetClassName */
                    $key = $targetClassName::getPayloadKey();
                }

                if (!array_key_exists($key, $payload)) {
                    continue;
                }

                if ($payload[$key] && $targetClass->implementsInterface(DateTimeInterface::class)) {
                    $payload[$key] = $targetClass->newInstance($payload[$key]);
                }

                if ($payload[$key] && $targetClass->implementsInterface(SerializablePayloadEdgeValue::class)) {
                    /** @var SerializablePayloadEdgeValue $targetClassName */
                    $payload[$key] = $targetClassName::fromPayloadValue($payload[$key]);
                }
            }

            if (array_key_exists($key, $payload)) {
                $args[$constructorKey] = $payload[$key];
            }
        }

        return $class->newInstance(...$args);
    }
}
