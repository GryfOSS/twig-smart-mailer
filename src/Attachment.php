<?php

declare(strict_types=1);

namespace GryfOSS\Mailer;

use GryfOSS\Mailer\Exception\InvalidAttachmentException;

/**
 * Represents a file attachment for email messages.
 *
 * This class handles file attachments that can be added to email messages.
 * It validates that the file exists and is readable before allowing it to be used.
 *
 * @package GryfOSS\Mailer
 * @author GryfOSS GitHub Team
 */
class Attachment
{
    /**
     * The file system path to the attachment file.
     */
    protected string $path;

    /**
     * Creates a new attachment instance.
     *
     * @param string $path The file system path to the attachment file
     * @param string|null $name Optional custom name for the attachment. If not provided,
     *                          the original filename will be used
     *
     * @throws InvalidAttachmentException When the file doesn't exist or is not readable
     */
    public function __construct(string $path, protected ?string $name = null)
    {
        $this->setPath($path);
    }

    /**
     * Sets and validates the file path for the attachment.
     *
     * @param string $path The file system path to validate and set
     *
     * @return self Returns this instance for method chaining
     *
     * @throws InvalidAttachmentException When the file doesn't exist or is not readable
     */
    private function setPath(string $path): self
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new InvalidAttachmentException($path);
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Gets the custom name for the attachment.
     *
     * @return string|null The custom name if set, null otherwise
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Gets the file system path to the attachment.
     *
     * @return string The validated file path
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
