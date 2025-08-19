<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer\Exception;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Exception\InvalidAttachmentException;
use GryfOSS\Mailer\Exception\InvalidEmailAddressException;
use GryfOSS\Mailer\Exception\InvalidEmailMessageException;
use GryfOSS\Mailer\Exception\InvalidImageException;
use GryfOSS\Mailer\Exception\NotUniqueEmbedNameException;
use GryfOSS\Mailer\Exception\SendException;
use GryfOSS\Mailer\Exception\SmartMailerException;

class ExceptionsTest extends TestCase
{
    public function testInvalidAttachmentException(): void
    {
        $path = '/path/to/invalid/file.pdf';
        $exception = new InvalidAttachmentException($path);

        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertEquals('File `/path/to/invalid/file.pdf` must exist and be readable.', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testInvalidAttachmentExceptionWithDifferentPath(): void
    {
        $path = '/another/invalid/file.txt';
        $exception = new InvalidAttachmentException($path);

        $this->assertEquals('File `/another/invalid/file.txt` must exist and be readable.', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testInvalidEmailAddressException(): void
    {
        $email = 'invalid-email';
        $exception = new InvalidEmailAddressException($email);

        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertEquals('Provided value of: `invalid-email` is not a valid e-mail address.', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testInvalidEmailMessageException(): void
    {
        $message = 'Invalid email message';
        $exception = new InvalidEmailMessageException($message);

        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testInvalidImageException(): void
    {
        $path = '/path/to/document.pdf';
        $exception = new InvalidImageException($path);

        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertEquals('File `/path/to/document.pdf` is not an image.', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testNotUniqueEmbedNameException(): void
    {
        $name = 'duplicate-name.jpg';
        $exception = new NotUniqueEmbedNameException($name);

        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertEquals('Embedded resource\'s name must be unique, use a different file or set the name explicitly. Used: `duplicate-name.jpg`.', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testSendException(): void
    {
        $message = 'Failed to send email';
        $exception = new SendException($message);

        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }

    public function testSendExceptionWithPrevious(): void
    {
        $previousException = new \Exception('Original error');
        $message = 'Failed to send email';
        $code = 500;

        $exception = new SendException($message, $code, $previousException);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testAllExceptionsInheritFromSmartMailerException(): void
    {
        $exceptions = [
            [InvalidEmailAddressException::class, 'test@'],
            [InvalidImageException::class, '/path/to/file'],
            [NotUniqueEmbedNameException::class, 'name'],
            [InvalidAttachmentException::class, '/path/to/file'],
        ];

        foreach ($exceptions as [$exceptionClass, $param]) {
            $exception = new $exceptionClass($param);
            $this->assertInstanceOf(SmartMailerException::class, $exception);
        }

        // Test SendException and InvalidEmailMessageException separately as they use regular constructors
        $sendException = new SendException('Test message');
        $messageException = new InvalidEmailMessageException('Test message');

        $this->assertInstanceOf(SmartMailerException::class, $sendException);
        $this->assertInstanceOf(SmartMailerException::class, $messageException);
    }

    public function testExceptionHierarchy(): void
    {
        $exception = new InvalidAttachmentException('/test/path');

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(SmartMailerException::class, $exception);
        $this->assertInstanceOf(InvalidAttachmentException::class, $exception);
    }
}
