<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer\Dsn;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Dsn\Gmail;

class GmailTest extends TestCase
{
    private Gmail $gmail;

    protected function setUp(): void
    {
        $this->gmail = new Gmail();
    }

    public function testSetAndGetUsername(): void
    {
        $username = 'user@gmail.com';

        $result = $this->gmail->setUsername($username);

        $this->assertSame($this->gmail, $result);
        $this->assertEquals($username, $this->gmail->getUsername());
    }

    public function testSetAndGetPassword(): void
    {
        $password = 'secret123';

        $result = $this->gmail->setPassword($password);

        $this->assertSame($this->gmail, $result);
        $this->assertEquals($password, $this->gmail->getPassword());
    }

    public function testSetNullUsername(): void
    {
        $this->gmail->setUsername('test');
        $this->gmail->setUsername(null);

        $this->assertNull($this->gmail->getUsername());
    }

    public function testSetNullPassword(): void
    {
        $this->gmail->setPassword('test');
        $this->gmail->setPassword(null);

        $this->assertNull($this->gmail->getPassword());
    }

    public function testDefaultValues(): void
    {
        $gmail = new Gmail();

        // Properties are typed as ?string so they start as null, not uninitialized
        $this->assertNull($gmail->getUsername());
        $this->assertNull($gmail->getPassword());
    }

    public function testToString(): void
    {
        $this->gmail
            ->setUsername('user@gmail.com')
            ->setPassword('secret123');

        $expected = 'gmail+smtp://user@gmail.com:secret123@default';

        $this->assertEquals($expected, (string) $this->gmail);
    }

    public function testToStringWithNullCredentials(): void
    {
        $this->gmail
            ->setUsername(null)
            ->setPassword(null);

        $expected = 'gmail+smtp://:@default';

        $this->assertEquals($expected, (string) $this->gmail);
    }

    public function testMethodChaining(): void
    {
        $result = $this->gmail
            ->setUsername('user@gmail.com')
            ->setPassword('secret123');

        $this->assertSame($this->gmail, $result);
        $this->assertEquals('user@gmail.com', $this->gmail->getUsername());
        $this->assertEquals('secret123', $this->gmail->getPassword());
    }
}
