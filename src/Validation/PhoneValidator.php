<?php

namespace Smsbox\Validation;

use Smsbox\Exception\SmsboxException;

class PhoneValidator
{
    /**
     * @param array<string> $phones
     *
     * @return array<string>
     *
     * @throws SmsboxException
     */
    public static function sanitizePhoneNumbers(array $phones): array
    {
        return array_map(function ($phones) {
            if (!is_string($phones)) {
                throw new SmsboxException('Phone must be a string.');
            }

            $clean = (string) preg_replace('/[^0-9+]/', '', $phones);

            if (!preg_match('/^\+?[0-9]{7,14}$/', $clean)) {
                throw new SmsboxException("Invalid phone: '{$phones}'");
            }

            return $clean;
        }, $phones);
    }
}
