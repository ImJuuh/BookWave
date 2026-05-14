<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
require_admin();

$bookId = (int)($_POST['book_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$stock = (int)($_POST['stock'] ?? 0);
$totalStock = (int)($_POST['total_stock'] ?? 0);
$rating = (float)($_POST['rating'] ?? 0);
$coverUrl = trim($_POST['cover_url'] ?? '');
$ageRestriction = (int)($_POST['age_restriction'] ?? 0);

if (
  $bookId <= 0 ||
  $title === '' ||
  $author === '' ||
  $category === '' ||
  $stock < 0 ||
  $totalStock < 0 ||
  $rating < 0 ||
  $rating > 5
) {
  header('Location: /bookwave/public/admin.php?error=invalid_data');
  exit;
}

$stmt = db()->prepare("
  UPDATE books
  SET title = ?,
      author = ?,
      category = ?,
      description = ?,
      stock = ?,
      total_stock = ?,
      rating = ?,
      age_restriction = ?,
      cover_url = ?
  WHERE id = ?
");

$stmt->execute([
  $title,
  $author,
  $category,
  $description,
  $stock,
  $totalStock,
  $rating,
  $ageRestriction,
  $coverUrl,
  $bookId
]);

header('Location: /bookwave/public/admin.php?success=book_updated');
exit;