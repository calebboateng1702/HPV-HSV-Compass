<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$goal = find_next_goal($user['id']);
$overall = overall_percent($user['id']);
$circumference = 264;
$offset = $circumference - ($overall / 100) * $circumference;

$hpvProgress = get_or_create_progress($user['id'], 'hpv');
$hsvProgress = get_or_create_progress($user['id'], 'hsv');

$pdo = get_db();
$apptStmt = $pdo->prepare('SELECT * FROM bookings WHERE user_id = ? AND status = "scheduled" ORDER BY appointment_date ASC LIMIT 3');
$apptStmt->execute([$user['id']]);
$upcomingAppts = $apptStmt->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<h2 style="color:var(--text-dark); margin-bottom:1.2rem;">Welcome, <?= e(explode(' ', $user['name'])[0]) ?> 👋</h2>

<div class="goal-card">
  <div class="goal-ring-wrap">
    <svg width="96" height="96" viewBox="0 0 96 96">
      <circle class="goal-ring-bg" cx="48" cy="48" r="42" fill="none" stroke-width="8"></circle>
      <circle class="goal-ring-fg" cx="48" cy="48" r="42" fill="none" stroke-width="8" stroke-dasharray="<?= $circumference ?>" stroke-dashoffset="<?= $offset ?>"></circle>
    </svg>
    <div class="goal-ring-label"><?= $overall ?>%</div>
  </div>
  <div class="goal-content">
    <div class="goal-eyebrow"><i class="fas fa-bullseye"></i> Today's goal</div>
    <div class="goal-title"><?= e($goal['title']) ?></div>
    <div class="goal-desc"><?= e($goal['desc']) ?></div>
    <?php if ($goal['topic']): ?>
      <a href="<?= BASE_URL ?>/<?= $goal['topic'] ?>.php" class="goal-btn">Continue Learning <i class="fas fa-arrow-right"></i></a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/booking.php" class="goal-btn">Explore more <i class="fas fa-arrow-right"></i></a>
    <?php endif; ?>
  </div>
</div>

<div class="quick-actions-row">
  <a href="<?= BASE_URL ?>/assistant.php" class="qa-card"><div class="qa-icon"><i class="fas fa-robot"></i></div><div><div class="qa-label">AI Assistant</div><div class="qa-sub">Ask a quick question</div></div></a>
  <a href="<?= BASE_URL ?>/booking.php" class="qa-card"><div class="qa-icon"><i class="fas fa-calendar-check"></i></div><div><div class="qa-label">Book Screening</div><div class="qa-sub">Ready to get tested?</div></div></a>
  <a href="<?= BASE_URL ?>/profile.php" class="qa-card"><div class="qa-icon"><i class="fas fa-user"></i></div><div><div class="qa-label">Your Profile</div><div class="qa-sub">Progress, bookings & more</div></div></a>
</div>

<div class="dash-grid">
  <div>
    <div class="mini-card">
      <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-chart-line"></i><h2 style="font-size:1.15rem;">Your learning progress</h2></div>
      <div class="progress-topic-card">
        <div class="progress-topic-head"><span><i class="fas fa-virus"></i> HPV</span><span><?= topic_percent($hpvProgress) ?>%</span></div>
        <div class="lesson-progress-bar"><div class="lesson-progress-fill" style="width:<?= topic_percent($hpvProgress) ?>%"></div></div>
      </div>
      <div class="progress-topic-card">
        <div class="progress-topic-head"><span><i class="fas fa-thermometer-half"></i> HSV</span><span><?= topic_percent($hsvProgress) ?>%</span></div>
        <div class="lesson-progress-bar"><div class="lesson-progress-fill" style="width:<?= topic_percent($hsvProgress) ?>%"></div></div>
      </div>
      <a href="<?= BASE_URL ?>/profile.php" class="btn btn-outline mt-2">View full progress <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
  <div>
    <div class="mini-card">
      <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-calendar-check"></i><h2 style="font-size:1.15rem;">Upcoming appointments</h2></div>
      <?php if (empty($upcomingAppts)): ?>
        <p class="empty-state" style="padding:1rem;">No appointments booked yet.</p>
        <a href="<?= BASE_URL ?>/booking.php" class="btn btn-primary btn-block">Book a screening</a>
      <?php else: foreach ($upcomingAppts as $appt): ?>
        <div class="appointment-card">
          <div><strong><?= e($appt['test_type']) ?></strong><br><span style="font-size:0.8rem; color:var(--text-muted);"><?= e($appt['clinic']) ?> · <?= e($appt['appointment_date']) ?></span></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
