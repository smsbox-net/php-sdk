<?php

namespace Smsbox;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Smsbox\Exception\SmsboxException;
use Smsbox\Interfaces\SMS\SmsboxClientInterface;
use Smsbox\Messages\SmsMessage;
use Smsbox\Services\SmsService;

class SmsboxClient implements SmsboxClientInterface
{
    /**
     * @var SmsService
     */
    private SmsService $smsService;

    /**
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * @param string               $apiKey  API key provided by SMSBOX
     * @param float                $timeout Maximum time in seconds for the request to complete, default 10 seconds
     * @param ClientInterface|null $client  Optional Guzzle HTTP client
     */
    public function __construct(string $apiKey, float $timeout = 10.0, ?ClientInterface $client = null)
    {
        $this->smsService = new SmsService($apiKey, $timeout, $client);
    }

    /**
     * Sends an SMS message via the SMSBOX service.
     *
     * @param SmsMessage $message The SMS message to be sent
     *
     * @return array<string, mixed> The API response
     *
     * @throws SmsboxException If there is an error in the SMS service
     * @throws GuzzleException If there is a network/HTTP error
     */
    public function sendSms(SmsMessage $message): array
    {
        return $this->smsService->send($message);
    }
}
