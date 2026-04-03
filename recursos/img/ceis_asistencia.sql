-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-03-2026 a las 00:43:26
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
  `estado` varchar(30) DEFAULT 'Ausente',
  `motivo_justificacion` text DEFAULT NULL,
  `archivo_evidencia` varchar(255) DEFAULT NULL,
  `estado_justificacion` enum('Pendiente','Aprobada','Rechazada') DEFAULT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id_asistencia`, `id_personal`, `fecha`, `hora_esperada`, `hora_entrada`, `hora_salida`, `estado`, `motivo_justificacion`, `archivo_evidencia`, `estado_justificacion`, `observacion`) VALUES
(1, 1, '2026-03-02', '07:00:00', '12:24:42', '12:24:48', 'Retraso', NULL, NULL, NULL, NULL),
(6, 1, '2026-03-03', '00:00:00', NULL, '00:59:59', 'Justificado', '[Llegada Tardía] - lñlñl', NULL, 'Aprobada', NULL),
(8, 7, '2026-03-03', '07:30:00', '14:33:19', NULL, 'Justificado', '[Llegada Tardía] - lol', NULL, 'Aprobada', NULL),
(9, 1, '2026-03-20', '07:30:00', '13:47:56', NULL, 'Justificado', '[Llegada Tardía] - xxx', NULL, 'Aprobada', NULL),
(10, 8, '2026-03-20', '07:30:00', '13:59:37', '17:58:20', 'Justificado', '[Inasistencia] - lolololol', '8_20260320_1774030071.jpeg', 'Rechazada', NULL),
(11, 9, '2026-03-19', '00:00:00', NULL, NULL, 'Retraso', 'error al editar', NULL, NULL, NULL),
(12, 9, '2026-03-18', '00:00:00', NULL, NULL, 'Justificado', '', NULL, 'Aprobada', NULL),
(13, 9, '2026-03-02', '00:00:00', NULL, NULL, 'Puntual', '', NULL, NULL, NULL),
(14, 11, '2026-03-23', '07:30:00', '22:02:07', NULL, 'Retraso', NULL, NULL, NULL, NULL);

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
(1, '07:30:00', '15:30:00', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `justificaciones`
--

CREATE TABLE `justificaciones` (
  `id_justificacion` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `fecha_falta` date NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_justificacion` enum('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'anthony12', '$2y$10$Go6KKee6IxC10we2mRLHW.zh/YPjZXKYtrRij7POs9rN.20IE6dz2', 1, 'Activo', 1, '$2y$10$pstBukKmaQR4ss3LRg9WZeVSjecc3d6UpIM2GPX.UVIMM6UfSeQaO', 2, '$2y$10$TRPe2Zhu63/Afg7siOVLwesWyzpoPpJSOrtrnwEGCJnPC.EHkcAUO', 3, '$2y$10$c83zZVq6RPUqdhiXOTLdK.AjWPHigmGOQVAVpFnNNV1oz9aKguMbq'),
(7, 'pepe12', '$2y$10$V1JmOzyNFo/a7BGe3wn.iOOEr7CMPisKAAc9gheMbgX7bVNIFsPlK', 3, 'Activo', 1, '$2y$10$T6I175gCD7mETrvESMnW5OVkiSYzwJNQE6hgcP5ATsebsK/W5PVe6', 2, '$2y$10$Y79hZBzKTzVFgFUW7oK0gu1coe/O5GtRTAuQa/oGquxpyM1JgePWi', 3, '$2y$10$U/8jhHFwqfibw4HfwTFFf.dHYSoBvJ0Y4ucnC25lCKjkLG5S9RgzS'),
(8, 'aaaa', '$2y$10$exkj2ntADTfPx0E39jbH8unNt4hR156jiq6lkJkNUY5hmB1fW9Rg.', 3, 'Activo', 1, '$2y$10$nrWFsZUsYb5fXDz9s/NGAO3NnCukMsD8J..ZwpZrh4j5IxjGmABlW', 2, '$2y$10$6cs/3wTW2HLIvrORN4tvM.vM.HJyttrWYUPTpxAUDVfjI5/I0Fz0a', 3, '$2y$10$DGPFla3Fk3SJQGfuaUO3kOG7/gcElHOCoQxwwhlIPHnv4IEB8apVm'),
(9, '1111', '$2y$10$dl0AQKCSNG6CGSE2H4huVO9mCbW1I.fAgNgwhcjqXsUdXBLgeBdvy', 3, 'Activo', 1, '$2y$10$pEh.M7lSmtz7KENPIYpn9.ddYmqA6WJHTA9G6hvL4jxO6hSJzUYk.', 2, '$2y$10$9FuPQYydzaRu4EcdsTr8pedtxJg0c3ZNSm24KsRO1H1XAlOW.28hC', 3, '$2y$10$kd31IVIjIG6UqFHMsKsDvefB5Jy3svArCPgPRI4zKRFDjYCT3NK0.'),
(10, '67576', '$2y$10$xZFk2MC7k1C5xb9WUunfbuIt2bg.Sc8y5hppTzUhRBhmW37Hf.f2e', 3, 'Activo', 2, '$2y$10$9/SCiIjeO/zIobg.c4HFX.sTJaDOatUHDDK0jSIjItbndcUPrwhWG', 1, '$2y$10$xJRIfkDhN7tisNdXwG48VeS/kY6WJRQa9sSJVZkh7FRzcybxFmXfO', 3, '$2y$10$XdL713Xm6dXz1QiagM5L7.uNKvS6AVjpO00ayvF5ya4fO2.M.3OWy'),
(11, '111', '$2y$10$x.cElj4c3Scv3tZjtt/oxOjcYWBlOV/zeocsUJ3OAP08.X/2R1IGm', 3, 'Activo', 1, '$2y$10$YSDAGF1YVT0kue1vasTigenulIuxG5LC8YQfyyvp3OCKTBynhkStm', 2, '$2y$10$YjXEM.bAzZ88DQXCj8jzkeF9sK0KOjJXlnt1lZrmiPnBy/Tii9UCi', 3, '$2y$10$Wu8A5zNEzY8PD9HGNqQ5YejWV671vMAN0KSviftkTnBA54akQ4YZe');

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
-- Indices de la tabla `justificaciones`
--
ALTER TABLE `justificaciones`
  ADD PRIMARY KEY (`id_justificacion`),
  ADD KEY `fk_justificacion_personal` (`id_personal`);

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
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
-- AUTO_INCREMENT de la tabla `justificaciones`
--
ALTER TABLE `justificaciones`
  MODIFY `id_justificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencia_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `justificaciones`
--
ALTER TABLE `justificaciones`
  ADD CONSTRAINT `fk_justificacion_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE;

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
