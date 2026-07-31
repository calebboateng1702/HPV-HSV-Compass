<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';


if (is_logged_in()) { header('Location: ' . BASE_URL . '/dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $errors = [];
    if ($name === '') $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password should be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $check = get_db()->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'An account with that email already exists — try signing in instead.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = get_db()->prepare(
    'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
);

$stmt->execute([$name, $email, $hash]);

$userId = get_db()->lastInsertId();

/* Log the user in immediately */
$_SESSION['user_id'] = $userId;

/* Optional success message */
$_SESSION['flash_success'] = 'Account created successfully. Welcome to HPV-HSV Compass!';

header('Location: ' . BASE_URL . '/dashboard.php');
exit;
    }
}

$pageTitle = 'Create Account';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<div class="auth-page-grid">
  <?php include __DIR__ . '/includes/auth_slideshow.php'; ?>

  <div class="auth-shell card card-radius">
    <div class="auth-header">
      <div class="circle-icon"><i class="fas fa-user-plus"></i></div>
      <h3>Create your account</h3>
      <p>Join for free and get your own learning dashboard</p>
    </div>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="input-group">
        <label>Full name</label>
        <div class="input-icon"><i class="fas fa-user field-icon"></i><input type="text" name="name" placeholder="Jane Doe" required></div>
      </div>
      <div class="input-group">
        <label>Email address</label>
        <div class="input-icon"><i class="fas fa-envelope field-icon"></i><input type="email" name="email" placeholder="you@example.com" required></div>
      </div>
      <div class="input-group">
        <label>Password</label>
        <div class="input-icon"><i class="fas fa-lock field-icon"></i><input type="password" name="password" id="signupPassword" placeholder="Create a password" required><button type="button" class="pw-toggle" data-target="signupPassword"><i class="fas fa-eye"></i></button></div>
      </div>
      <div class="input-group">
        <label>Confirm password</label>
        <div class="input-icon"><i class="fas fa-lock field-icon"></i><input type="password" name="confirm" id="confirmPassword" placeholder="Re-enter password" required><button type="button" class="pw-toggle" data-target="confirmPassword"><i class="fas fa-eye"></i></button></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Create my account</button>
    </form>
    <p class="auth-footer-link">Already have an account? <a href="<?= BASE_URL ?>/login.php" style="color:var(--teal); font-weight:700;">Sign in</a></p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
