<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid request']);
    exit;
}

$userId = current_user_id();
$pdo = get_db();
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    // This is a normal <form> POST from booking.php, so we redirect back with a flash message.
    $name = trim($_POST['patient_name'] ?? '');
    $test = trim($_POST['test_type'] ?? '');
    $clinic = trim($_POST['clinic'] ?? '');
    $date = trim($_POST['appointment_date'] ?? '');

    if ($name === '' || $test === '' || $clinic === '' || $date === '') {
        $_SESSION['flash_error'] = 'Please complete every step before confirming your booking.';
        header('Location: ' . BASE_URL . '/booking.php');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO bookings (user_id, patient_name, test_type, clinic, appointment_date) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $name, $test, $clinic, $date]);

    $_SESSION['flash_success'] = "Thank you {$name}, your {$test} at {$clinic} is scheduled for {$date}. A confirmation will be sent to your email.";
    header('Location: ' . BASE_URL . '/booking.php');
    exit;
}

if ($action === 'cancel') {
    // This is an AJAX call from main.js, so we respond with JSON.
    header('Content-Type: application/json');
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    echo json_encode(['ok' => $stmt->rowCount() > 0]);
    exit;
}

header('Content-Type: application/json');
http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Unknown action']);
