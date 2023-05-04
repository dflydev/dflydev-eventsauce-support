<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Serialization;

interface SerializablePayloadEdgeValue
{
    public function toPayloadValue(): string|array|null;

    public static function fromPayloadValue(string|array|null $value): static;
}
