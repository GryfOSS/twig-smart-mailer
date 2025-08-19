<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Exception;

/**
 * Exception thrown when a file attachment is invalid.
 *
 * This exception is thrown when attempting to create an Attachment with
 * a file that either doesn't exist or is not readable. The file must be
 * accessible on the filesystem and have appropriate read permissions.
 *
 * The exception message includes the file path that caused the issue.
 *
 * @package Praetorian\SmartMailer\Exception
 * @author Praetorian Technology
 */
class InvalidAttachmentException extends SmartMailerException
{
    /**
     * Creates a new InvalidAttachmentException.
     *
     * @param string $path The file path that is invalid (doesn't exist or not readable)
     */
    public function __construct(string $path)
    {
        parent::__construct(sprintf('File `%s` must exist and be readable.', $path));
    }
}
