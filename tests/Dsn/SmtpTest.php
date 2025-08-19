<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer\Dsn;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Dsn\Smtp;
use GryfOSS\Mailer\EncryptionMethod;
use GryfOSS\Mailer\LoginMethod;

class SmtpTest extends TestCase
{
    private Smtp $smtp;

    protected function setUp(): void
    {
        $this->smtp = new Smtp('smtp.example.com', 587);
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
        $encryption = EncryptionMethod::SSLTLS;

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

    public function testToString(): void
    {
        $this->smtp
            ->setHost('smtp.example.com')
            ->setPort(587)
            ->setUsername('user@example.com')
            ->setPassword('secret123');

        $expected = 'smtp://user@example.com:secret123@smtp.example.com:587?verify_peer=0';

        $this->assertEquals($expected, (string) $this->smtp);
    }

    public function testToStringWithNullCredentials(): void
    {
        $this->smtp
            ->setHost('smtp.example.com')
            ->setPort(587)
            ->setUsername(null)
            ->setPassword(null);

        $expected = 'smtp://:@smtp.example.com:587?verify_peer=0';

        $this->assertEquals($expected, (string) $this->smtp);
    }

    public function testMethodChaining(): void
    {
        $result = $this->smtp
            ->setHost('smtp.example.com')
            ->setPort(587)
            ->setUsername('user@example.com')
            ->setPassword('secret123')
            ->setEncryption(EncryptionMethod::SSLTLS)
            ->setLoginMethod(LoginMethod::PLAIN);

        $this->assertSame($this->smtp, $result);
        $this->assertEquals('smtp.example.com', $this->smtp->getHost());
        $this->assertEquals(587, $this->smtp->getPort());
        $this->assertEquals('user@example.com', $this->smtp->getUsername());
        $this->assertEquals('secret123', $this->smtp->getPassword());
        $this->assertEquals(EncryptionMethod::SSLTLS, $this->smtp->getEncryption());
        $this->assertEquals(LoginMethod::PLAIN, $this->smtp->getLoginMethod());
    }
}
