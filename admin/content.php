<?php

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

require_admin();
$pdo = get_db();

$content = $pdo->query('SELECT * FROM content ORDER BY topic, sort_order')->fetchAll();
$pageTitle = 'Content (CMS)';
require __DIR__ . '/_admin_header.php';
require __DIR__ . '/../includes/bg-pattern.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;"><i class="fas fa-file-lines"></i> Content Management</h2>
<p class="section-subtext">Edit the lesson content shown on the public HPV and HSV pages. Changes appear immediately — no code deployment needed.</p>

<div class="mini-card">
  <table class="admin-table">
    <thead><tr><th>Topic</th><th>Section</th><th>Title</th><th>Last updated</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($content as $c): ?>
        <tr>
          <td><span class="role-badge"><?= strtoupper(e($c['topic'])) ?></span></td>
          <td><?= e($c['section']) ?></td>
          <td><?= e($c['title']) ?></td>
          <td><?= time_ago($c['updated_at']) ?></td>
          <td><a href="<?= BASE_URL ?>/admin/content_edit.php?id=<?= $c['id'] ?>" style="border:none; background:rgba(44,125,160,0.1); color:var(--teal); padding:0.4rem 0.8rem; border-radius:0.6rem; font-size:0.78rem; font-weight:700;"><i class="fas fa-pen"></i> Edit</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
