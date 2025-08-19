<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer\Exception;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Exception\InvalidEmailMessageException;
use GryfOSS\Mailer\Exception\SendException;
use GryfOSS\Mailer\Exception\SmartMailerException;

class EmptyExceptionsTest extends TestCase
{
    public function testInvalidEmailMessageExceptionCanBeInstantiated(): void
    {
        $exception = new InvalidEmailMessageException('Test message');

        $this->assertInstanceOf(InvalidEmailMessageException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testSendExceptionCanBeInstantiated(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new SendException('Test message', 123, $previous);

        $this->assertInstanceOf(SendException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testSmartMailerExceptionIsAbstract(): void
    {
        $reflection = new \ReflectionClass(SmartMailerException::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function testExceptionHierarchy(): void
    {
        $this->assertTrue(is_subclass_of(InvalidEmailMessageException::class, SmartMailerException::class));
        $this->assertTrue(is_subclass_of(SendException::class, SmartMailerException::class));
    }
}
