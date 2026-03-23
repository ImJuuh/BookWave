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

try {
  $stmt = db()->prepare("DELETE FROM books WHERE id = ?");
  $stmt->execute([$bookId]);
  header('Location: /bookwave/public/admin.php?success=book_deleted');
  exit;
} catch (PDOException $e) {
  header('Location: /bookwave/public/admin.php?error=invalid_data');
  exit;
}
