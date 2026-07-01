-- Respaldo del Sistema CEIS Julian Yánez
-- Generado el: 14/06/2026 10:58:56 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `asistencias` VALUES('10', '3', '2026-06-04', '21:10:00', '21:01:00', NULL, 'Salida Temprana', '[Salida Temprana] - tenia ganas de hacer kaka', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('127', '4', '2026-06-04', '21:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('128', '5', '2026-06-04', '21:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('129', '6', '2026-06-04', '21:10:00', NULL, NULL, 'Retraso y Salida Temprana', 'porque yep', NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('130', '7', '2026-06-04', '21:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('131', '8', '2026-06-04', '21:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('132', '9', '2026-06-04', '21:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('133', '3', '2026-06-05', '21:10:00', '11:36:28', '18:33:43', 'Salida Temprana', '[Salida Temprana] - porque yep kskssksk', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('134', '4', '2026-06-05', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('135', '5', '2026-06-05', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('136', '6', '2026-06-05', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('137', '7', '2026-06-05', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('138', '8', '2026-06-05', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('139', '9', '2026-06-05', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('141', '3', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('142', '4', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('143', '5', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('144', '6', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('145', '7', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('146', '8', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('147', '9', '2026-06-08', '07:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('149', '3', '2026-06-09', '07:30:00', '13:41:45', NULL, 'Falta', '[Llegada Tardía] - lolpiuyfdvbjmmii', NULL, 'Rechazada', '[Denegada: kkkkkkkkkkkkkkk]');
INSERT INTO `asistencias` VALUES('150', '6', '2026-06-09', '10:30:00', '10:27:37', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('151', '4', '2026-06-09', '13:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('152', '5', '2026-06-09', '13:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('153', '7', '2026-06-09', '13:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('154', '8', '2026-06-09', '13:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('155', '9', '2026-06-09', '13:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('156', '3', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('157', '4', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('158', '5', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('159', '6', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('160', '7', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('161', '8', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('162', '9', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('163', '10', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('164', '11', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('165', '12', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('166', '13', '2026-06-10', '10:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('167', '3', '2026-06-03', '10:00:00', NULL, NULL, 'Justificado', '[Inasistencia] - Me dolia mucho la barriga :(', '3_20260603_1781183998.pdf', 'Aprobada', NULL);
INSERT INTO `asistencias` VALUES('168', '3', '2026-06-11', '12:00:00', '11:52:26', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('169', '3', '2026-06-02', '12:00:00', NULL, NULL, 'Justificado (Pendiente)', '[Inasistencia] - llegue tarde por falta de trasporte', '3_20260602_1781193189.jpg', 'Pendiente', NULL);
INSERT INTO `asistencias` VALUES('170', '3', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('171', '4', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('172', '4', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('173', '5', '2026-06-12', '12:00:00', NULL, NULL, 'Puntual', '', NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('174', '5', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('175', '6', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('176', '6', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('177', '7', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('178', '7', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('179', '8', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('180', '8', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('181', '9', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('182', '9', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('183', '10', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('184', '10', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('185', '11', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('186', '11', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('187', '12', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('188', '12', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('189', '13', '2026-06-12', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');
INSERT INTO `asistencias` VALUES('190', '13', '2026-06-11', '12:00:00', NULL, NULL, 'Falta', NULL, NULL, NULL, 'Falta generada automáticamente por inasistencia');


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
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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
INSERT INTO `bitacora` VALUES('17', '145', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Phrexiel marcó su entrada a las 19:15:49.', '2026-04-15 19:15:49', '::1');
INSERT INTO `bitacora` VALUES('18', '145', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 19:16:04.', '2026-04-15 19:16:04', '::1');
INSERT INTO `bitacora` VALUES('19', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 03:10:00, salida: 08:30:00, tolerancia: 60 minutos.', '2026-04-16 15:08:46', '::1');
INSERT INTO `bitacora` VALUES('20', '145', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Phrexiel marcó su entrada a las 15:09:01.', '2026-04-16 15:09:01', '::1');
INSERT INTO `bitacora` VALUES('21', '145', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 15:09:09.', '2026-04-16 15:09:09', '::1');
INSERT INTO `bitacora` VALUES('22', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:10:00, salida: 08:30:00, tolerancia: 60 minutos.', '2026-04-16 15:10:48', '::1');
INSERT INTO `bitacora` VALUES('23', '145', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Phrexiel marcó su entrada a las 15:10:51.', '2026-04-16 15:10:51', '::1');
INSERT INTO `bitacora` VALUES('24', '322', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado adawdawdwadaw dwadwadwadwa marcó su entrada a las 15:13:29.', '2026-04-16 15:13:29', '::1');
INSERT INTO `bitacora` VALUES('25', '322', 'Asistencia', 'Registro de Salida', 'El empleado adawdawdwadaw dwadwadwadwa marcó su salida a las 15:14:08.', '2026-04-16 15:14:08', '::1');
INSERT INTO `bitacora` VALUES('26', '145', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-16 15:16:06', '::1');
INSERT INTO `bitacora` VALUES('27', '145', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Phrexiel marcó su salida a las 15:16:55.', '2026-04-16 15:16:55', '::1');
INSERT INTO `bitacora` VALUES('28', '145', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'11111 1111\' en la fecha: 2026-04-16.', '2026-04-16 15:22:31', '::1');
INSERT INTO `bitacora` VALUES('29', '145', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'11111 1111\' en la fecha: 2026-04-16.', '2026-04-16 15:22:37', '::1');
INSERT INTO `bitacora` VALUES('32', '3', 'Asistencia', 'Registro de Entrada', 'El empleado koli vvv marcó su entrada a las 15:33:25.', '2026-04-16 15:33:26', '::1');
INSERT INTO `bitacora` VALUES('33', '3', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 30303030) correspondiente a: Abril de 2026.', '2026-04-16 15:33:45', '::1');
INSERT INTO `bitacora` VALUES('34', '4', 'Asistencia', 'Registro de Entrada', 'El empleado rita man marcó su entrada a las 15:36:45.', '2026-04-16 15:36:45', '::1');
INSERT INTO `bitacora` VALUES('35', '5', 'Asistencia', 'Registro de Entrada', 'El empleado lolo rerere marcó su entrada a las 15:37:39.', '2026-04-16 15:37:39', '::1');
INSERT INTO `bitacora` VALUES('36', '6', 'Asistencia', 'Registro de Entrada', 'El empleado kioki lolo marcó su entrada a las 15:40:10.', '2026-04-16 15:40:10', '::1');
INSERT INTO `bitacora` VALUES('37', '7', 'Asistencia', 'Registro de Entrada', 'El empleado mobica martin marcó su entrada a las 15:41:17.', '2026-04-16 15:41:17', '::1');
INSERT INTO `bitacora` VALUES('38', '8', 'Asistencia', 'Registro de Entrada', 'El empleado lolol d13edwdwa marcó su entrada a las 15:45:02.', '2026-04-16 15:45:02', '::1');
INSERT INTO `bitacora` VALUES('39', '9', 'Asistencia', 'Registro de Entrada', 'El empleado for mavi marcó su entrada a las 15:50:33.', '2026-04-16 15:50:33', '::1');
INSERT INTO `bitacora` VALUES('40', '10', 'Asistencia', 'Registro de Entrada', 'El empleado lovo qeqwe marcó su entrada a las 15:52:41.', '2026-04-16 15:52:41', '::1');
INSERT INTO `bitacora` VALUES('41', '11', 'Asistencia', 'Registro de Entrada', 'El empleado dede dede marcó su entrada a las 15:54:21.', '2026-04-16 15:54:21', '::1');
INSERT INTO `bitacora` VALUES('47', '13', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado dedeeded dedeed marcó su entrada a las 19:20:22.', '2026-04-24 19:20:22', '::1');
INSERT INTO `bitacora` VALUES('134', '11', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado dedededede dedededede marcó su entrada a las 22:18:05.', '2026-05-08 22:18:05', '::1');
INSERT INTO `bitacora` VALUES('135', '11', 'Asistencia', 'Registro de Salida', 'El empleado dedededede dedededede marcó su salida a las 22:18:06.', '2026-05-08 22:18:06', '::1');
INSERT INTO `bitacora` VALUES('136', '11', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'Anthony Maita\' en la fecha: 2026-05-08.', '2026-05-08 22:19:10', '::1');
INSERT INTO `bitacora` VALUES('137', '11', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'Anthony Maita\' en la fecha: 2026-05-08.', '2026-05-08 22:19:14', '::1');
INSERT INTO `bitacora` VALUES('176', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 21:10:00, salida: 23:30:00, tolerancia: 30 min.', '2026-06-04 21:00:57', '::1');
INSERT INTO `bitacora` VALUES('177', '3', 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 21:01:00.', '2026-06-04 21:01:00', '::1');
INSERT INTO `bitacora` VALUES('179', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Retraso\' para el empleado \'lauris viera\' en la fecha: 2026-06-03.', '2026-06-04 21:03:13', '::1');
INSERT INTO `bitacora` VALUES('180', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Feriado\' para el empleado \'lauris viera\' en la fecha: 2026-06-02.', '2026-06-04 21:03:21', '::1');
INSERT INTO `bitacora` VALUES('181', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Puntual\' para el empleado \'lauris viera\' en la fecha: 2026-06-04. | Ent: 21:02 - Sal: --:--', '2026-06-04 23:10:21', '::1');
INSERT INTO `bitacora` VALUES('182', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Puntual\' para el empleado \'lauris viera\' en la fecha: 2026-06-05.', '2026-06-04 23:10:41', '::1');
INSERT INTO `bitacora` VALUES('183', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-08.', '2026-06-04 23:10:47', '::1');
INSERT INTO `bitacora` VALUES('184', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-09.', '2026-06-04 23:10:52', '::1');
INSERT INTO `bitacora` VALUES('185', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-10.', '2026-06-04 23:10:55', '::1');
INSERT INTO `bitacora` VALUES('186', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-11.', '2026-06-04 23:11:04', '::1');
INSERT INTO `bitacora` VALUES('187', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-12.', '2026-06-04 23:11:09', '::1');
INSERT INTO `bitacora` VALUES('188', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-15.', '2026-06-04 23:11:13', '::1');
INSERT INTO `bitacora` VALUES('189', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-16.', '2026-06-04 23:11:16', '::1');
INSERT INTO `bitacora` VALUES('190', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-17.', '2026-06-04 23:11:19', '::1');
INSERT INTO `bitacora` VALUES('191', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-18.', '2026-06-04 23:11:22', '::1');
INSERT INTO `bitacora` VALUES('192', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-19.', '2026-06-04 23:11:26', '::1');
INSERT INTO `bitacora` VALUES('193', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-22.', '2026-06-04 23:11:29', '::1');
INSERT INTO `bitacora` VALUES('194', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-23.', '2026-06-04 23:11:32', '::1');
INSERT INTO `bitacora` VALUES('195', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-24.', '2026-06-04 23:11:35', '::1');
INSERT INTO `bitacora` VALUES('196', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-25.', '2026-06-04 23:11:39', '::1');
INSERT INTO `bitacora` VALUES('197', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-26.', '2026-06-04 23:11:43', '::1');
INSERT INTO `bitacora` VALUES('198', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-30.', '2026-06-04 23:11:46', '::1');
INSERT INTO `bitacora` VALUES('199', '3', 'Asistencia', 'Eliminación de Registro', 'Eliminó el registro de asistencia del personal ID: 2 en la fecha: 2026-06-29.', '2026-06-04 23:11:50', '::1');
INSERT INTO `bitacora` VALUES('200', '3', 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 11:36:28.', '2026-06-05 11:36:28', '::1');
INSERT INTO `bitacora` VALUES('201', '3', 'Security', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_05-06-2026_11-40-16.sql en el servidor.', '2026-06-05 11:40:16', '::1');
INSERT INTO `bitacora` VALUES('202', '3', 'Seguridad', 'Descarga de Respaldo Inmediata', 'Generó y descargó: respaldo_ceis_05-06-2026_11-40-28.sql', '2026-06-05 11:40:28', '::1');
INSERT INTO `bitacora` VALUES('203', '3', 'Seguridad', 'Subida de Archivo SQL', 'Subió el archivo externo: respaldo_ceis_05-06-2026_11-40-28.sql y se renombró a: respaldo_externo_05-06-2026_11-40-50.sql', '2026-06-05 11:40:50', '::1');
INSERT INTO `bitacora` VALUES('204', '3', 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'lauris viera\'. Cédula: 14669420, Estado: Activo, Rol: Personal.', '2026-06-05 12:08:07', '::1');
INSERT INTO `bitacora` VALUES('205', '3', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_externo_30-05-2026_12-37-26.sql del servidor.', '2026-06-05 18:29:30', '::1');
INSERT INTO `bitacora` VALUES('206', '3', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_02-06-2026_00-14-29.sql del servidor.', '2026-06-05 18:29:35', '::1');
INSERT INTO `bitacora` VALUES('207', '3', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_03-06-2026_15-32-01.sql del servidor.', '2026-06-05 18:29:38', '::1');
INSERT INTO `bitacora` VALUES('208', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 18:50:00, salida: 23:30:00, tolerancia: 30 min.', '2026-06-05 18:32:02', '::1');
INSERT INTO `bitacora` VALUES('209', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 18:30:00, salida: 23:30:00, tolerancia: 30 min.', '2026-06-05 18:32:16', '::1');
INSERT INTO `bitacora` VALUES('210', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 18:30:00, salida: 23:30:00, tolerancia: 30 min.', '2026-06-05 18:32:30', '::1');
INSERT INTO `bitacora` VALUES('211', '3', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 18:33:43.', '2026-06-05 18:33:43', '::1');
INSERT INTO `bitacora` VALUES('212', '3', 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'lauris viera\'. Cédula: 14669420, Estado: Activo, Rol: Supervisor.', '2026-06-05 18:34:50', '::1');
INSERT INTO `bitacora` VALUES('213', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'Jose maita\' en la fecha: 2026-06-04.', '2026-06-05 18:36:45', '::1');
INSERT INTO `bitacora` VALUES('214', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Puntual y Salida Irregular\' para el empleado \'Jose maita\' en la fecha: 2026-06-04.', '2026-06-05 18:37:12', '::1');
INSERT INTO `bitacora` VALUES('215', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Retraso y Salida Temprana\' para el empleado \'Jose maita\' en la fecha: 2026-06-04.', '2026-06-05 18:37:20', '::1');
INSERT INTO `bitacora` VALUES('216', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 07:30:00, salida: 15:30:00, tolerancia: 30 min.', '2026-06-05 19:11:33', '::1');
INSERT INTO `bitacora` VALUES('217', '3', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 30710894) correspondiente a: Noviembre de 2026.', '2026-06-05 21:46:04', '::1');
INSERT INTO `bitacora` VALUES('220', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 08:30:00, salida: 15:30:00, tolerancia: 30 min.', '2026-06-09 08:25:12', '::1');
INSERT INTO `bitacora` VALUES('226', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:30:00, salida: 12:30:00, tolerancia: 30 min.', '2026-06-09 10:26:23', '::1');
INSERT INTO `bitacora` VALUES('227', '6', 'Asistencia', 'Registro de Entrada', 'El empleado Jose maita marcó su entrada a las 10:27:37.', '2026-06-09 10:27:37', '::1');
INSERT INTO `bitacora` VALUES('228', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 08:30:00, salida: 10:20:00, tolerancia: 30 min.', '2026-06-09 10:28:00', '::1');
INSERT INTO `bitacora` VALUES('231', '3', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 13:41:45.', '2026-06-09 13:41:45', '::1');
INSERT INTO `bitacora` VALUES('232', '3', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia (Filtrado por cargo) correspondiente a: Junio de 2026.', '2026-06-09 13:45:56', '::1');
INSERT INTO `bitacora` VALUES('234', '3', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_externo_05-06-2026_11-40-50.sql del servidor.', '2026-06-10 20:13:58', '::1');
INSERT INTO `bitacora` VALUES('235', '3', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_05-06-2026_11-40-16.sql del servidor.', '2026-06-10 20:14:01', '::1');
INSERT INTO `bitacora` VALUES('236', '3', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_09-06-2026_13-48-24.sql del servidor.', '2026-06-10 20:14:06', '::1');
INSERT INTO `bitacora` VALUES('237', '3', 'Seguridad', 'Restauración Exitosa', 'Restauró la base de datos con: respaldo_ceis_10-06-2026_20-14-09.sql', '2026-06-10 20:16:14', '::1');
INSERT INTO `bitacora` VALUES('238', '3', 'Usuarios', 'Eliminación de Personal', 'Eliminó permanentemente al usuario: \'lauris12\' tras validación de seguridad.', '2026-06-10 20:57:20', '::1');
INSERT INTO `bitacora` VALUES('239', '3', 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'Lauris Viera\'. Cédula: 14669420, Estado: Activo, Rol: Supervisor.', '2026-06-10 21:33:58', '::1');
INSERT INTO `bitacora` VALUES('240', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:00:00, salida: 15:20:00, tolerancia: 30 min.', '2026-06-10 21:35:00', '::1');
INSERT INTO `bitacora` VALUES('241', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:00:00, salida: 15:20:00, tolerancia: 30 min.', '2026-06-10 21:40:26', '::1');
INSERT INTO `bitacora` VALUES('242', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 21:00:00, salida: 23:20:00, tolerancia: 30 min.', '2026-06-10 21:43:06', '::1');
INSERT INTO `bitacora` VALUES('243', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 19:00:00, salida: 21:20:00, tolerancia: 30 min.', '2026-06-10 21:43:16', '::1');
INSERT INTO `bitacora` VALUES('244', '3', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 10:00:00, salida: 15:20:00, tolerancia: 30 min.', '2026-06-10 21:44:19', '::1');
INSERT INTO `bitacora` VALUES('245', '3', 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'Anthony Maita\'. Cédula: 30710894, Estado: Activo, Rol: Administrador.', '2026-06-11 00:51:03', '::1');
INSERT INTO `bitacora` VALUES('246', '10', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 15:20:00, tolerancia: 30 min.', '2026-06-11 10:39:40', '::1');
INSERT INTO `bitacora` VALUES('247', '10', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 15:20:00, tolerancia: 30 min.', '2026-06-11 10:39:43', '::1');
INSERT INTO `bitacora` VALUES('248', '3', 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 11:52:26.', '2026-06-11 11:52:26', '::1');
INSERT INTO `bitacora` VALUES('249', '3', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte Individual (C.I: 87248548) correspondiente a: Junio de 2026.', '2026-06-11 11:56:43', '::1');
INSERT INTO `bitacora` VALUES('250', '3', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Junio de 2026.', '2026-06-11 12:09:04', '::1');
INSERT INTO `bitacora` VALUES('251', '3', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia (Filtrado por cargo) correspondiente a: Junio de 2026.', '2026-06-11 12:10:04', '::1');
INSERT INTO `bitacora` VALUES('252', '3', 'Asistencia', 'Modificación Manual', 'Cambió el estado a \'Puntual\' para el empleado \'manuel12 viera\' en la fecha: 2026-06-12.', '2026-06-14 22:58:45', '::1');


DROP TABLE IF EXISTS `cargos`;
CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_cargo` varchar(100) NOT NULL,
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cargos` VALUES('1', 'Director');
INSERT INTO `cargos` VALUES('2', 'Subdirector');
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

INSERT INTO `configuracion` VALUES('1', '12:00:00', '15:20:00', '30');


DROP TABLE IF EXISTS `configuraciones_preestablecidas`;
CREATE TABLE `configuraciones_preestablecidas` (
  `id_preestablecida` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `minutos_tolerancia` tinyint(4) NOT NULL DEFAULT 0,
  `es_activa` tinyint(1) NOT NULL DEFAULT 0,
  `id_usuario_creador` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_preestablecida`),
  KEY `idx_usuario_creador` (`id_usuario_creador`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Plantillas horarias preestablecidas para carga rápida en el formulario de configuración';

INSERT INTO `configuraciones_preestablecidas` VALUES('9', 'prueba', '14:10:00', '20:30:00', '30', '0', '1', '2026-05-08 16:41:32', '2026-05-08 16:41:32');


DROP TABLE IF EXISTS `feriados`;
CREATE TABLE `feriados` (
  `id_feriado` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_feriado`),
  UNIQUE KEY `fecha_unica` (`fecha`),
  KEY `fk_feriados_usuario` (`id_usuario`),
  CONSTRAINT `fk_feriados_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `feriados` VALUES('1', '2026-06-01', 'Día Festivo: Aniversario', '3', '2026-06-05 19:21:15');


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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 🚀 Datos omitidos para la tabla `notificaciones` (Archivada solo estructura).

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `personal` VALUES('3', '30710894', 'Anthony', 'Maita', 'perfil_1780700203_836.jpg', '04129875632', '1', '3', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('4', '06514651', 'sofia', 'maita', 'default.png', '16546546546', '3', '4', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('5', '59876317', 'manuel12', 'viera', 'default.png', '04123654798', '5', '5', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('6', '87248548', 'Jose', 'maita', 'default.png', '04125697631', '4', '6', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('7', '80450257', 'Marisol', 'Del Carmen', 'default.png', '04125879641', '4', '7', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('8', '31392816', 'Rosaura', 'Acosta', 'default.png', '01452148621', '3', '8', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('9', '15498498', 'Pedro', 'Martinez', 'default.png', '04126578937', '3', '9', NULL, NULL, '2026-06-04');
INSERT INTO `personal` VALUES('10', '14669420', 'Lauris', 'Viera', 'perfil_1781141442_403.jpg', '04145698728', '4', '10', NULL, NULL, '2026-06-10');
INSERT INTO `personal` VALUES('11', '89756441', 'Maruisio', 'Netal', 'default.png', '01412878787', '3', '11', NULL, NULL, '2026-06-10');
INSERT INTO `personal` VALUES('12', '45454545', 'Pepe', 'Suelto', 'default.png', '04123659774', '5', '12', NULL, NULL, '2026-06-10');
INSERT INTO `personal` VALUES('13', '30456325', 'Maria', 'Nieves', 'default.png', '04589721459', '6', '13', NULL, NULL, '2026-06-10');


DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` VALUES('1', 'Administrador');
INSERT INTO `roles` VALUES('2', 'Supervisor');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` VALUES('3', 'anthony12', '$2y$10$RpCHF2/Aajn5/6EaxiXECO6Nu.nFdVt4ZxZcZfmco17OYVH1YaeVG', '1', 'Activo', '1', '$2y$10$TDt3H6PI/Jywr/66TPLnIOw0e5f0ev4/5VoLYenVPwZ4CY/6v2xs.', '2', '$2y$10$cvJ/CNbRbyCBvL7E3JlY4.ly1RqvVVu8B9bOsNUxO7KJm3pOVcPJS', '3', '$2y$10$ppAwy7o8zvwkmKwM2YjgVuF7y1GA7czqvR9nd0fC2DTRAEANhyW52');
INSERT INTO `usuarios` VALUES('4', 'sofia12', '$2y$10$nDXSrHIOg.ryF1yr4WLW5ujS4rHPgh/sfaV4Vb0KMUmjI0Y2Y52Ca', '3', 'Activo', '1', '$2y$10$IKhzMjaWOganDHH1hwptu.3iganmKzt/OS0N8jGYYtR2lt6Vb/fJ.', '2', '$2y$10$akkIew238cl7GDbY8QRDvOm19QrAhvLxtnwoBuuMiPDAAw29ZEKne', '5', '$2y$10$VEhtfiLyxEyTzWYeStJ0Xu6ShIoEiKZW0C2V202w3qo4RLgqMt5uC');
INSERT INTO `usuarios` VALUES('5', 'manuel12', '$2y$10$Rd.JS5yXZBFWPnA.nKsX5uHnYNT4AUIuaxwC3jKqfRQxT3roPIsfi', '3', 'Activo', '1', '$2y$10$MMT.cn0iXdqtf7jwBLBos.Tu6PBtSF72T1Mtzp6B.5Jekm5mBn6V.', '2', '$2y$10$CR.Jk1zPwDMQm/uGuHwlTeZdqLwIgd9ZSMxeVUCkXPr5Wq.KoZBgC', '3', '$2y$10$tvUGQpvctJsTIRhUx6Rx1u.FmRPhI2.1RQzZJpDpaIYBmIPBSIpQu');
INSERT INTO `usuarios` VALUES('6', 'jose12', '$2y$10$7TankdX3WU/HBvP7uloCHuzQpMxfMEdvtCBpU.ngJLhSEsyZ9xWwS', '3', 'Activo', '1', '$2y$10$YzwXN3pQKJJ7Ky0dDxDS5eMRWG5a4K.YxzhkY/kAn3Q6DGArtzGaC', '2', '$2y$10$ht.AC658T7ETfLC82RbW0OeK1TsLu3/xcFs.rJelHRjadrJrQWW02', '3', '$2y$10$6AnH6Miq90XJRWZ2KqnJlOj9605ye5AxmkXDyMJPB8BZupKjKKee6');
INSERT INTO `usuarios` VALUES('7', 'marisol12', '$2y$10$tR9YEwE37bHvxWFMXRMZwuySpv5KaTpaNAZ1gzsU9efzQrGgTJUH.', '3', 'Activo', '1', '$2y$10$vaF63MKWi7EW6HAUaGroEuS35hBAR3vU2ZiHemz8Ms7Ckq5gnU1V.', '2', '$2y$10$D6IdZe6ofqU4xRHSUqxxveaov7V51.5j3UMvsIMj9gbVDSiuwDWIq', '3', '$2y$10$du8kYu.LKBachyVS59lfr.VFkULsS6ZleIjAZSrr6fhu/4NAFeLEa');
INSERT INTO `usuarios` VALUES('8', 'rosa12', '$2y$10$3PMZJVAiIXU6mEm8RkMC4OWf/xm7Xwt.o492guUm76ANL71A/FQVC', '3', 'Activo', '1', '$2y$10$F6oVb.lZhc/C7A3FUI/KleL1OOgZ2Pr0eSyzKHrAKAfyMH5ClWMaW', '2', '$2y$10$2Jh2a0tP5o4TvAZoCNxAG.mAyBCsEhJuvW6iJ6s46r7OccTfnTUOC', '3', '$2y$10$moxPGD/7m0kOqpDBakET6OrIZmBlQBMzv27eiTQBTrfcLgUJIOCla');
INSERT INTO `usuarios` VALUES('9', 'pedro12', '$2y$10$FzkKh21FcpPE2QQTOkE8/O22wksIvYbIWl51JQwWueaTVX/Xu2QX2', '3', 'Activo', '2', '$2y$10$6dHcTTJAk40PZJhiNILft.iKPpRcsBjSzIPSyub34Lh7dF/ccvWcK', '4', '$2y$10$BTWbGnh4/tnKjQHMo/NWKeDQBDb0AbI6L1viE3LqsCw5FUY0GGpWy', '1', '$2y$10$SvyitEzHd/dCRkGTUNHGOebxBfInJ3A/9kk46Fg0BWjG4WE.Offqm');
INSERT INTO `usuarios` VALUES('10', 'lauris12', '$2y$10$XCWuwztdf0ZR1zQ1dwwqFuT/SgiWKWmmO38VELkA3qEhQP25rEABO', '2', 'Activo', '1', '$2y$10$Z0.qHWqH1kix/p9qEmemy.FTEUV1fA8IXe9VNUfs0cUp7lVWegXsa', '2', '$2y$10$2zUs6dXzHWWegbAxd5zuN.PxScnE.Hr48a1n8alcTa3riSJFLpjgC', '4', '$2y$10$WCxiD22elWiDCSi72ktQ0.OwVhXCWR7/9SXJXR8d4pVEPwXFvwOfC');
INSERT INTO `usuarios` VALUES('11', 'maru12', '$2y$10$5vCaInYGX6n/K/3SdVojWOURYwMwgrZ7GBrqjDI09l1aSqziFiOsi', '3', 'Activo', '1', '$2y$10$1k9GK5UTWrsVnSFx7qbpZ.2wNQoy2kS3IKYOZEXZ2I3Gh4AC/StTy', '2', '$2y$10$lWQBoUWQH4h3SVRs64ytUOmp1mkXigaESOEz6muoL.zPAK3cYz0uq', '3', '$2y$10$PMHZtS5yZ..bRgINXR1WhuzVS1ysSYSyMBZQCb2ulMUTVZ2s0N.Km');
INSERT INTO `usuarios` VALUES('12', 'pepe12', '$2y$10$4sOuT/sntNF.guDzQLNFseqXk4wF4K0LrRofjfz3obQPtEUYzNCcO', '3', 'Activo', '3', '$2y$10$D/1EWYC4raO0.ywcRzmeXu31kSiUETxT0PXVYQPTHOHxuo/h5mkkq', '2', '$2y$10$4XkH3IjTC8Lal/MEcEdXXeZWUBhwuPmTFy7rbPjVT9QXY70FKsBa.', '1', '$2y$10$2m2CEZ3cOA5e/hxOEZSpgeZKqZMYPC5Ai83LiviCMASHjZDDE8RUy');
INSERT INTO `usuarios` VALUES('13', 'maria12', '$2y$10$hUsQWpNe3kCzEskVUgyAre24uZih/3e/2wyIi1iL4K8U4d/BfYJAS', '3', 'Activo', '1', '$2y$10$jsgZAjUHIU73WH8HsL5Nye9F1Ox7jsKDv1lqbZ0yTbLHGMF1.avju', '2', '$2y$10$WdXvA9HPDUZxFLHbI5MDwOw5wdXdUZtIlZVtvGXLPDnHMq9Gl0LFG', '3', '$2y$10$LHmnB32ncHG7fNRVwoKS9e2o2a.3QXqO8.ETAbZE9qThK96aqum/2');


SET FOREIGN_KEY_CHECKS=1;
