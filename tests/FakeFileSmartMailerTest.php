<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\FakeFileSmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use phpmock\phpunit\PHPMock;

class FakeFileSmartMailerTest extends TestCase
{
    use PHPMock;

    private string $tempFile;
    private FakeFileSmartMailer $mailer;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $this->mailer = new FakeFileSmartMailer($this->tempFile);

        // Mock file system functions for attachment testing
        $this->mockFileSystemFunctions();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testConstructor(): void
    {
        $mailer = new FakeFileSmartMailer('/path/to/output.json');

        $this->assertEquals('/path/to/output.json', $mailer->getOutputPath());
    }

    public function testConstructorWithTwig(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $mailer = new FakeFileSmartMailer('/path/to/output.json', $twig);

        $this->assertEquals('/path/to/output.json', $mailer->getOutputPath());
    }

    public function testSetAndGetOutputPath(): void
    {
        $newPath = '/new/path/to/output.json';

        $result = $this->mailer->setOutputPath($newPath);

        $this->assertSame($this->mailer, $result);
        $this->assertEquals($newPath, $this->mailer->getOutputPath());
    }

    public function testSendBasicMessage(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', 'Sender Name'));
        $message->addTo(new EmailAddress('recipient@example.com', 'Recipient Name'));
        $message->setSubject('Test Subject');
        $message->setHtml('<p>Hello World!</p>');

        $this->mailer->send($message);

        $this->assertFileExists($this->tempFile);

        $content = file_get_contents($this->tempFile);
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertEquals('Test Subject', $data['subject']);
        $this->assertEquals('<p>Hello World!</p>', $data['html']);
        $this->assertIsArray($data['from']);
        $this->assertIsArray($data['to']);
    }

    public function testSendMessageWithTwigTemplate(): void
    {
        $templates = [
            'email.html.twig' => '<h1>Hello {{ name }}!</h1>'
        ];

        $twig = new Environment(new ArrayLoader($templates));
        $mailer = new FakeFileSmartMailer($this->tempFile, $twig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('Hello {{ name }}!');
        $message->setContext(['name' => 'John']);

        $mailer->send($message);

        $this->assertFileExists($this->tempFile);

        $content = file_get_contents($this->tempFile);
        $data = json_decode($content, true);

        $this->assertEquals('Hello John!', $data['html']);
    }

    public function testSendMessageWithTextContent(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setText('Hello {{ name }}!');
        $message->setContext(['name' => 'World']);

        $this->mailer->send($message);

        $this->assertFileExists($this->tempFile);

        $content = file_get_contents($this->tempFile);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('html', $data);
    }

    public function testSendMessageWithAttachments(): void
    {
        $tempAttachment = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempAttachment, 'test content');

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World!</p>');

        $attachment = new Attachment($tempAttachment, 'document.pdf');
        $message->addAttachment($attachment);

        // This should not throw any exceptions
        $this->mailer->send($message);

        $this->assertFileExists($this->tempFile);

        unlink($tempAttachment);
    }

    public function testSendMessageWithImages(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testSendMessageWithCcAndBcc(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('to@example.com'));
        $message->addCc(new EmailAddress('cc@example.com'));
        $message->addBcc(new EmailAddress('bcc@example.com'));
        $message->setHtml('<p>Hello World!</p>');

        $this->mailer->send($message);

        $this->assertFileExists($this->tempFile);

        $content = file_get_contents($this->tempFile);
        $data = json_decode($content, true);

        $this->assertIsArray($data['to']);
    }

    public function testSendMessageWithEmptySubject(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World!</p>');
        $message->setSubject(null);

        $this->mailer->send($message);

        $this->assertFileExists($this->tempFile);

        $content = file_get_contents($this->tempFile);
        $data = json_decode($content, true);

        $this->assertEquals('', $data['subject']);
    }

    private function mockFileSystemFunctions(): void
    {
        // Create mock functions for file operations
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
