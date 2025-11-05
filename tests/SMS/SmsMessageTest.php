<?php

namespace Smsbox\Tests\Message;

use PHPUnit\Framework\TestCase;
use Smsbox\Exception\SmsboxException;
use Smsbox\Messages\SmsMessage;
use Smsbox\Options\SMS\SmsOptions;
use Smsbox\SmsboxClient;

/**
 * @internal
 */
class SmsMessageTest extends TestCase
{
    public function testConstructorSetsValues()
    {
        $options = $this->createMock(SmsOptions::class);
        $message = new SmsMessage(['+33612345678'], 'Hello World', $options);

        $this->assertSame(['+33612345678'], $message->getPhones());
        $this->assertSame('Hello World', $message->getContent());
        $this->assertSame($options, $message->getOptions());
    }

    public function testConstructorThrowsExceptionWhenPhoneEmpty()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Phone numbers cannot be empty.');

        new SmsMessage([], 'Hello');
    }

    public function testSetPhoneThrowsExceptionWhenEmpty()
    {
        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('Phone numbers cannot be empty.');

        new SmsMessage([], 'Hello');
    }

    public function testContentSetter()
    {
        $message = new SmsMessage(['0612345678'], 'Initial');
        $message->content('Updated Content');

        $this->assertSame('Updated Content', $message->getContent());
    }

    public function testOptionsSetter()
    {
        $options = $this->createMock(SmsOptions::class);
        $message = new SmsMessage(['0612345678'], 'Hello');
        $message->options($options);

        $this->assertSame($options, $message->getOptions());
    }

    public function testMaxRecipientsCount()
    {
        $mockClient = $this->createMock(SmsboxClient::class);

        $this->expectException(SmsboxException::class);
        $this->expectExceptionMessage('The number of phone numbers cannot exceed 500 recipients.');

        mt_srand(42);
        $phoneNumbers = [];
        for ($i = 0; $i < 501; $i++) {
            $prefix         = (rand(0, 1) ? '06' : '07');
            $phoneNumbers[] = $prefix . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        }

        $sms = new SmsMessage($phoneNumbers, 'Hello');

        $mockClient->sendSms($sms);
    }
}
