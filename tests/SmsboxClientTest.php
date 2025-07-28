<?php

namespace Smsbox\Tests;

use PHPUnit\Framework\TestCase;
use Smsbox\Messages\SmsMessage;
use Smsbox\Services\SmsService;
use Smsbox\SmsboxClient;

/**
 * @internal
 */
class SmsboxClientTest extends TestCase
{
    public function testSendSmsDelegatesToSmsService()
    {
        $message        = $this->createMock(SmsMessage::class);
        $expectedResult = [
            'data'  => 'OK 12345678',
            'code'  => 200,
            'refId' => '12345678',
        ];

        $smsServiceMock = $this->getMockBuilder(SmsService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        $smsServiceMock->expects($this->once())
            ->method('send')
            ->with($message)
            ->willReturn($expectedResult);

        $client     = new SmsboxClient('pub-xxxxxxxxxxx', 10.0);
        $reflection = new \ReflectionClass(SmsboxClient::class);
        $property   = $reflection->getProperty('smsService');
        $property->setAccessible(true);
        $property->setValue($client, $smsServiceMock);

        $result = $client->sendSms($message);

        $this->assertSame($expectedResult, $result);
    }
}
