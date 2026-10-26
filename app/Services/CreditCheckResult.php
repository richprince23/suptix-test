<?php

namespace App\Services;

class CreditCheckResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly string $reason = '',
    ) {}
}
