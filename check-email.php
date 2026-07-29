<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/mailer.php';


$email = trim($_GET['email'] ?? '');
$pageTitle = 'Check your email';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<div class="auth-page-grid">
  <?php include __DIR__ . '/includes/auth_slideshow.php'; ?>

  <div class="auth-shell card text-center">
    <div class="auth-header">
      <div class="circle-icon"><i class="fas fa-envelope-circle-check"></i></div>
      <h3>Check your email</h3>
      <p>We've sent a verification link to<br><strong><?= e($email) ?></strong></p>
    </div>

    <p class="section-subtext" style="margin: 0 auto 1.4rem;">Click the link in that email to activate your account. It expires in 24 hours.</p>

    <?php if (MAIL_MODE === 'log'): ?>
      <div class="flash" style="background: rgba(224,165,58,0.12); color:#8a5a3a; text-align:left;">
        <i class="fas fa-flask"></i> <strong>Local dev mode:</strong> no real email was sent. Open
        <code>storage/mail_log/</code> in the project folder and find the newest
        <code>.html</code> file for <?= e($email) ?> — it contains the actual verification link.
        Switch <code>MAIL_MODE</code> in <code>includes/mailer.php</code> to <code>'smtp'</code> once
        your server can send real mail.
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/resend_verification.php" style="margin-top:1rem;">
      <?= csrf_field() ?>
      <input type="hidden" name="email" value="<?= e($email) ?>">
      <button type="submit" class="btn btn-outline btn-block"><i class="fas fa-rotate"></i> Resend verification email</button>
    </form>

    <p class="auth-footer-link">Already verified? <a href="<?= BASE_URL ?>/login.php" style="color:var(--teal); font-weight:700;">Sign in</a></p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
