Feature: FakeFileSmartMailer Email Sending
  As a developer
  I want to test email sending using FakeFileSmartMailer
  So that I can verify email content and attachments are handled correctly

  Background:
    Given I have a FakeFileSmartMailer

  Scenario: Send plain text email
    Given I create a message with sender "sender@example.com" and recipient "recipient@example.com"
    And the message has subject "Plain Text Test"
    And the message has plain text content:
      """
      Hello World!
      This is a plain text email for testing.
      Best regards,
      Test Suite
      """
    When I send the message
    Then the email file should be created
    And the email file should contain "Hello World!"
    And the email file should contain "Plain Text Test"

  Scenario: Send HTML email with Twig template
    Given I create a message with sender "sender@example.com" and recipient "recipient@example.com"
    And the message has subject "Twig Template Test"
    And the message has Twig template content:
      """
      <h1>Hello {{ name }}!</h1>
      <p>Welcome to {{ service }}. Your account status is: <strong>{{ status }}</strong></p>
      <ul>
      {% for item in items %}
        <li>{{ item }}</li>
      {% endfor %}
      </ul>
      """
    And the message has context variables:
      | name    | John Doe     |
      | service | Smart Mailer |
      | status  | Active       |
      | items   | Item 1,Item 2,Item 3 |
    When I send the message
    Then the email file should be created
    And the email file should contain rendered Twig content "Hello John Doe!"
    And the email file should contain rendered Twig content "Welcome to Smart Mailer"
    And the email file should contain rendered Twig content "Active"

  Scenario: Send email with attachment
    Given I create a message with sender "sender@example.com" and recipient "recipient@example.com"
    And the message has subject "Attachment Test"
    And the message has plain text content:
      """
      Please find the attached document.
      """
    And the message has an attachment "test-document.txt" with content "This is a test document content for attachment testing."
    When I send the message
    Then the email file should be created
    And the email file should contain "Please find the attached document"
    And the email file should contain the attachment "test-document.txt"

  Scenario: Send email with embedded image
    Given I create a message with sender "sender@example.com" and recipient "recipient@example.com"
    And the message has subject "Embedded Image Test"
    And the message has HTML content:
      """
      <html>
      <body>
        <h1>Email with Embedded Image</h1>
        <p>Here is our logo:</p>
        <img src="cid:company-logo.png" alt="Company Logo" width="100">
        <p>Thank you!</p>
      </body>
      </html>
      """
    And the message has an embedded image "company-logo.png"
    When I send the message
    Then the email file should be created
    And the email file should contain "Email with Embedded Image"
    And the email file should contain embedded image "company-logo.png"

  Scenario: Send complex email with all features
    Given I create a message with sender "noreply@company.com" and recipient "customer@example.com"
    And the message has subject "Monthly Report - {{ month }} {{ year }}"
    And the message has HTML content:
      """
      <html>
      <body>
        <h1>Monthly Report for {{ month }} {{ year }}</h1>
        <img src="cid:header-logo.png" alt="Logo" style="float: right;">
        <p>Dear {{ customer_name }},</p>
        <p>Please find your monthly report attached.</p>
        <h2>Summary:</h2>
        <ul>
        {% for metric in metrics %}
          <li><strong>{{ metric.name }}:</strong> {{ metric.value }}</li>
        {% endfor %}
        </ul>
        <p>Best regards,<br>{{ company_name }}</p>
      </body>
      </html>
      """
    And the message has context variables:
      | month         | December           |
      | year          | 2024              |
      | customer_name | Alice Johnson      |
      | company_name  | Smart Mailer Corp  |
    And the message has an attachment "monthly-report.pdf" with content "FAKE PDF CONTENT FOR TESTING"
    And the message has an embedded image "header-logo.png"
    When I send the message
    Then the email file should be created
    And the email file should contain rendered Twig content "Monthly Report for December 2024"
    And the email file should contain rendered Twig content "Dear Alice Johnson"
    And the email file should contain rendered Twig content "Smart Mailer Corp"
    And the email file should contain the attachment "monthly-report.pdf"
    And the email file should contain embedded image "header-logo.png"
