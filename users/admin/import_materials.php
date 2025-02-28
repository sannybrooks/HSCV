<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager')) {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';
require_once '../../vendor/autoload.php'; // Подключение PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    // Загрузка файла Excel
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();

    // Чтение данных из файла
    $errors = [];
    $successCount = 0;

    foreach ($sheet->getRowIterator(2) as $row) { // Пропускаем заголовок
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $data = [];
        foreach ($cellIterator as $cell) {
            $data[] = $cell->getValue();
        }

        // Проверка данных
        if (count($data) >= 5) {
            $name = trim($data[0]);
            $unit = trim($data[1]);
            $warehouseName = trim($data[2]);
            $currentQuantity = (float)$data[3];
            $minQuantity = (float)$data[4];

            // Валидация данных
            if (empty($name) || empty($unit) || empty($warehouseName)) {
                $errors[] = "Ошибка в строке: Не заполнены обязательные поля.";
                continue;
            }

            if (!in_array($unit, ['кг', 'шт', 'м'])) {
                $errors[] = "Ошибка в строке: Недопустимая единица измерения.";
                continue;
            }

            if ($currentQuantity < 0 || $minQuantity < 0) {
                $errors[] = "Ошибка в строке: Остаток и минимальный остаток должны быть положительными числами.";
                continue;
            }

            // Проверка на уникальность названия сырья
            $sql = "SELECT COUNT(*) FROM raw_materials WHERE name = :name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $name]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Ошибка в строке: Сырье с названием '$name' уже существует.";
                continue;
            }

            // Получаем ID склада по названию
            $sql = "SELECT id FROM warehouses WHERE name = :name";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['name' => $warehouseName]);
            $warehouseId = $stmt->fetchColumn();

            if (!$warehouseId) {
                $errors[] = "Ошибка в строке: Склад '$warehouseName' не найден.";
                continue;
            }

            // Добавление сырья в базу данных
            $sql = "INSERT INTO raw_materials (name, unit, warehouse_id, current_quantity, min_quantity) 
                    VALUES (:name, :unit, :warehouse_id, :current_quantity, :min_quantity)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'unit' => $unit,
                'warehouse_id' => $warehouseId,
                'current_quantity' => $currentQuantity,
                'min_quantity' => $minQuantity
            ]);

            $successCount++;
        }
    }

    // Сообщение об успешном импорте или ошибках
    if (!empty($errors)) {
        $_SESSION['import_errors'] = $errors;
    }
    $_SESSION['import_success'] = "Успешно импортировано записей: $successCount";

    header('Location: /users/admin/warehouses.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Импорт сырья из Excel</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
    <!-- Шапка -->
    <?php include '../../includes/header.php'; ?>

    <!-- Боковое меню -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Основной контент -->
    <main class="main-content">
        <h1>Импорт сырья из Excel</h1>
        <form action="import_materials.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="file">Файл Excel:</label>
                <input type="file" id="file" name="file" required>
            </div>
            <button type="submit" class="btn">Импорт</button>
        </form>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>