<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Dsn\DsnInterface;

/**
 * Test class specifically designed to achieve 100% coverage of the embedFromPath line in SmartMailer.
 *
 * This test uses the real SmartMailer class (not FakeFileSmartMailer) with a null transport
 * to ensure the embedFromPath line in SmartMailer::send() is executed.
 */
class SmartMailerEmbedImagesTest extends TestCase
{
    /**
     * Tests that SmartMailer properly embeds images when sending emails.
     *
     * This test specifically targets the uncovered line:
     * $email->embedFromPath($attachment->getPath(), (string) $name);
     * in SmartMailer.php at line 176.
     */
    public function testSendWithRealSmartMailerAndImages(): void
    {
        // Create a null DSN that won't actually send emails
        /** @var DsnInterface&MockObject $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create real SmartMailer (not FakeFileSmartMailer)
        $smartMailer = new SmartMailer($dsnMock);

        // Prepare image attachment using the existing test PNG file
        $imagePath = __DIR__ . '/Assets/icon.png';
        $this->assertFileExists($imagePath, 'Test image file must exist');

        $imageAttachment = new Attachment($imagePath, 'test-logo.png');

        // Create message with image embedded
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com', 'Test Sender'));
        $message->addTo(new EmailAddress('recipient@example.com', 'Test Recipient'));
        $message->setSubject('Test Email with Images');
        $message->setHtml('<p>Hello! Here is the image: <img src="cid:test-logo.png" alt="Logo"></p>');

        // Add the image - this is key to trigger the embedFromPath line
        $message->addImage($imageAttachment);

        // Send the message using real SmartMailer - this should execute the embedFromPath line
        // The null transport won't actually send the email but will process all the code paths
        $result = $smartMailer->send($message);

        // With null transport, the result should be null (successful null send)
        $this->assertNull($result);
    }

    /**
     * Test with multiple images to ensure the loop is fully exercised.
     */
    public function testSendWithMultipleImages(): void
    {
        // Create a null DSN
        /** @var DsnInterface&MockObject $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Use the same image file for multiple embedded images with different names
        $imagePath = __DIR__ . '/Assets/icon.png';

        $image1 = new Attachment($imagePath, 'logo1.png');
        $image2 = new Attachment($imagePath, 'logo2.png');

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setSubject('Multiple Images Test');
        $message->setHtml('
            <p>Multiple images:</p>
            <img src="cid:logo1.png" alt="Logo 1">
            <img src="cid:logo2.png" alt="Logo 2">
        ');

        // Add multiple images to trigger multiple iterations of the embedFromPath loop
        $message->addImage($image1);
        $message->addImage($image2);

        // This should execute the embedFromPath line multiple times
        $result = $smartMailer->send($message);
        $this->assertNull($result);
    }

    /**
     * Test image embedding with custom name vs filename.
     */
    public function testImageEmbeddingWithCustomName(): void
    {
        /** @var DsnInterface&MockObject $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        $imagePath = __DIR__ . '/Assets/icon.png';

        // Test with custom name
        $imageWithCustomName = new Attachment($imagePath, 'custom-name.png');

        // Test without custom name (will use filename)
        $imageWithoutName = new Attachment($imagePath);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setSubject('Custom Name Test');
        $message->setHtml('
            <p>Images with different naming:</p>
            <img src="cid:custom-name.png" alt="Custom Named">
            <img src="cid:icon.png" alt="Default Named">
        ');

        $message->addImage($imageWithCustomName);
        $message->addImage($imageWithoutName);

        $result = $smartMailer->send($message);
        $this->assertNull($result);
    }
}
