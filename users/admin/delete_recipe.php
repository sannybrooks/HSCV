<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

$recipeId = $_GET['id'] ?? null;
if (!$recipeId) {
    header('Location: /users/admin/recipes.php');
    exit();
}

// Удаляем ингредиенты рецепта
$sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['recipe_id' => $recipeId]);

// Удаляем рецепт
$sql = "DELETE FROM recipes WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $recipeId]);

header('Location: /users/admin/recipes.php');
exit();
?>