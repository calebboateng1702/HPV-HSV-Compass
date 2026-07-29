<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok' => false]); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) { http_response_code(400); echo json_encode(['ok' => false]); exit; }

$userId = current_user_id();
$topic = $_POST['topic'] ?? '';
$section = $_POST['section'] ?? '';
if (!in_array($topic, ['hpv', 'hsv'], true)) { echo json_encode(['ok' => false]); exit; }

$pdo = get_db();
$check = $pdo->prepare('SELECT id FROM saved_articles WHERE user_id = ? AND topic = ? AND section = ?');
$check->execute([$userId, $topic, $section]);
$existing = $check->fetch();

if ($existing) {
    $del = $pdo->prepare('DELETE FROM saved_articles WHERE id = ?');
    $del->execute([$existing['id']]);
    echo json_encode(['ok' => true, 'saved' => false]);
} else {
    $ins = $pdo->prepare('INSERT INTO saved_articles (user_id, topic, section) VALUES (?, ?, ?)');
    $ins->execute([$userId, $topic, $section]);
    echo json_encode(['ok' => true, 'saved' => true]);
}
