<?php

declare(strict_types=1);

namespace GryfOSS\Mailer;

use Exception;
use GryfOSS\Mailer\Dsn\DsnInterface;
use GryfOSS\Mailer\Exception\InvalidEmailMessageException;
use GryfOSS\Mailer\Exception\SendException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Main email sending implementation with Twig template support.
 *
 * This class provides the primary implementation for sending emails using
 * Symfony Mailer with enhanced features including:
 * - Twig template rendering for HTML and text content
 * - File attachments and embedded images
 * - Flexible DSN-based transport configuration
 * - Message validation
 *
 * The class integrates Twig templating to allow dynamic content generation
 * using template syntax within email bodies.
 *
 * @package GryfOSS\Mailer
 * @author GryfOSS GitHub Team
 */
class SmartMailer implements SmartMailerInterface
{
    /**
     * Twig environment for template rendering.
     */
    protected ?Environment $twig = null;

    /**
     * Creates a new SmartMailer instance.
     *
     * @param DsnInterface $dsn The DSN configuration for the email transport
     * @param Environment|null $twig Optional Twig environment. If not provided,
     *                               a default filesystem-based environment will be created
     */
    public function __construct(protected DsnInterface $dsn, ?Environment $twig = null)
    {
        $this->twig = $twig ?? $this->createDummyTwig();
    }

    /**
     * Gets the current DSN configuration.
     *
     * @return DsnInterface The DSN configuration
     */
    public function getDsn(): DsnInterface
    {
        return $this->dsn;
    }

    /**
     * Sets a new DSN configuration.
     *
     * @param DsnInterface $dsn The new DSN configuration
     *
     * @return self Returns this instance for method chaining
     */
    public function setDsn(DsnInterface $dsn): self
    {
        $this->dsn = $dsn;

        return $this;
    }

    /**
     * Validates an email message before sending.
     *
     * Checks that the message has:
     * - A sender (from address)
     * - At least one recipient (to, cc, or bcc)
     * - At least one content type (HTML or text)
     *
     * @param Message $message The message to validate
     *
     * @return bool Always returns true if validation passes
     *
     * @throws InvalidEmailMessageException When validation fails
     */
    public function validate(Message $message)
    {
        if (!$message->getFrom()) {
            throw new InvalidEmailMessageException('Missing `from`.');
        }

        if (empty($message->getTo()) && empty($message->getCc()) && empty($message->getBcc())) {
            throw new InvalidEmailMessageException('Message must have at least one recipient.');
        }

        if (empty($message->getHtml()) && empty($message->getText())) {
            throw new InvalidEmailMessageException('Message must have at least one (html, text) body.');
        }

        return true;
    }

    /**
     * Sends an email message.
     *
     * This method:
     * 1. Validates the message
     * 2. Creates a Symfony Mailer transport from the DSN
     * 3. Renders Twig templates for HTML/text content
     * 4. Processes attachments and embedded images
     * 5. Sends the email
     *
     * @param Message $message The message to send
     *
     * @return mixed The result of the send operation
     *
     * @throws InvalidEmailMessageException When message validation fails
     * @throws SendException When sending fails
     */
    public function send(Message $message)
    {
        $this->validate($message);

        $dsn = (string) $this->getDsn();
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $from = $message->getFrom();

        // Generates the email
        $email = new Email();
        $email->addFrom(
            new Address($from->getAddress(), $from->getName() ?? '')
        );

        // Process subject through Twig if it contains template syntax
        $subject = $message->getSubject() ?? '';
        if (strpos($subject, '{{') !== false || strpos($subject, '{%') !== false) {
            $template = $this->twig->createTemplate($subject, 'email_subject');
            $subject = $template->render($message->getContext() ?? []);
        }
        $email->subject($subject);

        /* @var EmailAddress */
        foreach ($message->getTo() ?? [] as $to) {
            $email->addTo(new Address($to->getAddress(), $to->getName() ?? ''));
        }

        /* @var EmailAddress */
        foreach ($message->getCc() ?? [] as $to) {
            $email->addCc(new Address($to->getAddress(), $to->getName() ?? ''));
        }

        /* @var EmailAddress */
        foreach ($message->getBcc() ?? [] as $to) {
            $email->addBcc(new Address($to->getAddress(), $to->getName() ?? ''));
        }

        if ($message->getHtml()) {
            $template = $this->twig->createTemplate($message->getHtml(), 'email_html_body');
            $rendered = $template->render($message->getContext() ?? []);
            $email->html($rendered);
        }

        if ($message->getText()) {
            $template = $this->twig->createTemplate($message->getText(), 'email_html_body');
            $rendered = $template->render($message->getContext() ?? []);
            $email->text($rendered);
        }

        /* @var Attachment */
        foreach ($message->getAttachments() ?? [] as $attachment) {
            $email->attachFromPath($attachment->getPath(), $attachment->getName());
        }

        foreach ($message->getImages() ?? [] as $name => $attachment) {
            $email->embedFromPath($attachment->getPath(), (string) $name);
        }

        try {
            return $mailer->send($email);
        } catch (Exception $e) {
            throw new SendException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates a basic Twig environment for template rendering.
     *
     * This fallback method creates a minimal Twig environment with a
     * filesystem loader pointing to the current directory. It's used
     * when no custom Twig environment is provided.
     *
     * @return Environment A basic Twig environment
     */
    protected function createDummyTwig(): Environment
    {
        $loader = new FilesystemLoader('.');

        return new Environment($loader);
    }
}
