<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailerInterface;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\FakeFileSmartMailer;
use GryfOSS\Mailer\Dsn\DsnInterface;

class SmartMailerInterfaceTest extends TestCase
{
    public function testSmartMailerImplementsInterface(): void
    {
        /** @var DsnInterface $dsn */
        $dsn = $this->createMock(DsnInterface::class);
        $mailer = new SmartMailer($dsn);

        $this->assertInstanceOf(SmartMailerInterface::class, $mailer);
    }

    public function testFakeFileSmartMailerImplementsInterface(): void
    {
        $mailer = new FakeFileSmartMailer('/tmp/test');

        $this->assertInstanceOf(SmartMailerInterface::class, $mailer);
    }

    public function testInterfaceHasSendMethod(): void
    {
        $this->assertTrue(interface_exists(SmartMailerInterface::class));
        $this->assertTrue(method_exists(SmartMailerInterface::class, 'send'));
    }
}
