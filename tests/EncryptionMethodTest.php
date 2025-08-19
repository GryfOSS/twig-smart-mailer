<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\EncryptionMethod;

class EncryptionMethodTest extends TestCase
{
    public function testNoEncryption(): void
    {
        $method = EncryptionMethod::NO_ENCRYPTION;

        $this->assertEquals('', $method->value);
    }

    public function testSslTls(): void
    {
        $method = EncryptionMethod::SSLTLS;

        $this->assertEquals('SSL', $method->value);
    }

    public function testStartTls(): void
    {
        $method = EncryptionMethod::STARTTLS;

        $this->assertEquals('STARTTLS', $method->value);
    }

    public function testAllCases(): void
    {
        $cases = EncryptionMethod::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(EncryptionMethod::NO_ENCRYPTION, $cases);
        $this->assertContains(EncryptionMethod::SSLTLS, $cases);
        $this->assertContains(EncryptionMethod::STARTTLS, $cases);
    }

    public function testFromValue(): void
    {
        $this->assertEquals(EncryptionMethod::NO_ENCRYPTION, EncryptionMethod::from(''));
        $this->assertEquals(EncryptionMethod::SSLTLS, EncryptionMethod::from('SSL'));
        $this->assertEquals(EncryptionMethod::STARTTLS, EncryptionMethod::from('STARTTLS'));
    }

    public function testTryFromValue(): void
    {
        $this->assertEquals(EncryptionMethod::NO_ENCRYPTION, EncryptionMethod::tryFrom(''));
        $this->assertEquals(EncryptionMethod::SSLTLS, EncryptionMethod::tryFrom('SSL'));
        $this->assertEquals(EncryptionMethod::STARTTLS, EncryptionMethod::tryFrom('STARTTLS'));
        $this->assertNull(EncryptionMethod::tryFrom('INVALID'));
    }
}
