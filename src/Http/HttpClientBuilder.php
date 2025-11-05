<?php

namespace Smsbox\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Smsbox\Exception\SmsboxException;
use Smsbox\SmsboxClient;

/**
 * HTTP client builder for SMSBOX API communication.
 *
 * This class centralizes HTTP client configuration that is common
 * across all channels (ex.: 'sms').
 */
class HttpClientBuilder
{
    /**
     * Build a configured Guzzle HTTP client.
     *
     * Creates a Guzzle client with standardized configuration:
     * - Base URI for the API endpoint
     * - Timeout settings (request timeout and connection timeout)
     * - Common headers (Authorization, User-Agent)
     *
     * @param string               $baseUri API endpoint base URI
     * @param string               $apiKey  API key for authentication
     * @param float                $timeout Maximum time in seconds for the request to complete
     * @param ClientInterface|null $client  Optional pre-configured client to use instead
     *
     * @return ClientInterface The configured HTTP client
     *
     * @throws SmsboxException
     */
    public static function build(
        string $baseUri,
        string $apiKey,
        float $timeout = 10.0,
        ?ClientInterface $client = null
    ): ClientInterface {

        if ($apiKey == '') {
            throw new SmsboxException('API key is required.');
        }

        if ($client !== null) {
            return $client;
        }

        return new Client([
            'base_uri'        => $baseUri,
            'timeout'         => $timeout,
            'connect_timeout' => 3,
            'headers'         => self::getCommonHeaders($apiKey),
        ]);
    }

    /**
     * Get common HTTP headers for all API requests.
     *
     * Returns headers that should be included in all requests:
     * - Authorization: App key authentication
     * - User-Agent: SDK identification with version
     *
     * @param string $apiKey API key for authentication
     *
     * @return array<string, string> Array of HTTP headers
     */
    public static function getCommonHeaders(string $apiKey): array
    {
        return [
            'Authorization' => 'App ' . $apiKey,
            'User-Agent'    => 'smsbox-php-sdk/' . SmsboxClient::VERSION,
        ];
    }
}
