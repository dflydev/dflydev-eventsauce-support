<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Serialization;

trait EmptyPayloadSerializing
{
    final public function __construct()
    {
    }

    public function toPayload(): array
    {
        return [];
    }

    public static function fromPayload(array $payload): static
    {
        return new static();
    }
}
