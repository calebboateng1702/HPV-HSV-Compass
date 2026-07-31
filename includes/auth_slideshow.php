<?php
/**
 * Shared image slideshow shown alongside the login/register forms.
 * Include this from login.php / register.php inside the two-column auth grid.
 */
$authSlides = [
    [
        'img' => 'assets/images/slide 01.jpg',
    ],
    [
        'img' => 'assets/images/slide 02.jpg',
    ],
    [
        'img' => 'assets/images/slide 03.jpg',
    ],
    [
        'img' => 'assets/images/slide 04.jpg',
    ],
    [
        'img' => 'assets/images/slide 05.jpg',
    ],
];
?>
<div class="auth-slideshow auth-card-radius" id="authSlideshow">
  <?php foreach ($authSlides as $i => $slide): ?>
    <div class="auth-slide <?= $i === 0 ? 'active' : '' ?>">
      <img src="<?= e($slide['img']) ?>" alt="">
      <div class="auth-slide-overlay">
      </div>
    </div>
  <?php endforeach; ?>
  <div class="auth-slide-dots">
    <?php foreach ($authSlides as $i => $slide): ?>
      <button class="auth-slide-dot <?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>" aria-label="Show slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>
</div>
