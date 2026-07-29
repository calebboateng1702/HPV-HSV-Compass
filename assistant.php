<?php

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'AI Assistant';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/bg-pattern.php';
?>

<div class="card">
  <div class="section-heading"><i class="fas fa-robot"></i><h2>AI Assistant</h2></div>
  <div class="ai-disclaimer"><i class="fas fa-triangle-exclamation"></i> This assistant provides general educational information about HPV and HSV only. It is not a substitute for professional medical advice, diagnosis, or treatment.</div>

  <div class="chat-container">
    <div class="suggested-chips">
      <button class="chip">What is HPV?</button>
      <button class="chip">What is HSV?</button>
      <button class="chip">How is HPV prevented?</button>
      <button class="chip">What are HSV symptoms?</button>
      <button class="chip">Is there a cure?</button>
    </div>
    <div class="chat-messages" id="chatMessages">
      <div class="message ai-msg">👋 Hi! I'm your HPV & HSV assistant. Ask me about full names, symptoms, medications, prevention, transmission, or any related topic. I only answer questions about HPV and HSV.</div>
    </div>
    <div class="chat-input-area">
      <input type="text" id="chatInput" placeholder="Ask about HPV or HSV... e.g., 'What are symptoms of HSV?'">
      <button id="sendChatBtn"><i class="fas fa-paper-plane"></i> Send</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
