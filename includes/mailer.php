<?php
/**
 * ChargeHub.pk - Pure PHP Modular SMTP Mailer & Order Email Template Engine
 * 
 * Zero external dependencies. Uses stream sockets with SSL/TLS support.
 * Automatically provisions email_settings table in MySQL with a responsive template.
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Ensure email_settings table exists and is populated with default configuration
 */
function init_email_settings_table() {
    global $pdo;
    if (!$pdo) return;

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS email_settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            smtp_enabled TINYINT(1) DEFAULT 0,
            smtp_host VARCHAR(255) DEFAULT 'smtp.gmail.com',
            smtp_port INT DEFAULT 587,
            smtp_user VARCHAR(255) DEFAULT '',
            smtp_pass VARCHAR(255) DEFAULT '',
            smtp_encryption VARCHAR(10) DEFAULT 'tls',
            from_email VARCHAR(255) DEFAULT 'orders@chargehub.pk',
            from_name VARCHAR(255) DEFAULT 'ChargeHub Pakistan',
            admin_notification_email VARCHAR(255) DEFAULT '',
            notify_admin_on_order TINYINT(1) DEFAULT 1,
            email_subject VARCHAR(255) DEFAULT 'Order Confirmed #{order_number} - ChargeHub.pk',
            email_template_html MEDIUMTEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Check if any row exists; if not, seed default settings
        $count = (int)$pdo->query("SELECT COUNT(*) FROM email_settings")->fetchColumn();
        if ($count === 0) {
            $defaultTemplate = get_default_order_email_template();
            $stmt = $pdo->prepare("INSERT INTO email_settings (
                smtp_enabled, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_encryption,
                from_email, from_name, admin_notification_email, notify_admin_on_order,
                email_subject, email_template_html
            ) VALUES (0, 'smtp.gmail.com', 587, '', '', 'tls', 'orders@chargehub.pk', 'ChargeHub Pakistan', '', 1, 'Order Confirmed #{order_number} - ChargeHub.pk', ?)");
            $stmt->execute([$defaultTemplate]);
        }
    } catch (Exception $e) {
        error_log('Error initializing email_settings table: ' . $e->getMessage());
    }
}

/**
 * Default Responsive HTML Email Template for Order Confirmations
 */
