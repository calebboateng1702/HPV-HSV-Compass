<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'change_password') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    if (!password_verify($current, $user['password_hash'])) {
        $_SESSION['flash_error'] = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $_SESSION['flash_error'] = 'New password should be at least 6 characters.';
    } else {
        $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $upd->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        $_SESSION['flash_success'] = 'Password updated.';
    }
    header('Location: ' . BASE_URL . '/settings.php');
    exit;
}

$pageTitle = 'Settings';
require __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="section-heading"><i class="fas fa-gear"></i><h2>Settings</h2></div>

  <div class="setting-row">
    <div><div class="setting-label">Email reminders</div><div class="setting-sub">Get notified about upcoming appointments</div></div>
    <label class="switch"><input type="checkbox" class="setting-toggle" data-field="reminders_enabled" <?= $user['reminders_enabled'] ? 'checked' : '' ?>><span class="switch-track"></span></label>
  </div>
  <div class="setting-row">
    <div><div class="setting-label">Weekly learning digest</div><div class="setting-sub">A short recap of your progress each week</div></div>
    <label class="switch"><input type="checkbox" class="setting-toggle" data-field="digest_enabled" <?= $user['digest_enabled'] ? 'checked' : '' ?>><span class="switch-track"></span></label>
  </div>
</div>

<div class="card mt-2">
  <div class="section-heading"><i class="fas fa-key"></i><h2 style="font-size:1.2rem;">Change password</h2></div>
  <form method="POST" style="max-width:420px;">
    <?= csrf_field() ?>
    <input type="hidden" name="form" value="change_password">
    <div class="input-group"><label>Current password</label><div class="input-icon"><i class="fas fa-lock field-icon"></i><input type="password" name="current_password" required></div></div>
    <div class="input-group"><label>New password</label><div class="input-icon"><i class="fas fa-lock field-icon"></i><input type="password" name="new_password" required></div></div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update password</button>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
