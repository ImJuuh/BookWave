<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
$user = current_user();

if (!$user) {
  header('Location: /bookwave/public/login.php');
  exit;
}

$bookId = (int)($_POST['book_id'] ?? 0);
if ($bookId <= 0) die('Livro inválido.');

$pdo = db();

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? FOR UPDATE");
  $stmt->execute([$bookId]);
  $book = $stmt->fetch();

  if (!$book) {
    $pdo->rollBack();
    die('Livro não encontrado.');
  }

  if ((int)$book['stock'] <= 0) {
    $pdo->rollBack();
    header('Location: /bookwave/public/book.php?id=' . $bookId . '&erro=sem_stock');
    exit;
  }

  $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
  $stmt->execute([$bookId]);

  $rentedAt = date('Y-m-d H:i:s');
  $dueAt = date('Y-m-d H:i:s', strtotime('+14 days'));

  $stmt = $pdo->prepare("INSERT INTO rentals (user_id, book_id, status, rented_at, due_at) VALUES (?, ?, 'active', ?, ?)");
  $stmt->execute([$user['id'], $bookId, $rentedAt, $dueAt]);

  $pdo->commit();
  header('Location: /bookwave/public/my_rentals.php');
  exit;
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  die('Erro ao alugar o livro.');
}
