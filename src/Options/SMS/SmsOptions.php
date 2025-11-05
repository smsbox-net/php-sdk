<?php

namespace Smsbox\Options\SMS;

use Smsbox\Enum\SMS\Charset;
use Smsbox\Enum\SMS\Day;
use Smsbox\Enum\SMS\Encoding;
use Smsbox\Enum\SMS\Mode;
use Smsbox\Enum\SMS\Strategy;
use Smsbox\Enum\SMS\Udh;
use Smsbox\Exception\SmsboxException;
use Symfony\Component\Intl\Countries;

class SmsOptions
{
    /**
     * @var array<string, mixed>
     */
    private array $options = [];

    private \DateTimeImmutable $now;

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Exception
     */
    public function __construct(array $options = [], ?\DateTimeImmutable $now = null)
    {
        $this->options = $options;
        $this->now     = $now ?: new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    }

    /**
     * Sets the message sending mode.
     *
     * The mode controls the method or channel through which the message is sent.
     * The provided mode must be one of the allowed values returned by {@see Mode::getAll()}.
     *
     * @param string $mode The mode identifier (ex.: 'standard', 'expert').
     *
     * @return self
     *
     * @throws SmsboxException if the provided mode value is invalid
     */
    public function mode(string $mode): self
    {
        $allowedMode = Mode::getAll();

        if (!in_array($mode, $allowedMode, true)) {
            throw new SmsboxException('Invalid mode value');
        }

        if (isset($this->options['allow_vocal']) && $this->options['allow_vocal'] && !in_array($mode, [Mode::EXPERT, Mode::STANDARD])) {
            throw new SmsboxException("The '{$mode}' mode do not allow the vocal parameter.", 400);
        }

        $this->options['mode'] = $mode;

        return $this;
    }

    /**
     * Sets the message sending strategy.
     *
     * The strategy defines how the message dispatch is handled.
     * The provided strategy must be one of the allowed values returned by {@see Strategy::getAll()}.
     *
     * @param int $strategy the strategy identifier
     *
     * @return self
     *
     * @throws SmsboxException if the provided strategy value is not valid
     */
    public function strategy(int $strategy): self
    {
        $allowedStrategy = Strategy::getAll();

        if (!in_array($strategy, $allowedStrategy, true)) {
            throw new SmsboxException('Invalid strategy value');
        }

        $this->options['strategy'] = $strategy;

        return $this;
    }

    /**
     * Sets the scheduled date for the message sending time.
     *
     * This method must be used together with the `hour()` method and is mutually exclusive with `dateTime()`.
     * The date must be a valid string in `DD/MM/YYYY` format.
     *
     * @param string $date the scheduled date in `DD/MM/YYYY` format
     *
     * @return self
     *
     * @throws SmsboxException if `dateTime()` has already been set, or if the date format is invalid
     */
    public function date(string $date): self
    {
        if (isset($this->options['dateTime'])) {
            throw new SmsboxException(sprintf('Either %1$s::dateTime() or %1$s::date() and %1$s::hour() must be called, but not both.', self::class));
        }

        if (!\DateTimeImmutable::createFromFormat('d/m/Y', $date)) {
            throw new SmsboxException('The date must be in DD/MM/YYYY format.');
        }

        $this->options['date'] = $date;

        return $this;
    }

    /**
     * Sets the scheduled hour for the message sending time.
     *
     * This method must be used together with the `date()` method and is mutually exclusive with `dateTime()`.
     * The hour must be a valid time string in `HH:MM` 24-hour format.
     *
     * @param string $hour the scheduled hour in `HH:MM` format (24-hour clock)
     *
     * @return self
     *
     * @throws SmsboxException if `dateTime()` has already been set, or if the hour format is invalid
     */
    public function hour(string $hour): self
    {
        if (isset($this->options['dateTime'])) {
            throw new SmsboxException(sprintf('Either %1$s::dateTime() or %1$s::date() and %1$s::hour() must be called, but not both.', self::class));
        }

        if (!\DateTimeImmutable::createFromFormat('H:i', $hour)) {
            throw new SmsboxException('Hour must be in HH:MM format.');
        }

        $this->options['heure'] = $hour;

        return $this;
    }

    /**
     * Sets the scheduled date and time for the message.
     *
     * This method expects a DateTime object and sets it as the scheduled send time.
     * It enforces that the provided DateTime is in the future relative to the current time.
     * Also, it ensures mutual exclusivity with `date()` and `hour()` methods.
     * The DateTime is converted to the 'Europe/Paris' timezone before storing.
     *
     * @param \DateTime $dateTime the scheduled date and time for sending the message
     *
     * @return self
     *
     * @throws SmsboxException if both `dateTime()` and `date()`/`hour()` are used,
     *                         or if the provided DateTime is earlier than now
     */
    public function dateTime(\DateTime $dateTime): self
    {
        if (isset($this->options['date']) || isset($this->options['heure'])) {
            throw new SmsboxException(sprintf('Either %1$s::dateTime() or %1$s::date() and %1$s::hour() must be called, but not both.', self::class));
        }

        if ($dateTime < $this->now) {
            throw new SmsboxException('The given DateTime must be greater than the current date.');
        }

        $this->options['dateTime'] = $dateTime->setTimezone(new \DateTimeZone('Europe/Paris'));

        return $this;
    }

