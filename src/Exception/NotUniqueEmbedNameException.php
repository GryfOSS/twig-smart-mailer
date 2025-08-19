<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Exception;

/**
 * Exception thrown when attempting to embed an image with a duplicate name.
 *
 * This exception is thrown when trying to add an embedded image to a message
 * using a name (either custom name or filename) that already exists in the
 * message's embedded images collection. Each embedded image must have a
 * unique identifier for proper referencing in HTML content.
 *
 * The exception message includes the duplicate name that was attempted.
 *
 * @package Praetorian\SmartMailer\Exception
 * @author Praetorian Technology
 */
class NotUniqueEmbedNameException extends SmartMailerException
{
    /**
     * Creates a new NotUniqueEmbedNameException.
     *
     * @param string $name The duplicate embed name that was attempted
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Embedded resource\'s name must be unique, use a different file or set the name explicitly. Used: `%s`.', $name));
    }
}
