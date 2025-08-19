Praetorian Technology: Smart Mailer
===================================

![Tests status](https://github.com/praetoriantechnology/smart-mailer/workflows/tests/badge.svg)
![GitHub tag (latest SemVer)](https://img.shields.io/github/v/tag/praetoriantechnology/smart-mailer?label=latest%20version&sort=semver)

Smart mailer is a simple library which assists sending of emails with use of
symfony/mailer component. At the moment it requires SMTP connection, but grants
features like easy embedding of images, easier attachments adding, exceptions
tracking and use of string templates instead of files.

## Features

- **Twig Template Support**: Use Twig syntax in both HTML and text email content
- **Easy File Attachments**: Simple attachment management with validation
- **Embedded Images**: Support for inline images with automatic MIME type detection
- **Multiple Recipients**: Support for To, CC, and BCC recipients
- **DSN Configuration**: Flexible transport configuration via DSN strings
- **Gmail Integration**: Built-in Gmail transport support
- **Testing Support**: File-based mailer for development and testing
- **Exception Handling**: Comprehensive exception hierarchy for error handling

## Quick Start

```php
<?php

declare(strict_types=1);

use Praetorian\SmartMailer\Attachment;
use Praetorian\SmartMailer\EmailAddress;
use Praetorian\SmartMailer\Message;
use Praetorian\SmartMailer\SmartMailer;
use Praetorian\SmartMailer\Dsn\Smtp;

include 'vendor/autoload.php';

// Configure SMTP transport
$dsn = new Smtp();
$dsn->setHost('smtp.sample.com')
    ->setPort(465)
    ->setUsername('username')
    ->setPassword('password');

// Create message
$message = new Message();
$message->setFrom(new EmailAddress('sender@example.com', 'Sender Name'))
    ->addTo(new EmailAddress('recipient@example.com', 'Recipient Name'))
    ->setSubject('Test Email')
    ->setHtml('<h1>Hello {{ name }}!</h1><img src="cid:logo.png" alt="Logo"/>')
    ->setText('Hello {{ name }}!')
    ->setContext(['name' => 'World']);

// Add attachments and images
$attachment = new Attachment('/path/to/file.pdf', 'Document.pdf');
$message->addAttachment($attachment);

$logo = new Attachment('/path/to/logo.png', 'logo.png');
$message->addImage($logo);

// Send email
$mailer = new SmartMailer($dsn);
$mailer->send($message);
```

## Library Structure

### Core Classes

#### `SmartMailer`
Main email sending implementation with Twig template support. Integrates with Symfony Mailer for actual email delivery.

#### `FakeFileSmartMailer`
File-based implementation for testing and development. Writes email data to JSON files instead of sending actual emails.

#### `Message`
Represents an email message with all its components including recipients, content, attachments, and embedded images.

#### `EmailAddress`
Encapsulates an email address with optional display name and validation.

#### `Attachment`
Represents a file attachment with path validation and optional custom naming.

### DSN (Data Source Name) Classes

#### `DsnInterface`
Interface for creating DSN strings compatible with Symfony Mailer.

#### `Dsn\Smtp`
SMTP server DSN implementation with full configuration options.

#### `Dsn\Gmail`
Simplified Gmail DSN implementation using Symfony's Gmail transport.

### Configuration Enums

#### `EncryptionMethod`
Defines available SMTP encryption methods (SSL/TLS, STARTTLS, none).

#### `LoginMethod`
Defines available SMTP authentication methods (LOGIN, PLAIN, CRAM-MD5, etc.).

### Exception Hierarchy

#### `SmartMailerException`
Base exception class for all library-specific errors.

#### `InvalidEmailAddressException`
Thrown when email address format validation fails.

#### `InvalidAttachmentException`
Thrown when attachment file doesn't exist or isn't readable.

#### `InvalidEmailMessageException`
Thrown when message validation fails (missing required fields).

#### `InvalidImageException`
Thrown when attempting to embed a non-image file.

#### `NotUniqueEmbedNameException`
Thrown when trying to add an embedded image with a duplicate name.

#### `SendException`
Thrown when email sending fails.

## Advanced Usage

### Using Gmail Transport

```php
use Praetorian\SmartMailer\Dsn\Gmail;

$gmail = new Gmail();
$gmail->setUsername('your-email@gmail.com')
      ->setPassword('your-app-password');

$mailer = new SmartMailer($gmail);
```

### Embedded Images

You can easily embed any image by using `content id` of the resource. If your
filename is `sample.png` then to embed just place `cid:sample.png` in contents.
You can also define a custom name:

```php
$image = new Attachment('/path/to/image.png');
$image->setName('custom-name');
$message->addImage($image);

// In HTML: <img src="cid:custom-name" alt="Custom Image"/>
```

**Warning**: Embedded image names must be unique within a message!

### Twig Templates

Both HTML and text content support Twig templating:

```php
$message->setHtml('
    <h1>Welcome {{ user.name }}!</h1>
    <p>You have {{ notifications|length }} new notifications.</p>
    {% for notification in notifications %}
        <p>{{ notification.message }}</p>
    {% endfor %}
')
->setContext([
    'user' => ['name' => 'John Doe'],
    'notifications' => [
        ['message' => 'New message received'],
        ['message' => 'Profile updated']
    ]
]);
```

### Testing with FakeFileSmartMailer

```php
use Praetorian\SmartMailer\FakeFileSmartMailer;

$mailer = new FakeFileSmartMailer('/tmp/test-email.json');
$mailer->send($message);

// Email data will be written to /tmp/test-email.json as JSON
```

### Error Handling

```php
use Praetorian\SmartMailer\Exception\SmartMailerException;

try {
    $mailer->send($message);
} catch (SmartMailerException $e) {
    // Handle any SmartMailer-specific error
    echo "Email error: " . $e->getMessage();
}
```

## Requirements

- PHP 8.1+
- Symfony Mailer 6.0+ or 7.0+
- Twig 3.0+

## Installation

```bash
composer require praetoriantechnology/smart-mailer
```

## Roadmap

1.0: tests, comments ✅
2.0: other transports than SMTP Servers
3.0: Pub/Sub queues support

## Contribution

Any pull requests or issues reported are more than welcome.

## License

GPL 3+