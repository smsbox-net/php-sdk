<?php

require __DIR__ . '/vendor/autoload.php';

use Smsbox\Messages\SmsMessage;
use Smsbox\SmsboxClient;

$client = new SmsboxClient('pub-xxxxxxxxxxxx');

$sms = new SmsMessage(['+33XXXXXXXXX'], 'Hello, this is a simple test message!');

try {
    $response = $client->sendSms($sms);
    echo 'Simple SMS sent successfully!';
} catch (Exception $e) {
    echo 'Error sending SMS: ' . $e->getMessage() . ', with code: ' . $e->getCode() . PHP_EOL;
}
