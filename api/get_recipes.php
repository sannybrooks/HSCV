<?php
require_once '../includes/config.php';

$typeId = $_GET['type_id'] ?? '';

if (empty($typeId)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Тип продукции не указан']);
    exit();
}

// Получаем рецепты для выбранного типа
$sql = "SELECT id, code, description FROM recipes WHERE type_id = :type_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['type_id' => $typeId]);
$recipes = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($recipes);
?>