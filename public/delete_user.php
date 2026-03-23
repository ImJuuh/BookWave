<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
require_admin();

$admin = current_user();
$userId = (int)($_POST['user_id'] ?? 0);

if ($userId <= 0) {
  header('Location: /bookwave/public/admin_users.php');
  exit;
}

if ($userId === (int)$admin['id']) {
  header('Location: /bookwave/public/admin_users.php?error=cannot_delete_self');
  exit;
}

$stmt = db()->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ?");
$stmt->execute([$userId]);
$totalRentals = (int)$stmt->fetchColumn();

if ($totalRentals > 0) {
  header('Location: /bookwave/public/admin_users.php?error=user_has_rentals');
  exit;
}

$stmt = db()->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);

header('Location: /bookwave/public/admin_users.php?success=user_deleted');
exit;
