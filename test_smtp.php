<?php
require 'vendor/autoload.php';

use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Dsn\Smtp;

// Create SMTP DSN for MailHog
$dsn = new Smtp('localhost', 1025);

// Create SmartMailer
$mailer = new SmartMailer($dsn);

// Create a simple test message
$message = new Message();
$message->setFrom(new EmailAddress('test@example.com', 'Test Sender'));
$message->addTo(new EmailAddress('recipient@example.com', 'Test Recipient'));
$message->setSubject('Simple Test Email');
$message->setText('This is a test email to verify SMTP connectivity with MailHog.');

try {
    echo "Attempting to send email...\n";
    $result = $mailer->send($message);
    echo "Email sent successfully!\n";
    echo "Result: " . print_r($result, true) . "\n";
} catch (Exception $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
}
