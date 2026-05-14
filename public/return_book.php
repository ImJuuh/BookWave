<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
require_admin();

$rentalId = (int)($_POST['rental_id'] ?? 0);
$redirectUserId = (int)($_POST['redirect_user_id'] ?? 0);

if ($rentalId <= 0) {
  die('Aluguer inválido.');
}

$pdo = db();

try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
    SELECT rentals.*, books.id AS book_id
    FROM rentals
    INNER JOIN books ON rentals.book_id = books.id
    WHERE rentals.id = ?
    FOR UPDATE
  ");
  $stmt->execute([$rentalId]);
  $rental = $stmt->fetch();

  if (!$rental) {
    $pdo->rollBack();
    die('Aluguer não encontrado.');
  }

  if ($rental['status'] === 'returned') {
    $pdo->rollBack();

    if ($redirectUserId > 0) {
      header('Location: /bookwave/public/admin_user_details.php?id=' . $redirectUserId);
    } else {
      header('Location: /bookwave/public/admin_users.php');
    }
    exit;
  }

  $returnedAt = date('Y-m-d H:i:s');

  $stmt = $pdo->prepare("
    UPDATE rentals
    SET status = 'returned', returned_at = ?
    WHERE id = ?
  ");
  $stmt->execute([$returnedAt, $rentalId]);

  $stmt = $pdo->prepare("UPDATE books SET stock = stock + 1 WHERE id = ?");
  $stmt->execute([$rental['book_id']]);

  $pdo->commit();

  if ($redirectUserId > 0) {
    header('Location: /bookwave/public/admin_user_details.php?id=' . $redirectUserId);
  } else {
    header('Location: /bookwave/public/admin_users.php');
  }
  exit;

} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  die('Erro ao devolver o livro.');
}