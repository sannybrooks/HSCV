<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

$userId = $_GET['id'] ?? null;
if (!$userId) {
    header('Location: /users/admin/users.php');
    exit();
}

// Удаление пользователя
$sql = "DELETE FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $userId]);

header('Location: /users/admin/users.php');
exit();
?>