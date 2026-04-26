-- Respaldo del Sistema CEIS Julian Yánez
-- Generado el: 12/04/2026 02:54:21 PM
-- Generado por: anthony12

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `asistencias`;
CREATE TABLE `asistencias` (
  `id_asistencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_personal` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_esperada` time NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Ausente',
  `motivo_justificacion` text DEFAULT NULL,
  `archivo_evidencia` varchar(255) DEFAULT NULL,
  `estado_justificacion` enum('Pendiente','Aprobada','Rechazada') DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `asistencia_unica_dia` (`id_personal`,`fecha`),
  KEY `fk_asistencia_personal` (`id_personal`),
  CONSTRAINT `fk_asistencia_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `asistencias` VALUES('1', '144', '2026-04-03', '20:10:00', '22:46:45', '22:46:47', 'Retraso', '[Llegada Tardía] - porque si', NULL, 'Aprobada', NULL);
INSERT INTO `asistencias` VALUES('2', '301', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('3', '302', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('4', '303', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('5', '304', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('6', '305', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('7', '306', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('8', '307', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('9', '308', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('10', '309', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('11', '310', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('12', '311', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('13', '312', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('14', '313', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('15', '314', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('16', '315', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('17', '316', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('18', '317', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('19', '318', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('20', '319', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('21', '320', '2026-04-03', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('22', '144', '2026-04-05', '07:30:00', '13:28:36', NULL, 'Retraso y Salida Irregular', '[Llegada Tardía] - 333', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('23', '144', '2026-04-02', '07:30:00', NULL, '13:33:23', 'Salida Temprana', '[Salida Temprana] - deede', NULL, 'Aprobada', 'Salió a una hora más temprana - mediante justificación aprobada a las 01:33 PM.');
INSERT INTO `asistencias` VALUES('24', '144', '2026-04-01', '07:30:00', NULL, '13:32:32', 'Salida Temprana', '[Salida Temprana] - diantres', NULL, 'Aprobada', 'Salió a una hora más temprana - mediante justificación aprobada a las 01:32 PM.');
INSERT INTO `asistencias` VALUES('25', '321', '2026-04-07', '07:30:00', NULL, NULL, 'Falta', '[Llegada Tardía] - deeded', NULL, 'Rechazada', '[Denegada: Por gay]');
INSERT INTO `asistencias` VALUES('26', '144', '2026-04-07', '07:30:00', '01:35:44', '01:36:19', 'Salida Irregular', '[Salida Temprana] - swsw', NULL, 'Rechazada', '[Denegada: lololooooooooooooooooooooooooooookdsokfñskfñokesñfkñsekfñskefñokseñfkifgjerjfmtrtuoweugerkvrjhgoierkfkerpf\n\n\ndejiofjreifjerifoiergoijoerg\n\n\nekjfirjfirejfoirefoijefoijeriofjweijdfqjwfjdheriufhgoiwedjvjkfergh   \n\nfkforkforkf] [Denegada: eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee] [Denegada: ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd] [Denegada: dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd]');
INSERT INTO `asistencias` VALUES('27', '321', '2026-04-10', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');


DROP TABLE IF EXISTS `bitacora`;
CREATE TABLE `bitacora` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_bitacora`),
  KEY `fk_bitacora_usuario` (`id_usuario`),
  CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT INTO `bitacora` VALUES('1', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 15:30:00, tolerancia: 60 minutos.', '2026-04-03 22:46:29', '::1');
INSERT INTO `bitacora` VALUES('2', '145', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 22:46:47.', '2026-04-03 22:46:47', '::1');
INSERT INTO `bitacora` VALUES('3', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:30:00, salida: 02:30:00, tolerancia: 60 minutos.', '2026-04-03 22:51:39', '::1');
INSERT INTO `bitacora` VALUES('4', '145', 'Seguridad', 'Descarga de Respaldo Inmediata', 'Generó y descargó: respaldo_ceis_03-04-2026_23-44-30.sql', '2026-04-03 23:44:30', '::1');
INSERT INTO `bitacora` VALUES('5', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 03:30:00, tolerancia: 60 minutos.', '2026-04-05 13:28:18', '::1');
INSERT INTO `bitacora` VALUES('6', '145', 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_07-04-2026_00-03-33.sql en el servidor.', '2026-04-07 00:03:33', '::1');
INSERT INTO `bitacora` VALUES('7', '145', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Abril de 2026.', '2026-04-07 00:04:37', '::1');
INSERT INTO `bitacora` VALUES('8', '145', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_07-04-2026_00-03-33.sql del servidor.', '2026-04-07 00:06:19', '::1');
INSERT INTO `bitacora` VALUES('9', '145', 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_07-04-2026_00-09-12.sql en el servidor.', '2026-04-07 00:09:12', '::1');
INSERT INTO `bitacora` VALUES('10', '145', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'11111 1111\' en la fecha: 2026-04-07.', '2026-04-07 00:52:50', '::1');
INSERT INTO `bitacora` VALUES('11', '145', 'Usuarios', 'Edición de Perfil y Accesos', 'Modificó al usuario \'111\'. Nuevo estado: Activo, Nuevo rol: Subdirector.', '2026-04-07 01:32:50', '::1');
INSERT INTO `bitacora` VALUES('12', '145', 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Phrexiel marcó su entrada a las 01:35:44.', '2026-04-07 01:35:44', '::1');
INSERT INTO `bitacora` VALUES('13', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 03:36:00, tolerancia: 60 minutos.', '2026-04-07 01:35:55', '::1');
INSERT INTO `bitacora` VALUES('14', '145', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 01:36:19.', '2026-04-07 01:36:19', '::1');
INSERT INTO `bitacora` VALUES('15', '321', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'Anthony Phrexiel\' en la fecha: 2026-04-07.', '2026-04-07 01:40:13', '::1');
INSERT INTO `bitacora` VALUES('16', '145', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'11111 1111\' en la fecha: 2026-04-10.', '2026-04-11 15:22:07', '::1');


DROP TABLE IF EXISTS `cargos`;
CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_cargo` varchar(100) NOT NULL,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cargos` VALUES('1', 'Directora');
INSERT INTO `cargos` VALUES('2', 'Subdirectora');
INSERT INTO `cargos` VALUES('3', 'Docente de Aula');
INSERT INTO `cargos` VALUES('4', 'Personal Administrativo');
INSERT INTO `cargos` VALUES('5', 'Personal Obrero');
INSERT INTO `cargos` VALUES('6', 'Docente Especialista');


DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `hora_entrada_general` time NOT NULL DEFAULT '07:00:00',
  `hora_salida_general` time NOT NULL DEFAULT '13:00:00',
  `minutos_tolerancia` int(11) NOT NULL DEFAULT 15,
  PRIMARY KEY (`id_config`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `configuracion` VALUES('1', '07:30:00', '03:36:00', '60');


DROP TABLE IF EXISTS `justificaciones`;
CREATE TABLE `justificaciones` (
  `id_justificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_personal` int(11) NOT NULL,
  `fecha_falta` date NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `estado_justificacion` enum('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
  PRIMARY KEY (`id_justificacion`),
  KEY `fk_justificacion_personal` (`id_personal`),
  CONSTRAINT `fk_justificacion_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `tipo` enum('Informativa','Exito','Alerta') DEFAULT 'Informativa',
  `leido` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `fk_notif_usuario` (`id_usuario`),
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones` VALUES('1', '321', '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-07 00:54:19');
INSERT INTO `notificaciones` VALUES('2', '321', 'ATENCIÓN: Tu justificación del día 07-04-2026 ha sido RECHAZADA. Revisa las observaciones.', 'Alerta', '1', '2026-04-07 01:11:51');
INSERT INTO `notificaciones` VALUES('3', '321', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: Por gay', 'Alerta', '1', '2026-04-07 01:31:42');
INSERT INTO `notificaciones` VALUES('4', '321', 'Administración ha modificado tu perfil. Nuevo rol: Subdirector. Estado: Activo.', 'Informativa', '1', '2026-04-07 01:32:50');
INSERT INTO `notificaciones` VALUES('5', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: lololooooooooooooooooooooooooooookdsokfñskfñokesñfkñsekfñskefñokseñfkifgjerjfmtrtuoweugerkvrjhgoierkfkerpf\n\n\ndejiofjreifjerifoiergoijoerg\n\n\nekjfirjfirejfoirefoijefoijeriofjweijdfqjwfjdhe', 'Alerta', '1', '2026-04-07 01:36:57');
INSERT INTO `notificaciones` VALUES('6', '145', '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-07 01:39:37');
INSERT INTO `notificaciones` VALUES('7', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'Alerta', '1', '2026-04-07 01:40:49');
INSERT INTO `notificaciones` VALUES('8', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Alerta', '1', '2026-04-07 01:45:47');
INSERT INTO `notificaciones` VALUES('9', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Alerta', '1', '2026-04-07 01:46:39');


DROP TABLE IF EXISTS `personal`;
CREATE TABLE `personal` (
  `id_personal` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(15) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `foto_perfil` varchar(255) NOT NULL DEFAULT 'default.png',
  `telefono` varchar(20) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `hora_entrada_personalizada` time DEFAULT NULL,
  `hora_salida_personalizada` time DEFAULT NULL,
  `fecha_ingreso` date DEFAULT curdate(),
  PRIMARY KEY (`id_personal`),
  UNIQUE KEY `cedula_unica` (`cedula`),
  KEY `fk_personal_cargo` (`id_cargo`),
  KEY `fk_personal_usuario` (`id_usuario`),
  CONSTRAINT `fk_personal_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_personal_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=322 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `personal` VALUES('144', '30710894', 'Anthony', 'Phrexiel', 'default.png', '04128725284', '1', '145', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('301', '15000001', 'Carlos', 'Perez', 'default.png', '0414-1234501', '3', '301', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('302', '15000002', 'Maria', 'Gomez', 'default.png', '0414-1234502', '4', '302', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('303', '15000003', 'Juan', 'Rodriguez', 'default.png', '0414-1234503', '5', '303', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('304', '15000004', 'Ana', 'Fernandez', 'default.png', '0414-1234504', '6', '304', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('305', '15000005', 'Luis', 'Martinez', 'default.png', '0414-1234505', '3', '305', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('306', '15000006', 'Carmen', 'Lopez', 'default.png', '0414-1234506', '4', '306', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('307', '15000007', 'Jose', 'Garcia', 'default.png', '0414-1234507', '5', '307', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('308', '15000008', 'Laura', 'Sanchez', 'default.png', '0414-1234508', '6', '308', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('309', '15000009', 'Pedro', 'Romero', 'default.png', '0414-1234509', '3', '309', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('310', '15000010', 'Sofia', 'Suarez', 'default.png', '0414-1234510', '4', '310', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('311', '15000011', 'Miguel', 'Diaz', 'default.png', '0414-1234511', '5', '311', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('312', '15000012', 'Lucia', 'Torres', 'default.png', '0414-1234512', '6', '312', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('313', '15000013', 'Jorge', 'Ruiz', 'default.png', '0414-1234513', '3', '313', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('314', '15000014', 'Elena', 'Ramirez', 'default.png', '0414-1234514', '4', '314', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('315', '15000015', 'Raul', 'Flores', 'default.png', '0414-1234515', '5', '315', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('316', '15000016', 'Paula', 'Benitez', 'default.png', '0414-1234516', '6', '316', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('317', '15000017', 'Alberto', 'Acosta', 'default.png', '0414-1234517', '3', '317', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('318', '15000018', 'Rosa', 'Medina', 'default.png', '0414-1234518', '4', '318', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('319', '15000019', 'Fernando', 'Castro', 'default.png', '0414-1234519', '5', '319', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('320', '15000020', 'Teresa', 'Rojas', 'default.png', '0414-1234520', '6', '320', NULL, NULL, '2026-04-03');
INSERT INTO `personal` VALUES('321', '11111111', '11111', '1111', 'default.png', '13123123123', '4', '321', NULL, NULL, '2026-04-07');


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES('1', 'Director');
INSERT INTO `roles` VALUES('2', 'Subdirector');
INSERT INTO `roles` VALUES('3', 'Personal');


DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `pregunta_1` int(11) NOT NULL,
  `respuesta_1` varchar(255) NOT NULL,
  `pregunta_2` int(11) NOT NULL,
  `respuesta_2` varchar(255) NOT NULL,
  `pregunta_3` int(11) NOT NULL,
  `respuesta_3` varchar(255) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario_unico` (`nombre_usuario`),
  KEY `fk_usuario_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=322 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` VALUES('145', 'Anthony12', '$2y$10$Os//b3c/xPolijXKN6PCWeAjxpTnawe3PBM7h0jTn1O2famBF31GK', '1', 'Activo', '1', '$2y$10$ZC7Y828xdYBX3.PxnwuMgeii/5TeTXcv6S44vfQiRIbYVPo868hZy', '2', '$2y$10$AIKQk7tISGxDspIOr5dZUepFHgzal5nf.vdgO14/b1e.qYm4ny5Pm', '3', '$2y$10$dg6wCo0Z7wVwgBTvvXkh.uVbrnV5kqkGCOZWk2vRyN/rldCsA4Odm');
INSERT INTO `usuarios` VALUES('301', 'carlos.perez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('302', 'maria.gomez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('303', 'juan.rodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('304', 'ana.fernandez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('305', 'luis.martinez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('306', 'carmen.lopez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('307', 'jose.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('308', 'laura.sanchez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('309', 'pedro.romero', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('310', 'sofia.suarez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('311', 'miguel.diaz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('312', 'lucia.torres', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('313', 'jorge.ruiz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('314', 'elena.ramirez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('315', 'raul.flores', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('316', 'paula.benitez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('317', 'alberto.acosta', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('318', 'rosa.medina', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('319', 'fernando.castro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('320', 'teresa.rojas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', 'Activo', '1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
INSERT INTO `usuarios` VALUES('321', '111', '$2y$10$2Owhzg9Mwp4/Gvxw3NatD.M/LAqwUEgJXWQD4sCutF9QZn4I6fPw2', '2', 'Activo', '2', '$2y$10$Fzie3sziHea/hALpPPfSZeBznjirQz.ZN.dMYbvA4n0H9iabwNQ5e', '3', '$2y$10$8.y73YkdpXX6BIcC24Cim.pwpgX0gGN9dgfetsXNNE2JPeHgvYUQK', '4', '$2y$10$LfrKWr1orT/.TPPH83VuouOnh3h5IjHp8jdpImXoEMw9sYG5Tv0BS');


SET FOREIGN_KEY_CHECKS=1;
