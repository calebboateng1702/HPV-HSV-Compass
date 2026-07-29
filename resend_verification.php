<?php
require __DIR__ . '/includes/bg-pattern.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
verify_csrf();

$email = trim(strtolower($_POST['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Please enter a valid email address.';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$stmt = get_db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Don't reveal whether an account exists — same message and redirect either way.
$genericMessage = "If an account exists for {$email}, a verification email is on its way.";

if (!$user) {
    $_SESSION['flash_success'] = $genericMessage;
    header('Location: ' . BASE_URL . '/check-email.php?email=' . urlencode($email));
    exit;
}

if ($user['email_verified']) {
    $_SESSION['flash_success'] = 'That email is already verified — you can sign in.';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$result = send_verification_email($user);
if (!$result['ok']) {
    $_SESSION['flash_error'] = $result['error'];
} else {
    $_SESSION['flash_success'] = $genericMessage;
}

header('Location: ' . BASE_URL . '/check-email.php?email=' . urlencode($email));
exit;
