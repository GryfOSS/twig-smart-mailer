<?php

declare(strict_types=1);

namespace Praetorian\SmartMailer;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * File-based email implementation for testing and development.
 *
 * This class extends SmartMailer to provide a testing/development alternative
 * that writes email data to a file instead of actually sending emails. This is
 * useful for:
 * - Development environments where you don't want to send real emails
 * - Testing email generation without SMTP configuration
 * - Debugging email content and structure
 *
 * The generated file contains JSON data with email details including recipients,
 * subject, and rendered content.
 *
 * @package Praetorian\SmartMailer
 * @author Praetorian Technology
 */
class FakeFileSmartMailer extends SmartMailer implements SmartMailerInterface
{
    /**
     * Twig environment for template rendering.
     */
    protected ?Environment $twig = null;

    /**
     * Creates a new FakeFileSmartMailer instance.
     *
     * @param string $outputPath The file path where email data will be written
     * @param Environment|null $twig Optional Twig environment. If not provided,
     *                               a default filesystem-based environment will be created
     */
    public function __construct(protected string $outputPath, ?Environment $twig = null)
    {
        $this->twig = $twig ?? $this->createDummyTwig();
    }

    /**
     * Sets the output file path.
     *
     * @param string $outputPath The new file path for email output
     *
     * @return self Returns this instance for method chaining
     */
    public function setOutputPath(string $outputPath): self
    {
        $this->outputPath = $outputPath;

        return $this;
    }

    /**
     * Gets the current output file path.
     *
     * @return string The file path where emails are written
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * "Sends" an email by writing it to a file.
     *
     * This method validates the message and generates the email structure
     * like the parent class, but instead of sending it through SMTP, it
     * writes the email data as JSON to the configured output file.
     *
     * @param Message $message The message to process
     *
     * @return mixed The result of the file write operation
     *
     * @throws InvalidEmailMessageException When message validation fails
     */
    public function send(Message $message)
    {
        $this->validate($message);
        $from = $message->getFrom();

        // Generates the email
        $email = new Email();
        $email->addFrom(
            new Address($from->getAddress(), $from->getName() ?? '')
        );

        $email->subject($message->getSubject() ?? '');

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
            $email->attach($attachment->getPath(), $attachment->getName());
        }

        foreach ($message->getImages() ?? [] as $name => $attachment) {
            $email->embedFromPath($attachment->getPath(), $name, 'image/png');
        }

        $emailDta = [
            'from' => $email->getFrom(),
            'to' => $email->getTo(),
            'subject' => $email->getSubject(),
            'html' => $email->getHtmlBody(),
        ];

        file_put_contents($this->getOutputPath(), json_encode($emailDta));
    }
}
