<?php

declare(strict_types=1);

namespace GryfOSS\Mailer;

use GryfOSS\Mailer\Exception\InvalidEmailAddressException;

/**
 * Represents an email address with optional display name.
 *
 * This class encapsulates an email address and provides validation to ensure
 * the email format is correct. It also supports an optional display name
 * that can be shown alongside the email address.
 *
 * @package GryfOSS\Mailer
 * @author GryfOSS GitHub Team
 */
class EmailAddress
{
    /**
     * The validated email address (lowercase).
     */
    protected string $address;

    /**
     * Creates a new email address instance.
     *
     * @param string $address The email address to validate and store
     * @param string|null $name Optional display name for the email address
     *
     * @throws InvalidEmailAddressException When the email address format is invalid
     */
    public function __construct(string $address, protected ?string $name = null)
    {
        $this->setAddress($address);
    }

    /**
     * Returns the email address as a string.
     *
     * @return string The validated email address
     */
    public function __toString()
    {
        return $this->getAddress();
    }

    /**
     * Validates and sets the email address.
     *
     * The email address is validated using PHP's FILTER_VALIDATE_EMAIL filter
     * and converted to lowercase for consistency.
     *
     * @param string $address The email address to validate and set
     *
     * @return self Returns this instance for method chaining
     *
     * @throws InvalidEmailAddressException When the email address format is invalid
     */
    private function setAddress(string $address): self
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailAddressException($address);
        }

        $this->address = mb_strtolower(trim($address));

        return $this;
    }

    /**
     * Gets the validated email address.
     *
     * @return string The email address in lowercase
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Gets the display name for the email address.
     *
     * @return string|null The display name if set, null otherwise
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
