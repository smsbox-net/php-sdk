<?php

namespace Smsbox\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Smsbox\Enum\SMS\Mode;
use Smsbox\Exception\SmsboxException;
use Smsbox\Http\HttpClientBuilder;
use Smsbox\Messages\SmsMessage;

class SmsService
{
    private ClientInterface $client;

    public function __construct(string $apiKey, float $timeout, ?ClientInterface $client = null)
    {
        $this->client = HttpClientBuilder::build(
            'https://api.smsbox.pro/1.1/api.php',
            $apiKey,
            $timeout,
            $client
        );
    }

    /**
     * @param SmsMessage $message
     *
     * @return array{data: string, code: int, refId: array<string>}
     *
     * @throws GuzzleException|SmsboxException
     */
    public function send(SmsMessage $message): array
    {
        $phones  = $message->getPhones();
        $options = $this->buildOptions($message, $phones);

        try {
            $response = $this->client->request('POST', '', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $options,
            ]);
        } catch (RequestException $e) {
            $context = $e->getHandlerContext();

            if (($context['errno'] ?? null) === CURLE_OPERATION_TIMEOUTED) {
                if (($context['total_time'] ?? 0) >=
                    (($context['connect_time'] ?? 0) + ($context['pretransfer_time'] ?? 0))
                ) {
                    throw new SmsboxException('Response from API was not received. Please check if the request was processed before retrying.', $e->getCode(), $e);
                }
                throw new SmsboxException('Request was not sent to the API.', $e->getCode(), $e);
            }
            throw new SmsboxException($e->getMessage(), $e->getCode());
        }

        $body = (string) $response->getBody();
        $code = $response->getStatusCode();

        if (!preg_match('/^OK ([\d,]+)/', $body, $match)) {
            $this->handleApiError($body);
        }

        /** @var array{0: string, 1: string} $match */
        return [
            'data'  => 'Send successfully. (OK)',
            'code'  => $code,
            'refId' => explode(',', $match[1]),
        ];
    }

    /**
     * @param SmsMessage    $message
     * @param array<string> $phones
     *
     * @return array<string, mixed>
     *
     * @throws SmsboxException
     */
    private function buildOptions(SmsMessage $message, array $phones): array
    {
        $base = [
            'dest'  => implode(',', $phones),
            'msg'   => $message->getContent(),
            'id'    => 1,
            'usage' => 'php-sdk',
        ];

        $options = $message->getOptions();

        if ($options !== null) {

            if (!method_exists($options, 'toArray')) {
                throw new SmsboxException('Options object must implement toArray() method.');
            }

            $options = $options->toArray();
            $base    = array_merge($base, $options);

            $base['mode'] ??= 'Standard';
            $base['strategy'] ??= 4;

            if ($base['mode'] === Mode::EXPERT) {
                $base['origine'] = $base['sender'] ?? '';
            }

            unset($base['sender']);

            if (isset($base['daysMinMax']) && is_array($base['daysMinMax'])) {
                [$base['day_min'], $base['day_max']] = $base['daysMinMax'];
                unset($base['daysMinMax']);
            }

            if (isset($base['hoursMinMax']) && is_array($base['hoursMinMax'])) {
                [$base['hour_min'], $base['hour_max']] = $base['hoursMinMax'];
                unset($base['hoursMinMax']);
            }

            if (isset($base['dateTime']) && $base['dateTime'] instanceof \DateTimeInterface) {
                $base['date']  = $base['dateTime']->format('d/m/Y');
                $base['heure'] = $base['dateTime']->format('H:i');
                unset($base['dateTime']);
            }

            if (isset($base['variable']) && is_array($base['variable'])) {
                $base = $this->handleVariables($base);
            }
        }

        return $base;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     *
     * @throws SmsboxException
     */
    private function handleVariables(array $options): array
    {

        $msg        = $options['msg'];
        $variables  = $options['variable'];

        if (!is_string($options['dest'])) {
            throw new SmsboxException('Dest must be an array of phone numbers.');
        }

        $recipients = explode(',', $options['dest']);

        if (!isset($msg) || !is_string($msg)) {
            throw new SmsboxException('Message must be a string.');
        }

        if (!is_array($recipients)) {
            throw new SmsboxException('Dest must be an array of phone numbers.');
        }

        if (!is_array($variables)) {
            throw new SmsboxException("Expected 'variable' to be an array.");
        }

        if (
            !isset($variables[0])
            || !is_array($variables[0])
            || array_keys($variables) !== range(0, count($variables) - 1)
        ) {
            throw new SmsboxException('Variable must be a list of arrays.');
        }

        if (count($recipients) !== count($variables)) {
            throw new SmsboxException(sprintf('Mismatch between number of recipients (%d) and variable sets (%d).', count($recipients), count($variables)));
        }

        preg_match_all('%([0-9]+)%', $msg, $matches);
        $expectedCount = (int) max($matches[0]);

        foreach ($variables as $index => $varSet) {
            if (!is_array($varSet)) {
                throw new SmsboxException("Variable at index {$index} must be an array.");
            }

            if (count($varSet) !== $expectedCount) {
                throw new SmsboxException(sprintf('Recipient %d: Expected %d variables, got %d.', $index + 1, $expectedCount, count($varSet)));
            }
        }

        $lines = array_map(function ($number, $varSet) {
            $encodedVars = array_map(
                fn ($v) => str_replace([',', ';'], ['%d44%', '%d59%'], (string) $v),
                $varSet
            );

            return $number . ';' . implode(';', $encodedVars);
        }, $recipients, $variables);

        $options['dest'] = implode(',', $lines);

        $options['personnalise'] = 1;

        unset($options['variable']);

        return $options;
    }

    /**
     * @param string $body
     *
     * @throws SmsboxException
     */
    private function handleApiError(string $body): void
    {
        $messages = [
            'ERROR 01' => ['Some parameters are missing or invalid.', 400],
            'ERROR 02' => ['Incorrect credentials, suspended API key, suspended account, or restriction by source IP address.', 401],
            'ERROR 03' => ['Balance depleted or insufficient.', 402],
            'ERROR 04' => ['Invalid destination number or does not match the expected format.', 400],
            'ERROR 05' => ['Internal execution error within our services.', 500],
            'ERROR'    => ['Sending failed due to another reason (blacklisted number, duplicate content, unsupported prefix, etc.).', 400],
        ];

        if (isset($messages[$body])) {
            [$msg, $code] = $messages[$body];

            throw new SmsboxException("{$msg} ({$body})", $code);
        }

        throw new SmsboxException('Bad request.', 400);
    }
}
