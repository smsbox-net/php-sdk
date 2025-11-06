<?php

namespace Smsbox\Messages;

use Smsbox\Exception\SmsboxException;
use Smsbox\Interfaces\MessageInterface;
use Smsbox\Options\SMS\SmsOptions;
use Smsbox\Validation\PhoneValidator;

class SmsMessage implements MessageInterface
{
    /**
     * @var array<string>
     */
    private array $phones;

    /**
     * @var string
     */
    private string $content;

    /**
     * @var SmsOptions|null
     */
    private $options;

    /**
     * Constructor for the SMS message.
     *
     * @param array<string>   $phones  Array of recipient phone numbers in international format (ex.: +336XXXXXXXX)
     * @param string          $content The message content to be sent
     * @param SmsOptions|null $options Optional SMS options like strategy, scheduled date/time, mode, etc
     *
     * @throws SmsboxException If the SMS message cannot be created or options are invalid
     */
    public function __construct(array $phones, string $content, ?SmsOptions $options = null)
    {
        $this->phones  = $phones;
        $this->content = $content;
        $this->options = $options;

        $this->validate();
    }

    /**
     * @return array<string>
     */
    public function getPhones(): array
    {
        return $this->phones;
    }

    /**
     * @param string $content
     *
     * @return SmsMessage
     */
    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
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

    /**
     * Validate the message before sending.
     *
     * Implementation of MessageInterface.
     * Validates that phone numbers are not empty and within the 500 recipient limit.
     *
     * @throws SmsboxException If validation fails
     */
    public function validate(): void
    {
        if (empty($this->phones)) {
            throw new SmsboxException('Phone numbers cannot be empty.');
        }

        if (count($this->phones) > 500) {
            throw new SmsboxException('The number of phone numbers cannot exceed 500 recipients.');
        }

        $this->phones = PhoneValidator::sanitizePhoneNumbers($this->phones);

        if (strlen($this->content) < 1) {
            throw new SmsboxException('Your message should have a content.');
        }
    }
}
