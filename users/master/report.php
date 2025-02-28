<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем данные из GET-запроса
$productId = $_GET['product_id'];
$date = $_GET['date'];

// Получаем ID мастера и склада
$masterId = $_SESSION['user_id'];
$warehouseId = $_SESSION['warehouse_id']; // Предполагаем, что ID склада хранится в сессии

// Получаем данные о продукте, включая код рецепта
$sql = "SELECT id, brand, name, recipe_id, recipe_code FROM products WHERE id = :product_id"; // Добавлено поле recipe_code
$stmt = $pdo->prepare($sql);
$stmt->execute(['product_id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    die("Продукт не найден.");
}

$productName = $product['brand'] . ' ' . $product['name'];
$recipeId = $product['recipe_id'];
$recipeCode = $product['recipe_code']; // Получаем код рецепта

// Получаем фактические данные о выпуске продукции
$sql = "SELECT pl.actual_quantity 
        FROM production_logs pl
        JOIN production_plans pp ON pl.production_plan_id = pp.id
        WHERE pp.product_id = :product_id
        AND pp.master_id = :master_id
        AND pl.date = :date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'product_id' => $productId,
    'master_id' => $masterId,
    'date' => $date
]);
$log = $stmt->fetch();

if (!$log) {
    die("Данные о выпуске продукции не найдены.");
}

$actualQuantity = $log['actual_quantity'];

// Получаем ингредиенты рецепта
$sql = "SELECT m.id AS material_id, m.name AS material_name, ri.quantity AS material_quantity, m.unit 
        FROM recipe_ingredients ri
        JOIN materials m ON ri.material_id = m.id
        WHERE ri.recipe_id = :recipe_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['recipe_id' => $recipeId]);
$ingredients = $stmt->fetchAll();

// Рассчитываем итого кг и средний вес мешка
$totalKg = 0;
foreach ($ingredients as $ingredient) {
    if ($ingredient['unit'] === 'кг.') {
        $totalKg += $ingredient['material_quantity'] * $actualQuantity;
    }
}
$averageBagWeight = $totalKg / $actualQuantity;

// Получаем остатки цемента и муки
$sql = "SELECT m.id, m.name, ms.quantity 
        FROM material_stocks ms
        JOIN materials m ON ms.material_id = m.id
        WHERE ms.warehouse_id = :warehouse_id
        AND m.id IN (1, 7)"; // Цемент (id=1) и Мука (id=7)
$stmt = $pdo->prepare($sql);
$stmt->execute(['warehouse_id' => $warehouseId]);
$materialStocks = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Формат: [material_id => quantity]

$cementStock = $materialStocks[1] ?? 0; // Остаток цемента
$flourStock = $materialStocks[7] ?? 0; // Остаток муки
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 10;
            padding: 20px;
        }
        .report-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: calc(100% - 20px); /* Учитываем padding */
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 auto
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <h1>Отчет по выпуску продукции</h1>

        <!-- Продукт -->
        <div class="form-group">
            <label>Продукт:
            <input type="text" value="<?= htmlspecialchars($productName) ?>" readonly></label>
        </div>

        <!-- Код Продукта -->
        <div class="form-group">
            <label>Код рецепта:
            <input type="text" value="<?= htmlspecialchars($recipeCode) ?>" readonly></label>

        </div>

        <!-- Дата и время -->
        <div class="form-group">
            <label>Дата выпуска:</label>
            <input type="date" name="production_date" value="<?= htmlspecialchars($date) ?>" required>
        </div>
        <div class="form-group">
            <label>Время начала производства:</label>
            <input type="time" name="start_time" required>
        </div>
        <div class="form-group">
            <label>Время завершения производства:</label>
            <input type="time" name="end_time" required>
        </div>

        <!-- Количество выпущенной продукции -->
        <div class="form-group">
            <label>Количество выпущенной продукции:</label>
            <input type="number" name="actual_quantity" value="<?= htmlspecialchars($actualQuantity) ?>" readonly>
        </div>

        <!-- Использованное сырье -->
        <h2>Использованное сырье</h2>
        <table>
            <thead>
                <tr>
                    <th>Материал</th>
                    <th>Единица измерения</th>
                    <th>Количество по норме</th>
                    <th>Фактически использовано</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient): ?>
                    <tr>
                        <td><?= htmlspecialchars($ingredient['material_name']) ?></td>
                        <td><?= htmlspecialchars($ingredient['unit']) ?></td>
                        <td><?= htmlspecialchars($ingredient['material_quantity'] * $actualQuantity) ?></td>
                        <td>
                            <input type="number" 
                                class="used-quantity"  data-unit="<?= $ingredient['unit'] ?>"  
                                name="used_quantity[<?= $ingredient['material_id'] ?>]" 
                                value="<?= htmlspecialchars($ingredient['material_quantity'] * $actualQuantity) ?>" 
                                step="0.01">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Итого кг -->
        <div class="form-group">
            <label>Итого кг:</label>
            <input type="text" id="total-kg" value="<?= htmlspecialchars($totalKg) ?>" readonly>
        </div>

        <!-- Остатки цемента и муки -->
        <div class="form-group">
            <label>Остаток Цемента в силосе:</label>
            <input type="text" value="<?= htmlspecialchars($cementStock) ?>" readonly>
        </div>
        <div class="form-group">
            <label>Остаток Муки в силосе:</label>
            <input type="text" value="<?= htmlspecialchars($flourStock) ?>" readonly>
        </div>

        <!-- Кнопки -->
        <div class="form-group">
            <button type="submit" class="btn">Сохранить</button>
            <button type="button" class="btn" onclick="exportToExcel()">Экспорт в Excel</button>
            <button type="button" class="btn" onclick="exportToPDF()">Экспорт в PDF</button>
        </div>
    </div>

    <script>
        // Функция для пересчета "Итого кг"
        function updateTotalKg() {
            const inputs = document.querySelectorAll('.used-quantity');
            let totalKg = 0;

            inputs.forEach(input => {
                const unit = input.dataset.unit; // Получаем единицу измерения из data-unit
                if (unit === 'кг.') {
                    const value = parseFloat(input.value) || 0; // Преобразуем значение в число
                    totalKg += value;
                }
            });

            document.getElementById('total-kg').value = totalKg.toFixed(2);
        }

        // Обработка изменения значений в полях "Фактически использовано"
        document.querySelectorAll('.used-quantity').forEach(input => {
            input.addEventListener('input', updateTotalKg);
        });

        // Инициализация при загрузке страницы
        updateTotalKg();

        function exportToExcel() {
            alert("Экспорт в Excel");
            // Реализация экспорта в Excel
        }

        function exportToPDF() {
            alert("Экспорт в PDF");
            // Реализация экспорта в PDF
        }
    </script>
</body>
</html>