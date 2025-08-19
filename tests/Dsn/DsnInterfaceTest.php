<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer\Dsn;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Dsn\DsnInterface;
use GryfOSS\Mailer\Dsn\Smtp;
use GryfOSS\Mailer\Dsn\Gmail;

class DsnInterfaceTest extends TestCase
{
    public function testSmtpImplementsDsnInterface(): void
    {
        $smtp = new Smtp();

        $this->assertInstanceOf(DsnInterface::class, $smtp);
        $this->assertInstanceOf(\Stringable::class, $smtp);
    }

    public function testGmailImplementsDsnInterface(): void
    {
        $gmail = new Gmail();

        $this->assertInstanceOf(DsnInterface::class, $gmail);
        $this->assertInstanceOf(\Stringable::class, $gmail);
    }

    public function testDsnInterfaceExtendsStringable(): void
    {
        $this->assertTrue(interface_exists(DsnInterface::class));

        $reflection = new \ReflectionClass(DsnInterface::class);
        $interfaces = $reflection->getInterfaceNames();

        $this->assertContains(\Stringable::class, $interfaces);
    }

    public function testDsnInterfaceHasToStringMethod(): void
    {
        $this->assertTrue(method_exists(DsnInterface::class, '__toString'));
    }
}
