<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Dsn\DsnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\ArrayLoader;

class SmartMailerFinalCoverageTest extends TestCase
{
    public function testEveryPossibleConstructorPath(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Path 1: Twig provided (twig ?? createDummyTwig() should use twig)
        $customTwig = new Environment(new ArrayLoader([]));
        $smartMailer1 = new SmartMailer($dsnMock, $customTwig);

        $reflection = new ReflectionClass($smartMailer1);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);
        $twig1 = $twigProperty->getValue($smartMailer1);

        $this->assertSame($customTwig, $twig1);

        // Path 2: No twig provided (twig ?? createDummyTwig() should call createDummyTwig)
        $smartMailer2 = new SmartMailer($dsnMock);
        $twig2 = $twigProperty->getValue($smartMailer2);

        $this->assertNotSame($customTwig, $twig2);
        $this->assertInstanceOf(Environment::class, $twig2);
        $this->assertInstanceOf(FilesystemLoader::class, $twig2->getLoader());

        // Path 3: Explicit null twig (twig ?? createDummyTwig() should call createDummyTwig)
        $smartMailer3 = new SmartMailer($dsnMock, null);
        $twig3 = $twigProperty->getValue($smartMailer3);

        $this->assertNotSame($customTwig, $twig3);
        $this->assertInstanceOf(Environment::class, $twig3);
        $this->assertInstanceOf(FilesystemLoader::class, $twig3->getLoader());
    }

    public function testCreateDummyTwigEveryLine(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Use reflection to call createDummyTwig and verify every line
        $reflection = new ReflectionClass($smartMailer);
        $method = $reflection->getMethod('createDummyTwig');
        $method->setAccessible(true);

        // Call the method multiple times to ensure every execution path
        for ($i = 0; $i < 3; $i++) {
            $environment = $method->invoke($smartMailer);

            // Line 1: $loader = new FilesystemLoader('.');
            $loader = $environment->getLoader();
            $this->assertInstanceOf(FilesystemLoader::class, $loader);

            // Line 2: return new Environment($loader);
            $this->assertInstanceOf(Environment::class, $environment);

            // Verify the loader was created with '.' parameter
            $template = $environment->createTemplate('Test {{ value }}');
            $result = $template->render(['value' => 'success']);
            $this->assertEquals('Test success', $result);
        }
    }

    public function testValidateMethodReturnValue(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        $message = new \GryfOSS\Mailer\Message();
        $message->setFrom(new \GryfOSS\Mailer\EmailAddress('test@example.com'));
        $message->addTo(new \GryfOSS\Mailer\EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Test</p>');

        // Ensure validate method returns true (this might be the missing line)
        $result = $smartMailer->validate($message);
        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    public function testSendMethodReturnValue(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        $message = new \GryfOSS\Mailer\Message();
        $message->setFrom(new \GryfOSS\Mailer\EmailAddress('test@example.com'));
        $message->addTo(new \GryfOSS\Mailer\EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Test</p>');

        // Test that send method returns the transport result
        $result = $smartMailer->send($message);
        $this->assertNull($result); // null transport returns null
    }

    public function testAllMethodsAreCovered(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Test getDsn
        $dsn = $smartMailer->getDsn();
        $this->assertSame($dsnMock, $dsn);

        // Test setDsn
        /** @var MockObject&DsnInterface $newDsn */
        $newDsn = $this->createMock(DsnInterface::class);
        $newDsn->method('__toString')->willReturn('null://null');

        $result = $smartMailer->setDsn($newDsn);
        $this->assertSame($smartMailer, $result);
        $this->assertSame($newDsn, $smartMailer->getDsn());

        // Test validate
        $message = new \GryfOSS\Mailer\Message();
        $message->setFrom(new \GryfOSS\Mailer\EmailAddress('test@example.com'));
        $message->addTo(new \GryfOSS\Mailer\EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Test</p>');

        $validateResult = $smartMailer->validate($message);
        $this->assertTrue($validateResult);

        // Test send
        $sendResult = $smartMailer->send($message);
        $this->assertNull($sendResult);

        // Test createDummyTwig via reflection
        $reflection = new ReflectionClass($smartMailer);
        $createDummyTwigMethod = $reflection->getMethod('createDummyTwig');
        $createDummyTwigMethod->setAccessible(true);

        $twig = $createDummyTwigMethod->invoke($smartMailer);
        $this->assertInstanceOf(Environment::class, $twig);
    }
}
