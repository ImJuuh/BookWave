<?php
function db(): PDO {
  static $pdo = null;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $pdo = new PDO(
    "mysql:host=localhost;dbname=bookwave;charset=utf8mb4",
    "root",
    "",
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  return $pdo;
}
