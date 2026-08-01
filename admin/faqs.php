<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $q = trim($_POST['question'] ?? '');
        $a = trim($_POST['answer'] ?? '');
        if ($q !== '' && $a !== '') {
            $max = (int) $pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM faqs')->fetchColumn();
            $pdo->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?, ?, ?)')->execute([$q, $a, $max + 1]);
            $_SESSION['flash_success'] = 'FAQ added.';
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
        $_SESSION['flash_success'] = 'FAQ deleted.';
    }
    header('Location: ' . BASE_URL . '/admin/faqs.php');
    exit;
}

$faqs = $pdo->query('SELECT * FROM faqs ORDER BY sort_order')->fetchAll();
$pageTitle = 'Manage FAQs';
require __DIR__ . '/_admin_header.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;"><i class="fas fa-circle-question"></i> Manage FAQs</h2>

<div class="mini-card">
  <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-plus"></i><h2 style="font-size:1.05rem;">Add a new FAQ</h2></div>
  <form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="input-group"><label>Question</label><input type="text" name="question" class="form-plain" style="width:100%; padding:0.85rem 1rem; border:1px solid rgba(44,125,160,0.22); border-radius:1rem;" required></div>
    <div class="input-group"><label>Answer</label><textarea name="answer" rows="3" style="width:100%; padding:0.85rem 1rem; border:1px solid rgba(44,125,160,0.22); border-radius:1rem; font-family:inherit;" required></textarea></div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add FAQ</button>
  </form>
</div>

<div class="mini-card mt-2">
  <?php foreach ($faqs as $f): ?>
    <div class="appointment-card">
      <div style="max-width:80%;"><strong><?= e($f['question']) ?></strong><br><span style="font-size:0.82rem; color:var(--text-muted);"><?= e($f['answer']) ?></span></div>
      <form method="POST" onsubmit="return confirm('Delete this FAQ?');">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $f['id'] ?>">
        <button type="submit" class="btn btn-danger">Delete</button>
      </form>
    </div>
  <?php endforeach; if (empty($faqs)): ?><p class="empty-state">No FAQs yet.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
