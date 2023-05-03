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

namespace Dflydev\EventSauce\Support;

use Throwable;

/**
 * @param string|callable():(Throwable|string) $message
 * @param class-string<Throwable>|null $throwableType
 *
 * @throws \RuntimeException
 */
function guard(bool $assertion, string|callable $message, ?string $throwableType = null): void
{
    if ($assertion) {
        return;
    }

    if (is_callable($message)) {
        $message = $message();
    }

    if ($message instanceof Throwable) {
        throw $message;
    }

    $throwableType ??= \RuntimeException::class;

    throw new $throwableType($message);
}

/**
 * @template T of Throwable
 *
 * @param class-string<T> $throwableType
 * @param string|callable():string $message
 *
 * @return callable():T
 */
function lazyThrow(string $throwableType, string|callable $message = '', int $code = 0, ?Throwable $previous = null): callable
{
    return fn () => new $throwableType(is_callable($message) ? $message() : $message, $code, $previous);
}
