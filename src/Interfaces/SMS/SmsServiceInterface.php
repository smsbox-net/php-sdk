<?php

namespace Smsbox\Interfaces\SMS;

use Smsbox\Messages\SmsMessage;

interface SmsServiceInterface
{
    /**
     * Envoie un message SMS via l'API SMSBOX.
     *
     * @param SmsMessage $message
     *
     * @return array{data: string, code: int, refId: array<string>}
     */
    public function send(SmsMessage $message): array;
}
