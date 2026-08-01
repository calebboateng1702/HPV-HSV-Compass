<?php
/**
 * Renders a full HPV or HSV lesson page. Expects $topic = 'hpv' | 'hsv' to be set
 * before including this file.
 */
$pdo = get_db();
$stmt = $pdo->prepare('SELECT * FROM content WHERE topic = ? ORDER BY sort_order ASC');
$stmt->execute([$topic]);
$sections = $stmt->fetchAll();

$qStmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE topic = ? ORDER BY sort_order ASC');
$qStmt->execute([$topic]);
$quizQuestions = $qStmt->fetchAll();

$labels = ['hpv' => 'HPV', 'hsv' => 'HSV'];
$fullTitles = ['hpv' => 'Human Papillomavirus (HPV)', 'hsv' => 'Herpes Simplex Virus (HSV)'];
$topicIcon = $topic === 'hpv' ? 'fa-virus' : 'fa-thermometer-half';

$progress = null;
$savedSet = [];
if (is_logged_in()) {
    $progress = get_or_create_progress(current_user_id(), $topic);
    $savedStmt = $pdo->prepare('SELECT section FROM saved_articles WHERE user_id = ? AND topic = ?');
    $savedStmt->execute([current_user_id(), $topic]);
    $savedSet = array_column($savedStmt->fetchAll(), 'section');
}
$pct = $progress ? topic_percent($progress) : 0;

$heroContent = [
    'hpv' => [
        'eyebrow' => 'Understand the virus',
        'lead' => "HPV is one of the most common sexually transmitted infections — and one of the most misunderstood. Learn how it spreads, how it's prevented, and what the vaccine actually does.",
        'image' => 'https://images.pexels.com/photos/7659873/pexels-photo-7659873.jpeg?auto=compress&cs=tinysrgb&w=700',
    ],
    'hsv' => [
        'eyebrow' => 'Understand the virus',
        'lead' => 'HSV affects millions of people worldwide and is entirely manageable. Learn the facts about symptoms, treatment, and living well with it.',
        'image' => 'https://images.pexels.com/photos/7659874/pexels-photo-7659874.jpeg?auto=compress&cs=tinysrgb&w=700',
    ],
];

$pageTitle = $labels[$topic];
require __DIR__ . '/header.php';
?>

<section class="hero topic-hero">
  <div>
    <span class="hero-eyebrow"><i class="fas <?= $topicIcon ?>"></i> <?= e($heroContent[$topic]['eyebrow']) ?></span>
   <h1 class="hpv-hsv-hero-title">All About <span class="topic-hero-emphasis"><?= e($labels[$topic]) ?></span></h1>
   <p class="lead"><?= e($heroContent[$topic]['lead']) ?></p>
    <div class="hero-cta">
      <a href="#lessonCard" class="btn btn-primary"><i class="fas fa-book-open"></i> Jump to lessons</a>
      <?php if (!is_logged_in()): ?>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> Create free account</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="hero-portrait">
    <img src="<?= e($heroContent[$topic]['image']) ?>" alt="">
  </div>
</section>

