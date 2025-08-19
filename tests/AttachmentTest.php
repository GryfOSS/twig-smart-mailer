<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Exception\InvalidAttachmentException;
use ReflectionClass;

class AttachmentTest extends TestCase
{
    use PHPMock;

    private const TESTED_CLASS = Attachment::class;

    private const PATH_TO_EXISTING_AND_READABLE_FILE = '/path/to/file';

    private const PATH_TO_EXISTING_AND_UNREADABLE_FILE = '/path/to/unreadable-file';

    private const PATH_TO_NONEXISTENT_FILE = '/path/to/nonexistent-file';

    public function setUp(): void
    {
        $reflectionClass = new ReflectionClass(self::TESTED_CLASS);
        $testedClassNamespaceName = $reflectionClass->getNamespaceName();

        $file_exists_Mock = $this->getFunctionMock($testedClassNamespaceName, 'file_exists');
        $file_exists_Mock->expects($this->any())->willReturnMap([
            [self::PATH_TO_EXISTING_AND_READABLE_FILE, true],
            [self::PATH_TO_EXISTING_AND_UNREADABLE_FILE, true],
            [self::PATH_TO_NONEXISTENT_FILE, false],
        ]);

        $is_readable_Mock = $this->getFunctionMock($testedClassNamespaceName, 'is_readable');
        $is_readable_Mock->expects($this->any())->willReturnMap([
            [self::PATH_TO_EXISTING_AND_READABLE_FILE, true],
            [self::PATH_TO_EXISTING_AND_UNREADABLE_FILE, false],
            [self::PATH_TO_NONEXISTENT_FILE, false],
        ]);
    }

    public function testCreateAttachmentWithoutName(): void
    {
        $path = self::PATH_TO_EXISTING_AND_READABLE_FILE;
        $attachment = new Attachment($path);

        $this->assertEquals($path, $attachment->getPath());
        $this->assertNull($attachment->getName());
    }

    public function testCreateAttachmentWithName(): void
    {
        $path = self::PATH_TO_EXISTING_AND_READABLE_FILE;
        $name = 'Name';
        $attachment = new Attachment($path, $name);

        $this->assertEquals($path, $attachment->getPath());
        $this->assertEquals($name, $attachment->getName());
    }

    /**
     * @dataProvider provideInvalidPath
     */
    public function testThrowExceptionWhenCreatingAttachment(string $invalidPath): void
    {
        $this->expectException(InvalidAttachmentException::class);

        new Attachment($invalidPath);
    }

    public function provideInvalidPath(): array
    {
        return [
            [self::PATH_TO_EXISTING_AND_UNREADABLE_FILE],
            [self::PATH_TO_NONEXISTENT_FILE],
        ];
    }
}
