<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
$pageTitle = 'FAQ';
require __DIR__ . '/includes/header.php';

$faqs = get_db()->query('SELECT * FROM faqs ORDER BY sort_order ASC')->fetchAll();
?>

<div class="card">
  <div class="section-heading"><i class="fas fa-circle-question"></i><h2>Frequently Asked Questions</h2></div>
  <p class="section-subtext">Straightforward answers to the questions we hear most.</p>

  <?php if (empty($faqs)): ?>
    <p class="empty-state">No FAQs published yet.</p>
  <?php else: foreach ($faqs as $faq): ?>
    <div class="faq-item">
      <div class="faq-question"><?= e($faq['question']) ?> <i class="fas fa-chevron-down"></i></div>
      <div class="faq-answer"><?= e($faq['answer']) ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class="card mt-2 text-center">
  <p class="section-subtext" style="margin:0 0 1rem;">Still have questions?</p>
  <a href="<?= BASE_URL ?>/contact.php" class="btn btn-primary"><i class="fas fa-envelope"></i> Contact us</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
