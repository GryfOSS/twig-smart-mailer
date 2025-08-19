@smtp
Feature: SMTP Email Sending via MailHog
  As a developer
  I want to test actual email sending using SMTP
  So that I can verify emails are properly sent and received

  Background:
    Given I have a SmartMailer with SMTP configuration

  Scenario: Send plain text email via SMTP
    Given I create a message with sender "test@example.com" and recipient "user@example.com"
    And the message has subject "SMTP Plain Text Test"
    And the message has plain text content:
      """
      Hello from SMTP test!
      This email was sent via the fake SMTP server.
      Testing plain text functionality.
      """
    When I send the message
    Then one email should be sent to MailHog
    And the MailHog email should have subject "SMTP Plain Text Test"
    And the MailHog email should be sent from "test@example.com"
    And the MailHog email should be sent to "user@example.com"
    And the MailHog email should contain text "Hello from SMTP test!"

  Scenario: Send HTML email with Twig templates via SMTP
    Given I have a SmartMailer with Twig environment
    And I create a message with sender "system@company.com" and recipient "employee@company.com"
    And the message has subject "Welcome {{ name }}!"
    And the message has Twig template content:
      """
      <html>
      <body>
        <h1>Welcome {{ name }}!</h1>
        <p>Your role: <strong>{{ role }}</strong></p>
        <p>Department: {{ department }}</p>
        <p>Start date: {{ start_date }}</p>
        <hr>
        <p>Please review the following items:</p>
        <ol>
        {% for task in onboarding_tasks %}
          <li>{{ task }}</li>
        {% endfor %}
        </ol>
        <p>Best regards,<br>HR Team</p>
      </body>
      </html>
      """
    And the message has context variables:
      | name              | Sarah Williams        |
      | role              | Software Engineer     |
      | department        | Engineering          |
      | start_date        | 2024-01-15           |
      | onboarding_tasks  | Complete paperwork,Setup workstation,Meet team |
    When I send the message
    Then one email should be sent to MailHog
    And the MailHog email should have subject "Welcome Sarah Williams!"
    And the MailHog email should be sent from "system@company.com"
    And the MailHog email should be sent to "employee@company.com"
    And the MailHog email should contain text "Welcome Sarah Williams!"
    And the MailHog email should contain text "Software Engineer"
    And the MailHog email should contain text "Complete paperwork"

  Scenario: Send email with attachment via SMTP
    Given I create a message with sender "files@company.com" and recipient "client@external.com"
    And the message has subject "Project Documentation"
    And the message has HTML content:
      """
      <html>
      <body>
        <h2>Project Documentation Delivery</h2>
        <p>Dear Client,</p>
        <p>Please find the requested project documentation attached to this email.</p>
        <p>The document contains:</p>
        <ul>
          <li>Technical specifications</li>
          <li>Implementation timeline</li>
          <li>Resource requirements</li>
        </ul>
        <p>If you have any questions, please don't hesitate to contact us.</p>
        <p>Best regards,<br>Project Team</p>
      </body>
      </html>
      """
    And the message has an attachment "project-specs.txt" with content "TECHNICAL SPECIFICATIONS\n\n1. Architecture Overview\n2. Database Schema\n3. API Endpoints\n4. Security Requirements"
    When I send the message
    Then one email should be sent to MailHog
    And the MailHog email should have subject "Project Documentation"
    And the MailHog email should be sent from "files@company.com"
    And the MailHog email should be sent to "client@external.com"
    And the MailHog email should contain text "Project Documentation Delivery"
    And the MailHog email should have 1 attachments

  Scenario: Send email with embedded images via SMTP
    Given I create a message with sender "marketing@company.com" and recipient "subscriber@example.com"
    And the message has subject "Newsletter - Latest Updates"
    And the message has HTML content:
      """
      <html>
      <body style="font-family: Arial, sans-serif;">
        <div style="text-align: center;">
          <img src="cid:newsletter-header.png" alt="Newsletter Header" style="max-width: 600px;">
        </div>
        <h1>Latest Company Updates</h1>
        <p>Dear Valued Subscriber,</p>
        <p>We're excited to share our latest company updates with you!</p>

        <div style="background-color: #f5f5f5; padding: 20px; margin: 20px 0;">
          <h2>🎉 New Product Launch</h2>
          <p>We've launched our revolutionary new product that will change everything!</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
          <img src="cid:product-showcase.png" alt="Product Showcase" style="max-width: 400px;">
        </div>

        <p>Thank you for being part of our community!</p>
        <p>Best regards,<br>Marketing Team</p>
      </body>
      </html>
      """
    And the message has an embedded image "newsletter-header.png"
    And the message has an embedded image "product-showcase.png"
    When I send the message
    Then one email should be sent to MailHog
    And the MailHog email should have subject "Newsletter - Latest Updates"
    And the MailHog email should be sent from "marketing@company.com"
    And the MailHog email should be sent to "subscriber@example.com"
    And the MailHog email should contain text "Latest Company Updates"
    And the MailHog email should have embedded images

  Scenario: Send comprehensive email with all features via SMTP
    Given I have a SmartMailer with Twig environment
    And I create a message with sender "admin@ecommerce.com" and recipient "customer@buyer.com"
    And the message has subject "Order Confirmation #{{ order_id }}"
    And the message has HTML content:
      """
      <html>
      <body style="font-family: Arial, sans-serif; line-height: 1.6;">
        <div style="text-align: center; margin-bottom: 30px;">
          <img src="cid:company-logo.png" alt="Company Logo" style="max-height: 100px;">
        </div>

        <h1>Order Confirmation</h1>
        <p>Dear {{ customer_name }},</p>
        <p>Thank you for your order! Your order #{{ order_id }} has been confirmed.</p>

        <h2>Order Details:</h2>
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
          <thead>
            <tr style="background-color: #f8f9fa;">
              <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Item</th>
              <th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Quantity</th>
              <th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Price</th>
            </tr>
          </thead>
          <tbody>
          {% for item in order_items %}
            <tr>
              <td style="border: 1px solid #ddd; padding: 12px;">{{ item.name }}</td>
              <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">{{ item.quantity }}</td>
              <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">${{ item.price }}</td>
            </tr>
          {% endfor %}
          </tbody>
          <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
              <td colspan="2" style="border: 1px solid #ddd; padding: 12px; text-align: right;">Total:</td>
              <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">${{ total_amount }}</td>
            </tr>
          </tfoot>
        </table>

        <h2>Shipping Information:</h2>
        <p><strong>Address:</strong> {{ shipping_address }}<br>
        <strong>Estimated Delivery:</strong> {{ delivery_date }}</p>

        <p>You can track your order using the attached tracking document.</p>
        <p>If you have any questions, please contact our customer service.</p>

        <p>Thank you for choosing us!</p>
        <p>Best regards,<br>{{ company_name }} Team</p>
      </body>
      </html>
      """
    And the message has context variables:
      | order_id         | ORD-2024-001234        |
      | customer_name    | John Smith             |
      | total_amount     | 157.99                 |
      | shipping_address | 123 Main St, Anytown   |
      | delivery_date    | January 25, 2024       |
      | company_name     | E-Commerce Plus        |
    And the message has an attachment "order-receipt.pdf" with content "ORDER RECEIPT\nOrder ID: ORD-2024-001234\nCustomer: John Smith\nTotal: $157.99\nDate: 2024-01-20"
    And the message has an attachment "tracking-info.txt" with content "TRACKING INFORMATION\nCarrier: FastShip\nTracking Number: FS123456789\nEstimated Delivery: Jan 25, 2024"
    And the message has an embedded image "company-logo.png"
    When I send the message
    Then one email should be sent to MailHog
    And the MailHog email should have subject "Order Confirmation #ORD-2024-001234"
    And the MailHog email should be sent from "admin@ecommerce.com"
    And the MailHog email should be sent to "customer@buyer.com"
    And the MailHog email should contain text "Dear John Smith"
    And the MailHog email should contain text "Order #ORD-2024-001234"
    And the MailHog email should contain text "$157.99"
    And the MailHog email should contain text "E-Commerce Plus Team"
    And the MailHog email should have 2 attachments
    And the MailHog email should have embedded images
