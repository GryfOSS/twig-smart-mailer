<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Smtp;
use GryfOSS\Mailer\EncryptionMethod;
use GryfOSS\Mailer\LoginMethod;

class SmtpTest extends TestCase
{
    private Smtp $smtp;

    protected function setUp(): void
    {
        $this->smtp = new Smtp();
    }

    public function testSetAndGetHost(): void
    {
        $host = 'smtp.example.com';

        $result = $this->smtp->setHost($host);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals($host, $this->smtp->getHost());
    }

    public function testSetAndGetPort(): void
    {
        $port = 587;

        $result = $this->smtp->setPort($port);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals($port, $this->smtp->getPort());
    }

    public function testSetAndGetEncryption(): void
    {
        $encryption = EncryptionMethod::STARTTLS;

        $result = $this->smtp->setEncryption($encryption);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals($encryption, $this->smtp->getEncryption());
    }

    public function testDefaultEncryption(): void
    {
        $this->assertEquals(EncryptionMethod::SSLTLS, $this->smtp->getEncryption());
    }

    public function testSetAndGetLoginMethod(): void
    {
        $loginMethod = LoginMethod::PLAIN;

        $result = $this->smtp->setLoginMethod($loginMethod);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals($loginMethod, $this->smtp->getLoginMethod());
    }

    public function testDefaultLoginMethod(): void
    {
        $this->assertEquals(LoginMethod::LOGIN, $this->smtp->getLoginMethod());
    }

    public function testSetAndGetUsername(): void
    {
        $username = 'user@example.com';

        $result = $this->smtp->setUsername($username);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals($username, $this->smtp->getUsername());
    }

    public function testSetAndGetPassword(): void
    {
        $password = 'secret123';

        $result = $this->smtp->setPassword($password);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals($password, $this->smtp->getPassword());
    }

    public function testSetNullUsername(): void
    {
        $this->smtp->setUsername('test');
        $this->smtp->setUsername(null);

        $this->assertNull($this->smtp->getUsername());
    }

    public function testSetNullPassword(): void
    {
        $this->smtp->setPassword('test');
        $this->smtp->setPassword(null);

        $this->assertNull($this->smtp->getPassword());
    }

    public function testMethodChaining(): void
    {
        $result = $this->smtp
            ->setHost('smtp.example.com')
            ->setPort(587)
            ->setUsername('user@example.com')
            ->setPassword('secret123')
            ->setEncryption(EncryptionMethod::STARTTLS)
            ->setLoginMethod(LoginMethod::PLAIN);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals('smtp.example.com', $this->smtp->getHost());
        $this->assertEquals(587, $this->smtp->getPort());
        $this->assertEquals('user@example.com', $this->smtp->getUsername());
        $this->assertEquals('secret123', $this->smtp->getPassword());
        $this->assertEquals(EncryptionMethod::STARTTLS, $this->smtp->getEncryption());
        $this->assertEquals(LoginMethod::PLAIN, $this->smtp->getLoginMethod());
    }

    public function testDefaultValues(): void
    {
        $smtp = new Smtp();

        $this->assertEquals(EncryptionMethod::SSLTLS, $smtp->getEncryption());
        $this->assertEquals(LoginMethod::LOGIN, $smtp->getLoginMethod());
        $this->assertNull($smtp->getUsername());
        $this->assertNull($smtp->getPassword());
    }
}
