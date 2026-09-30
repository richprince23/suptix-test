<?php

namespace App\Contracts;

interface SmsProviderInterface
{
    public function sendSms(string $phoneNumber, string $message): array;

    public function isConfigured(): bool;

    public function getProviderName(): string;
}
