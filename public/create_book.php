<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
require_admin();

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$stock = (int)($_POST['stock'] ?? 0);
$totalStock = (int)($_POST['total_stock'] ?? 0);
$rating = (float)($_POST['rating'] ?? 0);
$coverUrl = trim($_POST['cover_url'] ?? '');

if ($title === '' || $author === '' || $category === '' || $stock < 0 || $rating < 0 || $rating > 5 || $totalStock < 0) {
  header('Location: /bookwave/public/admin.php?error=invalid_data');
  exit;
}

$stmt = db()->prepare("INSERT INTO books (title, author, category, description, stock, total_stock, rating, cover_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$title, $author, $category, $description, $stock, $totalStock, $rating, $coverUrl]);

header('Location: /bookwave/public/admin.php?success=book_created');
exit;
