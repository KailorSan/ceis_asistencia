-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2026 a las 21:08:07
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
(1, 1, '2026-05-07', '12:50:00', '14:22:24', NULL, 'Justificado', '[Inasistencia] - eddedededededede', NULL, 'Aprobada', NULL);

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
(114, 1, 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:00:00, salida: 15:30:00, tolerancia: 30 min.', '2026-05-07 14:37:10', '::1');

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
(1, '10:00:00', '15:30:00', 30);

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
(5, 'dededed', '14:10:00', '20:30:00', 30, 0, 1, '2026-04-28 23:10:00', '2026-04-28 23:10:00'),
(6, 'dedee', '14:10:00', '20:30:00', 30, 0, 1, '2026-04-28 23:10:07', '2026-04-28 23:10:07');

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
(50, 1, '¡Tu justificación del día 07-05-2026 ha sido APROBADA!', 'Exito', 1, '2026-05-07 15:51:54');

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
(1, '30710894', 'Anthony', 'Maita', 'default.png', '04040404040', 1, 1, '12:50:00', '16:03:00', '2026-04-16'),
(2, '14669420', 'Lauris', 'Viera', 'default.png', '01010101010', 4, 2, NULL, NULL, '2026-04-16'),
(3, '30303030', 'koli', 'vvv', 'default.png', '13213213213', 3, 3, NULL, NULL, '2026-04-16'),
(4, '89798789', 'rita', 'man', 'default.png', '13165464545', 5, 4, NULL, NULL, '2026-04-16'),
(5, '42342342', 'lolo', 'rerere', 'default.png', '43123212312', 6, 5, NULL, NULL, '2026-04-16'),
(6, '13213543', 'kioki', 'lolo', 'default.png', '04128974532', 3, 6, NULL, NULL, '2026-04-16'),
(7, '12331231', 'mobica', 'martin', 'default.png', '45654654654', 3, 7, NULL, NULL, '2026-04-16'),
(8, '46848944', 'lolol', 'd13edwdwa', 'default.png', '65456456165', 4, 8, NULL, NULL, '2026-04-16'),
(9, '12321312', 'for', 'mavi', 'default.png', '31231232131', 4, 9, NULL, NULL, '2026-04-16'),
(10, '53454354', 'lovo', 'qeqwe', 'default.png', '12312313131', 5, 10, NULL, NULL, '2026-04-16'),
(11, '21312312', 'dede', 'dede', 'default.png', '12323213123', 4, 11, NULL, NULL, '2026-04-16'),
(13, '12313123', 'dedeeded', 'dedeed', 'default.png', '12343242432', 4, 13, NULL, NULL, '2026-04-24');

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
(1, 'Director'),
(2, 'Subdirector'),
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
(1, 'anthony12', '$2y$10$DRWhHJmwnZRtbcyxXGpCyeGojFfPIddcj12gopj1x6ZwQs6M/8PbW', 1, 'Activo', 1, '$2y$10$BVyAkwrRzbBJ9tuAd2knsehVnQ8Qz6NXtpcJXU2GGggvUNtLU5Zt.', 2, '$2y$10$.bwdaZgtiZj38qIqFF43q.VdDmsObq6u40Qsr5AY2BLV4J8BOpZya', 3, '$2y$10$MrLRjTejXJVDLbswIiicBukk1CmRq1Wx1TXKo3RGoqofaOgNHKT8u'),
(2, 'lauri12', '$2y$10$jF3ZtGPxA9zaa8AiarONNueLijyAuwB0aI5P3ByVMJ1Nm1d1Ft8GK', 3, 'Activo', 3, '$2y$10$xbV.zubjzllTApaKNV3PWuyUdPoMhUDhswls0i4T8u/vVy3Z8iBVO', 1, '$2y$10$XexaOW3gdbHhzh308McX9OaIFOedZPGuA0uwurtNn9R2WruWhHth2', 4, '$2y$10$VIQGUc3MmqYLC7wqM48OPuQ3.TNNMn8.1Wb7iAoukAXSXzM0ErJTG'),
(3, 'ami', '$2y$10$gDwc4CDjCK0xMwJnP7USpOS4z9vaktD10y0J2cIfBClMvpjruZYWu', 3, 'Activo', 1, '$2y$10$Da3VYXXG2mClbVhRIfZ7VuVlIBuGzzv8NVRQZqZd4aLtK57pPK9dK', 2, '$2y$10$YrzcoyAPCyS48/nIoBdvzeseSSkWwTEqvwofTjXAAJlse3840sBrC', 3, '$2y$10$s1tgL9zNMWS3oiMK5RgZYeXbyW77NK7utl2K0xmZZVREho1JtTgie'),
(4, 'moon', '$2y$10$abtBC7ZvTeTF.SDrtdKRGe8ErWG3ZkzGLeFQocLxJ.A5pIIqqr3yO', 3, 'Activo', 1, '$2y$10$0L4O4R8mXguJcLvwbDSeNu.uBMRYiUbI.TKIqHMJ89ai/BpQL3huG', 2, '$2y$10$ivRwcqCx317Fy3PYbGMvS.qIydlVxlEDHTfzOKXJTcsCnEWs3FoKW', 4, '$2y$10$Z5hvOmU8Y4g7l/04EonN5OJGN55p3LBPoD023BSDJ6rfvKKJSzojm'),
(5, 'juan12', '$2y$10$v4CQMbEO9ycIyyiqbvH/pOCmKMidiSs1tIYojcWv7VN8WlUnjwdLy', 3, 'Activo', 1, '$2y$10$Ei.mjjIKBgb6xyQlIVV/q.czJBFC.NRL39EcZl/X.0lxbWn2yckry', 2, '$2y$10$owY7aZItWFGqFUTPtxVMTu4Cd7cXJB.a8/lcgw6emvo5wu.n0E.jK', 3, '$2y$10$3n6fKqmYI63BPLWioB2NdejGeHnk6ZLbTr7VeIsAMlfpf78nGm8Le'),
(6, 'bece', '$2y$10$B5rYIYfwkB5Loyav983ZE.EYcYcSxuM/otW2W9ij.7K5r8pAA/KlK', 3, 'Activo', 2, '$2y$10$LKDKp.0sTH6YfkB063Ir5ONNs/x.72iAX11WOORdxj5z05j9oEO2S', 3, '$2y$10$fA8valgK7mjUqBr8bpBCH.knVnvSJ/arPl7ckZL0EbEtKTpBL4IPm', 1, '$2y$10$JV0z3H91z93mophwnOkYBuI.7n2KxhEhjoj0wE8UQUzfA4LS8jzh.'),
(7, 'movi', '$2y$10$a00zM.TNxCXvm7fbzEasxOaF9thu2a1MznrLQzI86qY0bLY2Hleh.', 3, 'Activo', 1, '$2y$10$UP9GSTyxBf4Ub23npLox4OXKjK8jVoLD0nPyKKaUZ05FWlgVn9Jpq', 3, '$2y$10$/cjTepaPYyv.vzsdRAmDqOHhVUvb1QjaHZi1LI8vR/pGDlw8.tKEK', 2, '$2y$10$AZuT5ZpIyZSJla1WWjBPMOzyYgRcBabC/JwJKQOHGxrwV1drTqdFO'),
(8, 'ñopo', '$2y$10$Hk7z6LmWUNlEa/pFZQiRSeeJ9oBmowz0eicPERtF8Lp.OyzFLB5hu', 3, 'Activo', 1, '$2y$10$qRpfhY8QnAqK5ATaBub2O.HeK5pqnb7RvKau7cZ4SVj.RB1SjStCu', 2, '$2y$10$Uw4bU6tcXrsKs0qPN8WixOb1f3blRklOdpfAAnWNl48xuR7VqvTEW', 3, '$2y$10$EjXnUL6x.4er.p3V8DHQLueyUDwErhrlRK0YGSr2r6frstnna2KUq'),
(9, 'kilo', '$2y$10$7TxzFhUFLpzPtEa8aK4kuupWMjZ.BwOLZhyzF8bWo0gRu8v7.ZR36', 3, 'Activo', 1, '$2y$10$JLCzf3By/uCLyiORP5BCAOk9dfxZcXXvIMNu85UNSkQigMiN26M2W', 2, '$2y$10$PSdVNHNlIKLkOiXIdI8owuS2RA3YhSevns6K8GZmYbYH7f/CZ3gmi', 4, '$2y$10$VgD9H3bWsnkXokgmXF0G.uMNeoQvFVhJIR4iP//A612bP/4Dpfpk6'),
(10, 'polo', '$2y$10$bV3d1jbQ9PhApPxIErjWWOK7chkzvpRrrbKfnjBDli/Sy3BQz0Zei', 3, 'Activo', 1, '$2y$10$supjFoYp3.RS0A0cTBJX4O9Rm4fvHdEAPvipmD2WnojYCpjFLkbjq', 3, '$2y$10$70OVgjaeBVCqNIJRdYCW1uUXg2.Vfc2zwvBKlTxm3KLRKKtAL7EnW', 2, '$2y$10$2wukGQejN5n9gyWdvwuPu.OS./1ph0bM9XAp.N3Q73QRE4fOrjSY.'),
(11, 'jum', '$2y$10$Ep.hIzcFJB.CH8z9zoWoNuejx9rCiHsaV1ggH/bX3zfHVY1F2tscO', 3, 'Activo', 1, '$2y$10$.AB0icSJQkumkiK1Ct4H9u09c6y5EX57xUxr24N/py8Pih7K.mLRa', 2, '$2y$10$JjJk1L5w7pL5LRkaHrSEteDgLqJLQOoRnj/geyttY3sk1b3XqxkMK', 3, '$2y$10$jYRcGvrjMODzC027RGJYwuSbh3PbHrzk/7RgCIfOP9UI4QAsdwu7.'),
(13, 'meme', '$2y$10$5HO2n8qaANePkG/S.A.5cO02iDmT2UOPymhfqfaXmRFIWYtxm.dA.', 3, 'Activo', 2, '$2y$10$9HpLZQMzS16TnNsPXl8MMuJb91zHxC8ZaMGue9fU1O6oO3Yowt906', 1, '$2y$10$ISA.Uy331aXBIi8Nsy76Gu6wyLAud3vfkc02bXf2zssfr9I1Z9Qqy', 3, '$2y$10$4FfhXVHmFZuDpwyXxpglneiQgA8lETdj3.uNlbDEUQlGxF5RSh0L6');

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
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

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
  MODIFY `id_preestablecida` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
