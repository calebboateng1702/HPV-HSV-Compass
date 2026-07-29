<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';

$token = trim($_GET['token'] ?? '');
$pdo = get_db();

$status = 'invalid'; // invalid | expired | success
$userName = '';

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE verification_token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['email_verified']) {
            $status = 'success'; // already verified, treat as success (idempotent link)
            $userName = $user['name'];
        } elseif (strtotime($user['verification_token_expires']) < time()) {
            $status = 'expired';
        } else {
            $upd = $pdo->prepare('UPDATE users SET email_verified = 1, verification_token = NULL, verification_token_expires = NULL WHERE id = ?');
            $upd->execute([$user['id']]);
            $status = 'success';
            $userName = $user['name'];
        }
    }
}

$pageTitle = 'Verify Email';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<div class="auth-shell card text-center" style="margin: 2rem auto;">
  <?php if ($status === 'success'): ?>
    <div class="auth-header">
      <div class="circle-icon" style="background: linear-gradient(135deg, #4CAF6D, #2f7d4f);"><i class="fas fa-circle-check"></i></div>
      <h3>Email verified!</h3>
      <p><?= $userName ? 'Welcome, ' . e($userName) . '. ' : '' ?>Your account is ready to use.</p>
    </div>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-block"><i class="fas fa-arrow-right-to-bracket"></i> Sign in now</a>

  <?php elseif ($status === 'expired'): ?>
    <div class="auth-header">
      <div class="circle-icon" style="background: linear-gradient(135deg, #e0a53a, #c98d28);"><i class="fas fa-clock"></i></div>
      <h3>This link has expired</h3>
      <p>Verification links are valid for 24 hours. Request a new one below.</p>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/resend_verification.php">
      <?= csrf_field() ?>
      <input type="email" name="email" placeholder="Your email address" required class="form-plain" style="width:100%; padding:0.85rem 1rem; border:1px solid rgba(44,125,160,0.22); border-radius:1rem; margin-bottom:1rem;">
      <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-rotate"></i> Send a new link</button>
    </form>

  <?php else: ?>
    <div class="auth-header">
      <div class="circle-icon" style="background: linear-gradient(135deg, #C55A3A, #a34527);"><i class="fas fa-triangle-exclamation"></i></div>
      <h3>Invalid verification link</h3>
      <p>This link doesn't match any pending verification. It may have already been used.</p>
    </div>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-block"><i class="fas fa-arrow-right-to-bracket"></i> Go to sign in</a>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
