<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем ID сырья из запроса
$materialId = $_GET['id'] ?? null;
if (!$materialId) {
    header('Location: /users/admin/warehouses.php');
    exit();
}

// Удаление сырья из базы данных
$sql = "DELETE FROM raw_materials WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $materialId]);

header('Location: /users/admin/warehouses.php');
exit();
?>