<?php $current = basename($_SERVER['SCRIPT_NAME']); ?>
<header class="navbar">
  <a href="<?= BASE_URL ?>/index.php" class="logo">
    <div class="logo-icon"><img src="<?= BASE_URL ?>/assets/images/logo/logo.png" alt="HPV·HSV Compass Logo"></div>
  </a>

  <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
    <i class="fas fa-bars"></i>
  </button>

  <nav class="nav-links" id="navLinks">
    <?php if (is_logged_in()): ?>
      <a href="<?= BASE_URL ?>/dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= BASE_URL ?>/hpv.php" class="<?= $current === 'hpv.php' ? 'active' : '' ?>">HPV</a>
      <a href="<?= BASE_URL ?>/hsv.php" class="<?= $current === 'hsv.php' ? 'active' : '' ?>">HSV</a>
      <a href="<?= BASE_URL ?>/assistant.php" class="<?= $current === 'assistant.php' ? 'active' : '' ?>">AI Assistant</a>
      <a href="<?= BASE_URL ?>/booking.php" class="<?= $current === 'booking.php' ? 'active' : '' ?>">Booking</a>
      <a href="<?= BASE_URL ?>/profile.php" class="<?= $current === 'profile.php' ? 'active' : '' ?>">Profile</a>
      <?php if (is_admin()): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-pill"><i class="fas fa-user-shield"></i> Admin</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/logout.php" class="nav-pill nav-pill-outline"><i class="fas fa-sign-out-alt"></i> Sign out</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Home</a>
      <a href="<?= BASE_URL ?>/about.php" class="<?= $current === 'about.php' ? 'active' : '' ?>">About</a>
      <a href="<?= BASE_URL ?>/hpv.php" class="<?= $current === 'hpv.php' ? 'active' : '' ?>">HPV</a>
      <a href="<?= BASE_URL ?>/hsv.php" class="<?= $current === 'hsv.php' ? 'active' : '' ?>">HSV</a>
      <a href="<?= BASE_URL ?>/faq.php" class="<?= $current === 'faq.php' ? 'active' : '' ?>">FAQ</a>
      <a href="<?= BASE_URL ?>/contact.php" class="<?= $current === 'contact.php' ? 'active' : '' ?>">Contact</a>
      <a href="<?= BASE_URL ?>/login.php" class="nav-pill nav-pill-outline">Sign In</a>
      <a href="<?= BASE_URL ?>/register.php" class="nav-pill">Create Account</a>
    <?php endif; ?>
  </nav>

</header>
