<?php

require_once 'vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\ArrayLoader;

// Test the exact template and context from the test
$template = '
<h1>Order Confirmation</h1>
<p>Dear {{ customer_name }},</p>
<p>Thank you for your order! Your order #{{ order_id }} has been confirmed.</p>

<h2>Order Details:</h2>
<table>
  <tbody>
  {% for item in order_items %}
    <tr>
      <td>{{ item.name }}</td>
      <td>{{ item.quantity }}</td>
      <td>${{ "%.2f"|format(item.price) }}</td>
    </tr>
  {% endfor %}
  </tbody>
</table>
';

$loader = new ArrayLoader(['test' => $template]);
$twig = new Environment($loader);

$context = [
    'order_id' => 'ORD-2024-001234',
    'customer_name' => 'John Smith',
    'order_items' => [
        ['name' => 'Premium Widget', 'quantity' => 2, 'price' => 49.99],
        ['name' => 'Basic Tool', 'quantity' => 3, 'price' => 19.34]
    ]
];

try {
    $result = $twig->render('test', $context);
    echo "Success:\n" . $result . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
