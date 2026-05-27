-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-05-2026 a las 17:56:51
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
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id_asistencia` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_esperada` time NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Ausente',
  `motivo_justificacion` text DEFAULT NULL,
  `archivo_evidencia` varchar(255) DEFAULT NULL,
  `estado_justificacion` enum('Pendiente','Aprobada','Rechazada') DEFAULT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id_asistencia`, `id_personal`, `fecha`, `hora_esperada`, `hora_entrada`, `hora_salida`, `estado`, `motivo_justificacion`, `archivo_evidencia`, `estado_justificacion`, `observacion`) VALUES
(1, 1, '2026-05-22', '16:10:00', '15:15:22', NULL, 'Puntual', NULL, NULL, NULL, NULL),
(2, 2, '2026-05-21', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.'),
(3, 2, '2026-05-22', '00:00:00', NULL, NULL, 'Puntual', '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `id_usuario`, `modulo`, `accion`, `detalles`, `fecha_hora`, `ip`) VALUES
(1, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 15:30:00, tolerancia: 60 minutos.', '2026-04-03 22:46:29', '::1'),
(2, 145, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 22:46:47.', '2026-04-03 22:46:47', '::1'),
(3, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:30:00, salida: 02:30:00, tolerancia: 60 minutos.', '2026-04-03 22:51:39', '::1'),
(4, 145, 'Seguridad', 'Descarga de Respaldo Inmediata', 'Generó y descargó: respaldo_ceis_03-04-2026_23-44-30.sql', '2026-04-03 23:44:30', '::1'),
(5, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 03:30:00, tolerancia: 60 minutos.', '2026-04-05 13:28:18', '::1'),
(6, 145, 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_07-04-2026_00-03-33.sql en el servidor.', '2026-04-07 00:03:33', '::1'),
(7, 145, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Abril de 2026.', '2026-04-07 00:04:37', '::1'),
(8, 145, 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_07-04-2026_00-03-33.sql del servidor.', '2026-04-07 00:06:19', '::1'),
(9, 145, 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_07-04-2026_00-09-12.sql en el servidor.', '2026-04-07 00:09:12', '::1'),
(10, 145, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'11111 1111\' en la fecha: 2026-04-07.', '2026-04-07 00:52:50', '::1'),
(11, 145, 'Usuarios', 'Edición de Perfil y Accesos', 'Modificó al usuario \'111\'. Nuevo estado: Activo, Nuevo rol: Subdirector.', '2026-04-07 01:32:50', '::1'),
(12, 145, 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Phrexiel marcó su entrada a las 01:35:44.', '2026-04-07 01:35:44', '::1'),
(13, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 03:36:00, tolerancia: 60 minutos.', '2026-04-07 01:35:55', '::1'),
(14, 145, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 01:36:19.', '2026-04-07 01:36:19', '::1'),
(15, 321, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'Anthony Phrexiel\' en la fecha: 2026-04-07.', '2026-04-07 01:40:13', '::1'),
(16, 145, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'11111 1111\' en la fecha: 2026-04-10.', '2026-04-11 15:22:07', '::1'),
(17, 145, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Phrexiel marcó su entrada a las 19:15:49.', '2026-04-15 19:15:49', '::1'),
(18, 145, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 19:16:04.', '2026-04-15 19:16:04', '::1'),
(19, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 03:10:00, salida: 08:30:00, tolerancia: 60 minutos.', '2026-04-16 15:08:46', '::1'),
(20, 145, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Phrexiel marcó su entrada a las 15:09:01.', '2026-04-16 15:09:01', '::1'),
(21, 145, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 15:09:09.', '2026-04-16 15:09:09', '::1'),
(22, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:10:00, salida: 08:30:00, tolerancia: 60 minutos.', '2026-04-16 15:10:48', '::1'),
(23, 145, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Phrexiel marcó su entrada a las 15:10:51.', '2026-04-16 15:10:51', '::1'),
(24, 322, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado adawdawdwadaw dwadwadwadwa marcó su entrada a las 15:13:29.', '2026-04-16 15:13:29', '::1'),
(25, 322, 'Asistencia', 'Registro de Salida', 'El empleado adawdawdwadaw dwadwadwadwa marcó su salida a las 15:14:08.', '2026-04-16 15:14:08', '::1'),
(26, 145, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-16 15:16:06', '::1'),
(27, 145, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 15:16:55.', '2026-04-16 15:16:55', '::1'),
(28, 145, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'11111 1111\' en la fecha: 2026-04-16.', '2026-04-16 15:22:31', '::1'),
(29, 145, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'11111 1111\' en la fecha: 2026-04-16.', '2026-04-16 15:22:37', '::1'),
(30, 1, 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 15:28:36.', '2026-04-16 15:28:36', '::1'),
(31, 2, 'Asistencia', 'Registro de Entrada', 'El empleado Lauris Viera marcó su entrada a las 15:28:41.', '2026-04-16 15:28:41', '::1'),
(32, 3, 'Asistencia', 'Registro de Entrada', 'El empleado koli vvv marcó su entrada a las 15:33:25.', '2026-04-16 15:33:26', '::1'),
(33, 3, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 30303030) correspondiente a: Abril de 2026.', '2026-04-16 15:33:45', '::1'),
(34, 4, 'Asistencia', 'Registro de Entrada', 'El empleado rita man marcó su entrada a las 15:36:45.', '2026-04-16 15:36:45', '::1'),
(35, 5, 'Asistencia', 'Registro de Entrada', 'El empleado lolo rerere marcó su entrada a las 15:37:39.', '2026-04-16 15:37:39', '::1'),
(36, 6, 'Asistencia', 'Registro de Entrada', 'El empleado kioki lolo marcó su entrada a las 15:40:10.', '2026-04-16 15:40:10', '::1'),
(37, 7, 'Asistencia', 'Registro de Entrada', 'El empleado mobica martin marcó su entrada a las 15:41:17.', '2026-04-16 15:41:17', '::1'),
(38, 8, 'Asistencia', 'Registro de Entrada', 'El empleado lolol d13edwdwa marcó su entrada a las 15:45:02.', '2026-04-16 15:45:02', '::1'),
(39, 9, 'Asistencia', 'Registro de Entrada', 'El empleado for mavi marcó su entrada a las 15:50:33.', '2026-04-16 15:50:33', '::1'),
(40, 10, 'Asistencia', 'Registro de Entrada', 'El empleado lovo qeqwe marcó su entrada a las 15:52:41.', '2026-04-16 15:52:41', '::1'),
(41, 11, 'Asistencia', 'Registro de Entrada', 'El empleado dede dede marcó su entrada a las 15:54:21.', '2026-04-16 15:54:21', '::1'),
(42, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 03:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:37:33', '::1'),
(43, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 02:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:39:37', '::1'),
(44, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 02:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:39:47', '::1'),
(45, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:39:52', '::1'),
(46, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 14:45:07.', '2026-04-24 14:45:07', '::1'),
(47, 13, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado dedeeded dedeed marcó su entrada a las 19:20:22.', '2026-04-24 19:20:22', '::1'),
(48, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-24.', '2026-04-24 19:20:47', '::1'),
(49, 1, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Abril de 2026.', '2026-04-25 14:28:30', '::1'),
(50, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:23:41', '::1'),
(51, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:23:46', '::1'),
(52, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:23:51', '::1'),
(53, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:03', '::1'),
(54, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Temprana\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:08', '::1'),
(55, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Temprana\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:14', '::1'),
(56, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:21', '::1'),
(57, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-23.', '2026-04-25 21:24:25', '::1'),
(58, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Justificado\' para el empleado \'dede dede\' en la fecha: 2026-04-22.', '2026-04-25 21:24:30', '::1'),
(59, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-21.', '2026-04-25 21:24:36', '::1'),
(60, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-20.', '2026-04-25 21:24:40', '::1'),
(61, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'Lauris Viera\' en la fecha: 2026-04-17.', '2026-04-25 23:17:16', '::1'),
(62, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'Lauris Viera\' en la fecha: 2026-04-17.', '2026-04-25 23:17:20', '::1'),
(63, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'Lauris Viera\' en la fecha: 2026-04-17.', '2026-04-25 23:17:28', '::1'),
(64, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 15:30:00, tolerancia: 10 minutos.', '2026-04-25 23:43:26', '::1'),
(65, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-25 23:43:55', '::1'),
(66, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-25 23:45:33', '::1'),
(67, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-25 23:47:41', '::1'),
(68, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'fff\' creada (ID: 3). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-26 22:34:42', '::1'),
(69, 1, 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'fff\' (ID: 3) eliminada.', '2026-04-26 22:34:53', '::1'),
(70, 1, 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'Llegada tardía\' (ID: 2) eliminada.', '2026-04-26 22:38:16', '::1'),
(71, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'frfrrf\' creada (ID: 4). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-26 22:39:41', '::1'),
(72, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-26 22:42:57', '::1'),
(73, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-28 23:09:26', '::1'),
(74, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dededed\' creada (ID: 5). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:00', '::1'),
(75, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dedee\' creada (ID: 6). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:07', '::1'),
(76, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dededede\' creada (ID: 7). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:10', '::1'),
(77, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dedeed\' creada (ID: 8). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:16', '::1'),
(78, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 23:25:58.', '2026-04-28 23:25:58', '::1'),
(79, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 23:26:11.', '2026-04-28 23:26:11', '::1'),
(80, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:03:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:28:04', '::1'),
(81, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:28:14', '::1'),
(82, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:28:24', '::1'),
(83, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 1 min.', '2026-04-28 23:28:29', '::1'),
(84, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 120 min.', '2026-04-28 23:28:43', '::1'),
(85, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 1 min.', '2026-04-28 23:28:48', '::1'),
(86, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:29:00', '::1'),
(87, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 21:34:29.', '2026-04-29 21:34:29', '::1'),
(88, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 21:57:31.', '2026-04-29 21:57:31', '::1'),
(89, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 22:07:38.', '2026-04-29 22:07:38', '::1'),
(90, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 22:23:37.', '2026-04-29 22:23:37', '::1'),
(91, 1, 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'dedeed\' (ID: 8) eliminada.', '2026-04-29 22:35:19', '::1'),
(92, 1, 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'dededede\' (ID: 7) eliminada.', '2026-04-29 22:35:21', '::1'),
(93, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 22:59:32.', '2026-04-29 22:59:32', '::1'),
(94, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'Anthony Maita\'. Cédula: 30710894, Estado: Inactivo, Rol: Director.', '2026-04-29 23:45:10', '::1'),
(95, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'Anthony Maita\'. Cédula: 30710894, Estado: Activo, Rol: Director.', '2026-04-29 23:45:21', '::1'),
(96, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 11:32:49.', '2026-04-30 11:32:49', '::1'),
(97, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 11:36:52.', '2026-04-30 11:36:52', '::1'),
(98, 1, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Abril de 2026.', '2026-04-30 11:41:07', '::1'),
(99, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 17:30:00, tolerancia: 30 min.', '2026-04-30 11:49:33', '::1'),
(100, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-22.', '2026-04-30 13:16:07', '::1'),
(101, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-22.', '2026-04-30 13:16:17', '::1'),
(102, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-23.', '2026-04-30 13:16:21', '::1'),
(103, 1, 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_30-04-2026_13-48-08.sql en el servidor.', '2026-04-30 13:48:08', '::1'),
(104, 1, 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_30-04-2026_13-48-08.sql del servidor.', '2026-04-30 13:48:25', '::1'),
(105, 1, 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_30-04-2026_15-40-22.sql en el servidor.', '2026-04-30 15:40:22', '::1'),
(106, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 17:12:56.', '2026-05-01 17:12:56', '::1'),
(107, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 17:30:00, tolerancia: 30 min.', '2026-05-01 17:13:23', '::1'),
(108, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 15:30:00, tolerancia: 30 min.', '2026-05-01 17:13:32', '::1'),
(109, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 17:13:59.', '2026-05-01 17:13:59', '::1'),
(110, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 14:15:42.', '2026-05-07 14:15:42', '::1'),
(111, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 17:30:00, tolerancia: 30 min.', '2026-05-07 14:15:58', '::1'),
(112, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 14:21:15.', '2026-05-07 14:21:15', '::1'),
(113, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 14:22:24.', '2026-05-07 14:22:24', '::1'),
(114, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:00:00, salida: 15:30:00, tolerancia: 30 min.', '2026-05-07 14:37:10', '::1'),
(115, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min. Plantilla ID 5 marcada como activa.', '2026-05-08 16:41:11', '::1'),
(116, 1, 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'dededed\' (ID: 5) eliminada.', '2026-05-08 16:41:18', '::1'),
(117, 1, 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'dedee\' (ID: 6) eliminada.', '2026-05-08 16:41:21', '::1'),
(118, 1, 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'prueba\' creada (ID: 9). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-05-08 16:41:32', '::1'),
(119, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 17:51:45.', '2026-05-08 17:51:45', '::1'),
(120, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 17:51:53.', '2026-05-08 17:51:53', '::1'),
(121, 1, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 17:53:29.', '2026-05-08 17:53:29', '::1'),
(122, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dede dede\'. Cédula: 21312312, Estado: Activo, Rol: Personal.', '2026-05-08 17:54:14', '::1'),
(123, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dede dede\'. Cédula: 21312312, Estado: Activo, Rol: Personal.', '2026-05-08 17:55:04', '::1'),
(124, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dede dede\'. Cédula: 21312312, Estado: Activo, Rol: Personal.', '2026-05-08 17:55:15', '::1'),
(125, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dede dede\'. Cédula: 21312312, Estado: Activo, Rol: Personal.', '2026-05-08 17:56:39', '::1'),
(126, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dede dede\'. Cédula: 300000000000, Estado: Activo, Rol: Personal.', '2026-05-08 17:56:48', '::1'),
(127, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dede dedededede\'. Cédula: 300000000000, Estado: Activo, Rol: Personal.', '2026-05-08 17:56:56', '::1'),
(128, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dedededede dedededede\'. Cédula: 300000000000, Estado: Activo, Rol: Personal.', '2026-05-08 17:57:00', '::1'),
(129, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dedededede dedededede\'. Cédula: 300000000000, Estado: Activo, Rol: Personal.', '2026-05-08 17:57:06', '::1'),
(130, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dedededede dedededede\'. Cédula: 300000000000, Estado: Activo, Rol: Personal.', '2026-05-08 17:57:21', '::1'),
(131, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dedededede dedededede\'. Cédula: 300000000000, Estado: Activo, Rol: Personal.', '2026-05-08 17:57:24', '::1'),
(132, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 22:16:37.', '2026-05-08 22:16:37', '::1'),
(133, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'dedededede dedededede\'. Cédula: 300000000000, Estado: Activo, Rol: Subdirector.', '2026-05-08 22:16:52', '::1'),
(134, 11, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado dedededede dedededede marcó su entrada a las 22:18:05.', '2026-05-08 22:18:05', '::1'),
(135, 11, 'Asistencia', 'Registro de Salida', 'El empleado dedededede dedededede marcó su salida a las 22:18:06.', '2026-05-08 22:18:06', '::1'),
(136, 11, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'Anthony Maita\' en la fecha: 2026-05-08.', '2026-05-08 22:19:10', '::1'),
(137, 11, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'Anthony Maita\' en la fecha: 2026-05-08.', '2026-05-08 22:19:14', '::1'),
(138, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-05-08 22:24:04', '::1'),
(139, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'for mavi\'. Cédula: 12321312, Estado: Activo, Rol: Director.', '2026-05-08 22:29:16', '::1'),
(140, 1, 'Seguridad', 'Restauración Exitosa', 'Restauró la base de datos con: respaldo_ceis_08-05-2026_22-29-53.sql', '2026-05-20 23:20:32', '::1'),
(141, 1, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 30710894) correspondiente a: TODO EL AÑO de 2026.', '2026-05-20 23:21:09', '::1'),
(142, 1, 'Asistencia', 'Justificación por rango', 'Justificó al personal ID 11 desde 2026-05-01 hasta 2026-05-31 (21 días hábiles en total).', '2026-05-21 12:45:41', '::1'),
(143, 1, 'Asistencia', 'Justificación por rango', 'Justificó al personal ID 11 desde 2026-05-06 hasta 2026-05-13 (6 días hábiles en total) adjuntando 0 evidencias.', '2026-05-21 13:41:26', '::1'),
(144, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 16:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-05-21 15:52:45', '::1'),
(145, 1, 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 15:52:50.', '2026-05-21 15:52:50', '::1'),
(146, 2, 'Asistencia', 'Registro de Entrada', 'El empleado lauris viera marcó su entrada a las 15:56:13.', '2026-05-21 15:56:13', '::1'),
(147, 1, 'Asistencia', 'Justificación por rango', 'Justificó al personal ID 2 desde 2026-04-01 hasta 2026-05-20 (36 días hábiles en total) adjuntando 0 evidencias.', '2026-05-21 15:59:05', '::1'),
(148, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-21 16:51:15', '::1'),
(149, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Justificado\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-21 16:51:19', '::1'),
(150, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Falta\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-21 16:51:25', '::1'),
(151, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Salida Irregular\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-21 16:51:28', '::1'),
(152, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-21 16:51:37', '::1'),
(153, 1, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 14669420) correspondiente a: Abril de 2026.', '2026-05-21 17:20:13', '::1'),
(154, 1, 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 12:58:16.', '2026-05-22 12:58:16', '::1'),
(155, 1, 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 14:00:00.', '2026-05-22 14:00:00', '::1'),
(156, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'lauris viera\'. Cédula: 14669420, Estado: Activo, Rol: Subdirector.', '2026-05-22 14:01:19', '::1'),
(157, 1, 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'lauris viera\'. Cédula: 14669420, Estado: Activo, Rol: Subdirector.', '2026-05-22 14:01:38', '::1'),
(158, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-22 14:02:57', '::1'),
(159, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-22 14:15:06', '::1'),
(160, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-22 14:15:14', '::1'),
(161, 2, 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado lauris viera marcó su entrada a las 14:31:57.', '2026-05-22 14:31:57', '::1'),
(162, 2, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'Anthony Maita\' en la fecha: 2026-05-22.', '2026-05-22 14:32:45', '::1'),
(163, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-22 14:35:46', '::1'),
(164, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Temprana\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-22 14:35:58', '::1'),
(165, 2, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'Anthony Maita\' en la fecha: 2026-05-21.', '2026-05-22 14:37:21', '::1'),
(166, 1, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: TODO EL AÑO de 2026.', '2026-05-22 15:02:09', '::1'),
(167, 1, 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 14669420) correspondiente a: TODO EL AÑO de 2026.', '2026-05-22 15:13:49', '::1'),
(168, 1, 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 15:15:22.', '2026-05-22 15:15:22', '::1'),
(169, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'lauris viera\' en la fecha: 2026-05-21.', '2026-05-22 15:15:29', '::1'),
(170, 1, 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'lauris viera\' en la fecha: 2026-05-22.', '2026-05-22 15:15:34', '::1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL,
  `nombre_cargo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id_cargo`, `nombre_cargo`) VALUES
(1, 'Directora'),
(2, 'Subdirectora'),
(3, 'Docente de Aula'),
(4, 'Personal Administrativo'),
(5, 'Personal Obrero'),
(6, 'Docente Especialista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id_config` int(11) NOT NULL,
  `hora_entrada_general` time NOT NULL DEFAULT '07:00:00',
  `hora_salida_general` time NOT NULL DEFAULT '13:00:00',
  `minutos_tolerancia` int(11) NOT NULL DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id_config`, `hora_entrada_general`, `hora_salida_general`, `minutos_tolerancia`) VALUES
(1, '16:10:00', '20:30:00', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones_preestablecidas`
--

