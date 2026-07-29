// ============================================================
// HPV·HSV Compass — shared front-end behavior
// Progressive enhancement: every block checks the element exists
// before wiring up, so this file is safe to include on every page.
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

  // ---------- Toasts ----------
  window.showToast = function (message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
  };

  // ---------- Mobile nav toggle ----------
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      const expanded = navLinks.classList.contains('open');
      navToggle.setAttribute('aria-expanded', expanded);
    });
  }

  // ---------- Password show/hide ----------
  document.querySelectorAll('.pw-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.target);
      if (!input) return;
      const icon = btn.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  });

  // ---------- Auth page slideshow ----------
  const authSlideshow = document.getElementById('authSlideshow');
  if (authSlideshow) {
    const slides = authSlideshow.querySelectorAll('.auth-slide');
    const dots = authSlideshow.querySelectorAll('.auth-slide-dot');
    let currentSlide = 0;
    let slideTimer;

    function showSlide(index) {
      slides.forEach((s, i) => s.classList.toggle('active', i === index));
      dots.forEach((d, i) => d.classList.toggle('active', i === index));
      currentSlide = index;
    }

    function nextSlide() {
      showSlide((currentSlide + 1) % slides.length);
    }

    function startAutoRotate() {
      clearInterval(slideTimer);
      slideTimer = setInterval(nextSlide, 5000);
    }

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        showSlide(i);
        startAutoRotate();
      });
    });

    if (slides.length > 1) startAutoRotate();
  }

  // ---------- FAQ accordion ----------
  document.querySelectorAll('.faq-item').forEach(item => {
    item.querySelector('.faq-question')?.addEventListener('click', () => {
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!wasOpen) item.classList.add('open');
    });
  });

  // ---------- Lesson sub-nav (HPV / HSV pages) ----------
  document.querySelectorAll('.lesson-pill[data-section]').forEach(pill => {
    pill.addEventListener('click', () => {
      const topic = pill.dataset.topic;
      const section = pill.dataset.section;
      document.querySelectorAll(`.lesson-pill[data-topic="${topic}"]`).forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      document.querySelectorAll(`.lesson-section[data-topic="${topic}"]`).forEach(s => s.style.display = 'none');
      const target = document.querySelector(`.lesson-section[data-topic="${topic}"][data-section="${section}"]`);
      if (target) target.style.display = 'block';

      // Track progress for logged-in users (section !== quiz; quiz tracked on submit)
      if (section !== 'quiz' && window.IS_LOGGED_IN) {
        fetch(window.BASE_URL + '/api/progress.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `topic=${topic}&section=${section}&csrf_token=${encodeURIComponent(window.CSRF_TOKEN)}`
        }).then(r => r.json()).then(data => {
          if (data.ok && data.pct !== undefined) {
            const fill = document.querySelector(`.lesson-progress-fill[data-topic="${topic}"]`);
            if (fill) fill.style.width = data.pct + '%';
          }
        }).catch(() => {});
      }
    });
  });

  // ---------- Save article buttons ----------
  document.querySelectorAll('.save-article-btn[data-topic]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!window.IS_LOGGED_IN) { showToast('⚠️ Sign in to save articles.', 'error'); return; }
      const topic = btn.dataset.topic, section = btn.dataset.section;
      fetch(window.BASE_URL + '/api/save_article.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `topic=${topic}&section=${section}&csrf_token=${encodeURIComponent(window.CSRF_TOKEN)}`
      }).then(r => r.json()).then(data => {
        if (data.saved) {
          btn.classList.add('saved');
          btn.innerHTML = '<i class="fas fa-bookmark"></i> Saved';
          showToast('🔖 Article saved to your profile.', 'success');
        } else {
          btn.classList.remove('saved');
          btn.innerHTML = '<i class="fas fa-bookmark"></i> Save article';
          showToast('🗑️ Removed from saved articles.', 'info');
        }
      });
    });
  });

  // ---------- Quiz submission ----------
  document.querySelectorAll('.quiz-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const topic = form.dataset.topic;
      const formData = new FormData(form);
      const allAnswered = [...form.querySelectorAll('.quiz-question')].every((q, i) =>
        formData.get(`q${i}`) !== null
      );
      if (!allAnswered) { showToast('⚠️ Please answer every question before submitting.', 'error'); return; }
      formData.append('topic', topic);
      formData.append('csrf_token', window.CSRF_TOKEN);
      fetch(window.BASE_URL + '/api/progress.php?action=quiz', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.ok) {
            showToast(`✅ Quiz submitted — you scored ${data.score}/${data.total}.`, 'success');
            location.reload();
          } else {
            showToast(data.error || 'Something went wrong.', 'error');
          }
        });
    });
  });

  // ---------- Symptom Checker ----------
  const checkSymptomsBtn = document.getElementById('checkSymptomsBtn');
  if (checkSymptomsBtn) {
    checkSymptomsBtn.addEventListener('click', () => {
      const checked = Array.from(document.querySelectorAll('#checkerGrid input:checked')).map(i => i.value);
      const resultBox = document.getElementById('checkerResult');
      if (checked.length === 0) {
        resultBox.innerHTML = `<div class="checker-result"><strong>⚠️ Please select at least one option</strong>, or choose "No symptoms" if you're just being cautious.</div>`;
        return;
      }
      const noneOnly = checked.length === 1 && checked[0] === 'none';
      if (noneOnly) {
        resultBox.innerHTML = `<div class="checker-result low-concern"><strong><i class="fas fa-circle-check"></i> No symptoms reported.</strong><br>That's common — many HPV and HSV infections show no signs at all. Routine screening is still the best way to know your status.</div>`;
      } else {
        resultBox.innerHTML = `<div class="checker-result"><strong><i class="fas fa-triangle-exclamation"></i> A few things worth checking out.</strong><br>What you've described could have several causes and isn't something we can identify here. Consider booking a confidential screening, or ask the AI Assistant for general education while you decide.</div>
          <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="/booking.php" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Book a screening</a>
            <a href="/assistant.php" class="btn btn-outline"><i class="fas fa-robot"></i> Ask the AI Assistant</a>
          </div>`;
      }
    });
  }

  // ---------- Booking Wizard ----------
  const wizardStepsEls = document.querySelectorAll('.wizard-step');
  const wizardPanels = {
    1: document.getElementById('wizardStep1'), 2: document.getElementById('wizardStep2'),
    3: document.getElementById('wizardStep3'), 4: document.getElementById('wizardStep4')
  };
  if (wizardPanels[1]) {
    function goToWizardStep(step) {
      Object.entries(wizardPanels).forEach(([num, panel]) => panel.classList.toggle('active', parseInt(num) === step));
      wizardStepsEls.forEach(el => {
        const n = parseInt(el.dataset.step);
        el.classList.toggle('active', n === step);
        el.classList.toggle('done', n < step);
      });
      if (step === 4) buildConfirmationSummary();
    }

    document.querySelectorAll('.option-card input[type="radio"]').forEach(input => {
      input.addEventListener('change', () => {
        document.querySelectorAll(`input[name="${input.name}"]`).forEach(i => i.closest('.option-card').classList.remove('selected'));
        input.closest('.option-card').classList.add('selected');
      });
    });

    document.getElementById('toStep2Btn')?.addEventListener('click', () => {
      const name = document.getElementById('patientName').value.trim();
      const test = document.querySelector('input[name="test_type"]:checked');
      if (!name) { showToast('⚠️ Please enter your full name.', 'error'); return; }
      if (!test) { showToast('⚠️ Please choose a test.', 'error'); return; }
      goToWizardStep(2);
    });
    document.getElementById('backTo1Btn')?.addEventListener('click', () => goToWizardStep(1));
    document.getElementById('toStep3Btn')?.addEventListener('click', () => {
      if (!document.querySelector('input[name="clinic"]:checked')) { showToast('⚠️ Please choose a clinic.', 'error'); return; }
      goToWizardStep(3);
    });
    document.getElementById('backTo2Btn')?.addEventListener('click', () => goToWizardStep(2));
    document.getElementById('toStep4Btn')?.addEventListener('click', () => {
      if (!document.getElementById('appointmentDate').value) { showToast('⚠️ Please choose a date.', 'error'); return; }
      goToWizardStep(4);
    });
    document.getElementById('backTo3Btn')?.addEventListener('click', () => goToWizardStep(3));

    function buildConfirmationSummary() {
      const name = document.getElementById('patientName').value.trim();
      const test = document.querySelector('input[name="test_type"]:checked')?.value || '';
      const clinic = document.querySelector('input[name="clinic"]:checked')?.value || '';
      const date = document.getElementById('appointmentDate').value;
      document.getElementById('confirmationSummary').innerHTML = `
        <div class="confirmation-row"><span>Patient</span><strong>${name}</strong></div>
        <div class="confirmation-row"><span>Test</span><strong>${test}</strong></div>
        <div class="confirmation-row"><span>Clinic</span><strong>${clinic}</strong></div>
        <div class="confirmation-row"><span>Date</span><strong>${date}</strong></div>
      `;
    }
  }

  // ---------- Cancel appointment ----------
  document.querySelectorAll('.cancel-appt-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!confirm('Cancel this appointment?')) return;
      fetch(window.BASE_URL + '/api/booking_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=cancel&id=${btn.dataset.id}&csrf_token=${encodeURIComponent(window.CSRF_TOKEN)}`
      }).then(r => r.json()).then(data => {
        if (data.ok) { btn.closest('.appointment-card').remove(); showToast('🗑️ Appointment cancelled.', 'info'); }
      });
    });
  });

  // ---------- AI Assistant chat ----------
  const chatMessages = document.getElementById('chatMessages');
  const chatInput = document.getElementById('chatInput');
  const sendChatBtn = document.getElementById('sendChatBtn');
  if (chatMessages && chatInput) {
    function addMessage(text, isUser) {
      const div = document.createElement('div');
      div.className = 'message ' + (isUser ? 'user-msg' : 'ai-msg');
      div.innerText = text;
      chatMessages.appendChild(div);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    function sendMessage() {
      const question = chatInput.value.trim();
      if (!question) return;
      addMessage(question, true);
      chatInput.value = '';
      fetch(window.BASE_URL + '/api/chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `question=${encodeURIComponent(question)}&csrf_token=${encodeURIComponent(window.CSRF_TOKEN || '')}`
      }).then(r => r.json()).then(data => {
        setTimeout(() => addMessage(data.answer, false), 250);
      }).catch(() => addMessage('Sorry, something went wrong reaching the assistant.', false));
    }
    sendChatBtn?.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });
    document.querySelectorAll('.chip').forEach(chip => {
      chip.addEventListener('click', () => { chatInput.value = chip.innerText; sendMessage(); });
    });
  }

  // ---------- Profile sub-nav ----------
  document.querySelectorAll('.profile-subnav .lesson-pill[data-profile-section]').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('.profile-subnav .lesson-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      document.querySelectorAll('.profile-section').forEach(s => s.style.display = 'none');
      document.getElementById('profileSection-' + pill.dataset.profileSection).style.display = 'block';
    });
  });

  // ---------- Settings toggles ----------
  document.querySelectorAll('.setting-toggle').forEach(toggle => {
    toggle.addEventListener('change', () => {
      fetch(window.BASE_URL + '/api/settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `field=${toggle.dataset.field}&value=${toggle.checked ? 1 : 0}&csrf_token=${encodeURIComponent(window.CSRF_TOKEN)}`
      }).then(r => r.json()).then(data => {
        if (data.ok) showToast('✅ Setting updated.', 'success');
      });
    });
  });
});
