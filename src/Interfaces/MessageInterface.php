<?php

namespace Smsbox\Interfaces;

use Smsbox\Exception\SmsboxException;

/**
 * Base interface for all message types across different channels (ex.: 'sms')
 *
 * This interface defines the common contract that all message objects must implement,
 * regardless of the communication channel being used.
 */
interface MessageInterface
{
    /**
     * Gets the list of phone numbers associated with this message.
     *
     * Returns an array of phone numbers:
     * - SMS/WhatsApp/RCS: Phone numbers in international format (ex.: ['+33612345678'])
     *
     * @return string[] Array of recipient phone numbers
     */
    public function getPhones(): array;

    /**
     * Get channel-specific options for this message.
     *
     * Returns an options object specific to the channel (ex.: SmsOptions, etc.).
     * Returns null if no options have been set.
     *
     * @return object|null The options object or null
     */
    public function getOptions(): ?object;

    /**
     * Validate the message before sending.
     *
     * Performs validation checks specific to the message type and channel.
     * Should validate recipients, content, options, etc.
     *
     * @throws SmsboxException If validation fails
     */
    public function validate(): void;
}
