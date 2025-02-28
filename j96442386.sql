-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: 10.0.0.73:3306
-- Время создания: Фев 27 2025 г., 08:58
-- Версия сервера: 10.11.11-MariaDB-cll-lve-log
-- Версия PHP: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `j96442386`
--

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` enum('кг.','шт.','м.') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `materials`
--

INSERT INTO `materials` (`id`, `name`, `unit`) VALUES
(1, 'Цемент', 'кг.'),
(6, 'Песок', 'кг.'),
(7, 'Мука известняковая', 'кг.'),
(8, 'Премикс КСС', 'кг.');

-- --------------------------------------------------------

--
-- Структура таблицы `material_movements`
--

CREATE TABLE `material_movements` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `movement_type` enum('приход','расход','производство') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `movement_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `material_movements`
--

INSERT INTO `material_movements` (`id`, `material_id`, `warehouse_id`, `movement_type`, `quantity`, `movement_date`) VALUES
(1, 1, 1, 'приход', '10000.00', '2025-02-12');

-- --------------------------------------------------------

--
-- Структура таблицы `material_stocks`
--

CREATE TABLE `material_stocks` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `min_quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `material_stocks`
--

INSERT INTO `material_stocks` (`id`, `material_id`, `warehouse_id`, `quantity`, `min_quantity`) VALUES
(1, 1, 1, '10500.00', '5000.00'),
(2, 6, 1, '100500.00', '50000.00'),
(3, 7, 1, '15000.00', '5000.00'),
(4, 8, 1, '150.00', '50.00'),
(5, 1, 2, '10000.00', '5000.00'),
(6, 6, 2, '100000.00', '50000.00'),
(7, 7, 2, '10000.00', '5000.00'),
(8, 8, 2, '100.00', '50.00');

-- --------------------------------------------------------

--
-- Структура таблицы `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `production_logs`
--

