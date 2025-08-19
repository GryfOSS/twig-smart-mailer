<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Exception;

/**
 * Exception thrown when a file is not a valid image for embedding.
 *
 * This exception is thrown when attempting to add an embedded image
 * to a message using a file that is not detected as an image type.
 * The validation uses Symfony's MimeTypes component to check if the
 * file's MIME type starts with 'image/'.
 *
 * The exception message includes the file path that is not an image.
 *
 * @package Praetorian\SmartMailer\Exception
 * @author Praetorian Technology
 */
class InvalidImageException extends SmartMailerException
{
    /**
     * Creates a new InvalidImageException.
     *
     * @param string $path The file path that is not a valid image
     */
    public function __construct(string $path)
    {
        parent::__construct(sprintf('File `%s` is not an image.', $path));
    }
}
