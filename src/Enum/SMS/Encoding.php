<?php

namespace Smsbox\Enum\SMS;

final class Encoding
{
    public const DEFAULT = 'default';
    public const UNICODE = 'unicode';
    public const AUTO    = 'auto';

    /**
     * @return array<int, string>
     */
    public static function getAll(): array
    {
        return [
            self::AUTO,
            self::DEFAULT,
            self::UNICODE,
        ];
    }
}
