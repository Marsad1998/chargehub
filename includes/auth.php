<?php
/**
 * ChargeHub.pk - Authentication & Security Helpers
 * Protects admin routes, prevents CSRF, sanitizes outputs, and validates uploads.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookies across entire domain and all paths
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (!headers_sent()) {
        session_set_cookie_params([
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}

/**
 * Escapes output to prevent Cross-Site Scripting (XSS)
 */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generates URL-friendly slug
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', (string)$text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

/**
 * Checks if admin is logged in
 */
function is_admin_logged_in() {
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
}

/**
 * Requires admin login or redirects to login page
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Generates CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates CSRF Token
 */
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Sets a flash message in session
 */
function set_flash_message($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'text' => $message];
}

/**
 * Retrieves and clears flash messages
 */
function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Validates and handles safe image uploads
 * Returns array with ['success' => bool, 'filename' => string, 'filepath' => string, 'relative_url' => string, 'error' => string]
 */
function handle_image_upload($file, $target_dir, $prefix = 'img_') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error code: ' . ($file['error'] ?? 'missing')];
    }

    // Size limit: 12MB
    if ($file['size'] > 12 * 1024 * 1024) {
        return ['success' => false, 'error' => 'File exceeds 12MB limit'];
    }

    // Allowed extensions
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    if (!in_array($ext, $allowed_exts)) {
        return ['success' => false, 'error' => 'Invalid file extension. Only JPG, PNG, WebP, and SVG are allowed.'];
    }

    // Verify MIME Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'text/plain', 'text/html'];
    // For standard images, require real image mime
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])) {
        return ['success' => false, 'error' => 'Invalid image MIME type: ' . $mime];
    }

    // Ensure target directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Secure randomized filename
    $safe_name = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = rtrim($target_dir, '/') . '/' . $safe_name;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Compute web-accessible path relative to v7
        $web_path = str_replace([dirname(__DIR__) . '/', dirname(__DIR__)], '', $destination);
        $web_path = ltrim($web_path, '/');

        return [
            'success' => true,
            'filename' => $safe_name,
            'filepath' => $destination,
            'relative_url' => $web_path
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file. Check folder permissions.'];
}
