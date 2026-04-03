-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-03-2026 a las 00:42:31
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
-- Base de datos: `ceis_asistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id_personal` int(11) NOT NULL,
  `cedula` varchar(15) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `foto_perfil` varchar(255) NOT NULL DEFAULT 'default.png',
  `telefono` varchar(20) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `hora_entrada_personalizada` time DEFAULT NULL,
  `hora_salida_personalizada` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id_personal`, `cedula`, `nombres`, `apellidos`, `foto_perfil`, `telefono`, `id_cargo`, `id_usuario`, `hora_entrada_personalizada`, `hora_salida_personalizada`) VALUES
(1, '30710894', 'anthony', 'maita', 'perfil_1772520814_841.png', '04444444444', 1, 1, NULL, NULL),
(7, '55555555', 'Perika', 'Martinez', 'perfil_1772519474_806.png', '55555555555', 3, 7, NULL, NULL),
(8, '12312313', 'hkjhkjhkjhkj', 'gjhbkjhbkjn', 'default.png', '13221322132', 3, 8, NULL, NULL),
(9, '11111111', '111111111111', '111111111111', 'default.png', '11111111111', 4, 9, NULL, NULL),
(10, '76867876', 'jghjhgjhg', 'jhgjghjhgjghj', 'default.png', '56756756756', 3, 10, NULL, NULL),
(11, '90123131', 'djmnajdnkjoasjdsa', 'kdlkasdlpsamdksa', 'default.png', '16514651561', 3, 11, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id_personal`),
  ADD UNIQUE KEY `cedula_unica` (`cedula`),
  ADD KEY `fk_personal_cargo` (`id_cargo`),
  ADD KEY `fk_personal_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `fk_personal_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personal_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
