<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer;

use Praetorian\SmartMailer\Exception\InvalidImageException;
use Praetorian\SmartMailer\Exception\NotUniqueEmbedNameException;
use Symfony\Component\Mime\MimeTypes;

/**
 * Represents an email message with all its components.
 *
 * This class encapsulates all aspects of an email message including recipients,
 * content (HTML/text), attachments, embedded images, and context variables for
 * Twig template rendering. It provides a fluent interface for building messages.
 *
 * The message supports:
 * - Multiple recipients (to, cc, bcc)
 * - HTML and plain text content with Twig templating
 * - File attachments
 * - Embedded images for HTML content
 * - Context variables for template rendering
 *
 * @package Praetorian\SmartMailer
 * @author Praetorian Technology
 */
class Message
{
    /**
     * Array of primary recipients (To field).
     *
     * @var EmailAddress[]|null
     */
    protected ?array $to = null;

    /**
     * Array of carbon copy recipients (CC field).
     *
     * @var EmailAddress[]|null
     */
    protected ?array $cc = null;

    /**
     * Array of blind carbon copy recipients (BCC field).
     *
     * @var EmailAddress[]|null
     */
    protected ?array $bcc = null;

    /**
     * Context variables for Twig template rendering.
     *
     * @var array|null
     */
    protected ?array $context = null;

    /**
     * Array of file attachments.
     *
     * @var Attachment[]|null
     */
    protected ?array $attachments = null;

    /**
     * Array of embedded images indexed by their embed names.
     *
     * @var array<string, Attachment>|null
     */
    protected ?array $images = null;

    /**
     * The sender's email address.
     */
    protected ?EmailAddress $from = null;

    /**
     * HTML content template (supports Twig syntax).
     */
    protected ?string $html = null;

    /**
     * Plain text content template (supports Twig syntax).
     */
    protected ?string $text = null;

    /**
     * The email subject line.
     */
    protected ?string $subject = null;

    /**
     * Gets the array of primary recipients.
     *
     * @return EmailAddress[]|null Array of To recipients or null if none set
     */
    public function getTo(): ?array
    {
        return $this->to;
    }

