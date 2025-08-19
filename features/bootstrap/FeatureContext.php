<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\Client;
use GryfOSS\Mailer\SmartMailer;
use GryfOSS\Mailer\FakeFileSmartMailer;
use GryfOSS\Mailer\Message;
use GryfOSS\Mailer\EmailAddress;
use GryfOSS\Mailer\Attachment;
use GryfOSS\Mailer\Dsn\Smtp;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $tempFiles = [];
    private $lastEmailFile;
    private $lastMailer;
    private $lastMessage;
    private $lastSentResult;
    private $httpClient;
    private $mailhogApiUrl = 'http://localhost:8025/api/v1';

    public function __construct()
    {
        $this->httpClient = new Client(['timeout' => 30]);
    }

    /**
     * Clean up temporary files after each scenario
     *
     * @AfterScenario
     */
    public function cleanupTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }

    /**
     * Clear MailHog messages before each scenario
     *
     * @BeforeScenario @smtp
     */
    public function clearMailhogMessages()
    {
        try {
            $this->httpClient->delete($this->mailhogApiUrl . '/messages');
        } catch (Exception $e) {
            // MailHog might not be running, that's okay for file-based tests
        }
    }

    /**
     * @Given I have a FakeFileSmartMailer
     */
    public function iHaveAFakeFileSmartMailer()
    {
        $this->lastEmailFile = tempnam(sys_get_temp_dir(), 'behat_email_');
        $this->tempFiles[] = $this->lastEmailFile;
        $this->lastMailer = new FakeFileSmartMailer($this->lastEmailFile);
    }

    /**
     * @Given I have a SmartMailer with SMTP configuration
     */
    public function iHaveASmartMailerWithSmtpConfiguration()
    {
        $dsn = new Smtp('localhost', 1025, null, null);
        $this->lastMailer = new SmartMailer($dsn);
    }

    /**
     * @Given I have a SmartMailer with Twig environment
     */
    public function iHaveASmartMailerWithTwigEnvironment()
    {
        $dsn = new Smtp('localhost', 1025, null, null);

        $loader = new ArrayLoader([
            'email_template.html' => '<h1>Hello {{ name }}!</h1><p>{{ message }}</p>',
            'email_template.txt' => 'Hello {{ name }}! {{ message }}'
        ]);
        $twig = new Environment($loader);

        $this->lastMailer = new SmartMailer($dsn, $twig);
    }

    /**
     * @Given I create a message with sender :senderEmail and recipient :recipientEmail
     */
    public function iCreateAMessageWithSenderAndRecipient($senderEmail, $recipientEmail)
    {
        $this->lastMessage = new Message();
        $this->lastMessage->setFrom(new EmailAddress($senderEmail, 'Test Sender'));
        $this->lastMessage->addTo(new EmailAddress($recipientEmail, 'Test Recipient'));
    }

    /**
     * @Given the message has subject :subject
     */
    public function theMessageHasSubject($subject)
    {
        $this->lastMessage->setSubject($subject);
    }

    /**
     * @Given the message has plain text content:
     */
    public function theMessageHasPlainTextContent(PyStringNode $content)
    {
        $this->lastMessage->setText($content->getRaw());
    }

    /**
     * @Given the message has HTML content:
     */
    public function theMessageHasHtmlContent(PyStringNode $content)
    {
        $this->lastMessage->setHtml($content->getRaw());
    }

    /**
     * @Given the message has Twig template content:
     */
    public function theMessageHasTwigTemplateContent(PyStringNode $content)
    {
        $this->lastMessage->setHtml($content->getRaw());
    }

    /**
     * @Given the message has context variables:
     */
    public function theMessageHasContextVariables(TableNode $table)
    {
        $context = [];
        foreach ($table->getRowsHash() as $key => $value) {
            // Handle JSON-like object definitions for complex data
            if ($key === 'order_items' && strpos($value, ':') !== false) {
                // Parse simple object format like "Item A:2:29.99,Item B:1:45.00"
                $items = [];
                $itemStrings = explode(',', $value);
                foreach ($itemStrings as $itemString) {
                    $parts = explode(':', trim($itemString));
                    if (count($parts) === 3) {
                        $items[] = [
                            'name' => $parts[0],
                            'quantity' => (int)$parts[1],
                            'price' => (float)$parts[2]
                        ];
                    }
                }
                $context[$key] = $items;
            }
            // Handle comma-separated lists for arrays
            elseif (strpos($value, ',') !== false) {
                $context[$key] = explode(',', $value);
            } else {
                $context[$key] = $value;
            }
        }
        $this->lastMessage->setContext($context);
    }

    /**
     * @Given the message has an attachment :filename with content :content
     */
    public function theMessageHasAnAttachmentWithContent($filename, $content)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'behat_attachment_');
        $this->tempFiles[] = $tempFile;
        file_put_contents($tempFile, $content);

        $attachment = new Attachment($tempFile, $filename);
        $this->lastMessage->addAttachment($attachment);
    }

    /**
     * @Given the message has an embedded image :imageName
     */
    public function theMessageHasAnEmbeddedImage($imageName)
    {
        $imagePath = __DIR__ . '/../../tests/Assets/icon.png';
        if (!file_exists($imagePath)) {
            throw new Exception("Test image file not found at: $imagePath");
        }

        $attachment = new Attachment($imagePath, $imageName);
        $this->lastMessage->addImage($attachment);
    }

    /**
     * @When I send the message
     */
    public function iSendTheMessage()
    {
        $this->lastSentResult = $this->lastMailer->send($this->lastMessage);
    }

    /**
     * @Then the email file should be created
     */
    public function theEmailFileShouldBeCreated()
    {
        if (!file_exists($this->lastEmailFile)) {
            throw new Exception("Email file was not created at: " . $this->lastEmailFile);
        }
    }

    /**
     * @Then the email file should contain :text
     */
    public function theEmailFileShouldContain($text)
    {
        $content = file_get_contents($this->lastEmailFile);
        $emailData = json_decode($content, true);

        if (!$emailData) {
            throw new Exception("Email file does not contain valid JSON: $content");
        }

        // Check in subject, HTML body, and text body
        $searchFields = [
            $emailData['subject'] ?? '',
            $emailData['html'] ?? '',
            $emailData['text'] ?? ''
        ];

        $found = false;
        foreach ($searchFields as $field) {
            if (strpos($field, $text) !== false) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Email data does not contain expected text: $text\nActual content:\n" . json_encode($emailData, JSON_PRETTY_PRINT));
        }
    }

    /**
     * @Then the email file should contain the attachment :filename
     */
    public function theEmailFileShouldContainTheAttachment($filename)
    {
        $content = file_get_contents($this->lastEmailFile);
        $emailData = json_decode($content, true);

        if (!$emailData) {
            throw new Exception("Email file does not contain valid JSON");
        }

        // Check if the attachment is listed in the attachments array
        $attachments = $emailData['attachments'] ?? [];
        if (!in_array($filename, $attachments)) {
            throw new Exception("Email does not contain attachment: $filename\nFound attachments: " . implode(', ', $attachments));
        }
    }

    /**
     * @Then the email file should contain embedded image :imageName
     */
    public function theEmailFileShouldContainEmbeddedImage($imageName)
    {
        $content = file_get_contents($this->lastEmailFile);
        $emailData = json_decode($content, true);

        if (!$emailData) {
            throw new Exception("Email file does not contain valid JSON");
        }

        // Check if the image is listed in the images array
        $images = $emailData['images'] ?? [];
        if (!in_array($imageName, $images)) {
            throw new Exception("Email does not contain embedded image: $imageName\nFound images: " . implode(', ', $images));
        }

        // Also check if HTML content references the embedded image
        $html = $emailData['html'] ?? '';
        if (!empty($html) && strpos($html, "cid:$imageName") === false) {
            throw new Exception("Email HTML does not reference embedded image: $imageName");
        }
    }

    /**
     * @Then the email file should contain rendered Twig content :expectedText
     */
    public function theEmailFileShouldContainRenderedTwigContent($expectedText)
    {
        $content = file_get_contents($this->lastEmailFile);
        $emailData = json_decode($content, true);

        if (!$emailData) {
            throw new Exception("Email file does not contain valid JSON");
        }

        // Check in HTML body first, then text body
        $htmlBody = $emailData['html'] ?? '';
        $textBody = $emailData['text'] ?? '';

        if (strpos($htmlBody, $expectedText) === false && strpos($textBody, $expectedText) === false) {
            throw new Exception("Email does not contain rendered Twig content: $expectedText\nHTML:\n$htmlBody\nText:\n$textBody");
        }
    }

    /**
     * @Then one email should be sent to MailHog
     */
    public function oneEmailShouldBeSentToMailhog()
    {
        $this->waitForEmail(1);
        $emails = $this->getMailhogEmails();

        if (count($emails) !== 1) {
            throw new Exception("Expected 1 email in MailHog, but found " . count($emails));
        }
    }

    /**
     * @Then the MailHog email should have subject :subject
     */
    public function theMailhogEmailShouldHaveSubject($subject)
    {
        $emails = $this->getMailhogEmails();
        $email = $emails[0];

        if ($email['Content']['Headers']['Subject'][0] !== $subject) {
            throw new Exception("Expected subject '$subject' but got: " . $email['Content']['Headers']['Subject'][0]);
        }
    }

    /**
     * @Then the MailHog email should be sent from :fromEmail
     */
    public function theMailhogEmailShouldBeSentFrom($fromEmail)
    {
        $emails = $this->getMailhogEmails();
        $email = $emails[0];

        if ($email['From']['Mailbox'] . '@' . $email['From']['Domain'] !== $fromEmail) {
            $actualFrom = $email['From']['Mailbox'] . '@' . $email['From']['Domain'];
            throw new Exception("Expected from '$fromEmail' but got: $actualFrom");
        }
    }

    /**
     * @Then the MailHog email should be sent to :toEmail
     */
    public function theMailhogEmailShouldBeSentTo($toEmail)
    {
        $emails = $this->getMailhogEmails();
        $email = $emails[0];

        $found = false;
        foreach ($email['To'] as $recipient) {
            if ($recipient['Mailbox'] . '@' . $recipient['Domain'] === $toEmail) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Expected recipient '$toEmail' not found in email");
        }
    }

    /**
     * @Then the MailHog email should contain text :text
     */
    public function theMailhogEmailShouldContainText($text)
    {
        $emails = $this->getMailhogEmails();
        $email = $emails[0];

        $body = $email['Content']['Body'];
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("=\n", "", $body);
        if (strpos($body, $text) === false) {
            throw new Exception("Email body does not contain expected text: $text\nActual body:\n$body");
        }
    }

    /**
     * @Then the MailHog email should have :count attachments
     */
    public function theMailhogEmailShouldHaveAttachments($count)
    {
        $emails = $this->getMailhogEmails();
        $email = $emails[0];

        // Count MIME parts that are attachments
        $attachmentCount = 0;
        if (isset($email['MIME']['Parts'])) {
            foreach ($email['MIME']['Parts'] as $part) {
                if (isset($part['Headers']['Content-Disposition']) &&
                    strpos($part['Headers']['Content-Disposition'][0], 'attachment') !== false) {
                    $attachmentCount++;
                }
            }
        }

        if ($attachmentCount != $count) {
            throw new Exception("Expected $count attachments but found $attachmentCount");
        }
    }

    /**
     * @Then the MailHog email should have embedded images
     */
    public function theMailhogEmailShouldHaveEmbeddedImages()
    {
        $emails = $this->getMailhogEmails();
        $email = $emails[0];

        // Look for embedded images (Content-Disposition: inline)
        $embeddedCount = 0;
        if (isset($email['MIME']['Parts'])) {
            foreach ($email['MIME']['Parts'] as $part) {

                if (isset($part['MIME']) && is_array($part['MIME']) && isset($part['MIME']['Parts']) && is_array($email['MIME']['Parts'])) {
                    // Check for embedded images in the MIME part
                    foreach ($part['MIME']['Parts'] as $mimePart) {
                        if (isset($mimePart['Headers']['Content-Disposition']) &&
                            strpos($mimePart['Headers']['Content-Disposition'][0], 'inline') !== false &&
                            isset($mimePart['Headers']['Content-Type']) &&
                            strpos($mimePart['Headers']['Content-Type'][0], 'image/') !== false) {
                            $embeddedCount++;
                        }
                    }
                } else {
                    if (isset($part['Headers']['Content-Disposition']) &&
                        strpos($part['Headers']['Content-Disposition'][0], 'inline') !== false &&
                        isset($part['Headers']['Content-Type']) &&
                        strpos($part['Headers']['Content-Type'][0], 'image/') !== false) {
                        $embeddedCount++;
                    }
                }
            }


        }

        if ($embeddedCount === 0) {
            throw new Exception("No embedded images found in email");
        }
    }

    /**
     * Wait for emails to arrive in MailHog
     */
    private function waitForEmail($expectedCount, $maxWait = 10)
    {
        $waited = 0;
        while ($waited < $maxWait) {
            try {
                $emails = $this->getMailhogEmails();
                if (count($emails) >= $expectedCount) {
                    return;
                }
            } catch (Exception $e) {
                // Continue waiting
            }
            sleep(1);
            $waited++;
        }
    }

    /**
     * Get emails from MailHog API
     */
    private function getMailhogEmails()
    {
        try {
            $response = $this->httpClient->get($this->mailhogApiUrl . '/messages');
            $data = json_decode($response->getBody()->getContents(), true);
            $messages = $data['messages'] ?? $data;
            return is_array($messages) ? $messages : [];
        } catch (Exception $e) {
            throw new Exception("Failed to retrieve emails from MailHog: " . $e->getMessage());
        }
    }
}
