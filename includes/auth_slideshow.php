<?php
/**
 * Shared image slideshow shown alongside the login/register forms.
 * Include this from login.php / register.php inside the two-column auth grid.
 */
$authSlides = [
    [
        'img' => 'assets/images/slide 01.jpg',
        'quote' => 'Clear, judgment-free education — at your own pace.',
    ],
    [
        'img' => 'assets/images/slide 02.jpg',
        'quote' => 'Confidential screenings, booked in minutes.',
    ],
    [
        'img' => 'https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg?auto=compress&cs=tinysrgb&w=900',
        'quote' => 'Vaccination and prevention, explained simply.',
    ],
    [
        'img' => 'https://images.pexels.com/photos/7659874/pexels-photo-7659874.jpeg?auto=compress&cs=tinysrgb&w=900',
        'quote' => 'An AI assistant, ready whenever you have questions.',
    ],
];
?>
<div class="auth-slideshow auth-card-radius" id="authSlideshow">
  <?php foreach ($authSlides as $i => $slide): ?>
    <div class="auth-slide <?= $i === 0 ? 'active' : '' ?>">
      <img src="<?= e($slide['img']) ?>" alt="">
      <div class="auth-slide-overlay">
        <p class="auth-slide-quote"><?= e($slide['quote']) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="auth-slide-dots">
    <?php foreach ($authSlides as $i => $slide): ?>
      <button class="auth-slide-dot <?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>" aria-label="Show slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</div>
