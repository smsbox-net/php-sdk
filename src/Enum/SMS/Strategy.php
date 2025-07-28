<?php

namespace Smsbox\Enum\SMS;

final class Strategy
{
    public const PRIVATE             = 1;
    public const NOTIFICATION        = 2;
    public const NOT_MARKETING_GROUP = 3;
    public const MARKETING           = 4;

    /**
     * @return array<int, int>
     */
    public static function getAll(): array
    {
        return [
            self::PRIVATE,
            self::NOTIFICATION,
            self::NOT_MARKETING_GROUP,
            self::MARKETING,
        ];
    }
}