    /**
     * Sets the array of primary recipients.
     *
     * @param EmailAddress[] $to Array of primary recipients
     *
     * @return self Returns this instance for method chaining
     */
    public function setTo(array $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Gets the array of carbon copy recipients.
     *
     * @return EmailAddress[]|null Array of CC recipients or null if none set
     */
    public function getCc(): ?array
    {
        return $this->cc;
    }

    /**
     * Sets the array of carbon copy recipients.
     *
     * @param EmailAddress[] $cc Array of carbon copy recipients
     *
     * @return self Returns this instance for method chaining
     */
    public function setCc(array $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * Gets the array of blind carbon copy recipients.
     *
     * @return EmailAddress[]|null Array of BCC recipients or null if none set
     */
    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    /**
     * Sets the array of blind carbon copy recipients.
     *
     * @param EmailAddress[] $bcc Array of blind carbon copy recipients
     *
     * @return self Returns this instance for method chaining
     */
    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * Adds a primary recipient to the message.
     *
     * @param EmailAddress $emailAddress The recipient to add
     *
     * @return self Returns this instance for method chaining
     */
    public function addTo(EmailAddress $emailAddress)
    {
        return $this->addAddress($this->to, $emailAddress);
    }

    /**
     * Removes a primary recipient from the message.
     *
     * @param EmailAddress $emailAddress The recipient to remove
     *
     * @return self Returns this instance for method chaining
     */
    public function removeTo(EmailAddress $emailAddress)
    {
        return $this->removeAddress($this->to, $emailAddress);
    }

    /**
     * Checks if a primary recipient exists in the message.
     *
     * @param EmailAddress $emailAddress The recipient to check for
     *
     * @return bool True if the recipient exists, false otherwise
     */
    public function hasTo(EmailAddress $emailAddress)
    {
        return $this->hasAddress($this->to, $emailAddress);
    }

    /**
     * Adds a carbon copy recipient to the message.
     *
     * @param EmailAddress $emailAddress The recipient to add
     *
     * @return self Returns this instance for method chaining
     */
    public function addCc(EmailAddress $emailAddress)
    {
        return $this->addAddress($this->cc, $emailAddress);
    }

    /**
     * Removes a carbon copy recipient from the message.
     *
     * @param EmailAddress $emailAddress The recipient to remove
     *
     * @return self Returns this instance for method chaining
     */
    public function removeCc(EmailAddress $emailAddress)
    {
        return $this->removeAddress($this->cc, $emailAddress);
    }

    /**
     * Checks if a carbon copy recipient exists in the message.
     *
     * @param EmailAddress $emailAddress The recipient to check for
     *
     * @return bool True if the recipient exists, false otherwise
     */
    public function hasCc(EmailAddress $emailAddress)
    {
        return $this->hasAddress($this->cc, $emailAddress);
    }

    /**
     * Adds a blind carbon copy recipient to the message.
     *
     * @param EmailAddress $emailAddress The recipient to add
     *
     * @return self Returns this instance for method chaining
     */
    public function addBcc(EmailAddress $emailAddress)
    {
        return $this->addAddress($this->bcc, $emailAddress);
    }

    /**
     * Removes a blind carbon copy recipient from the message.
     *
     * @param EmailAddress $emailAddress The recipient to remove
     *
     * @return self Returns this instance for method chaining
     */
    public function removeBcc(EmailAddress $emailAddress)
    {
        return $this->removeAddress($this->bcc, $emailAddress);
    }

    /**
     * Checks if a blind carbon copy recipient exists in the message.
     *
     * @param EmailAddress $emailAddress The recipient to check for
     *
     * @return bool True if the recipient exists, false otherwise
     */
    public function hasBcc(EmailAddress $emailAddress)
    {
        return $this->hasAddress($this->bcc, $emailAddress);
    }

    /**
     * Gets the HTML content template.
     *
     * @return string|null The HTML template or null if not set
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * Sets the HTML content template.
     *
     * The content can include Twig template syntax and will be rendered
     * with the provided context variables.
     *
     * @param string|null $html The HTML template content
     *
     * @return self Returns this instance for method chaining
     */
    public function setHtml(?string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Gets the plain text content template.
     *
     * @return string|null The text template or null if not set
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Sets the plain text content template.
     *
     * The content can include Twig template syntax and will be rendered
     * with the provided context variables.
     *
     * @param string|null $text The plain text template content
     *
     * @return self Returns this instance for method chaining
     */
    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Gets the sender's email address.
     *
     * @return EmailAddress|null The sender's email address or null if not set
     */
    public function getFrom(): ?EmailAddress
    {
        return $this->from;
    }

    /**
     * Sets the sender's email address.
     *
     * @param EmailAddress|null $from The sender's email address
     *
     * @return self Returns this instance for method chaining
     */
    public function setFrom(?EmailAddress $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Gets the email subject line.
     *
     * @return string|null The subject line or null if not set
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Sets the email subject line.
     *
     * @param string|null $subject The subject line
     *
     * @return self Returns this instance for method chaining
     */
    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Gets the context variables for template rendering.
     *
     * @return array|null The context array or null if not set
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * Sets the context variables for template rendering.
     *
     * These variables will be available in both HTML and text templates
     * when they are rendered by Twig.
     *
     * @param array|null $context Associative array of variables
     *
     * @return self Returns this instance for method chaining
     */
    public function setContext(?array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Gets all file attachments.
     *
     * @return Attachment[]|null Array of attachments or null if none set
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * Adds a file attachment to the message.
     *
     * @param Attachment $attachment The attachment to add
     *
     * @return self Returns this instance for method chaining
     */
    public function addAttachment(Attachment $attachment)
    {
        if (!is_array($this->attachments)) {
            $this->attachments = [];
        }

        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Checks if a specific attachment exists in the message.
     *
     * @param Attachment $attachment The attachment to check for
     *
     * @return bool True if the attachment exists, false otherwise
     */
    public function hasAttachment(Attachment $attachment)
    {
        if (empty($this->attachments)) {
            return false;
        }

        if (array_search($attachment, $this->getAttachments(), true)) {
            return true;
        }

        return false;
    }

    /**
     * Removes a file attachment from the message.
     *
     * Note: There's a typo in the original method name 'removeAttachemnt'
     *
     * @param Attachment $attachment The attachment to remove
     *
     * @return self Returns this instance for method chaining
     */
    public function removeAttachemnt(Attachment $attachment)
    {
        while ($key = array_search($attachment, $this->getAttachments() ?? [], true)) {
            unset($this->attachments[$key]);
        }

        return $this;
    }

    /**
     * Gets all embedded images.
     *
     * @return array<string, Attachment>|null Array of images indexed by embed name or null if none set
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * Checks if an image with a specific embed name exists.
     *
     * @param string $name The embed name to check for
     *
     * @return bool True if an image with that name exists, false otherwise
     */
    public function hasImageKey(string $name)
    {
        return is_array($this->images) ? isset($this->images[$name]) : false;
    }

    /**
     * Adds an embedded image to the message.
     *
     * The image can be referenced in HTML content using "cid:{name}" where
     * name is either the custom name or the filename.
     *
     * @param Attachment $attachment The image file to embed
     *
     * @return self Returns this instance for method chaining
     *
     * @throws InvalidImageException When the file is not a valid image
     * @throws NotUniqueEmbedNameException When an image with the same name already exists
     */
    public function addImage(Attachment $attachment)
    {
        $mimeType = MimeTypes::getDefault()->guessMimeType($attachment->getPath());

        if(!str_starts_with(haystack: $mimeType, needle: 'image/')) {
            throw new InvalidImageException($attachment->getPath());
        }

        if (!is_array($this->images)) {
            $this->images = [];
        }

        $name = $this->getImageName($attachment);

        if ($this->hasImageKey($name)) {
            throw new NotUniqueEmbedNameException($name);
        }

        $this->images[$name] = $attachment;

        return $this;
    }

    /**
     * Checks if a specific image attachment exists in the message.
     *
     * @param Attachment $attachment The image attachment to check for
     *
     * @return bool True if the image exists, false otherwise
     */
    public function hasImage(Attachment $attachment)
    {
        if (empty($this->images)) {
            return false;
        }

        if (array_search($attachment, $this->getImages(), true)) {
            return true;
        }

        return false;
    }

    /**
     * Removes an embedded image by its embed name.
     *
     * @param string $name The embed name of the image to remove
     *
     * @return self Returns this instance for method chaining
     */
    public function removeImageByKey(string $name)
    {
        if (empty($this->images)) {
            return $this;
        }

        if (!$this->hasImageKey($name)) {
            return $this;
        }

        unset($this->images[$name]);

        return $this;
    }

    /**
     * Removes an embedded image by attachment reference.
     *
     * @param Attachment $attachment The image attachment to remove
     *
     * @return self Returns this instance for method chaining
     */
    public function removeImage(Attachment $attachment)
    {
        while ($key = array_search($attachment, $this->getImages() ?? [], true)) {
            unset($this->images[$key]);
        }
    }

    /**
     * Checks if an email address exists in a recipient collection.
     *
     * @param array|null $collection The recipient collection to check
     * @param EmailAddress $address The email address to look for
     *
     * @return bool True if the address exists, false otherwise
     */
    private function hasAddress($collection, EmailAddress $address)
    {
        return isset($collection[(string) $address]);
    }

    /**
     * Adds an email address to a recipient collection.
     *
     * @param array|null $collection Reference to the recipient collection
     * @param EmailAddress $address The email address to add
     *
     * @return self Returns this instance for method chaining
     */
    private function addAddress(&$collection, EmailAddress $address)
    {
        if (!is_array($collection)) {
            $collection = [];
        }

        $collection[(string) $address] = $address;

        return $this;
    }

    /**
     * Removes an email address from a recipient collection.
     *
     * @param array|null $collection Reference to the recipient collection
     * @param EmailAddress $emailAddress The email address to remove
     *
     * @return self Returns this instance for method chaining
     */
    private function removeAddress(&$collection, EmailAddress $emailAddress)
    {
        if (!is_array($collection)) {
            return $this;
        }
    }

    /**
     * Gets the embed name for an image attachment.
     *
     * Uses the custom name if set, otherwise falls back to the filename.
     *
     * @param Attachment $image The image attachment
     *
     * @return string The embed name to use
     */
    private function getImageName(Attachment $image)
    {
        return !empty($image->getName()) ? $image->getName() : basename($image->getPath());
    }
}