function get_default_order_email_template() {
    return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #{order_number}</title>
    <style>
        body, table, td, p, a, li, blockquote { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding: 30px 10px; }
        .main-card { background-color: #ffffff; margin: 0 auto; max-width: 620px; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #090e1a 0%, #002b80 50%, #0052cc 100%); padding: 32px 30px; text-align: center; }
        .logo-text { color: #ffffff; font-size: 26px; font-weight: 900; letter-spacing: -0.5px; margin: 0; text-decoration: none; display: inline-block; }
        .logo-text span { color: #ff5500; }
        .tagline { color: #94a3b8; font-size: 11px; margin-top: 4px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; }
        .body-content { padding: 32px 30px; }
        .badge-confirmed { display: inline-block; padding: 6px 14px; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; color: #047857; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px; }
        .order-headline { font-size: 22px; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.5px; }
        .order-intro { font-size: 13px; color: #64748b; line-height: 1.6; margin: 0 0 24px 0; }
        .order-pill { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: table; width: 100%; box-sizing: border-box; }
        .order-pill-col { display: table-cell; vertical-align: middle; }
        .pill-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; font-weight: 700; margin-bottom: 2px; }
        .pill-val { font-size: 14px; font-weight: 900; color: #0052cc; font-family: monospace, Courier, sans-serif; }
        .section-title { font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #0f172a; margin: 0 0 12px 0; padding-bottom: 8px; border-bottom: 2px solid #f1f5f9; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; padding: 8px 6px; border-bottom: 1px solid #e2e8f0; }
        .items-table td { padding: 12px 6px; border-bottom: 1px solid #f1f5f9; font-size: 12.5px; color: #334155; vertical-align: middle; }
        .item-name { font-weight: 700; color: #0f172a; }
        .totals-table { width: 100%; max-width: 280px; margin-left: auto; border-collapse: collapse; margin-bottom: 24px; }
        .totals-table td { padding: 6px 0; font-size: 12px; color: #64748b; }
        .totals-table .total-row td { border-top: 2px solid #e2e8f0; padding-top: 10px; font-size: 15px; font-weight: 900; color: #0f172a; }
        .totals-table .total-row .total-price { color: #0052cc; }
        .dest-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; margin-bottom: 24px; }
        .dest-grid { display: table; width: 100%; }
        .dest-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: #ffffff !important; text-decoration: none; padding: 12px 26px; border-radius: 9999px; font-size: 12px; font-weight: 800; letter-spacing: 0.3px; text-align: center; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }
        .guarantee-box { background-color: #eff6ff; border: 1px dashed #bfdbfe; border-radius: 12px; padding: 12px 16px; margin-top: 24px; text-align: center; font-size: 11px; color: #1e40af; font-weight: 600; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 30px; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.6; }
        .footer a { color: #0052cc; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            
            <!-- Header with Gradient Branding -->
            <div class="header">
                <a href="https://chargehub.pk" class="logo-text">CHARGE<span>HUB</span>.PK</a>
                <div class="tagline">Official Genuine Chargers &amp; High-Speed Cables</div>
            </div>

            <!-- Body Content -->
            <div class="body-content">
                <div style="text-align: center; margin-bottom: 16px;">
                    <span class="badge-confirmed">&#10003; Order Confirmed</span>
                    <h1 class="order-headline">Thank You for Your Order!</h1>
                    <p class="order-intro">Hi <strong>{customer_name}</strong>, we have received your order and our fulfillment team at Lahore Hub is already preparing your parcel.</p>
                </div>

                <!-- Order Reference Pill -->
                <div class="order-pill">
                    <div class="order-pill-col">
                        <div class="pill-label">Order Reference</div>
                        <div class="pill-val">#{order_number}</div>
                    </div>
                    <div class="order-pill-col" style="text-align: right;">
                        <div class="pill-label">Payment Method</div>
                        <div style="font-size: 12.5px; font-weight: 800; color: #047857;">&#128181; {payment_method}</div>
                    </div>
                </div>

                <!-- Items Ordered Table -->
                <div class="section-title">Ordered Items</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Product</th>
                            <th style="text-align: center; width: 15%;">Qty</th>
                            <th style="text-align: right; width: 25%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {order_items_html}
                    </tbody>
                </table>

                <!-- Financial Breakdown -->
                <table class="totals-table">
                    <tr>
                        <td>Subtotal</td>
                        <td style="text-align: right; font-weight: 700; color: #1e293b;">Rs. {subtotal}</td>
                    </tr>
                    <tr>
                        <td>Express Delivery</td>
                        <td style="text-align: right; font-weight: 800; color: #059669; text-transform: uppercase; font-size: 10.5px;">FREE</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total Amount:</td>
                        <td style="text-align: right;" class="total-price">Rs. {total_amount}</td>
                    </tr>
                </table>

                <!-- Delivery Destination Details -->
                <div class="section-title">Delivery Destination</div>
                <div class="dest-card">
                    <div class="dest-grid">
                        <div class="dest-col">
                            <div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Recipient</div>
                            <div style="font-weight: 800; color: #0f172a; font-size: 13px;">{customer_name}</div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">&#128222; {customer_phone}</div>
                        </div>
                        <div class="dest-col">
                            <div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">Shipping Address</div>
                            <div style="font-size: 12px; color: #334155; line-height: 1.4;">{customer_address}</div>
                            <div style="font-weight: 700; color: #0f172a; font-size: 12px; margin-top: 2px;">{customer_city}, Pakistan</div>
                        </div>
                    </div>
                </div>

                <!-- Instant WhatsApp Updates -->
                <div style="text-align: center; margin: 26px 0 16px 0;">
                    <a href="{whatsapp_link}" target="_blank" class="cta-btn">
                        &#128172; Track / Chat on WhatsApp
                    </a>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 8px;">Direct support &amp; open-box courier updates</div>
                </div>

                <!-- Warranty Shield Box -->
                <div class="guarantee-box">
                    &#128737;&#65039; <strong>18-Month Direct Swap Replacement Guarantee:</strong> All ChargeHub products are covered under our direct hassle-free replacement warranty.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 6px 0; font-weight: 700; color: #475569;">ChargeHub Pakistan &bull; Genuine Power, Guaranteed.</p>
                <p style="margin: 0 0 6px 0;">Lahore Hub: Trax / TCS Express Nationwide Delivery (2 - 3 Days)</p>
                <p style="margin: 0;">Need assistance? WhatsApp Support: <a href="https://wa.me/923001234567">+92 300 1234567</a></p>
            </div>

        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Fetch current email settings from database
 */
function get_email_settings() {
    global $pdo;
    init_email_settings_table();
    
    try {
        $stmt = $pdo->query("SELECT * FROM email_settings ORDER BY id ASC LIMIT 1");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        if ($row) return $row;
    } catch (Exception $e) {
        error_log('get_email_settings error: ' . $e->getMessage());
    }

    return [
        'smtp_enabled' => 0,
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_encryption' => 'tls',
        'from_email' => 'orders@chargehub.pk',
        'from_name' => 'ChargeHub Pakistan',
        'admin_notification_email' => '',
        'notify_admin_on_order' => 1,
        'email_subject' => 'Order Confirmed #{order_number} - ChargeHub.pk',
        'email_template_html' => get_default_order_email_template()
    ];
}

/**
 * Pure PHP SMTP Client Class
 */
class ChargeHubSMTP {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $encryption;
    private $timeout;
    private $socket = null;
    public $debugLog = [];

    public function __construct($host, $port, $user, $pass, $encryption = 'tls', $timeout = 15) {
        $this->host = trim($host);
        $this->port = (int)$port;
        $this->user = trim($user);
        $this->pass = trim($pass);
        $this->encryption = strtolower(trim($encryption));
        $this->timeout = $timeout;
    }

    private function log($msg) {
        $this->debugLog[] = date('H:i:s') . ' ' . $msg;
    }

    private function read() {
        $data = '';
        while (!feof($this->socket)) {
            $str = fgets($this->socket, 512);
            if ($str === false) break;
            $data .= $str;
            if (isset($str[3]) && $str[3] === ' ') break;
        }
        $this->log('< ' . trim($data));
        return $data;
    }

    private function write($cmd) {
        $this->log('> ' . (str_starts_with($cmd, 'AUTH') ? 'AUTH [redacted]' : (strlen($cmd) > 200 ? substr($cmd, 0, 200) . '...' : trim($cmd))));
        fwrite($this->socket, $cmd . "\r\n");
    }

    public function send($toEmail, $toName, $subject, $htmlBody, $fromEmail, $fromName, $replyTo = null) {
        $this->debugLog = [];
        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid recipient email address: ' . $toEmail];
        }

        $hostPrefix = '';
        if ($this->encryption === 'ssl') {
            $hostPrefix = 'ssl://';
        }

        $target = $hostPrefix . $this->host . ':' . $this->port;
        $this->log("Connecting to {$target}...");

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $errno = 0;
        $errstr = '';
        $this->socket = @stream_socket_client($target, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$this->socket) {
            $err = "Connection failed: {$errstr} ({$errno})";
            $this->log($err);
            return ['success' => false, 'error' => $err, 'log' => $this->debugLog];
        }

        stream_set_timeout($this->socket, $this->timeout);

        $greeting = $this->read();
        if (substr($greeting, 0, 3) !== '220') {
            fclose($this->socket);
            return ['success' => false, 'error' => 'Unexpected greeting from SMTP server: ' . $greeting, 'log' => $this->debugLog];
        }

        // Send EHLO
        $clientHost = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        $this->write('EHLO ' . $clientHost);
        $ehloResp = $this->read();

        // Handle STARTTLS
        if ($this->encryption === 'tls') {
            $this->write('STARTTLS');
            $starttlsResp = $this->read();
            if (substr($starttlsResp, 0, 3) === '220') {
                $crypto = @stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$crypto) {
                    fclose($this->socket);
                    return ['success' => false, 'error' => 'STARTTLS crypto handshake failed.', 'log' => $this->debugLog];
                }
                $this->write('EHLO ' . $clientHost);
                $this->read();
            } else {
                $this->log('Warning: STARTTLS not accepted, continuing unencrypted...');
            }
        }

        // Authentication
        if (!empty($this->user) && !empty($this->pass)) {
            $this->write('AUTH LOGIN');
            $authResp = $this->read();
            if (substr($authResp, 0, 3) !== '334') {
                fclose($this->socket);
                return ['success' => false, 'error' => 'SMTP rejected AUTH LOGIN command: ' . $authResp, 'log' => $this->debugLog];
            }

            $this->write(base64_encode($this->user));
            $userResp = $this->read();
            if (substr($userResp, 0, 3) !== '334') {
                fclose($this->socket);
                return ['success' => false, 'error' => 'SMTP rejected Username: ' . $userResp, 'log' => $this->debugLog];
            }

            $this->write(base64_encode($this->pass));
            $passResp = $this->read();
            if (substr($passResp, 0, 3) !== '235') {
                fclose($this->socket);
                return ['success' => false, 'error' => 'SMTP Authentication failed (Check credentials/app password): ' . $passResp, 'log' => $this->debugLog];
            }
            $this->log('Authentication successful.');
        }

        // MAIL FROM
        $this->write('MAIL FROM:<' . $fromEmail . '>');
        $mailFromResp = $this->read();
        if (substr($mailFromResp, 0, 3) !== '250') {
            fclose($this->socket);
            return ['success' => false, 'error' => 'MAIL FROM rejected: ' . $mailFromResp, 'log' => $this->debugLog];
        }

        // RCPT TO
        $this->write('RCPT TO:<' . $toEmail . '>');
        $rcptResp = $this->read();
        if (substr($rcptResp, 0, 3) !== '250' && substr($rcptResp, 0, 3) !== '251') {
            fclose($this->socket);
            return ['success' => false, 'error' => 'Recipient rejected: ' . $rcptResp, 'log' => $this->debugLog];
        }

        // DATA
        $this->write('DATA');
        $dataResp = $this->read();
        if (substr($dataResp, 0, 3) !== '354') {
            fclose($this->socket);
            return ['success' => false, 'error' => 'DATA command rejected: ' . $dataResp, 'log' => $this->debugLog];
        }

        // Build Standard MIME Headers
        $headers = [];
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . '>';
        $headers[] = 'To: =?UTF-8?B?' . base64_encode($toName) . '?= <' . $toEmail . '>';
        if ($replyTo) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }
        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers[] = 'Message-ID: <' . md5(uniqid(time(), true)) . '@' . $this->host . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $headers[] = 'X-Mailer: ChargeHub SMTP Mailer v7';

        $fullPayload = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";
        $this->write($fullPayload);

        $sendResp = $this->read();
        $this->write('QUIT');
        fclose($this->socket);

        if (substr($sendResp, 0, 3) === '250') {
            $this->log('Message accepted for delivery.');
            return ['success' => true, 'log' => $this->debugLog];
        } else {
            return ['success' => false, 'error' => 'Server rejected message payload: ' . $sendResp, 'log' => $this->debugLog];
        }
    }
}

/**
 * Main Trigger Function: Sends Order Confirmation Email
 * 
 * @param string|int|array $orderInput Order number, Order ID, or Order associative array
 * @return array ['success' => bool, 'message' => string]
 */
function send_order_confirmation_email($orderInput) {
    global $pdo;
    $settings = get_email_settings();

    // Fetch order record
    $order = null;
    if (is_array($orderInput)) {
        $order = $orderInput;
    } else {
        if (!$pdo) return ['success' => false, 'message' => 'Database connection unavailable'];
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? OR id = ? LIMIT 1");
        $stmt->execute([$orderInput, (int)$orderInput]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$order) {
        return ['success' => false, 'message' => 'Order not found in database'];
    }

    $customerEmail = trim($order['customer_email'] ?? '');
    if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'No valid customer email address found for this order'];
    }

    // Decode Items JSON to generate table rows
    $items = [];
    if (!empty($order['order_items_json'])) {
        $items = json_decode($order['order_items_json'], true) ?: [];
    }

    $itemsHtml = '';
    $itemsText = '';
    foreach ($items as $it) {
        $iName = htmlspecialchars($it['name'] ?? 'Product');
        $iQty = (int)($it['qty'] ?? 1);
        $iPrice = (float)($it['price'] ?? 0);
        $iTotal = $iPrice * $iQty;

        $itemsHtml .= "<tr>
            <td style=\"padding: 12px 6px; border-bottom: 1px solid #f1f5f9;\">
                <span style=\"font-weight: 700; color: #0f172a;\">{$iName}</span>
                <span style=\"font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;\">Rs. " . number_format($iPrice) . " each</span>
            </td>
            <td style=\"padding: 12px 6px; border-bottom: 1px solid #f1f5f9; text-align: center; font-weight: 700;\">{$iQty}</td>
            <td style=\"padding: 12px 6px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: 800; color: #0f172a;\">Rs. " . number_format($iTotal) . "</td>
        </tr>";

        $itemsText .= "• {$iName} (Qty: {$iQty}) - Rs. " . number_format($iTotal) . "\n";
    }

    if (empty($itemsHtml)) {
        $itemsHtml = '<tr><td colspan="3" style="padding: 12px; text-align: center; color: #94a3b8;">Standard Gear Order</td></tr>';
    }

    // WhatsApp Direct Link
    $waItems = urlencode($itemsText);
    $waMsg = urlencode("Hi ChargeHub! I placed Order #" . $order['order_number'] . ".\nTotal: Rs. " . number_format((float)$order['total_amount']) . "\nPlease confirm dispatch status.");
    $waLink = "https://wa.me/923001234567?text=" . $waMsg;

    // Placeholder Replacements
    $replacements = [
        '{order_number}' => htmlspecialchars($order['order_number']),
        '{customer_name}' => htmlspecialchars($order['customer_name']),
        '{customer_email}' => htmlspecialchars($customerEmail),
        '{customer_phone}' => htmlspecialchars($order['customer_phone']),
        '{customer_address}' => htmlspecialchars($order['customer_address']),
        '{customer_city}' => htmlspecialchars($order['customer_city']),
        '{payment_method}' => htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery (COD)'),
        '{subtotal}' => number_format((float)($order['subtotal'] ?? $order['total_amount'])),
        '{total_amount}' => number_format((float)$order['total_amount']),
        '{order_items_html}' => $itemsHtml,
        '{order_items_text}' => $itemsText,
        '{order_date}' => date('M d, Y h:i A', strtotime($order['created_at'] ?? 'now')),
        '{whatsapp_link}' => $waLink,
        '{site_name}' => 'ChargeHub.pk'
    ];

    // Build Final Subject and Body
    $subjectTemplate = !empty($settings['email_subject']) ? $settings['email_subject'] : 'Order Confirmed #{order_number} - ChargeHub.pk';
    $bodyTemplate = !empty($settings['email_template_html']) ? $settings['email_template_html'] : get_default_order_email_template();

    $finalSubject = str_replace(array_keys($replacements), array_values($replacements), $subjectTemplate);
    $finalBody = str_replace(array_keys($replacements), array_values($replacements), $bodyTemplate);

    $fromEmail = !empty($settings['from_email']) ? $settings['from_email'] : 'orders@chargehub.pk';
    $fromName = !empty($settings['from_name']) ? $settings['from_name'] : 'ChargeHub Pakistan';

    // 1. If SMTP is Enabled, dispatch through ChargeHubSMTP
    if (!empty($settings['smtp_enabled']) && !empty($settings['smtp_host'])) {
        $smtp = new ChargeHubSMTP(
            $settings['smtp_host'],
            $settings['smtp_port'],
            $settings['smtp_user'],
            $settings['smtp_pass'],
            $settings['smtp_encryption'] ?? 'tls'
        );

        $result = $smtp->send(
            $customerEmail,
            $order['customer_name'],
            $finalSubject,
            $finalBody,
            $fromEmail,
            $fromName
        );

        // Notify Admin Copy if enabled
        if (!empty($settings['notify_admin_on_order']) && !empty($settings['admin_notification_email'])) {
            $adminSubject = "[New Order Alert] #" . $order['order_number'] . " from " . $order['customer_name'];
            $smtp->send(
                $settings['admin_notification_email'],
                'Store Admin',
                $adminSubject,
                $finalBody,
                $fromEmail,
                $fromName
            );
        }

        return $result;
    }

    // 2. If SMTP is disabled or host is empty, fallback to standard PHP mail()
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion()
    ];

    $mailSent = @mail($customerEmail, $finalSubject, $finalBody, implode("\r\n", $headers));
    
    // Admin alert copy
    if (!empty($settings['notify_admin_on_order']) && !empty($settings['admin_notification_email'])) {
        $adminSubject = "[New Order Alert] #" . $order['order_number'] . " from " . $order['customer_name'];
        @mail($settings['admin_notification_email'], $adminSubject, $finalBody, implode("\r\n", $headers));
    }

    if ($mailSent) {
        return ['success' => true, 'message' => 'Dispatched via native mail()'];
    } else {
        return ['success' => false, 'message' => 'SMTP is not enabled and native mail() failed.'];
    }
}
