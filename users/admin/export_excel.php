<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../vendor/autoload.php';
require_once '../../includes/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Получаем данные для экспорта
$sql = "SELECT 
            m.name AS material_name, 
            w.name AS warehouse_name, 
            ms.quantity, 
            ms.min_quantity
        FROM material_stocks ms
        JOIN materials m ON ms.material_id = m.id
        JOIN warehouses w ON ms.warehouse_id = w.id";
$stmt = $pdo->query($sql);
$data = $stmt->fetchAll();

// Создаем новый документ Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Заголовки
$sheet->setCellValue('A1', 'Наименование');
$sheet->setCellValue('B1', 'Склад');
$sheet->setCellValue('C1', 'Остаток');
$sheet->setCellValue('D1', 'Минимальный остаток');

// Данные
$row = 2;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $row, $item['material_name']);
    $sheet->setCellValue('B' . $row, $item['warehouse_name']);
    $sheet->setCellValue('C' . $row, $item['quantity']);
    $sheet->setCellValue('D' . $row, $item['min_quantity']);
    $row++;
}

// Сохраняем файл
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="stocks.xlsx"');
$writer->save('php://output');
exit();