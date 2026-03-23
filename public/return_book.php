<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
$user = current_user();

if (!$user) {
  header('Location: /bookwave/public/login.php');
  exit;
}

$rentalId = (int)($_POST['rental_id'] ?? 0);
if ($rentalId <= 0) die('Aluguer inválido.');

$pdo = db();

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
    SELECT rentals.*, books.id AS book_id
    FROM rentals
    INNER JOIN books ON rentals.book_id = books.id
    WHERE rentals.id = ? AND rentals.user_id = ?
    FOR UPDATE
  ");
  $stmt->execute([$rentalId, $user['id']]);
  $rental = $stmt->fetch();

  if (!$rental) {
    $pdo->rollBack();
    die('Aluguer não encontrado.');
  }

  if ($rental['status'] === 'returned') {
    $pdo->rollBack();
    header('Location: /bookwave/public/my_rentals.php');
    exit;
  }

  $returnedAt = date('Y-m-d H:i:s');

  $stmt = $pdo->prepare("UPDATE rentals SET status = 'returned', returned_at = ? WHERE id = ?");
  $stmt->execute([$returnedAt, $rentalId]);

  $stmt = $pdo->prepare("UPDATE books SET stock = stock + 1 WHERE id = ?");
  $stmt->execute([$rental['book_id']]);

  $pdo->commit();
  header('Location: /bookwave/public/my_rentals.php');
  exit;
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  die('Erro ao devolver o livro.');
}
