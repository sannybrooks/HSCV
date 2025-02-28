<?php
// Настройки подключения к базе данных
$host = 'localhost'; // Хост
$db   = 'j96442386';  // Имя базы данных
$user = 'j96442386';      // Имя пользователя
$pass = 'j96442386';          // Пароль
$charset = 'utf8mb4'; // Кодировка

// Настройки PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Создаем подключение к базе данных
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // В случае ошибки выводим сообщение
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>