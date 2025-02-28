<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../vendor/autoload.php';
require_once '../../includes/config.php';

use Mpdf\Mpdf;

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

// Создаем PDF
$mpdf = new Mpdf();

// HTML-код для PDF
$html = '<h1>Остатки на складах</h1>
         <table border="1" cellpadding="10" cellspacing="0">
             <thead>
                 <tr>
                     <th>Наименование</th>
                     <th>Склад</th>
                     <th>Остаток</th>
                     <th>Минимальный остаток</th>
                 </tr>
             </thead>
             <tbody>';

foreach ($data as $item) {
    $html .= "<tr>
                  <td>{$item['material_name']}</td>
                  <td>{$item['warehouse_name']}</td>
                  <td>{$item['quantity']}</td>
                  <td>{$item['min_quantity']}</td>
              </tr>";
}

$html .= '</tbody></table>';

// Добавляем HTML в PDF
$mpdf->WriteHTML($html);

// Выводим PDF
$mpdf->Output('stocks.pdf', 'D');
exit();