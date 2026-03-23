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
  header('Location: /bookwave/public/admin_users.php?error=cannot_change_self');
  exit;
}

$stmt = db()->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
$stmt->execute([$userId]);

header('Location: /bookwave/public/admin_users.php?success=promoted');
exit;
