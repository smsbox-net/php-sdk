<?php

namespace Smsbox\Tests\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Smsbox\Exception\SmsboxException;
use Smsbox\Messages\SmsMessage;
use Smsbox\Options\SMS\SmsOptions;
use Smsbox\Services\SmsService;

/**
 * @internal
 */
class SmsServiceTest extends TestCase
{
    public function testSendSuccess()
    {
        $message = $this->createMock(SmsMessage::class);
        $message->method('getOptions')->willReturn(null);
        $message->method('getPhone')->willReturn(['+0600000000']);
        $message->method('getSubject')->willReturn('Hello');

        $mockResponse = new Response(200, [], 'OK 12345678');

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '',
                $this->anything()
            )
            ->willReturn($mockResponse);

        $smsService = new SmsService('pub-xxxxxxxxxxx', 10.0, $client);

        $result = $smsService->send($message);

        $this->assertSame('Send successfully. (OK)', $result['data']);
        $this->assertSame(200, $result['code']);
        $this->assertSame(['12345678'], $result['refId']);
    }

    public function testSendReturnsError()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Incorrect credentials, suspended API key, suspended account, or restriction by source IP address.');

        $message = $this->createMock(SmsMessage::class);
        $message->method('getOptions')->willReturn(null);
        $message->method('getPhone')->willReturn(['+0600000000']);
        $message->method('getSubject')->willReturn('Hello');

        $mockResponse = new Response(200, [], 'ERROR 02');

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '',
                $this->anything()
            )
            ->willReturn($mockResponse);

        $smsService = new SmsService('pub-xxxxxxxxxxx', 100, $client);
        $smsService->send($message);
    }

    public function testSendWithInvalidVariable()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Recipient 1: Expected 2 variables, got 1.');

        $options = $this->getMockBuilder(SmsOptions::class)
            ->onlyMethods(['toArray'])
            ->getMock();
        $options->method('toArray')->willReturn([
            'msg'      => 'Hello %1% %2%',
            'variable' => [['var1']],
        ]);

        $message = $this->createMock(SmsMessage::class);
        $message->method('getOptions')->willReturn($options);
        $message->method('getPhone')->willReturn(['+0600000000']);
        $message->method('getSubject')->willReturn('Hello %1% %2%');

        $client     = $this->createMock(ClientInterface::class);
        $smsService = new SmsService('pub-xxxxxxxxxxx', 100, $client);

        $smsService->send($message);
    }

    public function testSendReadTimeout()
    {
        $message = $this->createMock(SmsMessage::class);
        $message->method('getOptions')->willReturn(null);
        $message->method('getPhone')->willReturn(['+33612345678']);
        $message->method('getSubject')->willReturn('This is a test');

        $request   = new Request('POST', '');
        $exception = new \GuzzleHttp\Exception\RequestException(
            'Timeout',
            $request,
            null,
            null,
            ['errno' => CURLE_OPERATION_TIMEOUTED, 'total_time' => 5, 'connect_time' => 1, 'pretransfer_time' => 1]
        );

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $smsService = new SmsService('pub-xxxxxxxxxxx', 10.0, $client);

        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Response from API was not received. Please check if the request was processed before retrying.');

        $smsService->send($message);
    }
}
