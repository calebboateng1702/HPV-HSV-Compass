<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = get_db();
$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
$totalBookings = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$pendingBookings = (int) $pdo->query('SELECT COUNT(*) FROM bookings WHERE status = "scheduled"')->fetchColumn();
$unreadMessages = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();

$recentUsers = $pdo->query('SELECT * FROM users WHERE role = "user" ORDER BY created_at DESC LIMIT 5')->fetchAll();
$recentBookings = $pdo->query('SELECT b.*, u.name AS user_name FROM bookings b JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC LIMIT 5')->fetchAll();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/_admin_header.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;"><i class="fas fa-user-shield"></i> Admin Overview</h2>

<div class="admin-stats">
  <div class="admin-stat-card"><div class="num"><?= $totalUsers ?></div><div class="label">Registered Users</div></div>
  <div class="admin-stat-card"><div class="num"><?= $totalBookings ?></div><div class="label">Total Bookings</div></div>
  <div class="admin-stat-card"><div class="num"><?= $pendingBookings ?></div><div class="label">Scheduled Screenings</div></div>
  <div class="admin-stat-card"><div class="num"><?= $unreadMessages ?></div><div class="label">Unread Messages</div></div>
</div>

<div class="dash-grid">
  <div class="mini-card">
    <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-users"></i><h2 style="font-size:1.1rem;">Recent sign-ups</h2></div>
    <?php foreach ($recentUsers as $u): ?>
      <div class="appointment-card"><div><strong><?= e($u['name']) ?></strong><br><span style="font-size:0.8rem; color:var(--text-muted);"><?= e($u['email']) ?> · <?= time_ago($u['created_at']) ?></span></div></div>
    <?php endforeach; if (empty($recentUsers)): ?><p class="empty-state">No users yet.</p><?php endif; ?>
    <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline mt-2">Manage all users <i class="fas fa-arrow-right"></i></a>
  </div>
  <div class="mini-card">
    <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-calendar-check"></i><h2 style="font-size:1.1rem;">Recent bookings</h2></div>
    <?php foreach ($recentBookings as $b): ?>
      <div class="appointment-card"><div><strong><?= e($b['test_type']) ?></strong> <span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst($b['status']) ?></span><br><span style="font-size:0.8rem; color:var(--text-muted);"><?= e($b['user_name']) ?> · <?= e($b['appointment_date']) ?></span></div></div>
    <?php endforeach; if (empty($recentBookings)): ?><p class="empty-state">No bookings yet.</p><?php endif; ?>
    <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn btn-outline mt-2">Manage all bookings <i class="fas fa-arrow-right"></i></a>
  </div>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
