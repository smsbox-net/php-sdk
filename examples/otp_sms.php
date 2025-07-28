<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException;
use Smsbox\Enum\SMS\Mode;
use Smsbox\Enum\SMS\Strategy;
use Smsbox\Exception\SmsboxException;
use Smsbox\Messages\SmsMessage;
use Smsbox\Options\SMS\SmsOptions;
use Smsbox\SmsboxClient;

$options = (new SmsOptions())
    ->strategy(Strategy::NOTIFICATION)
    ->mode(Mode::EXPERT)
    ->sender('YOUR_SENDER')
    ->validity(5);

$messageText = '12345 is your OTP code. It will expire in 5 minutes.';

$recipientPhone = ['+336xxxxxxxx'];

$sms = new SmsMessage($recipientPhone, $messageText);
$sms->options($options);

$client = new SmsboxClient('pub-xxxxxxxxx-xxxxx-xxxxx');

try {
    $response = $client->sendSms($sms);
    echo 'OTP sent successfully' . PHP_EOL;
} catch (GuzzleException|SmsboxException $e) {
    echo 'Error sending OTP: ' . $e->getMessage() . PHP_EOL . 'Code: ' . $e->getCode();
}
