<?php

namespace Smsbox\Enum\SMS;

final class Mode
{
    public const STANDARD = 'Standard';
    public const EXPERT   = 'Expert';
    public const RESPONSE = 'Reponse';

    /**
     * @return array<int, string>
     */
    public static function getAll(): array
    {
        return [
            self::STANDARD,
            self::EXPERT,
            self::RESPONSE,
        ];
    }
}
