<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем ID мастера
$masterId = $_SESSION['user_id'];

// Получаем выбранную неделю из параметра
$startOfWeek = $_GET['week'] ?? date('Y-m-d');
$startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($startOfWeek)));
$endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($startOfWeek)));

// Получаем планы производства на выбранную неделю
$sql = "SELECT pp.id, p.id AS product_id, CONCAT(p.brand, ' ', p.name) AS product_name, pp.planned_quantity, pp.date 
        FROM production_plans pp
        JOIN products p ON pp.product_id = p.id
        WHERE pp.master_id = :master_id
        AND pp.date BETWEEN :start_date AND :end_date
        ORDER BY pp.date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'master_id' => $masterId,
    'start_date' => $startOfWeek,
    'end_date' => $endOfWeek
]);
$plans = $stmt->fetchAll();

// Получаем фактические данные за выбранную неделю
$sql = "SELECT pl.production_plan_id, SUM(pl.actual_quantity) AS total_actual 
        FROM production_logs pl
        WHERE pl.date BETWEEN :start_date AND :end_date
        GROUP BY pl.production_plan_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'start_date' => $startOfWeek,
    'end_date' => $endOfWeek
]);
$logs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // production_plan_id => total_actual

// Собираем данные о необходимых материалах
$materialsNeeded = [];

foreach ($plans as $plan) {
    // Рассчитываем остаток плана
    $plannedQuantity = $plan['planned_quantity'];
    $actualQuantity = $logs[$plan['id']] ?? 0; // Фактически произведенное количество
    $remainingQuantity = max(0, $plannedQuantity - $actualQuantity); // Остаток плана

    if ($remainingQuantity > 0) {
        // Получаем рецепт для продукта
        $sql = "SELECT r.id AS recipe_id 
                FROM products p
                JOIN recipes r ON p.recipe_id = r.id
                WHERE p.id = :product_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['product_id' => $plan['product_id']]);
        $recipe = $stmt->fetch();

        if ($recipe) {
            // Получаем ингредиенты для рецепта
            $sql = "SELECT ri.material_id, ri.quantity, m.name, m.unit 
                    FROM recipe_ingredients ri
                    JOIN materials m ON ri.material_id = m.id
                    WHERE ri.recipe_id = :recipe_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['recipe_id' => $recipe['recipe_id']]);
            $ingredients = $stmt->fetchAll();

            // Суммируем количество материалов с учетом остатка плана
            foreach ($ingredients as $ingredient) {
                $materialId = $ingredient['material_id'];
                $quantityNeeded = $ingredient['quantity'] * $remainingQuantity;

                if (!isset($materialsNeeded[$materialId])) {
                    $materialsNeeded[$materialId] = [
                        'name' => $ingredient['name'],
                        'unit' => $ingredient['unit'],
                        'quantity' => 0
                    ];
                }
                $materialsNeeded[$materialId]['quantity'] += $quantityNeeded;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список необходимого сырья и материалов</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <h1>Список необходимого сырья и материалов на неделю (по остатку плана)</h1>
    <table>
        <thead>
            <tr>
                <th>Материал</th>
                <th>Количество</th>
                <th>Единица измерения</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($materialsNeeded as $material): ?>
                <tr>
                    <td><?= htmlspecialchars($material['name']) ?></td>
                    <td><?= number_format($material['quantity'], 2) ?></td>
                    <td><?= htmlspecialchars($material['unit']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>