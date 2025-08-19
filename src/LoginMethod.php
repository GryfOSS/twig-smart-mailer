<?php

declare(strict_types=1);

namespace GryfOSS\Mailer;

/**
 * Enumeration of available SMTP authentication methods.
 *
 * This enum defines the supported authentication methods for SMTP connections,
 * providing a type-safe way to specify how authentication should be performed.
 *
 * @package GryfOSS\Mailer
 * @author GryfOSS GitHub Team
 */
enum LoginMethod: string
{
    /**
     * Automatic detection of the best authentication method.
     * The server will determine the most appropriate method.
     */
    case AUTO = '';

    /**
     * PLAIN authentication method.
     * Username and password are sent in plain text (but may be encrypted by TLS).
     */
    case PLAIN = 'PLAIN';

    /**
     * LOGIN authentication method.
     * Similar to PLAIN but uses a different protocol exchange.
     */
    case LOGIN = 'LOGIN';

    /**
     * CRAM-MD5 authentication method.
     * Uses MD5 hashing for password authentication.
     */
    case CRAM_MD5 = 'CRAM-MD5';

    /**
     * DIGEST-MD5 authentication method.
     * More secure MD5-based authentication with challenge-response.
     */
    case DIGEST_MD5 = 'DIGEST-MD5';
}
