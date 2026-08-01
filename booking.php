<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$pageTitle = 'Book a Screening';
require __DIR__ . '/includes/header.php';
?>

<div class="card">
  <div class="section-heading"><i class="fas fa-calendar-check"></i><h2>Book a Screening</h2></div>

  <div class="wizard-steps">
    <div class="wizard-step active" data-step="1"><div class="wizard-step-dot">1</div><div class="wizard-step-label">Choose Test</div></div>
    <div class="wizard-step" data-step="2"><div class="wizard-step-dot">2</div><div class="wizard-step-label">Choose Clinic</div></div>
    <div class="wizard-step" data-step="3"><div class="wizard-step-dot">3</div><div class="wizard-step-label">Choose Date</div></div>
    <div class="wizard-step" data-step="4"><div class="wizard-step-dot">4</div><div class="wizard-step-label">Confirmation</div></div>
  </div>

  <form id="bookingForm" method="POST" action="<?= BASE_URL ?>/api/booking_actions.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">

    <!-- STEP 1 -->
    <div class="wizard-panel active" id="wizardStep1">
      <div class="input-group">
        <label>Full name</label>
        <div class="input-icon"><i class="fas fa-user field-icon"></i><input type="text" name="patient_name" id="patientName" value="<?= e($user['name']) ?>" required></div>
      </div>
      <div class="option-card-group">
        <label class="option-card" data-value="HPV DNA Test"><input type="radio" name="test_type" value="HPV DNA Test"><div><div class="option-card-title">HPV DNA Test</div><div class="option-card-sub">Pap smear + HPV co-test</div></div></label>
        <label class="option-card" data-value="HSV-1/2 IgG Blood Test"><input type="radio" name="test_type" value="HSV-1/2 IgG Blood Test"><div><div class="option-card-title">HSV-1/2 IgG Blood Test</div><div class="option-card-sub">Simple blood draw</div></div></label>
        <label class="option-card" data-value="Comprehensive STI Panel"><input type="radio" name="test_type" value="Comprehensive STI Panel"><div><div class="option-card-title">Comprehensive STI Panel</div><div class="option-card-sub">Includes HPV &amp; HSV plus other common STIs</div></div></label>
      </div>
      <div class="wizard-nav-btns"><span></span><button type="button" class="btn btn-primary" id="toStep2Btn">Next: Choose Clinic <i class="fas fa-arrow-right"></i></button></div>
    </div>

    <!-- STEP 2 -->
    <div class="wizard-panel" id="wizardStep2">
      <div class="option-card-group">
        <label class="option-card" data-value="Downtown Wellness Clinic"><input type="radio" name="clinic" value="Downtown Wellness Clinic"><div><div class="option-card-title">Downtown Wellness Clinic</div><div class="option-card-sub">0.8 mi away · Open until 6pm</div></div></label>
        <label class="option-card" data-value="Riverside Health Center"><input type="radio" name="clinic" value="Riverside Health Center"><div><div class="option-card-title">Riverside Health Center</div><div class="option-card-sub">2.1 mi away · Open until 8pm</div></div></label>
        <label class="option-card" data-value="Community Sexual Health Hub"><input type="radio" name="clinic" value="Community Sexual Health Hub"><div><div class="option-card-title">Community Sexual Health Hub</div><div class="option-card-sub">3.4 mi away · Walk-ins welcome</div></div></label>
      </div>
      <div class="wizard-nav-btns"><button type="button" class="btn btn-outline" id="backTo1Btn"><i class="fas fa-arrow-left"></i> Back</button><button type="button" class="btn btn-primary" id="toStep3Btn">Next: Choose Date <i class="fas fa-arrow-right"></i></button></div>
    </div>

    <!-- STEP 3 -->
    <div class="wizard-panel" id="wizardStep3">
      <div class="input-group">
        <label>Preferred date</label>
        <div class="input-icon"><i class="fas fa-calendar field-icon"></i><input type="date" name="appointment_date" id="appointmentDate" min="<?= date('Y-m-d') ?>" required></div>
      </div>
      <div class="wizard-nav-btns"><button type="button" class="btn btn-outline" id="backTo2Btn"><i class="fas fa-arrow-left"></i> Back</button><button type="button" class="btn btn-primary" id="toStep4Btn">Review <i class="fas fa-arrow-right"></i></button></div>
    </div>

    <!-- STEP 4 -->
    <div class="wizard-panel" id="wizardStep4">
      <div class="confirmation-summary" id="confirmationSummary"></div>
      <div class="wizard-nav-btns"><button type="button" class="btn btn-outline" id="backTo3Btn"><i class="fas fa-arrow-left"></i> Back</button><button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Confirm Booking</button></div>
    </div>
  </form>
</div>

<div class="card mt-2">
  <div class="section-heading"><i class="fas fa-list"></i><h2 style="font-size:1.2rem;">Your appointments</h2></div>
  <?php
    $stmt = get_db()->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY appointment_date DESC');
    $stmt->execute([$user['id']]);
    $bookings = $stmt->fetchAll();
  ?>
  <?php if (empty($bookings)): ?>
    <div class="empty-state"><i class="fas fa-calendar-xmark" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>No appointments booked yet.</div>
  <?php else: foreach ($bookings as $b): ?>
    <div class="appointment-card">
      <div>
        <strong><?= e($b['test_type']) ?></strong>
        <span class="status-badge status-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span><br>
        <span style="font-size:0.82rem; color:var(--text-muted);"><?= e($b['clinic']) ?> · <?= e($b['appointment_date']) ?></span>
      </div>
      <?php if ($b['status'] === 'scheduled'): ?>
        <button class="btn btn-danger cancel-appt-btn" data-id="<?= $b['id'] ?>"><i class="fas fa-xmark"></i> Cancel</button>
      <?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
