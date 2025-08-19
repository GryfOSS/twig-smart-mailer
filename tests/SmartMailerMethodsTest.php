<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Dsn\DsnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class SmartMailerMethodsTest extends TestCase
{
    public function testListAllMethods(): void
    {
        $reflection = new ReflectionClass(SmartMailer::class);
        $methods = $reflection->getMethods();

        $methodNames = [];
        foreach ($methods as $method) {
            if ($method->getDeclaringClass()->getName() === SmartMailer::class) {
                $methodNames[] = $method->getName();
            }
        }

        // This will help us see what methods exist
        echo "SmartMailer methods: " . implode(', ', $methodNames) . "\n";

        $this->assertGreaterThan(0, count($methodNames));
    }
}
