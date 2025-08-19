<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Dsn\DsnInterface;
use GryfOSS\Mailer\Exception\SendException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;

class SmartMailerSendTest extends TestCase
{
    private MockObject&DsnInterface $dsnMock;
    private SmartMailer $smartMailer;

    protected function setUp(): void
    {
        $this->dsnMock = $this->createMock(DsnInterface::class);
        $this->dsnMock->method('__toString')->willReturn('null://null');

        $this->smartMailer = new SmartMailer($this->dsnMock);
    }

    public function testSendWithMinimalMessage(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        // Use null transport which doesn't actually send
        $this->smartMailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testSendWithCompleteMessage(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', 'Sender Name'));
        $message->addTo(new EmailAddress('to@example.com', 'To Name'));
        $message->addCc(new EmailAddress('cc@example.com', 'CC Name'));
        $message->addBcc(new EmailAddress('bcc@example.com', 'BCC Name'));
        $message->setSubject('Test Subject');
        $message->setHtml('<p>Hello {{ name }}!</p>');
        $message->setText('Hello {{ name }}!');
        $message->setContext(['name' => 'World']);

        // Use null transport which doesn't actually send
        $this->smartMailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testSendWithAttachments(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempFile, 'test content');

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $attachment = new Attachment($tempFile, 'document.pdf');
        $message->addAttachment($attachment);

        // Use null transport which doesn't actually send
        $this->smartMailer->send($message);

        unlink($tempFile);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testSendWithTwigTemplating(): void
    {
        $templates = [
            'email.html.twig' => '<h1>Hello {{ name }}!</h1>',
            'email.txt.twig' => 'Hello {{ name }}!'
        ];

        $twig = new Environment(new ArrayLoader($templates));
        $mailer = new SmartMailer($this->dsnMock, $twig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('{{ include("email.html.twig") }}');
        $message->setText('{{ include("email.txt.twig") }}');
        $message->setContext(['name' => 'John']);

        // Use null transport which doesn't actually send
        $mailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testSendThrowsSendExceptionOnTransportError(): void
    {
        // Create a DSN that will cause an error when sending
        /** @var MockObject&DsnInterface $badDsn */
        $badDsn = $this->createMock(DsnInterface::class);
        $badDsn->method('__toString')->willReturn('smtp://invalid.host.name:999');

        $mailer = new SmartMailer($badDsn);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $this->expectException(SendException::class);

        $mailer->send($message);
    }

    public function testSendWithOnlyTextBody(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setText('Plain text message');

        // Use null transport which doesn't actually send
        $this->smartMailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testSendWithEmptyNamedAddresses(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', ''));
        $message->addTo(new EmailAddress('to@example.com', ''));
        $message->addCc(new EmailAddress('cc@example.com', ''));
        $message->addBcc(new EmailAddress('bcc@example.com', ''));
        $message->setHtml('<p>Hello World</p>');

        // Use null transport which doesn't actually send
        $this->smartMailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testSendWithNullSubject(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');
        $message->setSubject(null);

        // Use null transport which doesn't actually send
        $this->smartMailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }

    public function testCreateDummyTwigIsUsedWhenNoTwigProvided(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        // This will use the createDummyTwig method internally
        $this->smartMailer->send($message);

        // If we get here, no exception was thrown
        $this->assertTrue(true);
    }
}