    /**
     * Sets the destination country ISO code.
     *
     * Validates the provided ISO code against the list of supported countries.
     * If the `Countries` class exists, the code must be a valid ISO country code.
     *
     * @param string $isoCode The destination country ISO code (ex.: 'US', 'FR', 'GB').
     *
     * @return self
     *
     * @throws SmsboxException if the ISO code is invalid or not supported
     */
    public function destIso(string $isoCode): self
    {
        if (class_exists(Countries::class) && !Countries::exists($isoCode)) {
            throw new SmsboxException(sprintf('The country code "%s" is not valid.', $isoCode));
        }

        $this->options['dest_iso'] = $isoCode;

        return $this;
    }

    /**
     * Sets the variables used for message personalization.
     *
     * The array should be a list of variable names.
     *
     * @param array<int, string>|array<array<int, string>> $variable List of variable names for message templates
     *
     * @return self
     */
    public function variable(array $variable): self
    {
        $this->options['variable'] = $variable;

        return $this;
    }

    /**
     * Sets the message encoding type.
     *
     * The encoding determines how the message content is processed and transmitted.
     * The provided encoding must be one of the allowed values returned by {@see Encoding::getAll()}.
     *
     * @param string $encoding the encoding type to use
     *
     * @return self
     *
     * @throws SmsboxException if the provided encoding is not valid
     */
    public function coding(string $encoding): self
    {
        $allowedEncoding = Encoding::getAll();

        if (!in_array($encoding, $allowedEncoding, true)) {
            throw new SmsboxException('Invalid encoding value');
        }

        $this->options['coding'] = $encoding;

        return $this;
    }

    /**
     * Sets the character set for the message encoding.
     *
     * The character set determines how the message content is encoded.
     * The provided charset must be one of the allowed values returned by {@see Charset::getAll()}.
     *
     * @param string $charset The character set to use (ex.: 'UTF-8', 'ISO-8859-1').
     *
     * @return self
     *
     * @throws SmsboxException if the provided charset is not valid
     */
    public function charset(string $charset): self
    {
        $allowedCharset = Charset::getAll();

        if (!in_array($charset, $allowedCharset, true)) {
            throw new SmsboxException('Invalid charset value');
        }

        $this->options['charset'] = $charset;

        return $this;
    }

    /**
     * Sets the UDH value for the message.
     *
     * The UDH is typically used for advanced messaging features such as message concatenation
     * or special encoding. The provided value must be one of the allowed UDH types defined in {@see Udh::getAll()}.
     *
     * @param int $udh the UDH value to use (must be in the allowed list)
     *
     * @return self
     *
     * @throws SmsboxException if the provided UDH value is not valid
     */
    public function udh(int $udh): self
    {
        $allowedUdh = Udh::getAll();

        if (!in_array($udh, $allowedUdh, true)) {
            throw new SmsboxException('Invalid udh value');
        }

        $this->options['udh'] = $udh;

        return $this;
    }

    /**
     * Enables or disables the delivery callback feature.
     *
     * When enabled, the system will attempt to send delivery status updates
     * to a predefined callback URL (as set in your SMSBox account).
     *
     * @param bool $callback whether to enable delivery callbacks (true to enable, false to disable)
     *
     * @return self
     */
    public function callback(bool $callback): self
    {
        $this->options['callback'] = $callback;

        return $this;
    }

    /**
     * Enables or disables vocal (text-to-speech) message delivery.
     *
     * When enabled, the message may be delivered as a voice call using text-to-speech,
     * typically for landline numbers in Metropolitan France.
     *
     * Note: Message vocalization is only available in Standard or Expert mode.
     *
     * @param bool $allowVocal whether to allow vocal delivery (true to enable, false to disable)
     *
     * @return self
     *
     * @throws SmsboxException
     */
    public function allowVocal(bool $allowVocal): self
    {

        if (is_string($this->options['mode']) && !in_array($this->options['mode'], [Mode::EXPERT, Mode::STANDARD])) {
            throw new SmsboxException("The '{$this->options['mode']}' mode do not allow the vocal parameter.", 400);
        }

        $this->options['allow_vocal'] = $allowVocal;

        return $this;
    }

    /**
     * Sets the maximum number of SMS parts allowed for a long message (concatenated SMS).
     *
     * This parameter defines the maximum number of SMS segments that can be used
     * to send a long message. If the number of segments required to deliver the full message
     * exceeds this value, the message will be truncated before submission.
     *
     * The value must be an integer between 1 and 8 (inclusive). The default is 8.
     * It is generally recommended not to exceed 3 parts for optimal delivery.
     *
     * Refer to the "msg" and "udh" parameters for more details on long messages.
     *
     * @param int $maxParts Maximum number of message parts (1–8)
     *
     * @return self
     *
     * @throws SmsboxException If the value is not within the allowed range
     */
    public function maxParts(int $maxParts): self
    {
        if ($maxParts < 1 || $maxParts > 8) {
            throw new SmsboxException(sprintf('The "max_parts" option must be an integer between 1 and 8, got "%d".', $maxParts));
        }

        $this->options['max_parts'] = $maxParts;

        return $this;
    }

