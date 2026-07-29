<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok' => false, 'error' => 'Not logged in']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false]); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(400); echo json_encode(['ok' => false, 'error' => 'Invalid request']); exit;
}

$userId = current_user_id();
$topic = $_POST['topic'] ?? '';
if (!in_array($topic, ['hpv', 'hsv'], true)) { echo json_encode(['ok' => false, 'error' => 'Invalid topic']); exit; }

$pdo = get_db();
get_or_create_progress($userId, $topic); // ensure row exists

if (($_GET['action'] ?? '') === 'quiz') {
    // Score the quiz server-side against quiz_questions
    $qStmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE topic = ? ORDER BY sort_order ASC');
    $qStmt->execute([$topic]);
    $questions = $qStmt->fetchAll();

    $score = 0;
    foreach ($questions as $i => $q) {
        $answer = $_POST['q' . $i] ?? null;
        if ($answer === null) { echo json_encode(['ok' => false, 'error' => 'Please answer every question.']); exit; }
        if (strtolower($answer) === strtolower($q['correct_option'])) $score++;
    }

    $upd = $pdo->prepare('UPDATE learning_progress SET quiz_done = 1, quiz_score = ? WHERE user_id = ? AND topic = ?');
    $upd->execute([$score, $userId, $topic]);

    echo json_encode(['ok' => true, 'score' => $score, 'total' => count($questions)]);
    exit;
}

// Otherwise: mark a single section as viewed
$section = $_POST['section'] ?? '';
$validSections = ['overview', 'symptoms', 'causes', 'prevention', 'vaccination'];
if (!in_array($section, $validSections, true)) { echo json_encode(['ok' => false, 'error' => 'Invalid section']); exit; }

$column = $section . '_done';
$stmt = $pdo->prepare("UPDATE learning_progress SET {$column} = 1 WHERE user_id = ? AND topic = ?");
$stmt->execute([$userId, $topic]);

$progress = get_or_create_progress($userId, $topic);
echo json_encode(['ok' => true, 'pct' => topic_percent($progress)]);
