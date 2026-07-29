<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in()) { http_response_code(401); echo json_encode(['ok' => false]); exit; }
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) { http_response_code(400); echo json_encode(['ok' => false]); exit; }

$allowedFields = ['reminders_enabled', 'digest_enabled'];
$field = $_POST['field'] ?? '';
$value = ($_POST['value'] ?? '0') === '1' ? 1 : 0;

if (!in_array($field, $allowedFields, true)) { echo json_encode(['ok' => false, 'error' => 'Invalid field']); exit; }

$stmt = get_db()->prepare("UPDATE users SET {$field} = ? WHERE id = ?");
$stmt->execute([$value, current_user_id()]);

echo json_encode(['ok' => true]);
