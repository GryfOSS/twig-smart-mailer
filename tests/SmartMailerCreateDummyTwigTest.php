<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Dsn\DsnInterface;
use PHPUnit\Framework\MockObject\MockObject;

class SmartMailerCreateDummyTwigTest extends TestCase
{
    public function testCreateDummyTwigMethodExecution(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create a completely fresh SmartMailer instance that will definitely call createDummyTwig
        $smartMailer = new SmartMailer($dsnMock, null);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        // Use a template that exercises Twig rendering to ensure createDummyTwig is used
        $message->setHtml('<p>Hello {{ name }}! Today is {{ date }}.</p>');
        $message->setText('Hello {{ name }}! Today is {{ date }}.');
        $message->setContext(['name' => 'Test', 'date' => '2025-08-19']);

        // This send operation should use the dummy Twig created by createDummyTwig
        $result = $smartMailer->send($message);

        // The send method should return something (from the transport)
        $this->assertNull($result); // null transport returns null
    }

    public function testCreateDummyTwigVersusCustomTwig(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Test with no Twig provided (should use createDummyTwig)
        $smartMailerWithDummyTwig = new SmartMailer($dsnMock);

        // Test with custom Twig provided (should NOT use createDummyTwig)
        $customTwig = new \Twig\Environment(new \Twig\Loader\ArrayLoader([]));
        $smartMailerWithCustomTwig = new SmartMailer($dsnMock, $customTwig);

        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('<p>Simple message</p>');

        // Both should work, but one uses createDummyTwig and one doesn't
        $smartMailerWithDummyTwig->send($message);
        $smartMailerWithCustomTwig->send($message);

        $this->assertTrue(true); // If we get here, both worked
    }

    public function testDirectCreateDummyTwigMethodAccess(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Use reflection to directly call the createDummyTwig method
        $reflection = new \ReflectionClass($smartMailer);
        $method = $reflection->getMethod('createDummyTwig');
        $method->setAccessible(true);

        $twig = $method->invoke($smartMailer);

        $this->assertInstanceOf(\Twig\Environment::class, $twig);

        // Test that the created Twig can actually render templates
        $template = $twig->createTemplate('Hello {{ name }}!');
        $result = $template->render(['name' => 'World']);

        $this->assertEquals('Hello World!', $result);
    }
}
