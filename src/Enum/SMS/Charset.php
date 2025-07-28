<?php

namespace Smsbox\Enum\SMS;

final class Charset
{
    public const ISO1  = 'iso-8859-1';
    public const ISO15 = 'iso-8859-15';
    public const UTF8  = 'utf-8';

    /**
     * @return array<int, string>
     */
    public static function getAll(): array
    {
        return [
            self::ISO1,
            self::ISO15,
            self::UTF8,
        ];
    }

    public static function isValid(string $charset): bool
    {
        return in_array($charset, self::getAll(), true);
    }
}
