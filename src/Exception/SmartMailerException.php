<?php

declare(strict_types=1);

namespace GryfOSS\Mailer\Exception;

use Exception;

/**
 * Base exception class for all SmartMailer-related exceptions.
 *
 * This abstract class serves as the base for all exceptions thrown by the
 * SmartMailer library. It extends PHP's standard Exception class and provides
 * a common type for catching any SmartMailer-specific errors.
 *
 * Usage example:
 * ```php
 * try {
 *     $mailer->send($message);
 * } catch (SmartMailerException $e) {
 *     // Handle any SmartMailer-related error
 *     echo "SmartMailer error: " . $e->getMessage();
 * }
 * ```
 *
 * @package GryfOSS\Mailer\Exception
 * @author GryfOSS GitHub Team
 */
abstract class SmartMailerException extends Exception
{
}
