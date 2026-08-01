<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'update_profile') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Please provide a valid name and email.';
    } else {
        $dupe = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $dupe->execute([$email, $user['id']]);
        if ($dupe->fetch()) {
            $_SESSION['flash_error'] = 'That email is already in use by another account.';
        } else {
            $upd = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
            $upd->execute([$name, $email, $phone, $user['id']]);
            $_SESSION['name'] = $name;
            $_SESSION['flash_success'] = 'Profile updated.';
        }
    }
    header('Location: ' . BASE_URL . '/profile.php');
    exit;
}

$hpvProgress = get_or_create_progress($user['id'], 'hpv');
$hsvProgress = get_or_create_progress($user['id'], 'hsv');

$bookingsStmt = $pdo->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY appointment_date DESC');
$bookingsStmt->execute([$user['id']]);
$bookings = $bookingsStmt->fetchAll();

$savedStmt = $pdo->prepare('
  SELECT sa.*, c.title, c.topic AS ctopic FROM saved_articles sa
  JOIN content c ON c.topic = sa.topic AND c.section = sa.section
  WHERE sa.user_id = ? ORDER BY sa.saved_at DESC');
$savedStmt->execute([$user['id']]);
$savedArticles = $savedStmt->fetchAll();

$chatCountStmt = $pdo->prepare('SELECT COUNT(*) FROM chat_logs WHERE user_id = ?');
$chatCountStmt->execute([$user['id']]);
$chatCount = (int) $chatCountStmt->fetchColumn();

$bookingCount = count($bookings);

$pageTitle = 'Profile';
require __DIR__ . '/includes/header.php';
?>

<div class="profile-hero">
  <div class="profile-avatar"><?= e(initials_from_name($user['name'])) ?></div>
  <div class="profile-hero-info">
    <h2><?= e($user['name']) ?></h2>
    <p><?= e($user['email']) ?></p>
    <span class="member-badge"><i class="fas fa-shield-heart"></i> Member since <?= date('M j, Y', strtotime($user['created_at'])) ?></span>
  </div>
</div>

<div class="profile-subnav">
  <button class="lesson-pill active" data-profile-section="overview"><i class="fas fa-id-card"></i> Overview</button>
  <button class="lesson-pill" data-profile-section="progress"><i class="fas fa-chart-line"></i> Progress</button>
  <button class="lesson-pill" data-profile-section="appointments"><i class="fas fa-calendar-check"></i> Appointments</button>
  <button class="lesson-pill" data-profile-section="saved"><i class="fas fa-bookmark"></i> Saved Articles</button>
  <a href="<?= BASE_URL ?>/settings.php" class="lesson-pill"><i class="fas fa-gear"></i> Settings</a>
</div>

<!-- OVERVIEW -->
<div class="profile-section" id="profileSection-overview">
  <div class="dash-grid">
    <div class="mini-card">
      <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-id-card"></i><h2 style="font-size:1.15rem;">Account details</h2></div>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="update_profile">
        <div class="input-group"><label>Full name</label><div class="input-icon"><i class="fas fa-user field-icon"></i><input type="text" name="name" value="<?= e($user['name']) ?>"></div></div>
        <div class="input-group"><label>Email address</label><div class="input-icon"><i class="fas fa-envelope field-icon"></i><input type="email" name="email" value="<?= e($user['email']) ?>"></div></div>
        <div class="input-group"><label>Phone (optional)</label><div class="input-icon"><i class="fas fa-phone field-icon"></i><input type="tel" name="phone" value="<?= e($user['phone']) ?>" placeholder="+1 (555) 000-0000"></div></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save changes</button>
      </form>
    </div>
    <div class="mini-card">
      <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-chart-simple"></i><h2 style="font-size:1.15rem;">Your activity</h2></div>
      <div class="profile-stats">
        <div class="pstat"><div class="pstat-number"><?= (int)$user['signins'] ?></div><div class="pstat-label">Sign-ins</div></div>
        <div class="pstat"><div class="pstat-number"><?= $bookingCount ?></div><div class="pstat-label">Tests booked</div></div>
        <div class="pstat"><div class="pstat-number"><?= $chatCount ?></div><div class="pstat-label">AI questions asked</div></div>
      </div>
      <a href="<?= BASE_URL ?>/hpv.php" class="btn btn-outline btn-block mt-2"><i class="fas fa-virus"></i> Continue learning</a>
    </div>
  </div>
</div>

<!-- PROGRESS -->
<div class="profile-section" id="profileSection-progress" style="display:none;">
  <div class="mini-card">
    <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-chart-line"></i><h2 style="font-size:1.15rem;">Learning progress</h2></div>
    <div class="progress-topic-card">
      <div class="progress-topic-head"><span><i class="fas fa-virus"></i> HPV</span><span><?= topic_percent($hpvProgress) ?>%</span></div>
      <div class="lesson-progress-bar"><div class="lesson-progress-fill" style="width:<?= topic_percent($hpvProgress) ?>%"></div></div>
      <div style="font-size:0.8rem; color:var(--text-muted); margin-top:6px;"><?= $hpvProgress['quiz_done'] ? 'Quiz completed — scored ' . (int)$hpvProgress['quiz_score'] . '/3' : 'Quiz not attempted yet' ?></div>
    </div>
    <div class="progress-topic-card">
      <div class="progress-topic-head"><span><i class="fas fa-thermometer-half"></i> HSV</span><span><?= topic_percent($hsvProgress) ?>%</span></div>
      <div class="lesson-progress-bar"><div class="lesson-progress-fill" style="width:<?= topic_percent($hsvProgress) ?>%"></div></div>
      <div style="font-size:0.8rem; color:var(--text-muted); margin-top:6px;"><?= $hsvProgress['quiz_done'] ? 'Quiz completed — scored ' . (int)$hsvProgress['quiz_score'] . '/3' : 'Quiz not attempted yet' ?></div>
    </div>
  </div>
</div>

<!-- APPOINTMENTS -->
<div class="profile-section" id="profileSection-appointments" style="display:none;">
  <div class="mini-card">
    <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-calendar-check"></i><h2 style="font-size:1.15rem;">Your appointments</h2></div>
    <?php if (empty($bookings)): ?>
      <div class="empty-state"><i class="fas fa-calendar-xmark" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>No appointments booked yet.</div>
    <?php else: foreach ($bookings as $b): ?>
      <div class="appointment-card">
        <div><strong><?= e($b['test_type']) ?></strong> <span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span><br><span style="font-size:0.82rem; color:var(--text-muted);"><?= e($b['clinic']) ?> · <?= e($b['appointment_date']) ?></span></div>
        <?php if ($b['status'] === 'scheduled'): ?><button class="btn btn-danger cancel-appt-btn" data-id="<?= $b['id'] ?>"><i class="fas fa-xmark"></i> Cancel</button><?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- SAVED ARTICLES -->
<div class="profile-section" id="profileSection-saved" style="display:none;">
  <div class="mini-card">
    <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-bookmark"></i><h2 style="font-size:1.15rem;">Saved articles</h2></div>
    <?php if (empty($savedArticles)): ?>
      <div class="empty-state"><i class="fas fa-bookmark" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>Nothing saved yet.</div>
    <?php else: foreach ($savedArticles as $a): ?>
      <div class="saved-article-card">
        <div><strong><?= strtoupper(e($a['ctopic'])) ?> — <?= e($a['title']) ?></strong></div>
        <a href="<?= BASE_URL ?>/<?= e($a['topic']) ?>.php" class="btn btn-outline"><i class="fas fa-arrow-right"></i> Open</a>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
