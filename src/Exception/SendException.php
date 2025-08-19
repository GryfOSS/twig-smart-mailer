<?php

declare(strict_types=1);

namespace GryfOSS\Mailer\Exception;

/**
 * Exception thrown when email sending fails.
 *
 * This exception is thrown when the actual email sending process fails,
 * typically due to SMTP server issues, network problems, or transport
 * configuration errors. It wraps the underlying exception from Symfony
 * Mailer to provide a consistent error handling interface.
 *
 * @package GryfOSS\Mailer\Exception
 * @author GryfOSS GitHub Team
 */
class SendException extends SmartMailerException
{
}
