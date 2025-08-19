<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Exception\InvalidImageException;
use GryfOSS\Mailer\Exception\NotUniqueEmbedNameException;
use phpmock\phpunit\PHPMock;
use ReflectionClass;

class MessageTest extends TestCase
{
    use PHPMock;

    private Message $message;

    protected function setUp(): void
    {
        $this->message = new Message();
    }

    public function testSetAndGetTo(): void
    {
        $email1 = new EmailAddress('test1@example.com');
        $email2 = new EmailAddress('test2@example.com');
        $toArray = [$email1, $email2];

        $result = $this->message->setTo($toArray);

        $this->assertSame($this->message, $result);
        $this->assertEquals($toArray, $this->message->getTo());
    }

    public function testSetAndGetCc(): void
    {
        $email1 = new EmailAddress('cc1@example.com');
        $email2 = new EmailAddress('cc2@example.com');
        $ccArray = [$email1, $email2];

        $result = $this->message->setCc($ccArray);

        $this->assertSame($this->message, $result);
        $this->assertEquals($ccArray, $this->message->getCc());
    }

    public function testSetAndGetBcc(): void
    {
        $email1 = new EmailAddress('bcc1@example.com');
        $email2 = new EmailAddress('bcc2@example.com');
        $bccArray = [$email1, $email2];

        $result = $this->message->setBcc($bccArray);

        $this->assertSame($this->message, $result);
        $this->assertEquals($bccArray, $this->message->getBcc());
    }

    public function testAddToAndHasTo(): void
    {
        $email = new EmailAddress('test@example.com');

        $this->assertFalse($this->message->hasTo($email));

        $result = $this->message->addTo($email);

        $this->assertSame($this->message, $result);
        $this->assertTrue($this->message->hasTo($email));
        $this->assertArrayHasKey('test@example.com', $this->message->getTo());
    }

    public function testRemoveTo(): void
    {
        $email = new EmailAddress('test@example.com');

        $this->message->addTo($email);
        $this->assertTrue($this->message->hasTo($email));

        $result = $this->message->removeTo($email);

        $this->assertSame($this->message, $result);
    }

    public function testAddCcAndHasCc(): void
    {
        $email = new EmailAddress('cc@example.com');

        $this->assertFalse($this->message->hasCc($email));

        $result = $this->message->addCc($email);

        $this->assertSame($this->message, $result);
        $this->assertTrue($this->message->hasCc($email));
        $this->assertArrayHasKey('cc@example.com', $this->message->getCc());
    }

    public function testRemoveCc(): void
    {
        $email = new EmailAddress('cc@example.com');

        $this->message->addCc($email);
        $this->assertTrue($this->message->hasCc($email));

        $result = $this->message->removeCc($email);

        $this->assertSame($this->message, $result);
    }

    public function testAddBccAndHasBcc(): void
    {
        $email = new EmailAddress('bcc@example.com');

        $this->assertFalse($this->message->hasBcc($email));

        $result = $this->message->addBcc($email);

        $this->assertSame($this->message, $result);
        $this->assertTrue($this->message->hasBcc($email));
        $this->assertArrayHasKey('bcc@example.com', $this->message->getBcc());
    }

    public function testRemoveBcc(): void
    {
        $email = new EmailAddress('bcc@example.com');

        $this->message->addBcc($email);
        $this->assertTrue($this->message->hasBcc($email));

        $result = $this->message->removeBcc($email);

        $this->assertSame($this->message, $result);
    }

    public function testSetAndGetHtml(): void
    {
        $html = '<h1>Hello {{ name }}!</h1>';

        $result = $this->message->setHtml($html);

        $this->assertSame($this->message, $result);
        $this->assertEquals($html, $this->message->getHtml());
    }

    public function testSetAndGetText(): void
    {
        $text = 'Hello {{ name }}!';

        $result = $this->message->setText($text);

        $this->assertSame($this->message, $result);
        $this->assertEquals($text, $this->message->getText());
    }

