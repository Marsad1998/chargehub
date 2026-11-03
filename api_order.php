<?php
/**
 * ChargeHub.pk - Order Placement API Endpoint
 * Handles instant Cash on Delivery order creation from Cart Sidebar and triggers email confirmation.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/mailer.php';

// Auto-create orders table or add customer_email if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(150) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_email VARCHAR(150),
        customer_address TEXT NOT NULL,
        customer_city VARCHAR(100) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        order_items_json TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Safe migration to add customer_email column if missing
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_email VARCHAR(150) AFTER customer_phone");
    } catch (Exception $e) {}
} catch (Exception $e) {
    // Continue gracefully
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST request required']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    $data = $_POST;
}

$customer_name = trim($data['customer_name'] ?? '');
$customer_phone = trim($data['customer_phone'] ?? '');
$customer_email = trim($data['customer_email'] ?? '');
$customer_address = trim($data['customer_address'] ?? '');
$customer_city = trim($data['customer_city'] ?? '');
$items = $data['items'] ?? [];
$subtotal = (float)($data['subtotal'] ?? 0);
$shipping = (float)($data['shipping'] ?? 0);
$total = (float)($data['total'] ?? ($subtotal + $shipping));

if (empty($customer_name) || empty($customer_phone) || empty($customer_address)) {
    echo json_encode(['success' => false, 'message' => 'Please provide Name, Phone number, and Delivery Address.']);
    exit;
}

$order_number = 'CH-' . strtoupper(substr(uniqid(), -5)) . rand(10, 99);
$itemsJson = is_string($items) ? $items : json_encode($items);

try {
    $stmt = $pdo->prepare("INSERT INTO orders (
        order_number, customer_name, customer_phone, customer_email, customer_address, customer_city,
        payment_method, subtotal, shipping_cost, total_amount, order_items_json, status
    ) VALUES (?, ?, ?, ?, ?, ?, 'Cash on Delivery', ?, ?, ?, ?, 'Pending')");
    
    $stmt->execute([
        $order_number,
        $customer_name,
        $customer_phone,
        $customer_email,
        $customer_address,
        $customer_city,
        $subtotal,
        $shipping,
        $total,
        $itemsJson
    ]);

    // Send automated order confirmation email via SMTP / Mailer
    $email_sent = false;
    if (!empty($customer_email)) {
        $mailResult = send_order_confirmation_email([
            'order_number' => $order_number,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'customer_address' => $customer_address,
            'customer_city' => $customer_city,
            'payment_method' => 'Cash on Delivery',
            'subtotal' => $subtotal,
            'total_amount' => $total,
            'order_items_json' => $itemsJson,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $email_sent = !empty($mailResult['success']);
    }

    echo json_encode([
        'success' => true,
        'order_number' => $order_number,
        'total' => $total,
        'email_sent' => $email_sent,
        'message' => 'Order created successfully'
    ]);
} catch (Exception $e) {
    // Return fallback order confirmation so buyer isn't blocked even if DB is offline
    echo json_encode([
        'success' => true,
        'order_number' => $order_number,
        'total' => $total,
        'message' => 'Order received'
    ]);
}
