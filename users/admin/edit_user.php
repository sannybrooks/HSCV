<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

require_once '../../includes/config.php';

$userId = $_GET['id'] ?? null;
if (!$userId) {
    header('Location: /users/admin/users.php');
    exit();
}

// Получаем данные о пользователе
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /users/admin/users.php');
    exit();
}

// Получаем список складов и мастеров
$warehouses = $pdo->query("SELECT id, name FROM warehouses")->fetchAll();
$masters = $pdo->query("SELECT id, full_name FROM users WHERE role = 'master'")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $role = $_POST['role'];
    $fullName = $_POST['full_name'];
    $position = $_POST['position'];
    $shift = $_POST['shift'];
    $workshop = $_POST['workshop'];
    $warehouseId = $_POST['warehouse_id'];
    $salaryType = $_POST['salary_type'];
    $salaryRate = $_POST['salary_rate'];
    $masterId = $_POST['master_id'];

    // Валидация данных
    if (empty($login) || empty($role) || empty($fullName)) {
        $error = "Пожалуйста, заполните обязательные поля.";
    } else {
        // Обновление данных пользователя
        $sql = "UPDATE users 
                SET login = :login, password = :password, role = :role, full_name = :full_name, 
                    position = :position, shift = :shift, workshop = :workshop, 
                    warehouse_id = :warehouse_id, salary_type = :salary_type, 
                    salary_rate = :salary_rate, master_id = :master_id 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'login' => $login,
            'password' => $password,
            'role' => $role,
            'full_name' => $fullName,
            'position' => $position,
            'shift' => $shift,
            'workshop' => $workshop,
            'warehouse_id' => $warehouseId,
            'salary_type' => $salaryType,
            'salary_rate' => $salaryRate,
            'master_id' => $masterId,
            'id' => $userId
        ]);

        header('Location: /users/admin/users.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать пользователя</title>
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
        <div class="user-form">
            <h1>Редактировать пользователя</h1>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="edit_user.php?id=<?php echo $userId; ?>" method="POST">
                <div class="form-group">
                    <label for="login">Логин:</label>
                    <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль (оставьте пустым, чтобы не менять):</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="role">Роль:</label>
                    <select id="role" name="role" required>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
                        <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Менеджер</option>
                        <option value="master" <?php echo $user['role'] === 'master' ? 'selected' : ''; ?>>Мастер</option>
                        <option value="accountant" <?php echo $user['role'] === 'accountant' ? 'selected' : ''; ?>>Бухгалтер</option>
                        <option value="worker" <?php echo $user['role'] === 'worker' ? 'selected' : ''; ?>>Рабочий</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="full_name">ФИО:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="position">Должность:</label>
                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($user['position']); ?>">
                </div>
                <div class="form-group">
                    <label for="shift">Смена:</label>
                    <select id="shift" name="shift">
                        <option value="утро" <?php echo $user['shift'] === 'день' ? 'selected' : ''; ?>>День</option>
                        <option value="ночь" <?php echo $user['shift'] === 'ночь' ? 'selected' : ''; ?>>Ночь</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="workshop">Цех:</label>
                    <input type="text" id="workshop" name="workshop" value="<?php echo htmlspecialchars($user['workshop']); ?>">
                </div>
                <div class="form-group">
                    <label for="warehouse_id">Склад:</label>
                    <select id="warehouse_id" name="warehouse_id">
                        <option value="">Не указан</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?php echo $warehouse['id']; ?>" <?php echo $warehouse['id'] === $user['warehouse_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($warehouse['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="salary_type">Тип зарплаты:</label>
                    <select id="salary_type" name="salary_type">
                        <option value="почасовая" <?php echo $user['salary_type'] === 'почасовая' ? 'selected' : ''; ?>>Почасовая</option>
                        <option value="сдельная" <?php echo $user['salary_type'] === 'сдельная' ? 'selected' : ''; ?>>Сдельная</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="salary_rate">Ставка:</label>
                    <input type="number" id="salary_rate" name="salary_rate" value="<?php echo htmlspecialchars($user['salary_rate']); ?>" step="0.01">
                </div>
                <div class="form-group">
                    <label for="master_id">Мастер:</label>
                    <select id="master_id" name="master_id">
                        <option value="">Не указан</option>
                        <?php foreach ($masters as $master): ?>
                            <option value="<?php echo $master['id']; ?>" <?php echo $master['id'] === $user['master_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($master['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Подвал -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Скрипты -->
    <script src="/assets/js/main.js"></script>
</body>
</html>