<?php
require '../../vendor/autoload.php';
require_once '../../includes/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Получаем данные из GET-запроса
$reportId = $_GET['report_id'];

// Получаем данные отчета
$sql = "SELECT pr.*, p.name AS product_name 
        FROM production_reports pr
        JOIN products p ON pr.product_id = p.id
        WHERE pr.id = :report_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['report_id' => $reportId]);
$report = $stmt->fetch();

if (!$report) {
    die("Отчет не найден.");
}

// Получаем детали отчета
$sql = "SELECT m.name AS material_name, prd.used_quantity, m.unit 
        FROM production_report_details prd
        JOIN materials m ON prd.material_id = m.id
        WHERE prd.report_id = :report_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['report_id' => $reportId]);
$details = $stmt->fetchAll();

// Создаем Excel-документ
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Заголовки
$sheet->setCellValue('A1', 'Продукт');
$sheet->setCellValue('B1', $report['product_name']);
$sheet->setCellValue('A2', 'Дата выпуска');
$sheet->setCellValue('B2', $report['report_date']);
$sheet->setCellValue('A3', 'Количество выпущенной продукции');
$sheet->setCellValue('B3', $report['actual_quantity']);

// Данные о сырье
$sheet->setCellValue('A5', 'Материал');
$sheet->setCellValue('B5', 'Количество');
$sheet->setCellValue('C5', 'Единица измерения');

$row = 6;
foreach ($details as $detail) {
    $sheet->setCellValue('A' . $row, $detail['material_name']);
    $sheet->setCellValue('B' . $row, $detail['used_quantity']);
    $sheet->setCellValue('C' . $row, $detail['unit']);
    $row++;
}

// Сохраняем файл
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="report_' . $reportId . '.xlsx"');
$writer->save('php://output');
exit();