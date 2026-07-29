<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';

if (is_logged_in()) { header('Location: ' . BASE_URL . '/dashboard.php'); exit; }

$redirect = $_GET['redirect'] ?? (BASE_URL . '/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['flash_error'] = 'Please enter both email and password.';
    } else {
        $stmt = get_db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Incorrect email or password.';
        } elseif (!$user['email_verified']) {
            $_SESSION['flash_error_html'] = 'Please verify your email before signing in. Check your inbox, or <a href="' . BASE_URL . '/check-email.php?email=' . urlencode($user['email']) . '" style="text-decoration:underline; font-weight:700;">resend the verification email</a>.';
        } else {
            $upd = get_db()->prepare('UPDATE users SET signins = signins + 1, last_login = NOW() WHERE id = ?');
            $upd->execute([$user['id']]);
            log_in_user($user);
            header('Location: ' . ($user['role'] === 'admin' ? (BASE_URL . '/admin/dashboard.php') : $_POST['redirect']));
            exit;
        }
    }
    header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_POST['redirect'] ?? $redirect));
    exit;
}

$pageTitle = 'Sign In';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<div class="auth-page-grid">
  <?php include __DIR__ . '/includes/auth_slideshow.php'; ?>

  <div class="auth-shell card card-radius">
    <div class="auth-header">
      <div class="circle-icon"><i class="fas fa-lock-open"></i></div>
      <h3>Sign in to your account</h3>
      <p>Access your dashboard, progress, and bookings</p>
    </div>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
      <div class="input-group">
        <label>Email address</label>
        <div class="input-icon"><i class="fas fa-envelope field-icon"></i><input type="email" name="email" placeholder="you@example.com" required></div>
      </div>
      <div class="input-group">
        <label>Password</label>
        <div class="input-icon"><i class="fas fa-lock field-icon"></i><input type="password" name="password" id="loginPassword" placeholder="••••••••" required><button type="button" class="pw-toggle" data-target="loginPassword"><i class="fas fa-eye"></i></button></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-arrow-right-to-bracket"></i> Sign in securely</button>
    </form>
    <p class="auth-footer-link">Don't have an account? <a href="<?= BASE_URL ?>/register.php" style="color:var(--teal); font-weight:700;">Create one</a></p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
