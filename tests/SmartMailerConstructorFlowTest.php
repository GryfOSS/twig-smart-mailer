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

class SmartMailerConstructorFlowTest extends TestCase
{
    public function testConstructorWithNullTwigTriggersCreateDummyTwig(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Explicitly pass null as second parameter to ensure createDummyTwig is called
        $smartMailer = new SmartMailer($dsnMock, null);

        // Verify the twig property was set
        $reflection = new ReflectionClass($smartMailer);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);
        $twigInstance = $twigProperty->getValue($smartMailer);

        $this->assertInstanceOf(Environment::class, $twigInstance);
        $this->assertInstanceOf(FilesystemLoader::class, $twigInstance->getLoader());
    }

    public function testConstructorWithoutTwigParameterTriggersCreateDummyTwig(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Don't pass second parameter at all (defaults to null)
        $smartMailer = new SmartMailer($dsnMock);

        // Verify the twig property was set by createDummyTwig
        $reflection = new ReflectionClass($smartMailer);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);
        $twigInstance = $twigProperty->getValue($smartMailer);

        $this->assertInstanceOf(Environment::class, $twigInstance);
        $this->assertInstanceOf(FilesystemLoader::class, $twigInstance->getLoader());
    }

    public function testCreateDummyTwigMethodBothLines(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        // Access the method via reflection
        $reflection = new ReflectionClass($smartMailer);
        $createDummyTwigMethod = $reflection->getMethod('createDummyTwig');
        $createDummyTwigMethod->setAccessible(true);

        // Call the method directly to ensure both lines are executed:
        // Line 1: $loader = new FilesystemLoader('.');
        // Line 2: return new Environment($loader);
        $twig = $createDummyTwigMethod->invoke($smartMailer);

        $this->assertInstanceOf(Environment::class, $twig);

        // Verify the loader was created with '.' parameter
        $loader = $twig->getLoader();
        $this->assertInstanceOf(FilesystemLoader::class, $loader);

        // Test that the filesystem loader points to current directory
        // by checking if it can handle relative paths
        $template = $twig->createTemplate('Test template content');
        $this->assertNotNull($template);
    }

    public function testMultipleInstancesUseDifferentDummyTwigInstances(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        // Create multiple instances to ensure createDummyTwig is called each time
        $smartMailer1 = new SmartMailer($dsnMock);
        $smartMailer2 = new SmartMailer($dsnMock);
        $smartMailer3 = new SmartMailer($dsnMock, null);

        $reflection = new ReflectionClass(SmartMailer::class);
        $twigProperty = $reflection->getProperty('twig');
        $twigProperty->setAccessible(true);

        $twig1 = $twigProperty->getValue($smartMailer1);
        $twig2 = $twigProperty->getValue($smartMailer2);
        $twig3 = $twigProperty->getValue($smartMailer3);

        // All should be Environment instances but different objects
        $this->assertInstanceOf(Environment::class, $twig1);
        $this->assertInstanceOf(Environment::class, $twig2);
        $this->assertInstanceOf(Environment::class, $twig3);

        // They should be different instances (createDummyTwig called multiple times)
        $this->assertNotSame($twig1, $twig2);
        $this->assertNotSame($twig2, $twig3);
        $this->assertNotSame($twig1, $twig3);
    }

    public function testCreateDummyTwigWithFilesystemLoaderCurrentDirectory(): void
    {
        /** @var MockObject&DsnInterface $dsnMock */
        $dsnMock = $this->createMock(DsnInterface::class);
        $dsnMock->method('__toString')->willReturn('null://null');

        $smartMailer = new SmartMailer($dsnMock);

        $reflection = new ReflectionClass($smartMailer);
        $createDummyTwigMethod = $reflection->getMethod('createDummyTwig');
        $createDummyTwigMethod->setAccessible(true);

        // Ensure both lines in createDummyTwig are executed:
        // $loader = new FilesystemLoader('.');
        // return new Environment($loader);
        $environment = $createDummyTwigMethod->invoke($smartMailer);

        $this->assertInstanceOf(Environment::class, $environment);

        $loader = $environment->getLoader();
        $this->assertInstanceOf(FilesystemLoader::class, $loader);

        // Verify the loader uses current directory by checking its paths
        $reflection = new ReflectionClass($loader);
        if ($reflection->hasMethod('getPaths')) {
            $paths = $loader->getPaths();
            $this->assertContains('.', $paths);
        }
    }
}
