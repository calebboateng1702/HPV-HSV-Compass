<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['scheduled', 'completed', 'cancelled'], true)) {
        $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$status, $id]);
        $_SESSION['flash_success'] = 'Booking status updated.';
    }
    header('Location: ' . BASE_URL . '/admin/bookings.php');
    exit;
}

$bookings = $pdo->query('
  SELECT b.*, u.name AS user_name, u.email AS user_email
  FROM bookings b JOIN users u ON u.id = b.user_id
  ORDER BY b.appointment_date DESC')->fetchAll();

$pageTitle = 'Manage Bookings';
require __DIR__ . '/_admin_header.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;"><i class="fas fa-calendar-check"></i> Manage Bookings</h2>

<div class="mini-card">
  <table class="admin-table">
    <thead><tr><th>Patient</th><th>Test</th><th>Clinic</th><th>Date</th><th>Status</th><th>Update</th></tr></thead>
    <tbody>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td><?= e($b['patient_name']) ?><br><span style="font-size:0.75rem; color:var(--text-muted);"><?= e($b['user_email']) ?></span></td>
          <td><?= e($b['test_type']) ?></td>
          <td><?= e($b['clinic']) ?></td>
          <td><?= e($b['appointment_date']) ?></td>
          <td><span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst($b['status']) ?></span></td>
          <td>
            <form method="POST" style="display:flex; gap:6px;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $b['id'] ?>">
              <select name="status" style="border-radius:0.6rem; border:1px solid rgba(44,125,160,0.25); padding:4px 8px; font-size:0.8rem;">
                <option value="scheduled" <?= $b['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="completed" <?= $b['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
              <button type="submit" style="border:none; background:rgba(44,125,160,0.1); color:var(--teal); padding:0.4rem 0.8rem; border-radius:0.6rem; font-size:0.78rem; font-weight:700; cursor:pointer;">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (empty($bookings)): ?>
        <tr><td colspan="6"><div class="empty-state">No bookings yet.</div></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