    /**
     * Sets the message validity period in minutes.
     *
     * This parameter defines how long the message is considered deliverable.
     * After this duration, if the message has not been delivered, the operator
     * will no longer attempt delivery.
     *
     * The validity must be an integer between 5 and 1440 minutes (24 hours).
     * The default validity period typically ranges from 48 to 72 hours, depending on the operator.
     *
     * Note: Some operators may ignore this parameter.
     *
     * @param int $validity Validity period in minutes (5–1440)
     *
     * @return self
     *
     * @throws SmsboxException If the validity is outside the allowed range
     */
    public function validity(int $validity): self
    {
        if ($validity < 5 || $validity > 1440) {
            throw new SmsboxException(sprintf('The "validity" option must be an integer between 5 and 1440, got "%d".', $validity));
        }

        $this->options['validity'] = $validity;

        return $this;
    }

    /**
     * Sets the allowed days of the week for message sending.
     *
     * This method restricts the sending of messages to a specific range of days, from `$min` to `$max`.
     * It is particularly useful when requests are triggered automatically at uncontrolled times.
     *
     * Both parameters must correspond to valid weekday values returned by `Day::getAll()`, where:
     * - 1 represents Monday
     * - 7 represents Sunday
     *
     * The minimum day must not be greater than the maximum day.
     *
     * Note: This setting is ignored for deferred (scheduled) messages, i.e., when the "hour" or "date" parameters are used.
     * The time zone used for validation is Europe/Paris.
     *
     * @param int $min Minimum allowed day for sending (1 = Monday, 7 = Sunday)
     * @param int $max Maximum allowed day for sending (1 = Monday, 7 = Sunday; must be >= $min)
     *
     * @return self
     *
     * @throws SmsboxException If either value is invalid or if $min > $max
     */
    public function daysMinMax(int $min, int $max): self
    {
        $allowedDaysMinMax = Day::getAll();

        if (!in_array($min, $allowedDaysMinMax, true)) {
            throw new SmsboxException('Invalid min day value');
        }

        if (!in_array($max, $allowedDaysMinMax, true)) {
            throw new SmsboxException('Invalid max day value');
        }

        if (!($min <= $max)) {
            throw new SmsboxException('The minimum day must be before the maximum day or the same.');
        }

        $this->options['daysMinMax'] = [$min, $max];

        return $this;
    }

    /**
     * Sets the allowed time range (in hours) for message sending.
     *
     * This method defines the daily time window during which messages are allowed to be sent.
     * It must be used in conjunction with day constraints (`day_min`, `day_max`) if you want to
     * fully restrict sending periods.
     *
     * Accepted values range from 0 (00:00) to 23 (23:00).
     * - `$min` defines the first hour of the day when sending is allowed.
     * - `$max` defines the last hour of the day when sending is allowed.
     *
     * Both values are interpreted in the Europe/Paris time zone.
     *
     * Note: These parameters are ignored when scheduling deferred messages
     * (i.e., when using the "hour" and/or "date" parameters).
     *
     * @param int $min Minimum allowed hour for sending (0–23)
     * @param int $max Maximum allowed hour for sending (0–23), must be >= $min
     *
     * @return self
     *
     * @throws SmsboxException If $min is negative, $max exceeds 23, or $min > $max
     */
    public function hoursMinMax(int $min, int $max): self
    {
        if ($min < 0 || $min > $max) {
            throw new SmsboxException('The minimum hour must be greater than 0 and lower than the maximum hour.');
        }

        if ($max > 23) {
            throw new SmsboxException('The maximum hour must be lower or equal to 23.');
        }

        $this->options['hoursMinMax'] = [$min, $max];

        return $this;
    }

    /**
     * Sets the sender (emitter) value for the SMS message.
     *
     * This parameter defines the sender name or number that will appear as the SMS emitter.
     * It is **required in Expert mode**, unless a default sender has been previously configured
     * in your account settings.
     *
     * - The sender can be either:
     *   - A phone number (up to 15 digits), or
     *   - An alphanumeric string (up to 11 characters).
     * - If left empty, the default sender associated with your account will be used.
     * - Sender customization is only available in **Expert mode**.
     *
     * IMPORTANT: You must pre-register any custom sender values via the "Sender Management"
     * section in your Client Portal before using them.
     *
     * @param string $sender The SMS sender (max 15 digits or 11 characters)
     *
     * @return self
     */
    public function sender(string $sender): self
    {
        if (strlen($sender) > 11) {
            throw new SmsboxException('The sender must be less than 11 characters.');
        }

        $this->options['sender'] = $sender;

        return $this;
    }

    /**
     * Converts the current object's options to an associative array.
     *
     * @return array<string, mixed> associative array of options
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
