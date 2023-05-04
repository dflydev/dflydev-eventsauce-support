<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Serialization;

interface SerializablePayloadEdgeKey
{
    public static function getPayloadKey(): string;
}
