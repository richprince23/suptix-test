<?php

namespace App\Helpers;

class PhoneFormatter
{
    /**
     * Format a Ghana phone number to E.164 (+233xxxxxxxxx).
     * Returns null if the number cannot be normalised.
     */
    public static function toE164Ghana(string $phoneNumber): ?string
    {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        return match (true) {
            strlen($phone) === 10 && str_starts_with($phone, '0') => '+233'.substr($phone, 1),
            strlen($phone) === 9                                   => '+233'.$phone,
            strlen($phone) === 12 && str_starts_with($phone, '233') => '+'.$phone,
            strlen($phone) === 13 && str_starts_with($phone, '+233') => $phone,
            default => null,
        };
    }
}
