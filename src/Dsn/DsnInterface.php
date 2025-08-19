<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer\Dsn;

use Stringable;

/**
 * Interface for Data Source Name (DSN) implementations.
 *
 * This interface defines the contract for creating DSN strings that can be used
 * by Symfony Mailer to establish connections to email services. DSNs encapsulate
 * all the connection information needed to send emails through various providers.
 *
 * @package Praetorian\SmartMailer\Dsn
 * @author Praetorian Technology
 */
interface DsnInterface extends Stringable
{
    /**
     * Converts the DSN configuration to a string format.
     *
     * This method should return a properly formatted DSN string that can be
     * consumed by Symfony Mailer's Transport::fromDsn() method.
     *
     * @return string The DSN string representation
     */
    public function __toString(): string;
}
