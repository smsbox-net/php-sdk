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
    ->strategy(Strategy::MARKETING)
    ->mode(Mode::EXPERT)
    ->sender('YOUR_BRAND_SENDER')
    ->variable([['John', '20']])
    ->date('01/10/2027')
    ->hour('11:00');

$client = new SmsboxClient('pub-xxxxxxxxx-xxxxx-xxxxx');

$sms = new SmsMessage(
    ['+336xxxxxxxx'],
    'Hi %1%, enjoy %2% off on all products this week only! Visit our store or shop online now.'
);
$sms->options($options);

try {
    $response = $client->sendSms($sms);
    echo 'Message scheduled successfully' . PHP_EOL;
} catch (GuzzleException|SmsboxException $e) {
    echo 'error: ' . $e->getMessage() . PHP_EOL . 'code: ' . $e->getCode();
}
