<?php
require '../../vendor/autoload.php';
require_once '../../includes/config.php';

use Mpdf\Mpdf;

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

// Создаем PDF-документ
$mpdf = new Mpdf();

// HTML-контент для PDF
$html = "<h1>Отчет по выпуску продукции</h1>
         <p><strong>Продукт:</strong> {$report['product_name']}</p>
         <p><strong>Дата выпуска:</strong> {$report['report_date']}</p>
         <p><strong>Количество выпущенной продукции:</strong> {$report['actual_quantity']}</p>
         <h2>Использованное сырье</h2>
         <table border='1' cellpadding='10'>
             <tr>
                 <th>Материал</th>
                 <th>Количество</th>
                 <th>Единица измерения</th>
             </tr>";

foreach ($details as $detail) {
    $html .= "<tr>
                 <td>{$detail['material_name']}</td>
                 <td>{$detail['used_quantity']}</td>
                 <td>{$detail['unit']}</td>
              </tr>";
}

$html .= "</table>";

// Добавляем HTML в PDF
$mpdf->WriteHTML($html);

// Выводим PDF
$mpdf->Output('report_' . $reportId . '.pdf', 'D');
exit();