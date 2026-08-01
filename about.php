<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
$pageTitle = 'About';
require __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="grid-2">
    <div>
      <div class="section-heading"><i class="fas fa-circle-info"></i><h2>About the platform</h2></div>
      <p class="section-subtext">Check mate is a digital sexual health education platform built to replace stigma with clear, structured, judgment-free learning — plus the practical tools to act on it.</p>
      <ul style="display:flex; flex-direction:column; gap:0.9rem; margin-top:1rem;">
        <li style="display:flex; gap:10px;"><i class="fas fa-graduation-cap" style="color:var(--teal); margin-top:3px;"></i> Structured lessons on HPV and HSV, written in plain language and reviewed for accuracy.</li>
        <li style="display:flex; gap:10px;"><i class="fas fa-chart-line" style="color:var(--teal); margin-top:3px;"></i> Personal progress tracking so you always know what you've learned and what's next.</li>
        <li style="display:flex; gap:10px;"><i class="fas fa-robot" style="color:var(--teal); margin-top:3px;"></i> An educational AI assistant available whenever a question comes up.</li>
        <li style="display:flex; gap:10px;"><i class="fas fa-calendar-check" style="color:var(--teal); margin-top:3px;"></i> A simple path to booking a confidential screening when you're ready.</li>
      </ul>
    </div>
    <div class="hero-img">
      <img src="https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg?auto=compress&cs=tinysrgb&w=700" alt="Healthcare education">
    </div>
  </div>
</div>

<div class="card mt-2">
  <div class="section-heading"><i class="fas fa-triangle-exclamation"></i><h2>Medical disclaimer</h2></div>
  <p class="section-subtext" style="margin-bottom:0;">This platform provides general educational information only. It is not a substitute for professional medical advice, diagnosis, or treatment. Always seek the advice of a qualified healthcare provider with questions about a medical condition. The AI Assistant in particular answers from a fixed set of educational content and cannot evaluate your individual symptoms.</p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
