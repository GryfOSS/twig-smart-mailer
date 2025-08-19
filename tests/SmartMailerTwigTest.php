<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Dsn\DsnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Twig\Environment;

class SmartMailerTwigTest extends TestCase
{
    public function testCreateDummyTwigMethodIsCalled(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create SmartMailer without Twig environment, which should trigger createDummyTwig
        $smartMailer = new SmartMailer($dsnMock);

        // Use reflection to access the protected twig property
        $reflection = new ReflectionClass($smartMailer);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);

        $twigInstance = $twigProperty->getValue($smartMailer);

        // Verify that a Twig environment was created
        $this->assertInstanceOf(Environment::class, $twigInstance);

        // Test that the createDummyTwig was actually called by sending a message
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Testing {{ test }}</p>');
        $message->setContext(['test' => 'dummy twig']);

        // This should not throw an exception and should use the dummy Twig
        $smartMailer->send($message);

        // If we reach here, the test passed
        $this->assertTrue(true);
    }

    public function testCreateDummyTwigMethodDirectAccess(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Use reflection to access the protected createDummyTwig method
        $reflection = new ReflectionClass($smartMailer);
        $createDummyTwigMethod = $reflection->getMethod('createDummyTwig');
        $createDummyTwigMethod->setAccessible(true);

        $twigInstance = $createDummyTwigMethod->invoke($smartMailer);

        $this->assertInstanceOf(Environment::class, $twigInstance);

        // Test that the environment can render a simple template
        $template = $twigInstance->createTemplate('Hello {{ name }}!');
        $rendered = $template->render(['name' => 'World']);

        $this->assertEquals('Hello World!', $rendered);
    }

    public function testSendMethodWithCustomTwigEnvironment(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create a custom Twig environment
        $customTwig = new Environment(new \Twig\Loader\ArrayLoader([
            'custom.html' => 'Custom {{ message }}!'
        ]));

        $smartMailer = new SmartMailer($dsnMock, $customTwig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('{{ include("custom.html") }}');
        $message->setContext(['message' => 'template']);

        // This should use the custom Twig environment, not createDummyTwig
        $smartMailer->send($message);

        $this->assertTrue(true);
    }
}
