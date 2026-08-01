<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id === (int) current_user_id() && in_array($action, ['delete', 'demote'], true)) {
        $_SESSION['flash_error'] = "You can't remove your own admin access from here.";
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $_SESSION['flash_success'] = 'User deleted.';
    } elseif ($action === 'promote') {
        $pdo->prepare('UPDATE users SET role = "admin" WHERE id = ?')->execute([$id]);
        $_SESSION['flash_success'] = 'User promoted to admin.';
    } elseif ($action === 'demote') {
        $pdo->prepare('UPDATE users SET role = "user" WHERE id = ?')->execute([$id]);
        $_SESSION['flash_success'] = 'Admin demoted to regular user.';
    } elseif ($action === 'verify') {
        $pdo->prepare('UPDATE users SET email_verified = 1, verification_token = NULL, verification_token_expires = NULL WHERE id = ?')->execute([$id]);
        $_SESSION['flash_success'] = 'User manually marked as verified.';
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'Manage Users';
require __DIR__ . '/_admin_header.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;"><i class="fas fa-users"></i> Manage Users</h2>

<div class="mini-card">
  <table class="admin-table">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Sign-ins</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="role-badge <?= $u['role'] === 'admin' ? 'admin' : '' ?>"><?= ucfirst($u['role']) ?></span></td>
          <td>
            <?php if ($u['email_verified']): ?>
              <span class="status-badge status-completed"><i class="fas fa-circle-check"></i> Verified</span>
            <?php else: ?>
              <span class="status-badge status-cancelled"><i class="fas fa-circle-xmark"></i> Unverified</span>
            <?php endif; ?>
          </td>
          <td><?= (int)$u['signins'] ?></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div class="table-actions">
              <?php if (!$u['email_verified']): ?>
                <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="verify"><button type="submit">Verify</button></form>
              <?php endif; ?>
              <?php if ($u['role'] === 'user'): ?>
                <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="promote"><button type="submit">Promote</button></form>
              <?php else: ?>
                <form method="POST" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="demote"><button type="submit">Demote</button></form>
              <?php endif; ?>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user? This cannot be undone.');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete"><button type="submit" class="danger">Delete</button></form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
