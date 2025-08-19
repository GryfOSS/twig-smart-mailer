<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Exception;

/**
 * Exception thrown when an email address format is invalid.
 *
 * This exception is thrown when creating an EmailAddress instance with
 * a string that doesn't match valid email address format. The validation
 * uses PHP's FILTER_VALIDATE_EMAIL filter.
 *
 * The exception message includes the invalid email address that was provided.
 *
 * @package Praetorian\SmartMailer\Exception
 * @author Praetorian Technology
 */
class InvalidEmailAddressException extends SmartMailerException
{
    /**
     * Creates a new InvalidEmailAddressException.
     *
     * @param string $textProvided The invalid email address that was provided
     */
    public function __construct(string $textProvided)
    {
        parent::__construct(sprintf('Provided value of: `%s` is not a valid e-mail address.', $textProvided));
    }
}
