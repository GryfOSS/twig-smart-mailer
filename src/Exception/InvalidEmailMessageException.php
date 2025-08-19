<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Exception;

/**
 * Exception thrown when an email message is invalid.
 *
 * This exception is thrown during message validation when required fields
 * are missing or the message structure is invalid. Common validation failures
 * include:
 * - Missing sender (from address)
 * - No recipients (to, cc, or bcc)
 * - No content (both HTML and text are empty)
 *
 * @package Praetorian\SmartMailer\Exception
 * @author Praetorian Technology
 */
class InvalidEmailMessageException extends SmartMailerException
{
}
