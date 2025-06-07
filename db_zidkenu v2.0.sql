-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-06-2025 a las 06:55:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_zidkenu`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clients`
--

INSERT INTO `clients` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `tax_id`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Tech Solutions Inc.', 'Ana Pérez', 'contact@techsolutions.com', '987654321', 'Av. Tecnológica 123, Lima', '20567894512', 'Cliente preferencial', 1, '2025-05-10 10:00:00', '2025-05-10 10:00:00'),
(2, 'Diseño Creativo SAC', 'Carlos Rojas', 'info@disenocreativo.com', '912345678', 'Calle Diseño 456, Arequipa', '20654321876', 'Proyectos de diseño gráfico', 2, '2025-05-11 11:30:00', '2025-05-11 11:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `manager_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `status` enum('planning','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'planning',
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `client_id`, `manager_id`, `start_date`, `end_date`, `budget`, `status`, `priority`, `created_at`, `updated_at`) VALUES
(1, 'Sistema de Gestión Zidkenu', 'Desarrollo de sistema de gestión de proyectos', 1, 1, '2025-05-01', '2025-07-30', 15000.00, 'in_progress', 'high', '2025-05-10 12:00:00', '2025-05-10 12:00:00'),
(2, 'Rediseño Web Corporativo', 'Rediseño completo del sitio web', 2, 2, '2025-05-15', '2025-06-30', 8000.00, 'planning', 'medium', '2025-05-11 13:00:00', '2025-05-11 13:00:00'),
(3, 'SADASD', 'DASDASDSADAC SADFADEAS', 2, 2, '2025-05-16', '2026-03-12', 1.00, 'in_progress', 'medium', '2025-05-16 17:43:48', '2025-05-16 17:43:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_reports`
--

CREATE TABLE `project_reports` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('progress','issue','milestone','general') NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `project_reports`
--

INSERT INTO `project_reports` (`id`, `project_id`, `user_id`, `title`, `content`, `type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Inicio del proyecto', 'Se ha iniciado el desarrollo del sistema de gestión', 'milestone', '2025-05-10 12:05:00', '2025-05-10 12:05:00'),
(2, 1, 3, 'Base de datos completada', 'Se ha terminado el diseño e implementación de la base de datos', 'progress', '2025-05-10 12:40:00', '2025-05-10 12:40:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_team`
--

CREATE TABLE `project_team` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('member','leader','observer') NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `project_team`
--

INSERT INTO `project_team` (`project_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 1, 'leader', '2025-05-10 12:00:00'),
(1, 3, 'member', '2025-05-10 12:00:00'),
(1, 5, 'member', '2025-05-10 12:00:00'),
(2, 2, 'leader', '2025-05-11 13:00:00'),
(2, 3, 'member', '2025-05-11 13:00:00'),
(3, 1, 'leader', '2025-05-16 17:43:48'),
(3, 2, 'leader', '2025-05-16 17:43:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('not_started','in_progress','completed','deferred','cancelled') NOT NULL DEFAULT 'not_started',
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `progress` tinyint(3) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `assigned_to`, `created_by`, `due_date`, `status`, `priority`, `progress`, `created_at`, `updated_at`) VALUES
(1, 1, 'Diseño de base de datos', 'Crear el modelo entidad-relación para el sistema', 3, 1, '2025-05-15', 'completed', 'medium', 100, '2025-05-10 12:30:00', '2025-05-23 17:01:18'),
(2, 1, 'Desarrollo del módulo de usuarios', 'Implementar CRUD para usuarios', 5, 1, '2025-05-20', 'in_progress', 'high', 60, '2025-05-10 12:35:00', '2025-05-10 12:35:00'),
(3, 2, 'Reunión inicial con cliente', 'Definir requerimientos del rediseño', 2, 2, '2025-05-16', 'not_started', 'medium', 0, '2025-05-11 13:30:00', '2025-05-11 13:30:00'),
(4, 2, 'werwer', 'werwerwe', 3, 1, '2025-07-29', 'not_started', 'critical', 0, '2025-05-23 16:51:29', '2025-05-23 16:51:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `teams`
--

INSERT INTO `teams` (`id`, `name`, `description`, `code`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'asdasdasd', 'asdasdasd', NULL, 1, '2025-05-23 17:21:12', '2025-05-23 17:21:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('leader','member') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 1, 1, 'leader', '2025-05-23 17:21:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `dni` int(8) NOT NULL,
  `celular` int(9) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT '../assets/foto_perfil/default.png',
  `role` enum('admin','manager','member') NOT NULL DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nombres`, `apellidos`, `fecha_nacimiento`, `dni`, `celular`, `foto_perfil`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Cesar123', '$2y$10$IKXhQ4BVYXbukxyf/7HDxugvXhXSU6oiTuKN0FpUJMc1Z52VnLyfu', 'cesar@gmail.com', 'Cesar Adrianofdsfsdf', 'Flores Soria', '2005-07-07', 72438452, 976421243, '../assets/foto_perfil/1.png', 'admin', '2025-05-07 16:20:52', '2025-05-16 17:54:25'),
(2, 'juan123', '$2y$10$zWGLve7XOeRTA2NxkrE.h.rf0RP80qOqPI0JHLIpYmtqIt.92vfFe', 'juan@gmail.com', 'Juan', 'Incarroca', '2025-04-30', 72381231, 971232134, '../assets/foto_perfil/2.jpg', 'manager', '2025-05-07 17:12:20', '2025-05-07 17:12:20'),
(3, 'Josue', '$2y$10$OPCtsRzATR.zPXp/vN9zJu0GqL5a96TIvPRAyQBOBR6QcSIoRlWVq', 'josue@gmail.com', 'Josue', 'Quispe Rivera', '2014-02-04', 72312341, 937274712, '../assets/foto_perfil/default.jpg', 'member', '2025-05-07 17:35:01', '2025-05-07 17:35:01'),
(5, 'b', '$2y$10$R/ky7MUaVEVSXO5JqOoVLuojwB1joWKxydChaIdjyx9Zp4the.87G', 'b@gmail.com', 'b', 'b', '2025-05-08', 74523234, 972374322, '../assets/foto_perfil/default.jpg', 'member', '2025-05-07 17:55:39', '2025-05-07 17:55:39'),
(6, 'prueba', '$2y$10$sdhYSp8sOV1NBWc/Wq7pH.2zasj0GXXPOwWN4AlET7HTKeAUFViTK', 'ja@gmail.com', 'Prueba', 'Lab', '2025-03-13', 74318221, 957123321, '../assets/foto_perfil/default.jpg', 'member', '2025-05-09 16:46:21', '2025-05-09 16:46:21'),
(8, 'asdasd', '$2y$10$3dFyh55G./QEnSSsRBsY0eUrjRHMiR8QKcpkVzL007O1clsFD5gAi', 'gpoccori@gmail.com', 'Godofredo', 'Umeres', '1999-02-12', 72319874, 123123543, '../assets/foto_perfil/default.jpg', 'member', '2025-05-16 17:55:11', '2025-05-16 17:55:11');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indices de la tabla `project_reports`
--
ALTER TABLE `project_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `project_team`
--
ALTER TABLE `project_team`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_team_code` (`code`);

--
-- Indices de la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_user` (`team_id`,`user_id`),
  ADD KEY `idx_team_id` (`team_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `project_reports`
--
ALTER TABLE `project_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `project_reports`
--
ALTER TABLE `project_reports`
  ADD CONSTRAINT `project_reports_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `project_team`
--
ALTER TABLE `project_team`
  ADD CONSTRAINT `project_team_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_team_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
