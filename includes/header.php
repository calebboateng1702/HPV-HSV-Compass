<?php
/**
 * Shared header. Expects $pageTitle to be set before including.
 * Requires config/database.php and includes/auth.php to already be loaded.
 */
if (!isset($pageTitle)) $pageTitle = 'HPV·HSV Compass';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — HPV·HSV Compass</title>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="icon" href="<?= BASE_URL ?>/assets/images/logo/favicon/favicon.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo/favicon/favicon.ico') ?>" type="image/png" sizes="32x32">
<link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/logo/favicon/favicon.png?v=<?= filemtime(__DIR__ . '/../assets/images/logo/favicon/favicon.ico') ?>" type="16x16">
<link rel="icon" href="<?= BASE_URL ?>/assets/images/logo/favicon/favicon.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<body>
<script>
  window.IS_LOGGED_IN = <?= is_logged_in() ? 'true' : 'false' ?>;
  window.CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
  window.BASE_URL = <?= json_encode(BASE_URL) ?>;
</script>
<?php include __DIR__ . '/navbar.php'; ?>
<main class="page-shell">
<?= flash_message_html() ?>
