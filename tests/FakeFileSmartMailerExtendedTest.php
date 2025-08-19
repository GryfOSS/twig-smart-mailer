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

class FakeFileSmartMailerExtendedTest extends TestCase
{
    public function testSendWithComplexTwigTemplate(): void
    {
        $templates = [
            'header.twig' => '<header>{{ title }}</header>',
            'footer.twig' => '<footer>{{ year }}</footer>',
            'main.twig' => '{{ include("header.twig") }}<main>Hello {{ name }}!</main>{{ include("footer.twig") }}'
        ];

        $twig = new Environment(new ArrayLoader($templates));
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile, $twig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', 'Sender Name'));
        $message->addTo(new EmailAddress('recipient@example.com', 'Recipient Name'));
        $message->setSubject('Complex Template Test');
        $message->setHtml('{{ include("main.twig") }}');
        $message->setContext([
            'title' => 'Welcome',
            'name' => 'John',
            'year' => '2024'
        ]);

        $mailer->send($message);

        $this->assertFileExists($tempFile);

        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);

        $this->assertStringContainsString('Welcome', $data['html']);
        $this->assertStringContainsString('John', $data['html']);
        $this->assertStringContainsString('2024', $data['html']);

        unlink($tempFile);
    }

    public function testSendWithMultipleAttachments(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile);

        $tempAttachment1 = tempnam(sys_get_temp_dir(), 'attachment1_');
        $tempAttachment2 = tempnam(sys_get_temp_dir(), 'attachment2_');
        file_put_contents($tempAttachment1, 'Content 1');
        file_put_contents($tempAttachment2, 'Content 2');

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Multiple attachments test</p>');

        $attachment1 = new Attachment($tempAttachment1, 'file1.txt');
        $attachment2 = new Attachment($tempAttachment2, 'file2.txt');

        $message->addAttachment($attachment1);
        $message->addAttachment($attachment2);

        $mailer->send($message);

        $this->assertFileExists($tempFile);

        unlink($tempFile);
        unlink($tempAttachment1);
        unlink($tempAttachment2);
    }

    public function testSendWithEmptyContext(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>No variables here</p>');
        $message->setText('No variables here');
        $message->setContext([]); // Empty context

        $mailer->send($message);

        $this->assertFileExists($tempFile);

        unlink($tempFile);
    }

    public function testSendWithNullContext(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>No variables here</p>');
        $message->setText('No variables here');
        $message->setContext(null); // Null context

        $mailer->send($message);

        $this->assertFileExists($tempFile);

        unlink($tempFile);
    }

    public function testSendOnlyTextMessage(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_email_');
        $mailer = new FakeFileSmartMailer($tempFile);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setText('Plain text only {{ name }}');
        $message->setContext(['name' => 'World']);

        $mailer->send($message);

        $this->assertFileExists($tempFile);

        $content = file_get_contents($tempFile);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('html', $data);

        unlink($tempFile);
    }

    public function testGetAndSetOutputPath(): void
    {
        $initialPath = '/initial/path';
        $newPath = '/new/path';

        $mailer = new FakeFileSmartMailer($initialPath);

        $this->assertEquals($initialPath, $mailer->getOutputPath());

        $result = $mailer->setOutputPath($newPath);

        $this->assertSame($mailer, $result);
        $this->assertEquals($newPath, $mailer->getOutputPath());
    }
}
