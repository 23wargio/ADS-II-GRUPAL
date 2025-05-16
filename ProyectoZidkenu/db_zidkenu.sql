-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-05-2025 a las 19:12:43
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

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
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `dni` int(8) NOT NULL,
  `celular` int(9) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT '../assets/foto_perfil/default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nombres`, `apellidos`, `fecha_nacimiento`, `dni`, `celular`, `foto_perfil`, `created_at`) VALUES
(1, 'Cesar123', '$2y$10$IKXhQ4BVYXbukxyf/7HDxugvXhXSU6oiTuKN0FpUJMc1Z52VnLyfu', 'cesar@gmail.com', 'Cesar Adriano', 'Flores Soria', '2005-07-07', 72438452, 976421243, '../assets/foto_perfil/1.png', '2025-05-07 16:20:52'),
(2, 'juan123', '$2y$10$zWGLve7XOeRTA2NxkrE.h.rf0RP80qOqPI0JHLIpYmtqIt.92vfFe', 'juan@gmail.com', 'Juan', 'Incarroca', '2025-04-30', 72381231, 971232134, '../assets/foto_perfil/2.jpg', '2025-05-07 17:12:20'),
(3, 'Josue', '$2y$10$OPCtsRzATR.zPXp/vN9zJu0GqL5a96TIvPRAyQBOBR6QcSIoRlWVq', 'josue@gmail.com', 'Josue', 'Quispe Rivera', '2014-02-04', 72312341, 937274712, '../assets/foto_perfil/default.jpg', '2025-05-07 17:35:01'),
(5, 'b', '$2y$10$R/ky7MUaVEVSXO5JqOoVLuojwB1joWKxydChaIdjyx9Zp4the.87G', 'b@gmail.com', 'b', 'b', '2025-05-08', 74523234, 972374322, '../assets/foto_perfil/default.jpg', '2025-05-07 17:55:39'),
(6, 'prueba', '$2y$10$sdhYSp8sOV1NBWc/Wq7pH.2zasj0GXXPOwWN4AlET7HTKeAUFViTK', 'ja@gmail.com', 'Prueba', 'Lab', '2025-03-13', 74318221, 957123321, '../assets/foto_perfil/default.jpg', '2025-05-09 16:46:21');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
