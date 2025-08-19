<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\LoginMethod;

class LoginMethodTest extends TestCase
{
    public function testAuto(): void
    {
        $method = LoginMethod::AUTO;

        $this->assertEquals('', $method->value);
    }

    public function testPlain(): void
    {
        $method = LoginMethod::PLAIN;

        $this->assertEquals('PLAIN', $method->value);
    }

    public function testLogin(): void
    {
        $method = LoginMethod::LOGIN;

        $this->assertEquals('LOGIN', $method->value);
    }

    public function testCramMd5(): void
    {
        $method = LoginMethod::CRAM_MD5;

        $this->assertEquals('CRAM-MD5', $method->value);
    }

    public function testDigestMd5(): void
    {
        $method = LoginMethod::DIGEST_MD5;

        $this->assertEquals('DIGEST-MD5', $method->value);
    }

    public function testAllCases(): void
    {
        $cases = LoginMethod::cases();

        $this->assertCount(5, $cases);
        $this->assertContains(LoginMethod::AUTO, $cases);
        $this->assertContains(LoginMethod::PLAIN, $cases);
        $this->assertContains(LoginMethod::LOGIN, $cases);
        $this->assertContains(LoginMethod::CRAM_MD5, $cases);
        $this->assertContains(LoginMethod::DIGEST_MD5, $cases);
    }

    public function testFromValue(): void
    {
        $this->assertEquals(LoginMethod::AUTO, LoginMethod::from(''));
        $this->assertEquals(LoginMethod::PLAIN, LoginMethod::from('PLAIN'));
        $this->assertEquals(LoginMethod::LOGIN, LoginMethod::from('LOGIN'));
        $this->assertEquals(LoginMethod::CRAM_MD5, LoginMethod::from('CRAM-MD5'));
        $this->assertEquals(LoginMethod::DIGEST_MD5, LoginMethod::from('DIGEST-MD5'));
    }

    public function testTryFromValue(): void
    {
        $this->assertEquals(LoginMethod::AUTO, LoginMethod::tryFrom(''));
        $this->assertEquals(LoginMethod::PLAIN, LoginMethod::tryFrom('PLAIN'));
        $this->assertEquals(LoginMethod::LOGIN, LoginMethod::tryFrom('LOGIN'));
        $this->assertEquals(LoginMethod::CRAM_MD5, LoginMethod::tryFrom('CRAM-MD5'));
        $this->assertEquals(LoginMethod::DIGEST_MD5, LoginMethod::tryFrom('DIGEST-MD5'));
        $this->assertNull(LoginMethod::tryFrom('INVALID'));
    }
}
