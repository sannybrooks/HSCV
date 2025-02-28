<?php

$role = $_SESSION['role'] ?? 'guest'; // По умолчанию 'guest', если пользователь не авторизован

?>

<div class="sidebar">
    <?php if ($role === 'admin'): ?>
        <!-- Sidebar для администратора -->
        <ul>
            <li><a href="/users/admin/index.php" class="active"><i class="fas fa-home"></i> Главная</a></li>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn" onclick="toggleDropdown()">
                    <i class="fas fa-industry"></i> Производство <i class="fas fa-caret-down"></i>
                </a>
                <div class="dropdown-content" id="productionDropdown">
                    <a href="/users/admin/plan.php"><i class="fas fa-calendar-alt"></i> Выпуск</a>
                    <a href="/users/admin/edit_plan.php"><i class="fas fa-edit"></i> Заявка</a>
                </div>
            </li>
            <li><a href="/users/admin/products.php"><i class="fas fa-box"></i> Продукция</a></li>
            <li><a href="/users/admin/recipes.php"><i class="fas fa-book"></i> Рецепты</a></li>
            <li><a href="/users/admin/warehouses.php"><i class="fas fa-warehouse"></i> Склады</a></li>
            <li><a href="/users/admin/users.php"><i class="fas fa-users"></i> Пользователи</a></li>
            <li><a href="/users/admin/reports.php"><i class="fas fa-chart-bar"></i> Отчеты</a></li>
        </ul>
    <?php elseif ($role === 'manager'): ?>
        <!-- Sidebar для менеджера -->
        <ul>
            <li><a href="/users/manager/index.php" class="active"><i class="fas fa-home"></i> Главная</a></li>
            <li><a href="/users/manager/plans.php"><i class="fas fa-calendar-alt"></i> Планы производства</a></li>
            <li><a href="/users/manager/orders.php"><i class="fas fa-truck"></i> Заказы сырья</a></li>
            <li><a href="/users/manager/reports.php"><i class="fas fa-chart-bar"></i> Отчеты</a></li>
        </ul>
    <?php elseif ($role === 'master'): ?>
        <!-- Sidebar для мастера -->
        <ul>
            <li><a href="/users/master/index.php" class="active"><i class="fas fa-home"></i> Главная</a></li>
            <li><a href="/users/master/plan.php"><i class="fas fa-calendar-alt"></i> План на неделю</a></li>
            <li><a href="/users/master/workers.php"><i class="fas fa-users"></i> Рабочие</a></li>
            <li><a href="/users/master/logs.php"><i class="fas fa-clipboard-list"></i> Учет рабочего времени</a></li>
        </ul>
    <?php else: ?>
        <!-- Sidebar для гостя (если нужно) -->
        <ul>
            <li><a href="/login.php"><i class="fas fa-sign-in-alt"></i> Войти</a></li>
        </ul>
    <?php endif; ?>
</div>

<script>
    function toggleDropdown() {
        const dropdownContent = document.getElementById("productionDropdown");
        const dropdownButton = document.querySelector(".dropdown .dropbtn");
        dropdownContent.classList.toggle("show");
        dropdownButton.parentElement.classList.toggle("active");
    }

    // Закрыть выпадающий список, если кликнуть вне его
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            const dropdowns = document.getElementsByClassName("dropdown-content");
            const buttons = document.getElementsByClassName("dropbtn");
            for (let i = 0; i < dropdowns.length; i++) {
                const openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                    buttons[i].parentElement.classList.remove('active');
                }
            }
        }
    }
</script>