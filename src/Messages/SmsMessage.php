<?php

namespace Smsbox\Messages;

use Smsbox\Exception\SmsboxException;
use Smsbox\Interfaces\SMS\SmsMessageInterface;
use Smsbox\Options\SMS\SmsOptions;

class SmsMessage implements SmsMessageInterface
{
    /**
     * @var array<string>
     */
    private array $phone;

    /**
     * @var string
     */
    private string $subject;

    /**
     * @var string
     */
    private string $from;

    /**
     * @var SmsOptions|null
     */
    private $options;

    /**
     * Constructor for the SMS message.
     *
     * @param array<string>   $phone   Array of recipient phone numbers in international format (e.g., +336XXXXXXXX)
     * @param string          $subject The message content to be sent
     * @param string          $from    Optional sender ID (customizable). **Only supported when using SmsOptions with mode set to EXPERT**. Defaults to empty string.
     * @param SmsOptions|null $options Optional SMS options like strategy, scheduled date/time, mode, etc
     *
     * @throws SmsboxException If the SMS message cannot be created or options are invalid
     */
    public function __construct(array $phone, string $subject, string $from = '', ?SmsOptions $options = null)
    {
        if (empty($phone)) {
            throw new SmsboxException('Phone numbers cannot be empty.');
        }

        if (count($phone) > 500) {
            throw new SmsboxException('The number of phone numbers cannot exceed 500 recipients.');
        }

        $this->phone   = $phone;
        $this->subject = $subject;
        $this->from    = $from;
        $this->options = $options;
    }

    /**
     * @return array<string>
     */
    public function getPhone(): array
    {
        return $this->phone;
    }

    /**
     * @return array<string>
     */
    public function getRecipientId(): array
    {
        return $this->phone;
    }

    /**
     * @param string $subject
     *
     * @return SmsMessage
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $from
     *
     * @return SmsMessage
     */
    public function from(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param SmsOptions $options
     *
     * @return SmsMessage
     */
    public function options(SmsOptions $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return SmsOptions|null
     */
    public function getOptions(): ?object
    {
        return $this->options;
    }
}
