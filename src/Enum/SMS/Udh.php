<?php

namespace Smsbox\Enum\SMS;

final class Udh
{
    public const DISABLED_CONCAT = 0;
    public const SIX_BYTES       = 1;
    public const SEVEN_BYTES     = 2;

    /**
     * @return array<int, int>
     */
    public static function getAll(): array
    {
        return [
            self::DISABLED_CONCAT,
            self::SIX_BYTES,
            self::SEVEN_BYTES,
        ];
    }
}
