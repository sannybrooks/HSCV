<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

require_once '../../includes/config.php';

// Получаем данные для графиков
$data = [];

// 1. Выпуск за день
$sql = "SELECT SUM(actual_quantity) AS total FROM production_logs WHERE DATE(date) = CURDATE()";
$data['dailyProduction'] = $pdo->query($sql)->fetchColumn();

// 2. Расход сырья за неделю
$sql = "SELECT 
            SUM(CASE WHEN material_id = 1 THEN quantity ELSE 0 END) AS cement,
            SUM(CASE WHEN material_id = 6 THEN quantity ELSE 0 END) AS sand,
            SUM(CASE WHEN material_id = 7 THEN quantity ELSE 0 END) AS flour
        FROM material_movements 
        WHERE movement_type = 'расход' 
        AND movement_date BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND CURDATE()";
$data['weeklyMaterialUsage'] = $pdo->query($sql)->fetch();

// 3. Текущие остатки цемента и муки по складам
$sql = "SELECT 
            w.name AS warehouse_name,
            SUM(CASE WHEN material_id = 1 THEN quantity ELSE 0 END) AS cement,
            SUM(CASE WHEN material_id = 7 THEN quantity ELSE 0 END) AS flour
        FROM material_stocks ms
        JOIN warehouses w ON ms.warehouse_id = w.id
        WHERE material_id IN (1, 7)
        GROUP BY w.name";
$data['currentStocks'] = $pdo->query($sql)->fetchAll();

// Возвращаем данные в формате JSON
header('Content-Type: application/json');
echo json_encode($data);