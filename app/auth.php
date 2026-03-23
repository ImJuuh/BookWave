<?php
require_once __DIR__ . '/db.php';

function start_session(): void {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

function current_user(): ?array {
  start_session();

  if (empty($_SESSION['user_id'])) {
    return null;
  }

  $stmt = db()->prepare("SELECT id, name, email, is_admin, created_at FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  return $user ?: null;
}

function require_login(): void {
  if (!current_user()) {
    header('Location: /bookwave/public/login.php');
    exit;
  }
}

function require_admin(): void {
  $user = current_user();

  if (!$user || (int)$user['is_admin'] !== 1) {
    die('Acesso negado.');
  }
}
