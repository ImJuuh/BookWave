<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
require_admin();

$bookId = (int)($_POST['book_id'] ?? 0);

if ($bookId <= 0) {
  header('Location: /bookwave/public/admin.php?error=invalid_data');
  exit;
}

$stmt = db()->prepare("SELECT is_active FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
  header('Location: /bookwave/public/admin.php?error=invalid_data');
  exit;
}

$newStatus = (int)$book['is_active'] === 1 ? 0 : 1;

$stmt = db()->prepare("UPDATE books SET is_active = ? WHERE id = ?");
$stmt->execute([$newStatus, $bookId]);

header('Location: /bookwave/public/admin.php?success=status_updated');
exit;