CREATE TABLE `production_logs` (
  `id` int(11) NOT NULL,
  `production_plan_id` int(11) DEFAULT NULL,
  `actual_quantity` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `production_logs`
--

INSERT INTO `production_logs` (`id`, `production_plan_id`, `actual_quantity`, `date`) VALUES
(25, 2, 999, '2025-02-17'),
(26, 8, 443, '2025-02-17'),
(28, 10, 444, '2025-02-24'),
(29, 9, 888, '2025-02-24'),
(31, 9, 677, '2025-02-25'),
(32, 10, 366, '2025-02-25'),
(33, 9, 433, '2025-02-26');

-- --------------------------------------------------------

--
-- Структура таблицы `production_plans`
--

CREATE TABLE `production_plans` (
  `id` int(11) NOT NULL,
  `master_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `planned_quantity` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `production_plans`
--

INSERT INTO `production_plans` (`id`, `master_id`, `product_id`, `planned_quantity`, `date`) VALUES
(2, 2, 7, 2000, '2025-02-17'),
(8, 2, 8, 4000, '2025-02-17'),
(9, 3, 7, 4000, '2025-02-24'),
(10, 3, 8, 3000, '2025-02-24'),
(11, 5, 7, 1000, '2025-02-24');

-- --------------------------------------------------------

--
-- Структура таблицы `production_reports`
--

CREATE TABLE `production_reports` (
  `id` int(11) NOT NULL,
  `master_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `production_reports`
--

INSERT INTO `production_reports` (`id`, `master_id`, `report_date`, `created_at`) VALUES
(1, 2, '2025-02-17', '2025-02-17 12:39:18'),
(2, 2, '2025-02-17', '2025-02-17 13:58:12'),
(3, 2, '2025-02-17', '2025-02-17 13:58:19'),
(4, 2, '2025-02-17', '2025-02-17 14:00:06'),
(5, 2, '2025-02-17', '2025-02-17 14:09:32'),
(6, 2, '2025-02-17', '2025-02-17 14:10:13');

-- --------------------------------------------------------

--
-- Структура таблицы `production_report_details`
--

CREATE TABLE `production_report_details` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `recipe_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `full_description` text DEFAULT NULL,
  `preview` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `recipe_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `recipe_code`, `description`, `full_description`, `preview`, `created_at`, `recipe_id`, `type_id`) VALUES
(7, 'Штукатурка М50', 'Утепляй-Ка', '001', 'штукатурка', 'штукатурка', '690efd8572ea7849011c7bb64a7fed43.png', '2025-02-10 22:58:52', 1, 3),
(8, 'Клей для Кладки', 'Утепляй-Ка', '002', 'Клей для Кладки', '', 'default.jpg', '2025-02-16 20:28:06', 6, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `product_types`
--

CREATE TABLE `product_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `product_types`
--

INSERT INTO `product_types` (`id`, `name`) VALUES
(2, 'клей для блоков'),
(1, 'клей для плитки'),
(4, 'стяжка'),
(3, 'штукатурка');

-- --------------------------------------------------------

--
-- Структура таблицы `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `recipes`
--

INSERT INTO `recipes` (`id`, `code`, `description`, `created_at`, `type_id`) VALUES
(1, '001', 'Рецепт для штукатурки.', '2025-02-10 21:50:36', 3),
(6, '002', 'Клей для кладки', '2025-02-16 18:02:09', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`id`, `recipe_id`, `material_id`, `quantity`) VALUES
(5, 6, 1, '5.00'),
(6, 6, 6, '18.00'),
(7, 6, 8, '0.50'),
(8, 6, 7, '1.50'),
(13, 1, 1, '4.00'),
(14, 1, 6, '19.00'),
(15, 1, 7, '2.00');

-- --------------------------------------------------------

--
-- Структура таблицы `salaries`
--

CREATE TABLE `salaries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `period` date NOT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `fines` decimal(10,2) DEFAULT NULL,
  `bonuses` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `allowances` decimal(10,2) DEFAULT NULL,
  `total_salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','master','accountant','worker') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `shift` enum('день','ночь') DEFAULT NULL,
  `workshop` varchar(100) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `salary_type` enum('почасовая','сдельная') DEFAULT NULL,
  `salary_rate` decimal(10,2) DEFAULT NULL,
  `master_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `role`, `full_name`, `position`, `shift`, `workshop`, `warehouse_id`, `salary_type`, `salary_rate`, `master_id`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Иванов Иван', 'Администратор', '', '', NULL, 'почасовая', '120000.00', NULL),
(2, 'master', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master', 'Николай', 'Мастер смены', 'день', 'Виноградово Цех 1', 1, 'сдельная', NULL, NULL),
(3, 'master2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master', 'Вячеслав', 'Мастер смены', 'день', 'Виноградово Цех 2', 1, 'сдельная', NULL, NULL),
(5, 'master3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'master', 'Александр', 'Мастер смены', 'день', 'Громовка Цех', 2, 'сдельная', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Дамп данных таблицы `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `address`, `created_at`) VALUES
(1, 'Склад 1', 'Виноградово', '2025-02-10 21:57:26'),
(2, 'Склад 2', 'Громовка', '2025-02-10 21:57:26');

-- --------------------------------------------------------

--
-- Структура таблицы `work_logs`
--

CREATE TABLE `work_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `master_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `pieces_produced` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `material_movements`
--
ALTER TABLE `material_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Индексы таблицы `material_stocks`
--
ALTER TABLE `material_stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Индексы таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `production_logs`
--
ALTER TABLE `production_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_plan_date` (`production_plan_id`,`date`);

--
-- Индексы таблицы `production_plans`
--
ALTER TABLE `production_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_id` (`master_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `production_reports`
--
ALTER TABLE `production_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_id` (`master_id`);

--
-- Индексы таблицы `production_report_details`
--
ALTER TABLE `production_report_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Индексы таблицы `product_types`
--
ALTER TABLE `product_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `type_id` (`type_id`);

--
-- Индексы таблицы `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_recipe_material` (`recipe_id`,`material_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Индексы таблицы `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `master_id` (`master_id`);

--
-- Индексы таблицы `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `work_logs`
--
ALTER TABLE `work_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `master_id` (`master_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `material_movements`
--
ALTER TABLE `material_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `material_stocks`
--
ALTER TABLE `material_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `production_logs`
--
ALTER TABLE `production_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT для таблицы `production_plans`
--
ALTER TABLE `production_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `production_reports`
--
ALTER TABLE `production_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `production_report_details`
--
ALTER TABLE `production_report_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `product_types`
--
ALTER TABLE `product_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `work_logs`
--
ALTER TABLE `work_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `material_movements`
--
ALTER TABLE `material_movements`
  ADD CONSTRAINT `material_movements_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `material_movements_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Ограничения внешнего ключа таблицы `material_stocks`
--
ALTER TABLE `material_stocks`
  ADD CONSTRAINT `material_stocks_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `material_stocks_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Ограничения внешнего ключа таблицы `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `production_logs`
--
ALTER TABLE `production_logs`
  ADD CONSTRAINT `production_logs_ibfk_1` FOREIGN KEY (`production_plan_id`) REFERENCES `production_plans` (`id`);

--
-- Ограничения внешнего ключа таблицы `production_plans`
--
ALTER TABLE `production_plans`
  ADD CONSTRAINT `production_plans_ibfk_1` FOREIGN KEY (`master_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `production_plans_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `production_reports`
--
ALTER TABLE `production_reports`
  ADD CONSTRAINT `production_reports_ibfk_1` FOREIGN KEY (`master_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `production_report_details`
--
ALTER TABLE `production_report_details`
  ADD CONSTRAINT `production_report_details_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `production_reports` (`id`),
  ADD CONSTRAINT `production_report_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `product_types` (`id`);

--
-- Ограничения внешнего ключа таблицы `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `product_types` (`id`);

--
-- Ограничения внешнего ключа таблицы `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`),
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Ограничения внешнего ключа таблицы `salaries`
--
ALTER TABLE `salaries`
  ADD CONSTRAINT `salaries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`master_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `work_logs`
--
ALTER TABLE `work_logs`
  ADD CONSTRAINT `work_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `work_logs_ibfk_2` FOREIGN KEY (`master_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
