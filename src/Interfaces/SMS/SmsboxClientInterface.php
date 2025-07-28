<?php

namespace Smsbox\Interfaces\SMS;

use GuzzleHttp\Exception\GuzzleException;
use Smsbox\Exception\SmsboxException;
use Smsbox\Messages\SmsMessage;

interface SmsboxClientInterface
{
    /**
     * @param SmsMessage $message
     *
     * @return array<string, mixed>
     *
     * @throws SmsboxException|GuzzleException
     */
    public function sendSms(SmsMessage $message): array;
}
