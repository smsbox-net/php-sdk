<?php

namespace Smsbox\Interfaces\SMS;

use Smsbox\Options\SMS\SmsOptions;

interface SmsMessageInterface
{
    /**
     * @return array<string>
     */
    public function getPhone(): array;

    /**
     * @return array<string>
     */
    public function getRecipientId(): array;

    /**
     * @param string $subject
     *
     * @return self
     */
    public function subject(string $subject): self;

    /**
     * @return string
     */
    public function getSubject(): string;

    /**
     * @param string $from
     *
     * @return self
     */
    public function from(string $from): self;

    /**
     * @return string
     */
    public function getFrom(): string;

    /**
     * @param SmsOptions $options
     *
     * @return self
     */
    public function options(SmsOptions $options): self;

    /**
     * @return SmsOptions|null
     */
    public function getOptions(): ?object;
}
