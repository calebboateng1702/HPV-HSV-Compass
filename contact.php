<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Please fill in all fields with a valid email address.';
    } else {
        $stmt = get_db()->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $message]);
        $_SESSION['flash_success'] = "Thanks, {$name} — your message has been sent. We typically reply within one business day.";
    }
    header('Location: ' . BASE_URL . '/contact.php');
    exit;
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<div class="card">
  <div class="section-heading"><i class="fas fa-envelope-open-text"></i><h2>Contact us</h2></div>
  <p class="section-subtext">Questions about the platform, partnerships, or accessibility? Reach out — we typically reply within one business day.</p>
  <div class="grid-2">
    <div>
      <div style="display:flex; gap:12px; margin-bottom:1.1rem;"><i class="fas fa-envelope" style="width:38px; height:38px; border-radius:10px; background:rgba(44,125,160,0.1); color:var(--teal); display:flex; align-items:center; justify-content:center; flex-shrink:0;"></i><div><strong>Email</strong><br>support@hpvhsvcompass.example</div></div>
      <div style="display:flex; gap:12px; margin-bottom:1.1rem;"><i class="fas fa-phone" style="width:38px; height:38px; border-radius:10px; background:rgba(44,125,160,0.1); color:var(--teal); display:flex; align-items:center; justify-content:center; flex-shrink:0;"></i><div><strong>Support line</strong><br>1-800-555-0142 (24/7)</div></div>
      <div style="display:flex; gap:12px;"><i class="fas fa-location-dot" style="width:38px; height:38px; border-radius:10px; background:rgba(44,125,160,0.1); color:var(--teal); display:flex; align-items:center; justify-content:center; flex-shrink:0;"></i><div><strong>Partner clinics</strong><br>Available in most major metro areas</div></div>
    </div>
    <form method="POST" class="form-plain" style="display:flex; flex-direction:column; gap:0.9rem;">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="Your name" required>
      <input type="email" name="email" placeholder="Your email" required>
      <textarea name="message" placeholder="How can we help?" required></textarea>
      <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Send message</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
