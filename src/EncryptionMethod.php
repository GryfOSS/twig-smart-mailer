<?php

declare(strict_types=1);

namespace GryfOSS\Mailer;

/**
 * Enumeration of available SMTP encryption methods.
 *
 * This enum defines the supported encryption methods for SMTP connections,
 * providing a type-safe way to specify how the connection should be secured.
 *
 * @package GryfOSS\Mailer
 * @author GryfOSS GitHub Team
 */
enum EncryptionMethod: string
{
    /**
     * No encryption - plain text connection.
     * Not recommended for production use.
     */
    case NO_ENCRYPTION = '';

    /**
     * SSL/TLS encryption from the start of the connection.
     * Typically used on port 465.
     */
    case SSLTLS = 'SSL';

    /**
     * STARTTLS encryption - starts plain and upgrades to encrypted.
     * Typically used on port 587.
     */
    case STARTTLS = 'STARTTLS';
}
