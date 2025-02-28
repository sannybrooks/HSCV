<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем ID типа продукции из запроса
$typeId = $_GET['id'] ?? null;
if (!$typeId) {
    header('Location: /users/admin/product_types.php');
    exit();
}

// Проверяем, связан ли тип с продуктами или рецептами
$sql = "SELECT COUNT(*) FROM products WHERE type_id = :type_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['type_id' => $typeId]);
$productCount = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) FROM recipes WHERE type_id = :type_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['type_id' => $typeId]);
$recipeCount = $stmt->fetchColumn();

if ($productCount > 0 || $recipeCount > 0) {
    // Если тип связан с продуктами или рецептами, удаление запрещено
    $_SESSION['error'] = 'Невозможно удалить тип продукции, так как он связан с другими данными.';
    header('Location: /users/admin/product_types.php');
    exit();
}

// Удаление типа продукции
$sql = "DELETE FROM product_types WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $typeId]);

header('Location: /users/admin/product_types.php');
exit();
?>