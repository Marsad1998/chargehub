<?php
/**
 * ChargeHub.pk - Admin Email & SMTP Settings & Template Manager
 */

$page_title = 'Email & SMTP Settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../includes/mailer.php';

$settings = get_email_settings();

$flash_success = '';
$flash_error = '';
$test_log = [];

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $isValidToken = function_exists('validate_csrf_token') ? validate_csrf_token($token) : (function_exists('verify_csrf_token') ? verify_csrf_token($token) : true);
    
    if (!$isValidToken) {
        $flash_error = 'Session expired or invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // 1. SAVE SMTP CONFIGURATION
        if ($action === 'save_smtp') {
            $smtp_enabled = isset($_POST['smtp_enabled']) ? 1 : 0;
            $smtp_host = trim($_POST['smtp_host'] ?? '');
            $smtp_port = (int)($_POST['smtp_port'] ?? 587);
            $smtp_user = trim($_POST['smtp_user'] ?? '');
            $smtp_pass = trim($_POST['smtp_pass'] ?? '');
            $smtp_encryption = trim($_POST['smtp_encryption'] ?? 'tls');
            $from_email = trim($_POST['from_email'] ?? 'orders@chargehub.pk');
            $from_name = trim($_POST['from_name'] ?? 'ChargeHub Pakistan');
            $admin_notification_email = trim($_POST['admin_notification_email'] ?? '');
            $notify_admin_on_order = isset($_POST['notify_admin_on_order']) ? 1 : 0;

            try {
                $stmt = $pdo->prepare("UPDATE email_settings SET 
                    smtp_enabled = ?,
                    smtp_host = ?,
                    smtp_port = ?,
                    smtp_user = ?,
                    smtp_pass = ?,
                    smtp_encryption = ?,
                    from_email = ?,
                    from_name = ?,
                    admin_notification_email = ?,
                    notify_admin_on_order = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $smtp_enabled, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_encryption,
                    $from_email, $from_name, $admin_notification_email, $notify_admin_on_order,
                    $settings['id']
                ]);

                $flash_success = 'SMTP settings saved successfully!';
                $settings = get_email_settings();
            } catch (Exception $e) {
                $flash_error = 'Database error: ' . $e->getMessage();
            }
        }

        // 2. SAVE EMAIL TEMPLATE
        elseif ($action === 'save_template') {
            $email_subject = trim($_POST['email_subject'] ?? '');
            $email_template_html = trim($_POST['email_template_html'] ?? '');

            if (empty($email_subject)) {
                $flash_error = 'Subject line cannot be empty.';
            } elseif (empty($email_template_html)) {
                $flash_error = 'Template HTML cannot be empty.';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE email_settings SET email_subject = ?, email_template_html = ? WHERE id = ?");
                    $stmt->execute([$email_subject, $email_template_html, $settings['id']]);
                    $flash_success = 'Email template updated successfully!';
                    $settings = get_email_settings();
                } catch (Exception $e) {
                    $flash_error = 'Database error: ' . $e->getMessage();
                }
            }
        }

        // 3. RESET EMAIL TEMPLATE TO DEFAULT
        elseif ($action === 'reset_template') {
            try {
                $defaultTpl = get_default_order_email_template();
                $defaultSub = 'Order Confirmed #{order_number} - ChargeHub.pk';
                $stmt = $pdo->prepare("UPDATE email_settings SET email_subject = ?, email_template_html = ? WHERE id = ?");
                $stmt->execute([$defaultSub, $defaultTpl, $settings['id']]);
                $flash_success = 'Email template reset to default responsive design!';
                $settings = get_email_settings();
            } catch (Exception $e) {
                $flash_error = 'Error resetting template: ' . $e->getMessage();
            }
        }

        // 4. TEST SMTP CONNECTION
        elseif ($action === 'test_email') {
            $test_to = trim($_POST['test_to'] ?? '');
            if (empty($test_to) || !filter_var($test_to, FILTER_VALIDATE_EMAIL)) {
                $flash_error = 'Please enter a valid recipient email for the test.';
            } elseif (empty($settings['smtp_host'])) {
                $flash_error = 'Please enter an SMTP Host first.';
            } else {
                $smtp = new ChargeHubSMTP(
                    $settings['smtp_host'],
                    $settings['smtp_port'],
                    $settings['smtp_user'],
                    $settings['smtp_pass'],
                    $settings['smtp_encryption']
                );

                $testSubject = "⚡ ChargeHub SMTP Test (" . date('H:i:s') . ")";
                $testBody = "<div style='font-family:sans-serif;padding:24px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;max-width:500px;'>
                    <h2 style='color:#0052cc;margin:0 0 10px 0;'>✓ SMTP Connection Successful!</h2>
                    <p style='color:#334155;font-size:14px;line-height:1.6;'>This is a live test email sent from your ChargeHub Admin Portal via <strong>{$settings['smtp_host']}:{$settings['smtp_port']}</strong>.</p>
                    <div style='background:#ffffff;padding:12px;border-radius:8px;font-size:12px;color:#64748b;'>
                        <div><strong>Encryption:</strong> " . strtoupper($settings['smtp_encryption']) . "</div>
                        <div><strong>From:</strong> {$settings['from_name']} &lt;{$settings['from_email']}&gt;</div>
                        <div><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</div>
                    </div>
                </div>";

                $res = $smtp->send(
                    $test_to,
                    'ChargeHub Admin',
                    $testSubject,
                    $testBody,
                    $settings['from_email'],
                    $settings['from_name']
                );

                $test_log = $smtp->debugLog;

                if ($res['success']) {
                    $flash_success = "Test email sent successfully to {$test_to}!";
                } else {
                    $flash_error = "Test failed: " . ($res['error'] ?? 'Unknown socket error');
                }
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<!-- MAIN APP CONTENT CONTAINER -->
<div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-app-canvas">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <main class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto w-full">

        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-app">
            <div>
                <div class="flex items-center space-x-2 text-xs text-app-muted mb-1">
                    <a href="index.php" class="hover:underline">Dashboard</a>
                    <span>/</span>
                    <span class="text-app-title font-semibold">Email &amp; SMTP</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-app-title brand-font tracking-tight flex items-center space-x-2">
                    <span>✉️</span>
                    <span>Email &amp; SMTP Configuration</span>
                </h2>
                <p class="text-xs text-app-muted mt-0.5">
                    Configure automated order confirmations sent to customers via SMTP once an order is placed.
                </p>
            </div>

            <!-- Status Pill -->
            <div class="flex items-center space-x-2">
                <?php if (!empty($settings['smtp_enabled'])): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-500 border border-emerald-500/30 flex items-center space-x-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>SMTP Active</span>
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-500 border border-amber-500/30 flex items-center space-x-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>SMTP Disabled (Native Mail fallback)</span>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- FLASH NOTIFICATIONS -->
        <?php if (!empty($flash_success)): ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-xs font-bold flex items-center justify-between animate-fadeIn">
            <div class="flex items-center space-x-2">
                <span class="text-base">✓</span>
                <span><?= e($flash_success) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-lg leading-none cursor-pointer">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500 text-xs font-bold flex items-center justify-between animate-fadeIn">
            <div class="flex items-center space-x-2">
                <span class="text-base">⚠️</span>
                <span><?= e($flash_error) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-lg leading-none cursor-pointer">&times;</button>
        </div>
        <?php endif; ?>

        <!-- SETTINGS TABS -->
        <div class="flex border-b border-app space-x-2 sm:space-x-4">
            <button type="button" onclick="switchTab('smtp')" id="tab-btn-smtp" class="px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 border-blue-600 text-blue-600 transition-all cursor-pointer flex items-center space-x-2">
                <span>🔌</span>
                <span>SMTP Server Settings</span>
            </button>
            <button type="button" onclick="switchTab('template')" id="tab-btn-template" class="px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 border-transparent text-app-muted hover:text-app-title transition-all cursor-pointer flex items-center space-x-2">
                <span>📝</span>
                <span>Order Email Template</span>
            </button>
            <button type="button" onclick="switchTab('test')" id="tab-btn-test" class="px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 border-transparent text-app-muted hover:text-app-title transition-all cursor-pointer flex items-center space-x-2">
                <span>🚀</span>
                <span>Test Connection</span>
            </button>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: SMTP CONFIGURATION -->
        <!-- ============================================================= -->
        <div id="tab-content-smtp" class="space-y-6">
            <form method="POST" action="settings-email.php" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="save_smtp">

                <div class="p-5 sm:p-7 rounded-2xl bg-app-card border border-app shadow-sm space-y-6">
                    
                    <!-- Master Enable Toggle -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl bg-slate-500/5 border border-app gap-4">
                        <div>
                            <div class="font-bold text-sm text-app-title flex items-center space-x-2">
                                <span>Enable Automated SMTP Email Notifications</span>
                            </div>
                            <p class="text-xs text-app-muted mt-0.5">
                                When enabled, customer order confirmation emails are dispatched automatically using these SMTP credentials.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="smtp_enabled" value="1" <?= !empty($settings['smtp_enabled']) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-400/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Quick Preset Buttons -->
                    <div>
                        <span class="text-[11px] font-bold text-app-muted uppercase tracking-wider block mb-2">Quick Presets:</span>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <button type="button" onclick="applyPreset('gmail')" class="px-3 py-1.5 rounded-lg input-app border border-app hover:border-blue-500 text-app-title font-semibold transition-all cursor-pointer">
                                🔴 Gmail / G Suite (587 TLS)
                            </button>
                            <button type="button" onclick="applyPreset('outlook')" class="px-3 py-1.5 rounded-lg input-app border border-app hover:border-blue-500 text-app-title font-semibold transition-all cursor-pointer">
                                🔵 Outlook / Office 365 (587 TLS)
                            </button>
                            <button type="button" onclick="applyPreset('cpanel')" class="px-3 py-1.5 rounded-lg input-app border border-app hover:border-blue-500 text-app-title font-semibold transition-all cursor-pointer">
                                🟡 cPanel / Webmail (465 SSL)
                            </button>
                            <button type="button" onclick="applyPreset('sendgrid')" class="px-3 py-1.5 rounded-lg input-app border border-app hover:border-blue-500 text-app-title font-semibold transition-all cursor-pointer">
                                🟢 SendGrid (587 TLS)
                            </button>
                        </div>
                    </div>

                    <!-- SMTP Fields Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                        <!-- Host -->
                        <div class="lg:col-span-2">
                            <label class="block font-bold text-app-title mb-1.5">SMTP Server Host *</label>
                            <input type="text" id="smtp_host" name="smtp_host" value="<?= e($settings['smtp_host']) ?>" required placeholder="e.g. smtp.gmail.com or mail.chargehub.pk" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono focus:outline-none focus:border-blue-500">
                        </div>

                        <!-- Port -->
                        <div>
                            <label class="block font-bold text-app-title mb-1.5">SMTP Port *</label>
                            <input type="number" id="smtp_port" name="smtp_port" value="<?= (int)$settings['smtp_port'] ?>" required placeholder="587 or 465" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono focus:outline-none focus:border-blue-500">
                        </div>

                        <!-- Encryption Type -->
                        <div>
                            <label class="block font-bold text-app-title mb-1.5">Encryption *</label>
                            <select id="smtp_encryption" name="smtp_encryption" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-semibold focus:outline-none focus:border-blue-500 cursor-pointer">
                                <option value="tls" <?= ($settings['smtp_encryption'] === 'tls') ? 'selected' : '' ?>>TLS (STARTTLS - Port 587 Recommended)</option>
                                <option value="ssl" <?= ($settings['smtp_encryption'] === 'ssl') ? 'selected' : '' ?>>SSL (Implicit SSL - Port 465)</option>
                                <option value="none" <?= ($settings['smtp_encryption'] === 'none') ? 'selected' : '' ?>>None (Unencrypted - Port 25)</option>
                            </select>
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block font-bold text-app-title mb-1.5">SMTP Username / Email *</label>
                            <input type="text" id="smtp_user" name="smtp_user" value="<?= e($settings['smtp_user']) ?>" placeholder="e.g. orders@chargehub.pk" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono focus:outline-none focus:border-blue-500">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block font-bold text-app-title mb-1.5 flex items-center justify-between">
                                <span>SMTP Password / App Password *</span>
                                <button type="button" onclick="togglePassVisibility()" class="text-[10px] text-blue-500 hover:underline cursor-pointer">Show/Hide</button>
                            </label>
                            <input type="password" id="smtp_pass" name="smtp_pass" value="<?= e($settings['smtp_pass']) ?>" placeholder="••••••••••••••••" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-mono focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Sender Info & Admin Notifications -->
                    <div class="pt-4 border-t border-app">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-app-muted mb-3">Sender &amp; Administrator Alerts</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-app-title mb-1.5">Sender Email Address *</label>
                                <input type="email" name="from_email" value="<?= e($settings['from_email']) ?>" required placeholder="orders@chargehub.pk" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs focus:outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block font-bold text-app-title mb-1.5">Sender Display Name *</label>
                                <input type="text" name="from_name" value="<?= e($settings['from_name']) ?>" required placeholder="ChargeHub Pakistan" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs focus:outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block font-bold text-app-title mb-1.5">Admin Notification Email</label>
                                <input type="email" name="admin_notification_email" value="<?= e($settings['admin_notification_email']) ?>" placeholder="owner@chargehub.pk (Optional)" class="w-full px-3 py-2.5 rounded-xl input-app border border-app text-app-title text-xs focus:outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="inline-flex items-center space-x-2 text-xs text-app-title cursor-pointer">
                                <input type="checkbox" name="notify_admin_on_order" value="1" <?= !empty($settings['notify_admin_on_order']) ? 'checked' : '' ?> class="rounded text-blue-600 focus:ring-0">
                                <span>Also send an order copy alert to the Admin Notification Email whenever an order is placed</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all active:scale-95 cursor-pointer flex items-center space-x-1.5">
                            <span>💾</span>
                            <span>Save SMTP Settings</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 2: EMAIL TEMPLATE EDITOR -->
        <!-- ============================================================= -->
        <div id="tab-content-template" class="hidden space-y-6">
            <form method="POST" action="settings-email.php" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="save_template">

                <div class="p-5 sm:p-7 rounded-2xl bg-app-card border border-app shadow-sm space-y-5">
                    
                    <!-- Subject line -->
                    <div>
                        <label class="block font-bold text-app-title text-xs mb-1.5">Email Subject Line *</label>
                        <input type="text" name="email_subject" value="<?= e($settings['email_subject']) ?>" required placeholder="Order Confirmed #{order_number} - ChargeHub.pk" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs font-semibold focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Available Placeholders Reference Chips -->
                    <div class="p-4 rounded-xl bg-slate-500/5 border border-app space-y-2">
                        <span class="text-[11px] font-bold text-app-muted uppercase tracking-wider block">Click any placeholder to copy or insert into template:</span>
                        <div class="flex flex-wrap gap-1.5 text-[11px] font-mono">
                            <?php 
                            $tags = [
                                '{order_number}' => 'Order ID (e.g. CH-84921)',
                                '{customer_name}' => 'Customer Full Name',
                                '{customer_phone}' => 'Customer Phone',
                                '{customer_email}' => 'Customer Email',
                                '{customer_address}' => 'Delivery Address',
                                '{customer_city}' => 'City Name',
                                '{payment_method}' => 'COD / Payment Method',
                                '{subtotal}' => 'Subtotal in PKR',
                                '{total_amount}' => 'Grand Total in PKR',
                                '{order_items_html}' => 'Formatted Products Table Rows',
                                '{order_items_text}' => 'Plain-text list of items',
                                '{order_date}' => 'Date of order',
                                '{whatsapp_link}' => 'Direct WhatsApp tracking URL',
                                '{site_name}' => 'ChargeHub.pk'
                            ];
                            foreach ($tags as $tag => $desc): ?>
                            <button type="button" onclick="insertTag('<?= $tag ?>')" title="<?= e($desc) ?>" class="px-2 py-1 rounded bg-blue-500/10 hover:bg-blue-500/20 text-blue-500 font-bold border border-blue-500/20 transition-all cursor-pointer">
                                <?= $tag ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- HTML Editor Area -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="font-bold text-app-title text-xs">HTML Email Template Code *</label>
                            <div class="flex items-center space-x-2">
                                <button type="button" onclick="openTemplatePreview()" class="px-3 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] shadow-sm transition-all flex items-center space-x-1 cursor-pointer">
                                    <span>👁️</span>
                                    <span>Preview Live Email</span>
                                </button>
                            </div>
                        </div>
                        <textarea id="email_template_html" name="email_template_html" rows="18" required class="w-full p-4 rounded-xl input-app border border-app text-app-title text-xs font-mono leading-relaxed focus:outline-none focus:border-blue-500"><?= e($settings['email_template_html']) ?></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <button type="button" onclick="confirmResetTemplate()" class="text-xs text-red-500 hover:underline font-semibold cursor-pointer">
                            ↺ Reset to Default Official Template
                        </button>
                        
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="openTemplatePreview()" class="px-4 py-2.5 rounded-xl bg-slate-500/10 hover:bg-slate-500/20 text-app-title font-bold text-xs transition-all cursor-pointer">
                                Preview
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all active:scale-95 cursor-pointer flex items-center space-x-1.5">
                                <span>💾</span>
                                <span>Save Email Template</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>

            <!-- Hidden Reset Form -->
            <form id="reset-template-form" method="POST" action="settings-email.php" class="hidden">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <input type="hidden" name="action" value="reset_template">
            </form>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 3: TEST SMTP CONNECTION -->
        <!-- ============================================================= -->
        <div id="tab-content-test" class="hidden space-y-6">
            <div class="p-5 sm:p-7 rounded-2xl bg-app-card border border-app shadow-sm space-y-6">
                <div>
                    <h3 class="font-bold text-base text-app-title">Send a Test Email via SMTP</h3>
                    <p class="text-xs text-app-muted mt-0.5">
                        Verify your SMTP credentials by sending a live test email and inspecting the complete socket transaction log.
                    </p>
                </div>

                <form method="POST" action="settings-email.php" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <input type="hidden" name="action" value="test_email">

                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="flex-1 w-full">
                            <label class="block font-bold text-app-title text-xs mb-1">Recipient Email Address *</label>
                            <input type="email" name="test_to" required placeholder="your-email@example.com" class="w-full px-3.5 py-2.5 rounded-xl input-app border border-app text-app-title text-xs focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="sm:pt-5 w-full sm:w-auto">
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all active:scale-95 cursor-pointer flex items-center justify-center space-x-1.5">
                                <span>🚀</span>
                                <span>Send Test Email</span>
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($test_log)): ?>
                <!-- Socket Transaction Log -->
                <div class="pt-4 border-t border-app space-y-2">
                    <span class="text-xs font-bold text-app-title flex items-center space-x-1.5">
                        <span>📟</span>
                        <span>SMTP Connection &amp; Socket Transcript:</span>
                    </span>
                    <div class="p-4 rounded-xl bg-slate-950 text-emerald-400 font-mono text-[11px] leading-relaxed max-h-80 overflow-y-auto space-y-1 border border-slate-800">
                        <?php foreach ($test_log as $logLine): ?>
                            <div><?= htmlspecialchars($logLine) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

</div>

<!-- ================================================================= -->
<!-- EMAIL TEMPLATE LIVE PREVIEW MODAL -->
<!-- ================================================================= -->
<div id="preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div onclick="closeTemplatePreview()" class="absolute inset-0 bg-black/70 backdrop-blur-xs"></div>
    <div class="relative w-full max-w-4xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-app overflow-hidden flex flex-col max-h-[92vh]">
        <div class="p-4 px-6 border-b border-app flex items-center justify-between bg-app-header">
            <div class="flex items-center space-x-2">
                <span class="text-lg">📧</span>
                <span class="font-bold text-sm text-app-title">Live Responsive Email Preview</span>
            </div>
            <button type="button" onclick="closeTemplatePreview()" class="w-8 h-8 rounded-full bg-slate-500/15 hover:bg-slate-500/25 text-app-title flex items-center justify-center text-lg cursor-pointer">&times;</button>
        </div>
        <div class="flex-1 overflow-hidden p-2 sm:p-4 bg-slate-100 dark:bg-slate-950">
            <iframe id="preview-frame" class="w-full h-full min-h-[500px] border-0 rounded-2xl shadow-inner bg-white"></iframe>
        </div>
        <div class="p-3 px-6 border-t border-app bg-app-header flex items-center justify-between text-xs text-app-muted">
            <span>Renders sample data dynamically in realistic client viewport</span>
            <button type="button" onclick="closeTemplatePreview()" class="px-4 py-1.5 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 text-app-title font-bold cursor-pointer">Close Preview</button>
        </div>
    </div>
</div>

<script>
    // Tab Switching
    function switchTab(tab) {
        ['smtp', 'template', 'test'].forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            const content = document.getElementById('tab-content-' + t);
            if (t === tab) {
                btn.className = 'px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 border-blue-600 text-blue-600 transition-all cursor-pointer flex items-center space-x-2';
                content.classList.remove('hidden');
            } else {
                btn.className = 'px-4 py-2.5 font-bold text-xs sm:text-sm border-b-2 border-transparent text-app-muted hover:text-app-title transition-all cursor-pointer flex items-center space-x-2';
                content.classList.add('hidden');
            }
        });
    }

    // SMTP Quick Presets
    function applyPreset(provider) {
        const host = document.getElementById('smtp_host');
        const port = document.getElementById('smtp_port');
        const enc = document.getElementById('smtp_encryption');

        if (provider === 'gmail') {
            host.value = 'smtp.gmail.com';
            port.value = '587';
            enc.value = 'tls';
        } else if (provider === 'outlook') {
            host.value = 'smtp.office365.com';
            port.value = '587';
            enc.value = 'tls';
        } else if (provider === 'cpanel') {
            host.value = 'mail.' + window.location.hostname.replace('www.', '');
            port.value = '465';
            enc.value = 'ssl';
        } else if (provider === 'sendgrid') {
            host.value = 'smtp.sendgrid.net';
            port.value = '587';
            enc.value = 'tls';
        }
    }

    // Toggle Password Visibility
    function togglePassVisibility() {
        const p = document.getElementById('smtp_pass');
        p.type = (p.type === 'password') ? 'text' : 'password';
    }

    // Insert Tag into Template Textarea
    function insertTag(tag) {
        const textarea = document.getElementById('email_template_html');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.focus();
        textarea.selectionEnd = start + tag.length;
    }

    // Reset Confirmation
    function confirmResetTemplate() {
        if (confirm('Are you sure you want to reset the template to the official default responsive layout? Any custom changes will be overwritten.')) {
            document.getElementById('reset-template-form').submit();
        }
    }

    // Live Template Preview
    function openTemplatePreview() {
        const rawTpl = document.getElementById('email_template_html').value;
        const sampleItems = `
            <tr>
                <td style="padding: 12px 6px; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-weight: 700; color: #0f172a;">Anker 65W GaN Fast Wall Charger</span>
                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">Rs. 4,500 each</span>
                </td>
                <td style="padding: 12px 6px; border-bottom: 1px solid #f1f5f9; text-align: center; font-weight: 700;">1</td>
                <td style="padding: 12px 6px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: 800; color: #0f172a;">Rs. 4,500</td>
            </tr>
            <tr>
                <td style="padding: 12px 6px; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-weight: 700; color: #0f172a;">Baseus 100W Braided Type-C Cable</span>
                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">Rs. 1,200 each</span>
                </td>
                <td style="padding: 12px 6px; border-bottom: 1px solid #f1f5f9; text-align: center; font-weight: 700;">2</td>
                <td style="padding: 12px 6px; border-bottom: 1px solid #f1f5f9; text-align: right; font-weight: 800; color: #0f172a;">Rs. 2,400</td>
            </tr>
        `;

        let rendered = rawTpl
            .replace(/{order_number}/g, 'CH-78491')
            .replace(/{customer_name}/g, 'Saad Akbar')
            .replace(/{customer_phone}/g, '0300 1234567')
            .replace(/{customer_email}/g, 'saad@example.com')
            .replace(/{customer_address}/g, 'House 42, Street 8, Sector Y, DHA Phase 3')
            .replace(/{customer_city}/g, 'Lahore')
            .replace(/{payment_method}/g, 'Cash on Delivery (COD)')
            .replace(/{subtotal}/g, '6,900')
            .replace(/{total_amount}/g, '6,900')
            .replace(/{order_items_html}/g, sampleItems)
            .replace(/{order_date}/g, '<?= date("M d, Y h:i A") ?>')
            .replace(/{whatsapp_link}/g, 'https://wa.me/<?= e(function_exists("get_whatsapp_config") ? get_whatsapp_config()["number_clean"] : "923001234567") ?>')
            .replace(/{site_name}/g, 'ChargeHub.pk');

        const frame = document.getElementById('preview-frame');
        const modal = document.getElementById('preview-modal');
        modal.classList.remove('hidden');
        
        frame.srcdoc = rendered;
    }

    function closeTemplatePreview() {
        document.getElementById('preview-modal').classList.add('hidden');
    }

    <?php if (!empty($test_log)): ?>
    // Auto-open test tab if test was just executed
    switchTab('test');
    <?php endif; ?>
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
