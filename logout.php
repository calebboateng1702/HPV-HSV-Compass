<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
log_out_user();
header('Location: ' . BASE_URL . '/index.php');
exit;
