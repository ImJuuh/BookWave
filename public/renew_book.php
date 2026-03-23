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

  $stmt = $pdo->prepare("SELECT * FROM rentals WHERE id = ? AND user_id = ? FOR UPDATE");
  $stmt->execute([$rentalId, $user['id']]);
  $rental = $stmt->fetch();

  if (!$rental) {
    $pdo->rollBack();
    die('Aluguer não encontrado.');
  }

  if ($rental['status'] !== 'active') {
    $pdo->rollBack();
    header('Location: /bookwave/public/my_rentals.php');
    exit;
  }

  if ((int)$rental['renewed_count'] >= 2) {
    $pdo->rollBack();
    header('Location: /bookwave/public/my_rentals.php?erro=limite_renovacoes');
    exit;
  }

  $currentDueAt = strtotime($rental['due_at']);
  $newDueAt = date('Y-m-d H:i:s', strtotime('+7 days', $currentDueAt));

  $stmt = $pdo->prepare("UPDATE rentals SET due_at = ?, renewed_count = renewed_count + 1 WHERE id = ?");
  $stmt->execute([$newDueAt, $rentalId]);

  $pdo->commit();
  header('Location: /bookwave/public/my_rentals.php');
  exit;
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  die('Erro ao renovar o aluguer.');
}
