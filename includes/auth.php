<?php
/**
 * Authentication & session helpers.
 * Include this after config/database.php on every page that needs
 * to know whether someone is logged in.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Fetch the full current user row (fresh from DB). Returns null if not logged in. */
function current_user() {
    if (!is_logged_in()) return null;
    static $cached = null;
    if ($cached !== null) return $cached;
    $stmt = get_db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([current_user_id()]);
    $cached = $stmt->fetch();
    return $cached;
}

/** Redirect to login if the visitor is not authenticated. */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please sign in to continue.';
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
        exit;
    }
}

/** Redirect non-admins away from admin pages. */
function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('403 — Admin access only.');
    }
}

function log_in_user(array $user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
}

function log_out_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** Simple CSRF token helpers */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

function flash_get($key) {
    if (!empty($_SESSION[$key])) {
        $val = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $val;
    }
    return null;
}
