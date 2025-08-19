<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;

class MessageEdgeCasesTest extends TestCase
{
    public function testRemoveImageFromEmptyCollection(): void
    {
        $message = new Message();
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test');

        $attachment = new Attachment($tempFile, 'test.jpg');

        // Try to remove from empty collection - should not error
        $message->removeImage($attachment);

        $this->assertNull($message->getImages());

        unlink($tempFile);
    }

    public function testRemoveAddressFromNullCollection(): void
    {
        $message = new Message();
        $email = new EmailAddress('test@example.com');

        // Test remove operations on null collections
        $result1 = $message->removeTo($email);
        $result2 = $message->removeCc($email);
        $result3 = $message->removeBcc($email);

        $this->assertSame($message, $result1);
        $this->assertSame($message, $result2);
        $this->assertSame($message, $result3);
    }

    public function testHasAddressInNullCollection(): void
    {
        $message = new Message();
        $email = new EmailAddress('test@example.com');

        // Test has operations on null collections
        $this->assertFalse($message->hasTo($email));
        $this->assertFalse($message->hasCc($email));
        $this->assertFalse($message->hasBcc($email));
    }

    public function testAddMultipleIdenticalAddresses(): void
    {
        $message = new Message();
        $email = new EmailAddress('test@example.com');

        // Add same email multiple times - should only store once due to key usage
        $message->addTo($email);
        $message->addTo($email);
        $message->addTo($email);

        $toRecipients = $message->getTo();
        $this->assertCount(1, $toRecipients);
        $this->assertTrue($message->hasTo($email));
    }

    public function testRemoveAttachmentMultipleTimes(): void
    {
        $message = new Message();
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'test.txt');

        // Add same attachment multiple times
        $message->addAttachment($attachment);
        $message->addAttachment($attachment);
        $message->addAttachment($attachment);

        $this->assertTrue($message->hasAttachment($attachment));

        // Remove should remove all instances
        $message->removeAttachemnt($attachment);

        $this->assertFalse($message->hasAttachment($attachment));

        unlink($tempFile);
    }

    public function testRemoveAttachmentFromEmptyArray(): void
    {
        $message = new Message();
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test content');

        $attachment = new Attachment($tempFile, 'test.txt');

        // Try to remove from empty collection
        $result = $message->removeAttachemnt($attachment);

        $this->assertSame($message, $result);

        unlink($tempFile);
    }

    public function testImageNameFallback(): void
    {
        $message = new Message();

        // Create a real JPEG file for this test
        $tempImageFile = tempnam(sys_get_temp_dir(), 'test_image_') . '.jpg';
        $jpegHeader = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00H\x00H\x00\x00\xFF\xDB\x00C\x00";
        file_put_contents($tempImageFile, $jpegHeader . str_repeat("\x00", 1000));

        // Test with attachment without explicit name - should use filename
        $attachment = new Attachment($tempImageFile); // No name provided

        $message->addImage($attachment);

        $expectedName = basename($tempImageFile);
        $this->assertTrue($message->hasImageKey($expectedName));

        unlink($tempImageFile);
    }

    public function testEmailAddressToStringInCollection(): void
    {
        $message = new Message();
        $email1 = new EmailAddress('test1@example.com');
        $email2 = new EmailAddress('test2@example.com');

        $message->addTo($email1);
        $message->addTo($email2);

        $recipients = $message->getTo();

        // Test that string keys are used correctly
        $this->assertArrayHasKey('test1@example.com', $recipients);
        $this->assertArrayHasKey('test2@example.com', $recipients);
        $this->assertCount(2, $recipients);
    }
}
