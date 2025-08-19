<?php

declare(strict_types=1);

namespace GryfOSS\Mailer\Dsn;

/**
 * Gmail Data Source Name (DSN) implementation.
 *
 * This class creates DSN strings specifically for Gmail SMTP connections using
 * Symfony Mailer's built-in Gmail transport. It simplifies Gmail configuration
 * by only requiring username and password, as the Gmail transport handles
 * the server configuration automatically.
 *
 * The generated DSN follows the format:
 * gmail+smtp://username:password@default
 *
 * This DSN leverages Symfony Mailer's Gmail transport which automatically
 * configures the appropriate SMTP settings for Gmail.
 *
 * @package GryfOSS\Mailer\Dsn
 * @author GryfOSS GitHub Team
 */
class Gmail implements DsnInterface
{
    /**
     * Gmail username (email address).
     */
    protected ?string $username;

    /**
     * Gmail password or app-specific password.
     */
    protected ?string $password;

    /**
     * Gets the Gmail username.
     *
     * @return string|null The Gmail username (email address) or null if not set
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Sets the Gmail username.
     *
     * @param string|null $username The Gmail email address
     *
     * @return self Returns this instance for method chaining
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the Gmail password.
     *
     * @return string|null The password or null if not set
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Sets the Gmail password.
     *
     * For accounts with 2-factor authentication enabled, you should use
     * an app-specific password instead of your regular account password.
     *
     * @param string|null $password The Gmail password or app-specific password
     *
     * @return self Returns this instance for method chaining
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Converts the Gmail configuration to a DSN string.
     *
     * Creates a DSN string that utilizes Symfony Mailer's Gmail transport.
     * The format is: gmail+smtp://username:password@default
     *
     * The "default" hostname tells Symfony Mailer to use Gmail's default
     * SMTP settings (smtp.gmail.com:587 with STARTTLS).
     *
     * @return string The Gmail DSN string
     */
    public function __toString(): string
    {
        return sprintf('gmail+smtp://%s:%s@default', $this->getUsername(), $this->getPassword());
    }
}

