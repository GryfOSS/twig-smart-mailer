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

class FakeFileSmartMailerCompleteTest extends TestCase
{
    public function testSendWithImages(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $imagePath = __DIR__ . '/Assets/icon.png';
        
        $mailer = new FakeFileSmartMailer($tempFile);
        
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Email with embedded image</p>');
        
        $imageAttachment = new Attachment($imagePath, 'embedded-image.png');
        $message->addImage($imageAttachment);
        
        $mailer->send($message);
        
        $this->assertFileExists($tempFile);
        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);
        
        $this->assertArrayHasKey('html', $data);
        $this->assertStringContainsString('Email with embedded image', $data['html']);
        
        unlink($tempFile);
    }

    public function testGetSetOutputPath(): void
    {
        $originalPath = tempnam(sys_get_temp_dir(), 'test_original_');
        $newPath = tempnam(sys_get_temp_dir(), 'test_new_');

        $mailer = new FakeFileSmartMailer($originalPath);

        $this->assertEquals($originalPath, $mailer->getOutputPath());

        $result = $mailer->setOutputPath($newPath);

        $this->assertSame($mailer, $result);
        $this->assertEquals($newPath, $mailer->getOutputPath());

        unlink($originalPath);
        unlink($newPath);
    }

    public function testSendCreatesDummyTwigWhenNoneProvided(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $mailer->send($message);

        $this->assertFileExists($tempFile);
        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('html', $data);

        unlink($tempFile);
    }

    public function testSendWithTwigEnvironment(): void
    {
        $templates = [
            'test.html' => '<h1>Hello {{ name }}!</h1>'
        ];

        $twig = new Environment(new ArrayLoader($templates));
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile, $twig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('{{ include("test.html") }}');
        $message->setContext(['name' => 'World']);

        $mailer->send($message);

        $this->assertFileExists($tempFile);
        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);

        $this->assertStringContainsString('Hello World!', $data['html']);

        unlink($tempFile);
    }

    public function testSendWritesCompleteEmailData(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $tempAttachment = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempAttachment, 'test content');

        $mailer = new FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', 'Sender Name'));
        $message->addTo(new EmailAddress('to@example.com', 'To Name'));
        $message->addCc(new EmailAddress('cc@example.com', 'CC Name'));
        $message->addBcc(new EmailAddress('bcc@example.com', 'BCC Name'));
        $message->setSubject('Test Subject');
        $message->setHtml('<p>HTML Body</p>');
        $message->setText('Text Body');

        $attachment = new Attachment($tempAttachment, 'test.txt');
        $message->addAttachment($attachment);

        $mailer->send($message);

        $this->assertFileExists($tempFile);
        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);
        $this->assertArrayHasKey('subject', $data);
        $this->assertArrayHasKey('html', $data);
        $this->assertEquals('Test Subject', $data['subject']);
        $this->assertStringContainsString('HTML Body', $data['html']);

        unlink($tempFile);
        unlink($tempAttachment);
    }
}
