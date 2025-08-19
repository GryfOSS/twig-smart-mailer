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
use Twig\Loader\FilesystemLoader;

class SmartMailerDummyTwigTest extends TestCase
{
    public function testCreateDummyTwigFunctionalityWithFileSystem(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create a test template file in the current directory to test filesystem loader
        $testTemplateContent = 'Hello {{ name }}! This is from filesystem: {{ value }}.';
        $testTemplateFile = 'test_template.twig';
        file_put_contents($testTemplateFile, $testTemplateContent);

        try {
            // Create SmartMailer without Twig environment to trigger createDummyTwig
            $smartMailer = new SmartMailer($dsnMock);

            // Use reflection to get the created Twig environment
            $reflection = new ReflectionClass($smartMailer);
            $twigProperty = $reflection->getProperty('twig');
            $twigProperty->setAccessible(true);
            $twigInstance = $twigProperty->getValue($smartMailer);

            $this->assertInstanceOf(Environment::class, $twigInstance);

            // Test that the dummy Twig can load templates from current directory
            $template = $twigInstance->load($testTemplateFile);
            $rendered = $template->render([
                'name' => 'Test',
                'value' => 'filesystem loader working'
            ]);

            $expected = 'Hello Test! This is from filesystem: filesystem loader working.';
            $this->assertEquals($expected, $rendered);

            // Test that the loader is indeed a FilesystemLoader pointing to current directory
            $loader = $twigInstance->getLoader();
            $this->assertInstanceOf(FilesystemLoader::class, $loader);

            // Verify the loader can access files in current directory
            $this->assertTrue($loader->exists($testTemplateFile));

        } finally {
            // Clean up test file
            if (file_exists($testTemplateFile)) {
                unlink($testTemplateFile);
            }
        }
    }

    public function testCreateDummyTwigMethodDirectlyWithReflection(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Use reflection to directly access and test createDummyTwig method
        $reflection = new ReflectionClass($smartMailer);
        $createDummyTwigMethod = $reflection->getMethod('createDummyTwig');
        $createDummyTwigMethod->setAccessible(true);

        // Call the method directly
        $twigEnvironment = $createDummyTwigMethod->invoke($smartMailer);

        $this->assertInstanceOf(Environment::class, $twigEnvironment);

        // Verify the loader is FilesystemLoader
        $loader = $twigEnvironment->getLoader();
        $this->assertInstanceOf(FilesystemLoader::class, $loader);

        // Test that the environment can create templates inline
        $template = $twigEnvironment->createTemplate('Direct test: {{ value }}');
        $result = $template->render(['value' => 'success']);

        $this->assertEquals('Direct test: success', $result);
    }

    public function testSmartMailerWithDummyTwigVersusCustomTwig(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create SmartMailer with no Twig (should use createDummyTwig)
        $smartMailerWithDummy = new SmartMailer($dsnMock);

        // Create SmartMailer with custom Twig (should NOT use createDummyTwig)
        $customTwig = new Environment(new \Twig\Loader\ArrayLoader([
            'custom.html' => 'Custom: {{ message }}'
        ]));
        $smartMailerWithCustom = new SmartMailer($dsnMock, $customTwig);

        // Test that both work but use different Twig environments
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('recipient@example.com'));
        $message->setHtml('Simple message: {{ content }}');
        $message->setContext(['content' => 'test']);

        // Both should work without throwing exceptions
        $smartMailerWithDummy->send($message);
        $smartMailerWithCustom->send($message);

        // Verify they use different Twig instances
        $reflection = new ReflectionClass($smartMailerWithDummy);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);

        $dummyTwig = $twigProperty->getValue($smartMailerWithDummy);
        $injectedTwig = $twigProperty->getValue($smartMailerWithCustom);

        $this->assertNotSame($dummyTwig, $injectedTwig);
        $this->assertInstanceOf(FilesystemLoader::class, $dummyTwig->getLoader());
        $this->assertInstanceOf(\Twig\Loader\ArrayLoader::class, $injectedTwig->getLoader());
    }

    public function testCreateDummyTwigWithFilesystemAccess(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create a nested directory structure to test filesystem loader
        $testDir = 'test_templates';
        $testSubDir = $testDir . '/sub';

        if (!is_dir($testSubDir)) {
            mkdir($testSubDir, 0755, true);
        }

        $templateFile = $testSubDir . '/nested.twig';
        file_put_contents($templateFile, 'Nested template: {{ data }}');

        try {
            $smartMailer = new SmartMailer($dsnMock);

            // Get the dummy Twig environment
            $reflection = new ReflectionClass($smartMailer);
            $twigProperty = $reflection->getProperty('twig');
            $twigProperty->setAccessible(true);
            $twigInstance = $twigProperty->getValue($smartMailer);

            // Test that it can load templates from subdirectories
            $template = $twigInstance->load($templateFile);
            $result = $template->render(['data' => 'filesystem working']);

            $this->assertEquals('Nested template: filesystem working', $result);

        } finally {
            // Clean up
            if (file_exists($templateFile)) {
                unlink($templateFile);
            }
            if (is_dir($testSubDir)) {
                rmdir($testSubDir);
            }
            if (is_dir($testDir)) {
                rmdir($testDir);
            }
        }
    }
}