CREATE TABLE `configuraciones_preestablecidas` (
  `id_preestablecida` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `minutos_tolerancia` tinyint(4) NOT NULL DEFAULT 0,
  `es_activa` tinyint(1) NOT NULL DEFAULT 0,
  `id_usuario_creador` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Plantillas horarias preestablecidas para carga rápida en el formulario de configuración';

--
-- Volcado de datos para la tabla `configuraciones_preestablecidas`
--

INSERT INTO `configuraciones_preestablecidas` (`id_preestablecida`, `nombre`, `hora_entrada`, `hora_salida`, `minutos_tolerancia`, `es_activa`, `id_usuario_creador`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(9, 'prueba', '14:10:00', '20:30:00', 30, 0, 1, '2026-05-08 16:41:32', '2026-05-08 16:41:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

CREATE TABLE `feriados` (
  `id_feriado` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `tipo` enum('Informativa','Exito','Alerta') DEFAULT 'Informativa',
  `leido` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `mensaje`, `tipo`, `leido`, `fecha_creacion`) VALUES
(1, 321, '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-07 00:54:19'),
(2, 321, 'ATENCIÓN: Tu justificación del día 07-04-2026 ha sido RECHAZADA. Revisa las observaciones.', 'Alerta', 1, '2026-04-07 01:11:51'),
(3, 321, 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: Por gay', 'Alerta', 1, '2026-04-07 01:31:42'),
(4, 321, 'Administración ha modificado tu perfil. Nuevo rol: Subdirector. Estado: Activo.', 'Informativa', 1, '2026-04-07 01:32:50'),
(5, 145, 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: lololooooooooooooooooooooooooooookdsokfñskfñokesñfkñsekfñskefñokseñfkifgjerjfmtrtuoweugerkvrjhgoierkfkerpf\n\n\ndejiofjreifjerifoiergoijoerg\n\n\nekjfirjfirejfoirefoijefoijeriofjweijdfqjwfjdhe', 'Alerta', 1, '2026-04-07 01:36:57'),
(6, 145, '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-07 01:39:37'),
(7, 145, 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'Alerta', 1, '2026-04-07 01:40:49'),
(8, 145, 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Alerta', 1, '2026-04-07 01:45:47'),
(9, 145, 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Alerta', 1, '2026-04-07 01:46:39'),
(10, 145, '¡Tu justificación del día 15-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-16 15:11:15'),
(11, 322, '¡Tu justificación del día 16-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-16 15:13:58'),
(12, 322, '¡Tu justificación del día 16-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-16 15:14:32'),
(13, 145, '¡Tu justificación del día 16-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-16 15:17:06'),
(14, 13, '¡Tu justificación del día 01-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 14:37:23'),
(15, 1, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 14:38:33'),
(16, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 14:38:35'),
(17, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:03:02'),
(18, 13, '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:03:04'),
(19, 13, 'ATENCIÓN: Tu justificación del 24-04-2026 ha sido RECHAZADA. Motivo: porquwe si', 'Alerta', 1, '2026-04-24 15:15:22'),
(20, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:15:50'),
(21, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:18:08'),
(22, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:23:59'),
(23, 13, 'ATENCIÓN: Tu justificación del 24-04-2026 ha sido RECHAZADA. Motivo: 33', 'Alerta', 1, '2026-04-24 15:25:08'),
(24, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:29:18'),
(25, 13, '¡Tu justificación del día 10-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:34:12'),
(26, 13, '¡Tu justificación del día 13-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:36:14'),
(27, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 15:38:51'),
(28, 1, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-24 17:20:13'),
(29, 1, 'ATENCIÓN: Tu justificación del 24-04-2026 ha sido RECHAZADA. Motivo: sffse', 'Alerta', 1, '2026-04-24 17:21:42'),
(30, 13, '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', 0, '2026-04-24 19:20:31'),
(31, 1, '¡Tu justificación del día 13-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-28 23:19:02'),
(32, 1, '¡Tu justificación del día 28-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-28 23:26:29'),
(33, 1, '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-28 23:30:05'),
(34, 1, '¡Tu justificación del día 29-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 21:34:46'),
(35, 1, '¡Tu justificación del día 29-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 22:09:58'),
(36, 1, '¡Tu justificación del día 09-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 22:10:00'),
(37, 1, 'ATENCIÓN: Tu justificación del 29-04-2026 ha sido RECHAZADA. Motivo: gygihiuhuihuhiuhiuhuhihuihiuhihihuihiuhiuhiuh', 'Alerta', 1, '2026-04-29 22:13:20'),
(38, 1, '¡Tu justificación del día 29-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 22:23:41'),
(39, 1, '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 22:52:33'),
(40, 1, '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 23:29:16'),
(41, 1, '¡Tu justificación del día 09-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 23:29:19'),
(42, 1, '¡Tu justificación del día 30-03-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-29 23:44:23'),
(43, 1, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-04-29 23:45:10'),
(44, 1, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-04-29 23:45:21'),
(45, 1, '¡Tu justificación del día 30-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-30 11:36:48'),
(46, 1, '¡Tu justificación del día 30-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-30 11:37:24'),
(47, 1, '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', 1, '2026-04-30 11:38:17'),
(48, 1, '¡Tu justificación del día 01-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-01 17:12:59'),
(49, 1, 'ATENCIÓN: Tu justificación del 07-05-2026 ha sido RECHAZADA. Motivo: no weon', 'Alerta', 1, '2026-05-07 14:21:03'),
(50, 1, '¡Tu justificación del día 07-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-07 15:51:54'),
(51, 1, '¡Tu justificación del día 08-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-08 17:51:49'),
(52, 1, '¡Tu justificación del día 08-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-08 17:52:11'),
(53, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:54:14'),
(54, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:55:04'),
(55, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:55:15'),
(56, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:56:39'),
(57, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:56:48'),
(58, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:56:56'),
(59, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:57:00'),
(60, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:57:06'),
(61, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:57:21'),
(62, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 17:57:24'),
(63, 11, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-08 22:16:52'),
(64, 1, '¡Tu justificación del día 08-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-08 22:18:26'),
(65, 11, '¡Tu justificación del día 08-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-08 22:18:41'),
(66, 9, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 0, '2026-05-08 22:29:16'),
(67, 11, '¡Se han justificado 21 días de tu asistencia por motivo de: Esta de reposo medico jeje!', 'Exito', 0, '2026-05-21 12:45:41'),
(68, 11, '¡Se han justificado 6 días de tu asistencia por motivo de: kmkmkmkmkmkk!', 'Exito', 0, '2026-05-21 13:41:26'),
(69, 2, '¡Se han justificado 36 días de tu asistencia por motivo de: wswwsswwsswswswswwssw!', 'Exito', 1, '2026-05-21 15:59:05'),
(70, 2, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-22 14:01:19'),
(71, 2, 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', 1, '2026-05-22 14:01:38'),
(72, 1, '¡Tu justificación del día 22-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-22 14:02:27');

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
  `hora_salida_personalizada` time DEFAULT NULL,
  `fecha_ingreso` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id_personal`, `cedula`, `nombres`, `apellidos`, `foto_perfil`, `telefono`, `id_cargo`, `id_usuario`, `hora_entrada_personalizada`, `hora_salida_personalizada`, `fecha_ingreso`) VALUES
(1, '30710894', 'Anthony', 'Maita', 'default.png', '04444444444', 1, 1, NULL, NULL, '2026-05-21'),
(2, '14669420', 'lauris', 'viera', 'default.png', '04115615616', 4, 2, NULL, NULL, '2026-05-21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Personal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `pregunta_1` int(11) NOT NULL,
  `respuesta_1` varchar(255) NOT NULL,
  `pregunta_2` int(11) NOT NULL,
  `respuesta_2` varchar(255) NOT NULL,
  `pregunta_3` int(11) NOT NULL,
  `respuesta_3` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `password`, `id_rol`, `estado`, `pregunta_1`, `respuesta_1`, `pregunta_2`, `respuesta_2`, `pregunta_3`, `respuesta_3`) VALUES
(1, 'anthony12', '$2y$10$Uv4ayT6GjpFI00XZNDq5N.IkQz65VlQCjH3E9M/RaxG41drYX7kZ6', 1, 'Activo', 1, '$2y$10$htzzRkd.nQPZDgvW8u2iCeYkFCyGHKJh41brG/LsKPAIxcMbzxt46', 3, '$2y$10$Vkwtfx5OJKjRLNPoNiVo0uTsngmiK1QX.Y1fCgQNPE4g3EuzCsS26', 2, '$2y$10$SQXHaHK0IMCzDLseoAKEZ.abI1Mz6RjIUy9UlmZ1f8T2IxclDBecO'),
(2, 'lauris12', '$2y$10$mxVSuk/k3G079pL4SlkLeuDebbUiUll4VQz0pag3YCKYr7IAQItqS', 2, 'Activo', 1, '$2y$10$vkJoVAbfzU8bLcTCs3jzs.EUZuY/swj3.rh8OBBiZvd3Edy/ehlQO', 2, '$2y$10$3/FCEh/fdozGYu54H2jZOeoKKvmYhRXKmjFxXw1GxrEkS2kY4rVOW', 3, '$2y$10$kHhXOkZbwtqintnKuNXevu37RRcGepOdzHJRTkhKs7MduOpmXAHI2');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `asistencia_unica_dia` (`id_personal`,`fecha`),
  ADD KEY `fk_asistencia_personal` (`id_personal`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `fk_bitacora_usuario` (`id_usuario`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id_config`);

--
-- Indices de la tabla `configuraciones_preestablecidas`
--
ALTER TABLE `configuraciones_preestablecidas`
  ADD PRIMARY KEY (`id_preestablecida`),
  ADD KEY `idx_usuario_creador` (`id_usuario_creador`);

--
-- Indices de la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id_feriado`),
  ADD UNIQUE KEY `fecha_unica` (`fecha`),
  ADD KEY `fk_feriados_usuario` (`id_usuario`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_notif_usuario` (`id_usuario`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id_personal`),
  ADD UNIQUE KEY `cedula_unica` (`cedula`),
  ADD KEY `fk_personal_cargo` (`id_cargo`),
  ADD KEY `fk_personal_usuario` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario_unico` (`nombre_usuario`),
  ADD KEY `fk_usuario_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuraciones_preestablecidas`
--
ALTER TABLE `configuraciones_preestablecidas`
  MODIFY `id_preestablecida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id_feriado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencia_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD CONSTRAINT `fk_feriados_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `fk_personal_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personal_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
