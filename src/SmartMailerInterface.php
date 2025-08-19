<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer;

/**
 * Interface for email sending implementations.
 *
 * This interface defines the contract that all email sending implementations
 * must follow, ensuring a consistent API for sending email messages.
 *
 * @package Praetorian\SmartMailer
 * @author Praetorian Technology
 */
interface SmartMailerInterface
{
    /**
     * Sends an email message.
     *
     * @param Message $message The message to send
     *
     * @return mixed The result of the send operation (implementation-dependent)
     *
     * @throws \Praetorian\SmartMailer\Exception\InvalidEmailMessageException When the message is invalid
     * @throws \Praetorian\SmartMailer\Exception\SendException When sending fails
     */
    public function send(Message $message);
}
