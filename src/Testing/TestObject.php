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

/**
 * @template T of object
 */
class TestObject
{
    /** @var array<string,mixed> */
    private array $arguments = [];

    /** @var list<array<string,mixed>|callable> */
    private mixed $defaults = [];

    /**
     * @param class-string<T> $className
     */
    final private function __construct(
        private readonly string $className,
    ) {
    }

    /**
     * @param class-string<T> $className
     */
    public static function ofType(string $className): static
    {
        return (new static($className))
            ->withDefaults(static::defaultDefinition(...));
    }

    /**
     * @param array<int,mixed> $arguments
     *
     * @throws \ReflectionException
     */
    public function fromPartial(array $arguments): static
    {
        $instance = clone $this;

        $reflectionClas = new \ReflectionClass($instance->className);

        $constructor = $reflectionClas->getConstructor();

        if (is_null($constructor)) {
            throw new \RuntimeException('No constructor found for class "'.$instance->className.'".');
        }

        foreach ($constructor->getParameters() as $index => $parameter) {
            if (!array_key_exists($index, $arguments)) {
                break;
            }

            if (is_null($arguments[$index]) && !$parameter->isOptional()) {
                continue;
            }

            $instance->arguments[$parameter->getName()] = $arguments[$index];
        }

        return $instance;
    }

    /**
     * @param array<string,mixed>|callable $definition
     *
     * @return $this
     */
    public function withDefaults(mixed $definition): static
    {
        $instance = clone $this;
        $instance->defaults[] = $definition;

        return $instance;
    }

    /**
     * @return T
     */
    public function build(): object
    {
        $className = $this->className;

        $arguments = array_map(fn ($argument) => is_callable($argument) ? $argument() : $argument, $this->arguments);

        /** @var array $definition */
        $definition = call_user_func_array(
            'array_merge',
            array_map(fn ($defaults) => is_callable($defaults) ? ($defaults)() : $defaults, $this->defaults)
        );

        foreach ($definition as $argumentName => $argumentValue) {
            if (array_key_exists($argumentName, $arguments)) {
                continue;
            }

            $arguments[$argumentName] = is_callable($argumentValue) ? $argumentValue() : $argumentValue;
        }

        return new $className(...$arguments);
    }

    public static function defaultDefinition(): array
    {
        return [];
    }
}
