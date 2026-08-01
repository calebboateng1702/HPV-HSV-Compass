<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/messages.php');
    exit;
}

$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'Messages';
require __DIR__ . '/_admin_header.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;"><i class="fas fa-envelope"></i> Contact Messages</h2>

<div class="mini-card">
  <?php foreach ($messages as $m): ?>
    <div class="appointment-card" style="align-items:flex-start;">
      <div>
        <strong><?= e($m['name']) ?></strong> <span style="font-size:0.78rem; color:var(--text-muted);">&lt;<?= e($m['email']) ?>&gt; · <?= time_ago($m['created_at']) ?></span>
        <?php if (!$m['is_read']): ?><span class="status-badge status-scheduled" style="margin-left:6px;">New</span><?php endif; ?>
        <p style="margin:0.5rem 0 0; font-size:0.88rem; color:#2c3e50;"><?= e($m['message']) ?></p>
      </div>
      <?php if (!$m['is_read']): ?>
        <form method="POST"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $m['id'] ?>"><button type="submit" class="btn btn-outline">Mark read</button></form>
      <?php endif; ?>
    </div>
  <?php endforeach; if (empty($messages)): ?><p class="empty-state">No messages yet.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
