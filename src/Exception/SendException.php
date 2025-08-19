<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Exception;

/**
 * Exception thrown when email sending fails.
 *
 * This exception is thrown when the actual email sending process fails,
 * typically due to SMTP server issues, network problems, or transport
 * configuration errors. It wraps the underlying exception from Symfony
 * Mailer to provide a consistent error handling interface.
 *
 * @package Praetorian\SmartMailer\Exception
 * @author Praetorian Technology
 */
class SendException extends SmartMailerException
{
}
