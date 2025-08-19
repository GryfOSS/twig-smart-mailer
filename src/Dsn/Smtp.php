<?php

declare(strict_types=1);

namespace GryfOSS\Mailer\Dsn;

use GryfOSS\Mailer\EncryptionMethod;
use GryfOSS\Mailer\LoginMethod;

/**
 * SMTP Data Source Name (DSN) implementation.
 *
 * This class creates DSN strings for SMTP server connections that can be used
 * by Symfony Mailer's Transport::fromDsn() method. It encapsulates all the
 * necessary SMTP connection parameters and formats them into a proper DSN string.
 *
 * The generated DSN follows the format:
 * smtp://username:password@host:port?verify_peer=0
 *
 * @package GryfOSS\Mailer\Dsn
 * @author GryfOSS GitHub Team
 */
class Smtp implements DsnInterface
{
    /**
     * SMTP server hostname or IP address.
     */
    protected string $host;

    /**
     * SMTP server port number.
     */
    protected int $port;

    /**
     * Encryption method for the connection.
     */
    protected EncryptionMethod $encryption = EncryptionMethod::SSLTLS;

    /**
     * Authentication method for SMTP login.
     */
    protected LoginMethod $loginMethod = LoginMethod::LOGIN;

    /**
     * Username for SMTP authentication.
     */
    protected ?string $username = null;

    /**
     * Password for SMTP authentication.
     */
    protected ?string $password = null;

    /**
     * Gets the SMTP server hostname.
     *
     * @return string The server hostname or IP address
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets the SMTP server hostname.
     *
     * @param string $host The server hostname or IP address
     *
     * @return self Returns this instance for method chaining
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Gets the SMTP server port number.
     *
     * @return int The port number
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Sets the SMTP server port number.
     *
     * Common ports:
     * - 25: Standard SMTP (not recommended for client connections)
     * - 465: SMTP over SSL/TLS
     * - 587: SMTP with STARTTLS
     *
     * @param int $port The port number
     *
     * @return self Returns this instance for method chaining
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Gets the encryption method.
     *
     * @return EncryptionMethod The current encryption method
     */
    public function getEncryption(): EncryptionMethod
    {
        return $this->encryption;
    }

    /**
     * Sets the encryption method.
     *
     * @param EncryptionMethod $encryption The encryption method to use
     *
     * @return self Returns this instance for method chaining
     */
    public function setEncryption(EncryptionMethod $encryption): self
    {
        $this->encryption = $encryption;

        return $this;
    }

    /**
     * Gets the authentication method.
     *
     * @return LoginMethod The current authentication method
     */
    public function getLoginMethod(): LoginMethod
    {
        return $this->loginMethod;
    }

    /**
     * Sets the authentication method.
     *
     * @param LoginMethod $loginMethod The authentication method to use
     *
     * @return self Returns this instance for method chaining
     */
    public function setLoginMethod(LoginMethod $loginMethod): self
    {
        $this->loginMethod = $loginMethod;

        return $this;
    }

    /**
     * Gets the username for authentication.
     *
     * @return string|null The username or null if not set
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Sets the username for authentication.
     *
     * @param string|null $username The username for SMTP authentication
     *
     * @return self Returns this instance for method chaining
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the password for authentication.
     *
     * @return string|null The password or null if not set
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Sets the password for authentication.
     *
     * @param string|null $password The password for SMTP authentication
     *
     * @return self Returns this instance for method chaining
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Converts the SMTP configuration to a DSN string.
     *
     * Creates a DSN string that can be used by Symfony Mailer to establish
     * an SMTP connection. The format is:
     * smtp://username:password@host:port?verify_peer=0
     *
     * Note: The verify_peer=0 parameter is included as a workaround for
     * development environments where SSL certificate verification might fail.
     *
     * @return string The SMTP DSN string
     */
    public function __toString(): string
    {
        //TODO solve why we cannot verify peer on dev
        return sprintf('smtp://%s:%s@%s:%s?verify_peer=0', $this->getUsername(), $this->getPassword(), $this->getHost(), $this->getPort());
    }
}
