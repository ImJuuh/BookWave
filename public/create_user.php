<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

start_session();
require_admin();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$birth_date = $_POST['birth_date'] ?? null;

if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
  header('Location: /bookwave/public/admin_users.php?error=invalid_user_data');
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  header('Location: /bookwave/public/admin_users.php?error=invalid_user_data');
  exit;
}

if (strlen($password) < 6) {
  header('Location: /bookwave/public/admin_users.php?error=invalid_user_data');
  exit;
}

if ($password !== $confirmPassword) {
  header('Location: /bookwave/public/admin_users.php?error=password_mismatch');
  exit;
}

$stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$existingUser = $stmt->fetch();

if ($existingUser) {
  header('Location: /bookwave/public/admin_users.php?error=email_exists');
  exit;
}

$stmt = db()->prepare("INSERT INTO users (name, email, password_hash, is_admin, birth_date) VALUES (?, ?, ?, 0, ?)");
$stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $birth_date]);

header('Location: /bookwave/public/admin_users.php?success=user_created');
exit;
