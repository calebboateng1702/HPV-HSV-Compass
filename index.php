<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
if (is_logged_in()) { header('Location: ' . BASE_URL . '/dashboard.php'); exit; }
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div>
    <span class="hero-eyebrow"><i class="fas fa-shield-heart"></i> Stigma-free sexual health education</span>
    <h1>Understand <span class="hpv-hsv-emphasis">HPV</span> & <span class="hpv-hsv-emphasis">HSV</span> — clearly, privately, and without stigmatization.</h1>
    <p class="lead">Structured lessons, a knowledge quiz, an educational AI assistant, and a simple path to booking a confidential screening — all in one place.</p>
    <div class="hero-cta">
      <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create free account</a>
      <a href="<?= BASE_URL ?>/hpv.php" class="btn btn-outline"><i class="fas fa-book-open"></i> Start learning</a>
    </div>
    <div class="stats-row">
      <div class="stat-item"><div class="num">200+</div><div class="label">HPV strains covered</div></div>
      <div class="stat-item"><div class="num">6</div><div class="label">Structured lesson steps</div></div>
      <div class="stat-item"><div class="num">24/7</div><div class="label">AI Assistant availability</div></div>
    </div>
  </div>
  <div class="hero-img">
    <img src="assets/images/hero.png" alt="Person learning online">
  </div>
</section>

<div class="card mb-2">
  <div class="section-heading"><i class="fas fa-quote-left"></i><h2>Our philosophy</h2></div>
  <p class="section-subtext">HPV & HSV are common, manageable, and never define your worth. We built this platform to replace stigma with clear, structured, judgment-free learning.</p>
</div>

<div class="card mb-2">
  <div class="section-heading"><i class="fas fa-layer-group"></i><h2>Everything you need in one place</h2></div>
  <p class="section-subtext">Six tools that work together, so you're never stuck wondering what to do next.</p>
  <div class="grid-3">
    <div class="feature-card"><div class="fc-icon"><i class="fas fa-book-open-reader"></i></div><h4>Structured lessons</h4><p>Overview, symptoms, causes, prevention, and vaccination — broken into short, focused sections.</p></div>
    <div class="feature-card"><div class="fc-icon"><i class="fas fa-square-check"></i></div><h4>Knowledge quizzes</h4><p>Check your understanding at the end of each topic and see where to review.</p></div>
    <div class="feature-card"><div class="fc-icon"><i class="fas fa-robot"></i></div><h4>AI assistant</h4><p>Ask plain-language questions about HPV or HSV any time and get an instant, focused answer.</p></div>
    <div class="feature-card"><div class="fc-icon"><i class="fas fa-stethoscope"></i></div><h4>Symptom checker</h4><p>A guided, non-diagnostic tool to help you decide whether it's time to get screened.</p></div>
    <div class="feature-card"><div class="fc-icon"><i class="fas fa-calendar-check"></i></div><h4>Screening booking</h4><p>Pick a test, a clinic, and a time in a simple three-step flow.</p></div>
    <div class="feature-card"><div class="fc-icon"><i class="fas fa-chart-simple"></i></div><h4>Progress dashboard</h4><p>See your completion percentage, saved articles, and upcoming appointments in one view.</p></div>
  </div>
</div>

<div class="card text-center">
  <div class="section-heading" style="justify-content:center;"><i class="fas fa-arrow-right"></i><h2>Ready to start?</h2></div>
  <p class="section-subtext" style="margin: 0 auto 1.4rem;">Create a free account to unlock full lessons, quizzes, and progress tracking.</p>
  <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Create free account</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
