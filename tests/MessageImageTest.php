<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Exception\InvalidImageException;
use GryfOSS\Mailer\Exception\NotUniqueEmbedNameException;

class MessageImageTest extends TestCase
{
    private Message $message;
    private string $tempImageFile;

    protected function setUp(): void
    {
        $this->message = new Message();

        // Create a temporary file with JPEG signature to fool MimeTypes
        $this->tempImageFile = tempnam(sys_get_temp_dir(), 'test_image_') . '.jpg';

        // JPEG file signature (magic bytes)
        $jpegHeader = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00H\x00H\x00\x00\xFF\xDB\x00C\x00";
        file_put_contents($this->tempImageFile, $jpegHeader . str_repeat("\x00", 1000));
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempImageFile)) {
            unlink($this->tempImageFile);
        }
    }

    public function testAddImageWithValidJpeg(): void
    {
        $attachment = new Attachment($this->tempImageFile, 'photo.jpg');

        $result = $this->message->addImage($attachment);

        $this->assertSame($this->message, $result);
        $this->assertTrue($this->message->hasImageKey('photo.jpg'));
        $this->assertTrue($this->message->hasImage($attachment));
        $this->assertIsArray($this->message->getImages());
        $this->assertArrayHasKey('photo.jpg', $this->message->getImages());
    }

    public function testAddImageWithoutName(): void
    {
        $attachment = new Attachment($this->tempImageFile);

        $this->message->addImage($attachment);

        $expectedName = basename($this->tempImageFile);
        $this->assertTrue($this->message->hasImageKey($expectedName));
    }

    public function testAddImageThrowsExceptionForNonImage(): void
    {
        $this->expectException(InvalidImageException::class);
        $this->expectExceptionMessage('is not an image');

        $tempTextFile = tempnam(sys_get_temp_dir(), 'test_text_');
        file_put_contents($tempTextFile, 'This is not an image');

        $attachment = new Attachment($tempTextFile, 'document.txt');

        try {
            $this->message->addImage($attachment);
        } finally {
            unlink($tempTextFile);
        }
    }

    public function testAddImageThrowsExceptionForDuplicateName(): void
    {
        $this->expectException(NotUniqueEmbedNameException::class);
        $this->expectExceptionMessage('must be unique');

        $attachment1 = new Attachment($this->tempImageFile, 'photo.jpg');
        $attachment2 = new Attachment($this->tempImageFile, 'photo.jpg');

        $this->message->addImage($attachment1);
        $this->message->addImage($attachment2);
    }

    public function testRemoveImageByKey(): void
    {
        $attachment = new Attachment($this->tempImageFile, 'photo.jpg');
        $this->message->addImage($attachment);

        $this->assertTrue($this->message->hasImageKey('photo.jpg'));

        $result = $this->message->removeImageByKey('photo.jpg');

        $this->assertSame($this->message, $result);
        $this->assertFalse($this->message->hasImageKey('photo.jpg'));
    }

    public function testRemoveImageByKeyWithEmptyImages(): void
    {
        $result = $this->message->removeImageByKey('nonexistent');

        $this->assertSame($this->message, $result);
    }

    public function testRemoveImageByKeyWithNonexistentKey(): void
    {
        $attachment = new Attachment($this->tempImageFile, 'photo.jpg');
        $this->message->addImage($attachment);

        $result = $this->message->removeImageByKey('nonexistent');

        $this->assertSame($this->message, $result);
        $this->assertTrue($this->message->hasImageKey('photo.jpg'));
    }

    public function testRemoveImage(): void
    {
        $attachment = new Attachment($this->tempImageFile, 'photo.jpg');
        $this->message->addImage($attachment);

        $this->assertTrue($this->message->hasImage($attachment));

        $this->message->removeImage($attachment);

        $this->assertFalse($this->message->hasImage($attachment));
    }

    public function testHasImageReturnsFalseForEmptyImages(): void
    {
        $attachment = new Attachment($this->tempImageFile, 'photo.jpg');

        $this->assertFalse($this->message->hasImage($attachment));
    }
}
