<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/ai_responses.php';
header('Content-Type: application/json');

require_login(); // AI assistant is an authenticated feature

$question = trim($_POST['question'] ?? '');
if ($question === '') { echo json_encode(['answer' => 'Please type a question.']); exit; }
if (mb_strlen($question) > 500) { $question = mb_substr($question, 0, 500); }

$answer = get_ai_response($question);

$stmt = get_db()->prepare('INSERT INTO chat_logs (user_id, question, answer) VALUES (?, ?, ?)');
$stmt->execute([current_user_id(), $question, $answer]);

echo json_encode(['answer' => $answer]);
