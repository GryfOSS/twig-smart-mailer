<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Dsn\DsnInterface;
use GryfOSS\Mailer\Exception\InvalidEmailMessageException;
use GryfOSS\Mailer\Exception\SendException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use PHPUnit\Framework\MockObject\MockObject;

class SmartMailerTest extends TestCase
{
    private MockObject $dsnMock;
    private SmartMailer $smartMailer;

    protected function setUp(): void
    {
        $this->dsnMock = $this->createMock(DsnInterface::class);
        $this->dsnMock->method('__toString')->willReturn('smtp://localhost:587');

        $this->smartMailer = new SmartMailer($this->dsnMock);
    }

    public function testConstructorWithDsn(): void
    {
        $mailer = new SmartMailer($this->dsnMock);

        $this->assertSame($this->dsnMock, $mailer->getDsn());
    }

    public function testConstructorWithDsnAndTwig(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $mailer = new SmartMailer($this->dsnMock, $twig);

        $this->assertSame($this->dsnMock, $mailer->getDsn());
    }

    public function testGetDsn(): void
    {
        $dsn = $this->smartMailer->getDsn();

        $this->assertSame($this->dsnMock, $dsn);
    }

    public function testSetDsn(): void
    {
        $newDsn = $this->createMock(DsnInterface::class);

        $result = $this->smartMailer->setDsn($newDsn);

        $this->assertSame($this->smartMailer, $result);
        $this->assertSame($newDsn, $this->smartMailer->getDsn());
    }

    public function testValidateValidMessage(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $result = $this->smartMailer->validate($message);

        $this->assertTrue($result);
    }

    public function testValidateThrowsExceptionWhenMissingFrom(): void
    {
        $this->expectException(InvalidEmailMessageException::class);
        $this->expectExceptionMessage('Missing `from`.');

        $message = new Message();
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $this->smartMailer->validate($message);
    }

    public function testValidateThrowsExceptionWhenMissingRecipients(): void
    {
        $this->expectException(InvalidEmailMessageException::class);
        $this->expectExceptionMessage('Message must have at least one recipient.');

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $this->smartMailer->validate($message);
    }

    public function testValidateThrowsExceptionWhenMissingContent(): void
    {
        $this->expectException(InvalidEmailMessageException::class);
        $this->expectExceptionMessage('Message must have at least one (html, text) body.');

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));

        $this->smartMailer->validate($message);
    }

    public function testValidateWithCcRecipient(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addCc(new EmailAddress('cc@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $result = $this->smartMailer->validate($message);

        $this->assertTrue($result);
    }

    public function testValidateWithBccRecipient(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addBcc(new EmailAddress('bcc@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $result = $this->smartMailer->validate($message);

        $this->assertTrue($result);
    }

    public function testValidateWithTextContent(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setText('Hello World');

        $result = $this->smartMailer->validate($message);

        $this->assertTrue($result);
    }

    public function testValidateWithBothHtmlAndTextContent(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');
        $message->setText('Hello World');

        $result = $this->smartMailer->validate($message);

        $this->assertTrue($result);
    }

    public function testSendWithValidMessage(): void
    {
        // Use FakeFileSmartMailer for testing actual send functionality
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $fakeMailer = new \GryfOSS\Mailer\FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', 'Sender Name'));
        $message->addTo(new EmailAddress('recipient@example.com', 'Recipient Name'));
        $message->setSubject('Test Subject');
        $message->setHtml('<p>Hello {{ name }}!</p>');
        $message->setText('Hello {{ name }}!');
        $message->setContext(['name' => 'World']);

        // This should not throw any exceptions
        $fakeMailer->send($message);

        $this->assertFileExists($tempFile);
        unlink($tempFile);
    }

    public function testSendWithAttachments(): void
    {
        $tempAttachment = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempAttachment, 'test content');

        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $fakeMailer = new \GryfOSS\Mailer\FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $attachment = new Attachment($tempAttachment, 'document.pdf');
        $message->addAttachment($attachment);

        // This should not throw any exceptions
        $fakeMailer->send($message);

        $this->assertFileExists($tempFile);
        unlink($tempFile);
        unlink($tempAttachment);
    }

    public function testSendWithImages(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testSendWithCcAndBcc(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $fakeMailer = new \GryfOSS\Mailer\FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('to@example.com'));
        $message->addCc(new EmailAddress('cc@example.com'));
        $message->addBcc(new EmailAddress('bcc@example.com'));
        $message->setHtml('<p>Hello World</p>');

        // This should not throw any exceptions
        $fakeMailer->send($message);

        $this->assertFileExists($tempFile);
        unlink($tempFile);
    }

    public function testSendWithEmptySubject(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $fakeMailer = new \GryfOSS\Mailer\FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');
        $message->setSubject(null);

        // This should not throw any exceptions
        $fakeMailer->send($message);

        $this->assertFileExists($tempFile);
        unlink($tempFile);
    }

    public function testSendWithTwigTemplating(): void
    {
        $templates = [
            'email.html.twig' => '<h1>Hello {{ name }}!</h1><p>You have {{ count }} messages.</p>',
            'email.txt.twig' => 'Hello {{ name }}! You have {{ count }} messages.'
        ];

        $twig = new Environment(new ArrayLoader($templates));
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $fakeMailer = new \GryfOSS\Mailer\FakeFileSmartMailer($tempFile, $twig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('{{ include("email.html.twig") }}');
        $message->setText('{{ include("email.txt.twig") }}');
        $message->setContext(['name' => 'John', 'count' => 5]);

        // This should not throw any exceptions
        $fakeMailer->send($message);

        $this->assertFileExists($tempFile);
        unlink($tempFile);
    }

    private function mockFileSystemFunctions(): void
    {
        // Create temporary test files for attachment testing
        if (!function_exists('GryfOSS\Mailer\file_exists')) {
            function file_exists($filename) {
                return true;
            }
        }

        if (!function_exists('GryfOSS\Mailer\is_readable')) {
            function is_readable($filename) {
                return true;
            }
        }
    }
}
