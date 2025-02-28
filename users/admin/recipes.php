<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

// Получаем список рецептов с названием типа продукции
$sql = "SELECT r.id, r.code, pt.name AS type_name, r.description 
        FROM recipes r
        JOIN product_types pt ON r.type_id = pt.id";
$recipes = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление рецептами</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <main class="main-content">
        <h1>Управление рецептами</h1>
        <a href="add_recipe.php" class="btn">Добавить рецепт</a>
        <table>
            <thead>
                <tr>
                    <th>Код рецепта</th>
                    <th>Тип продукции</th>
                    <th>Описание</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recipes as $recipe): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($recipe['code']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['type_name']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['description']); ?></td>
                        <td>
                            <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn">Редактировать</a>
                            <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" class="btn">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>