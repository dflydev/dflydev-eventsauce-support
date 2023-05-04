<?php

declare(strict_types=1);

namespace Dflydev\EventSauce\Support\Transaction;

interface Transaction
{
    public function begin(): void;

    public function commit(): void;

    public function rollBack(): void;
}
