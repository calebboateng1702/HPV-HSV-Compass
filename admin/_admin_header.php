<?php
/** Wraps admin pages with a sidebar layout. Requires $pageTitle set, and require_admin() already called. */
require __DIR__ . '/../includes/header.php';
$adminCurrent = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $adminCurrent === 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a>
    <a href="<?= BASE_URL ?>/admin/users.php" class="<?= $adminCurrent === 'users.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a>
    <a href="<?= BASE_URL ?>/admin/bookings.php" class="<?= $adminCurrent === 'bookings.php' ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> Bookings</a>
    <a href="<?= BASE_URL ?>/admin/content.php" class="<?= $adminCurrent === 'content.php' || $adminCurrent === 'content_edit.php' ? 'active' : '' ?>"><i class="fas fa-file-lines"></i> Content (CMS)</a>
    <a href="<?= BASE_URL ?>/admin/faqs.php" class="<?= $adminCurrent === 'faqs.php' ? 'active' : '' ?>"><i class="fas fa-circle-question"></i> FAQs</a>
    <a href="<?= BASE_URL ?>/admin/messages.php" class="<?= $adminCurrent === 'messages.php' ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Messages</a>
    <a href="<?= BASE_URL ?>/dashboard.php"><i class="fas fa-arrow-left"></i> Back to site</a>
  </aside>
  <div class="admin-content">
