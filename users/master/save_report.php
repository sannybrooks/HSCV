<?php
session_start();
require_once '../../includes/config.php';

// Проверка авторизации и роли
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'master') {
    header('Location: /login.php');
    exit();
}

// Получаем данные из формы
$productId = $_POST['product_id'];
$date = $_POST['date'];
$actualQuantity = $_POST['actual_quantity'];
$usedQuantities = $_POST['used_quantity']; // Массив с фактически использованным сырьем

// Получаем ID мастера и склада
$masterId = $_SESSION['user_id'];
$warehouseId = $_SESSION['warehouse_id'];

// Начинаем транзакцию
$pdo->beginTransaction();

try {
    // Сохраняем отчет в таблицу production_reports
    $sql = "INSERT INTO production_reports (master_id, product_id, report_date, actual_quantity) 
            VALUES (:master_id, :product_id, :report_date, :actual_quantity)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'master_id' => $masterId,
        'product_id' => $productId,
        'report_date' => $date,
        'actual_quantity' => $actualQuantity
    ]);
    $reportId = $pdo->lastInsertId();

    // Сохраняем детали отчета и обновляем остатки
    foreach ($usedQuantities as $materialId => $usedQuantity) {
        // Сохраняем детали отчета
        $sql = "INSERT INTO production_report_details (report_id, material_id, used_quantity) 
                VALUES (:report_id, :material_id, :used_quantity)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'report_id' => $reportId,
            'material_id' => $materialId,
            'used_quantity' => $usedQuantity
        ]);

        // Обновляем остатки в material_stocks
        $sql = "UPDATE material_stocks 
                SET quantity = quantity - :used_quantity 
                WHERE material_id = :material_id 
                AND warehouse_id = :warehouse_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'used_quantity' => $usedQuantity,
            'material_id' => $materialId,
            'warehouse_id' => $warehouseId
        ]);

        // Фиксируем списание в material_movements
        $sql = "INSERT INTO material_movements (material_id, warehouse_id, movement_type, quantity, movement_date) 
                VALUES (:material_id, :warehouse_id, 'расход', :quantity, :movement_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'material_id' => $materialId,
            'warehouse_id' => $warehouseId,
            'quantity' => $usedQuantity,
            'movement_date' => $date
        ]);
    }

    // Фиксируем транзакцию
    $pdo->commit();

    // Перенаправляем обратно на страницу мастера
    header('Location: /master/index.php');
    exit();
} catch (Exception $e) {
    // Откатываем транзакцию в случае ошибки
    $pdo->rollBack();
    die("Ошибка при сохранении отчета: " . $e->getMessage());
}