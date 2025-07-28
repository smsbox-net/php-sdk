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
    ->mode(Mode::STANDARD)
    ->strategy(Strategy::NOTIFICATION)
    ->variable([['John', 30], ['Jean', 26]]);

$client = new SmsboxClient('pub-xxxxxxxxx-xxxxx-xxxxx');

$sms = new SmsMessage(
    ['+336xxxxxxxx', '06 xx xx xx xx'],
    'Hi %1%, enjoy your %2%th birthday and come claim your reward on our website!'
);
$sms->options($options);

try {
    $response = $client->sendSms($sms);
    echo 'Message scheduled successfully' . PHP_EOL;
} catch (GuzzleException|SmsboxException $e) {
    echo 'error: ' . $e->getMessage() . PHP_EOL . 'code: ' . $e->getCode();
}
