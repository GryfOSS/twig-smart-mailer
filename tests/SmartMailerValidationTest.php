<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Mailer;

use PHPUnit\Framework\TestCase;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Dsn\DsnInterface;
use GryfOSS\Mailer\Exception\InvalidEmailMessageException;

class SmartMailerValidationTest extends TestCase
{
    private SmartMailer $mailer;

    protected function setUp(): void
    {
        /** @var DsnInterface $dsn */
        $dsn = $this->createMock(DsnInterface::class);
        $this->mailer = new SmartMailer($dsn);
    }

    public function testValidateEmptyToWithCcRecipients(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addCc(new EmailAddress('cc@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $result = $this->mailer->validate($message);
        $this->assertTrue($result);
    }

    public function testValidateEmptyToWithBccRecipients(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addBcc(new EmailAddress('bcc@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $result = $this->mailer->validate($message);
        $this->assertTrue($result);
    }

    public function testValidateMultipleRecipientTypes(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('to@example.com'));
        $message->addCc(new EmailAddress('cc@example.com'));
        $message->addBcc(new EmailAddress('bcc@example.com'));
        $message->setHtml('<p>Hello World</p>');

        $result = $this->mailer->validate($message);
        $this->assertTrue($result);
    }

    public function testValidateWithOnlyTextContent(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('to@example.com'));
        $message->setText('Hello World');

        $result = $this->mailer->validate($message);
        $this->assertTrue($result);
    }

    public function testValidateWithBothHtmlAndTextContent(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('to@example.com'));
        $message->setHtml('<p>Hello World</p>');
        $message->setText('Hello World');

        $result = $this->mailer->validate($message);
        $this->assertTrue($result);
    }

    public function testValidateWithEmptyArrays(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->setTo([]); // Empty array
        $message->setCc([]); // Empty array
        $message->setBcc([]); // Empty array
        $message->setHtml('<p>Hello World</p>');

        $this->expectException(InvalidEmailMessageException::class);
        $this->expectExceptionMessage('Message must have at least one recipient.');

        $this->mailer->validate($message);
    }

    public function testValidateWithEmptyStringContent(): void
    {
        $message = new Message();
        $message->setFrom(new EmailAddress('sender@example.com'));
        $message->addTo(new EmailAddress('to@example.com'));
        $message->setHtml(''); // Empty string
        $message->setText(''); // Empty string

        $this->expectException(InvalidEmailMessageException::class);
        $this->expectExceptionMessage('Message must have at least one (html, text) body.');

        $this->mailer->validate($message);
    }
}
