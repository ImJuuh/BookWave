<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = current_user();

if (!$user) {
    header('Location: /bookwave/public/login.php');
    exit;
}

$bookId = (int)($_POST['book_id'] ?? 0);
if ($bookId <= 0) {
    header('Location: /bookwave/public/?erro=livro_invalido');
    exit;
}

$pdo = db();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? FOR UPDATE");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        $pdo->rollBack();
        header('Location: /bookwave/public/?erro=nao_encontrado');
        exit;
    }

    // --- NOVA VALIDAÇÃO: IMPEDIR ALUGUER DUPLICADO DO MESMO LIVRO ---
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND book_id = ? AND status = 'active'");
    $stmt->execute([$user['id'], $bookId]);
    $jaAlugou = (int)$stmt->fetchColumn();

    if ($jaAlugou > 0) {
        $pdo->rollBack();
        header('Location: /bookwave/public/book.php?id=' . $bookId . '&erro=ja_alugado');
        exit;
    }

    // --- VERIFICAÇÃO DE IDADE ---
    if (!empty($user['birth_date'])) {
        $birthDate = new DateTime($user['birth_date']);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;

        if ((int)$book['age_restriction'] >= 18 && $age < 18) {
            $pdo->rollBack();
            header('Location: /bookwave/public/book.php?id=' . $bookId . '&erro=idade_insuficiente');
            exit;
        }
    }

    // --- VERIFICAÇÃO DE STOCK ---
    if ((int)$book['stock'] <= 0) {
        $pdo->rollBack();
        header('Location: /bookwave/public/book.php?id=' . $bookId . '&erro=sem_stock');
        exit;
    }

    // Deduzir 1 unidade do stock
    $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
    $stmt->execute([$bookId]);

    // Datas de aluguer e entrega (14 dias)
    $rentedAt = date('Y-m-d H:i:s');
    $dueAt = date('Y-m-d H:i:s', strtotime('+14 days'));

    // Registar o aluguer
    $stmt = $pdo->prepare("INSERT INTO rentals (user_id, book_id, status, rented_at, due_at) VALUES (?, ?, 'active', ?, ?)");
    $stmt->execute([$user['id'], $bookId, $rentedAt, $dueAt]);

    $pdo->commit();
    header('Location: /bookwave/public/my_rentals.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: /bookwave/public/book.php?id=' . $bookId . '&erro=erro_sistema');
    exit;
}