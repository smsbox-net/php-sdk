# 📦 SMSBOX PHP SDK

---

![PHP Lint & Tests](https://img.shields.io/github/actions/workflow/status/smsbox-net/php-sdk/php_lint_and_test.yaml?branch=main)
![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)

![The SMSBOX logo](./smsbox-logo-en.png)

The **SMSBOX PHP SDK** provides a simple and efficient way to send SMS messages directly from your PHP applications.  
Whether you are sending alerts, notifications, or marketing messages, this SDK makes the process fast and easy to integrate.

For more information, visit the official website: [SMSBOX](https://www.smsbox.net)

---

## ⚠️ Requirements

- PHP **7.4+** (compatible with PHP 8.x)
- Composer

---

## 📖 Documentation

For detailed information about the SMSBOX Sending SMS API parameters, see the official documentation: [SMS API](https://www.smsbox.net/en/tools-development#doc-sms-en).

---

## ✉️ Features

- Send SMS messages quickly and reliably, customizable sender ID, scheduling, etc.

---

## ⚙️ Installation

Install via Composer:

```sh
composer require smsbox/php-sdk
```

---

## ⚡ Quick Start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Smsbox\SmsboxClient;
use Smsbox\Options\SMS\SmsOptions;
use Smsbox\Messages\SmsMessage;
use Smsbox\Enum\SMS\Strategy;

try {
    $client = new SmsboxClient(SMSBOX_API_KEY);

    $message = new SmsMessage(
        ['+336XXXXXXXX'],
        'Hello! This is a test message.'
    );

    $options = (new SmsOptions());
    ->strategy(Strategy::MARKETING);

    $message->options($options);

    $response = $client->sendSms($message);
    echo 'Message sent successfully. Reference ID: ' . $response['refId'] . PHP_EOL;

} catch (GuzzleException|SmsboxException $e) {
    echo 'Error sending OTP: ' . $e->getMessage() . PHP_EOL . 'Code: ' . $e->getCode();
}
```