<div class="card" id="lessonCard">
  <div class="section-heading"><i class="fas <?= $topicIcon ?>"></i><h2><?= e($fullTitles[$topic]) ?></h2></div>

  <?php if (!is_logged_in()): ?>
    <div class="flash flash-error" style="background: rgba(44,125,160,0.1); color: var(--text-dark);">
      <i class="fas fa-lock"></i> <a href="<?= BASE_URL ?>/login.php" style="color:var(--teal); font-weight:700;">Sign in</a> or
      <a href="<?= BASE_URL ?>/register.php" style="color:var(--teal); font-weight:700;">create a free account</a> to track your progress and save articles.
    </div>
  <?php endif; ?>

  <div class="lesson-subnav">
    <?php foreach ($sections as $i => $sec): ?>
      <button class="lesson-pill <?= $i === 0 ? 'active' : '' ?>" data-topic="<?= $topic ?>" data-section="<?= e($sec['section']) ?>">
        <?php if ($progress && $progress[$sec['section'] . '_done']): ?><i class="fas fa-circle-check" style="color:#4CAF6D;"></i><?php endif; ?>
        <?= e($sec['title']) ?>
      </button>
    <?php endforeach; ?>
    <button class="lesson-pill" data-topic="<?= $topic ?>" data-section="quiz">
      <?php if ($progress && $progress['quiz_done']): ?><i class="fas fa-circle-check" style="color:#4CAF6D;"></i><?php endif; ?>
      Quiz
    </button>
  </div>

  <?php if ($progress): ?>
    <div class="lesson-progress-bar"><div class="lesson-progress-fill" data-topic="<?= $topic ?>" style="width:<?= $pct ?>%"></div></div>
    <p style="font-size:0.8rem; color:var(--text-muted); margin:-0.6rem 0 1rem;"><?= $pct ?>% complete</p>
  <?php endif; ?>

  <?php foreach ($sections as $i => $sec): $isSaved = in_array($sec['section'], $savedSet); ?>
    <div class="lesson-section" data-topic="<?= $topic ?>" data-section="<?= e($sec['section']) ?>" style="display: <?= $i === 0 ? 'block' : 'none' ?>;">
      <h3><i class="fas <?= e($sec['icon']) ?>"></i> <?= e($fullTitles[$topic]) ?> — <?= e($sec['title']) ?></h3>
      <?= $sec['body'] /* trusted admin-authored HTML from the content table */ ?>
      <div class="lesson-body-actions">
        <?php if (is_logged_in()): ?>
          <button class="save-article-btn <?= $isSaved ? 'saved' : '' ?>" data-topic="<?= $topic ?>" data-section="<?= e($sec['section']) ?>">
            <i class="fas fa-bookmark"></i> <?= $isSaved ? 'Saved' : 'Save article' ?>
          </button>
        <?php else: ?><span></span><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- QUIZ SECTION -->
  <div class="lesson-section" data-topic="<?= $topic ?>" data-section="quiz" style="display:none;">
    <?php if ($progress && $progress['quiz_done']):
      $passed = $progress['quiz_score'] >= ceil(count($quizQuestions) * 0.6);
      $nextTopic = $topic === 'hpv' ? 'hsv' : null; ?>
      <h3><i class="fas <?= $topicIcon ?>"></i> <?= $labels[$topic] ?> Quiz</h3>
      <div class="quiz-result">
        <i class="fas <?= $passed ? 'fa-circle-check' : 'fa-rotate' ?>" style="font-size:1.8rem; color:<?= $passed ? '#4CAF6D' : '#e0a53a' ?>;"></i>
        <div class="quiz-score"><?= (int)$progress['quiz_score'] ?>/<?= count($quizQuestions) ?></div>
        <p><?= $passed ? "Nice work — you've got a solid grasp of the basics." : 'Worth another look — review the lesson sections above.' ?></p>
        <div class="completed-badge"><i class="fas fa-circle-check"></i> Completed ✓</div>
        <?php if ($nextTopic): ?>
          <div class="mt-2"><a href="<?= BASE_URL ?>/hsv.php" class="btn btn-primary">Continue to HSV <i class="fas fa-arrow-right"></i></a></div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <h3><i class="fas <?= $topicIcon ?>"></i> <?= $labels[$topic] ?> Quiz</h3>
      <?php if (!is_logged_in()): ?>
        <p class="section-subtext">Sign in to save your quiz score to your profile.</p>
      <?php else: ?>
        <p class="section-subtext">Answer all questions, then submit to see your score.</p>
      <?php endif; ?>
      <form class="quiz-form" data-topic="<?= $topic ?>">
        <?php foreach ($quizQuestions as $i => $q): ?>
          <div class="quiz-question">
            <h4><?= $i + 1 ?>. <?= e($q['question']) ?></h4>
            <label class="quiz-option"><input type="radio" name="q<?= $i ?>" value="a"> <?= e($q['option_a']) ?></label>
            <label class="quiz-option"><input type="radio" name="q<?= $i ?>" value="b"> <?= e($q['option_b']) ?></label>
            <?php if ($q['option_c']): ?><label class="quiz-option"><input type="radio" name="q<?= $i ?>" value="c"> <?= e($q['option_c']) ?></label><?php endif; ?>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Quiz</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/footer.php'; ?>