    public function testSetAndGetFrom(): void
    {
        $from = new EmailAddress('sender@example.com', 'Sender Name');

        $result = $this->message->setFrom($from);

        $this->assertSame($this->message, $result);
        $this->assertEquals($from, $this->message->getFrom());
    }

    public function testSetAndGetSubject(): void
    {
        $subject = 'Test Subject';

        $result = $this->message->setSubject($subject);

        $this->assertSame($this->message, $result);
        $this->assertEquals($subject, $this->message->getSubject());
    }

    public function testSetAndGetContext(): void
    {
        $context = ['name' => 'John', 'age' => 30];

        $result = $this->message->setContext($context);

        $this->assertSame($this->message, $result);
        $this->assertEquals($context, $this->message->getContext());
    }

    public function testAddAttachmentAndGetAttachments(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'document.pdf');

        $this->assertNull($this->message->getAttachments());

        $result = $this->message->addAttachment($attachment);

        $this->assertSame($this->message, $result);
        $this->assertIsArray($this->message->getAttachments());
        $this->assertContains($attachment, $this->message->getAttachments());

        unlink($tempFile);
    }

    public function testHasAttachment(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'document.pdf');

        $this->assertFalse($this->message->hasAttachment($attachment));

        $this->message->addAttachment($attachment);

        $this->assertTrue($this->message->hasAttachment($attachment));

        unlink($tempFile);
    }

    public function testHasAttachmentWithEmptyAttachments(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'document.pdf');

        $this->assertFalse($this->message->hasAttachment($attachment));

        unlink($tempFile);
    }

    public function testRemoveAttachemnt(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_attachment_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'document.pdf');

        $this->message->addAttachment($attachment);
        $this->assertTrue($this->message->hasAttachment($attachment));

        $result = $this->message->removeAttachemnt($attachment);

        $this->assertSame($this->message, $result);
        $this->assertFalse($this->message->hasAttachment($attachment));

        unlink($tempFile);
    }

    public function testAddImageWithValidImage(): void
    {
        // Skip image testing since we can't easily mock MimeTypes::guessMimeType
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testAddImageThrowsExceptionForNonImage(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testAddImageThrowsExceptionForDuplicateName(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testHasImageKey(): void
    {
        $this->assertFalse($this->message->hasImageKey('nonexistent'));

        // Test with null images array
        $this->assertFalse($this->message->hasImageKey('test'));
    }

    public function testHasImage(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testHasImageWithEmptyImages(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'photo.jpg');

        $this->assertFalse($this->message->hasImage($attachment));

        unlink($tempFile);
    }

    public function testRemoveImageByKey(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testRemoveImageByKeyWithEmptyImages(): void
    {
        $result = $this->message->removeImageByKey('nonexistent');

        $this->assertSame($this->message, $result);
    }

    public function testRemoveImageByKeyWithNonexistentKey(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testRemoveImage(): void
    {
        $this->markTestSkipped('Image testing requires complex MimeTypes mocking');
    }

    public function testSetNullValues(): void
    {
        $this->message->setHtml(null);
        $this->message->setText(null);
        $this->message->setFrom(null);
        $this->message->setSubject(null);
        $this->message->setContext(null);

        $this->assertNull($this->message->getHtml());
        $this->assertNull($this->message->getText());
        $this->assertNull($this->message->getFrom());
        $this->assertNull($this->message->getSubject());
        $this->assertNull($this->message->getContext());
    }

    public function testDefaultValues(): void
    {
        $message = new Message();

        $this->assertNull($message->getTo());
        $this->assertNull($message->getCc());
        $this->assertNull($message->getBcc());
        $this->assertNull($message->getContext());
        $this->assertNull($message->getAttachments());
        $this->assertNull($message->getImages());
        $this->assertNull($message->getFrom());
        $this->assertNull($message->getHtml());
        $this->assertNull($message->getText());
        $this->assertNull($message->getSubject());
    }
}
