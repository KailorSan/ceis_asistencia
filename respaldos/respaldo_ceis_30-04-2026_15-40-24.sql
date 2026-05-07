-- Respaldo del Sistema CEIS Julian Yánez
-- Generado el: 30/04/2026 03:40:24 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `asistencias` VALUES('1', '1', '2026-04-16', '15:10:00', '15:28:36', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('2', '2', '2026-04-16', '15:10:00', '15:28:41', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('3', '3', '2026-04-16', '15:10:00', '15:33:25', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('4', '4', '2026-04-16', '15:10:00', '15:36:45', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('5', '5', '2026-04-16', '15:10:00', '15:37:39', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('6', '6', '2026-04-16', '15:10:00', '15:40:10', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('7', '7', '2026-04-16', '15:10:00', '15:41:17', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('8', '8', '2026-04-16', '15:10:00', '15:45:02', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('9', '9', '2026-04-16', '15:10:00', '15:50:33', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('10', '10', '2026-04-16', '15:10:00', '15:52:41', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('11', '11', '2026-04-16', '15:10:00', '15:54:21', NULL, 'Puntual y Salida Irregular', NULL, NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('12', '13', '2026-04-01', '15:10:00', NULL, NULL, 'Salida Irregular', '[Inasistencia] - dede', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('13', '13', '2026-04-24', '03:10:00', '19:20:22', NULL, 'Retraso y Salida Irregular', '[Llegada Tardía] - r4r4r4r4r4r4r4rr4r4r4r4', '13_20260424_1777056817.png', 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('14', '1', '2026-04-24', '03:10:00', '14:45:07', NULL, 'Falta', '[Llegada Tardía] - ssfssefsefsefsefsfsf', NULL, 'Rechazada', '[Denegada: sffse]');
INSERT INTO `asistencias` VALUES('15', '13', '2026-04-08', '14:10:00', NULL, NULL, 'Salida Irregular', '[Salida Temprana] - dedeed', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('16', '13', '2026-04-10', '14:10:00', NULL, NULL, 'Salida Irregular', '[Inasistencia] - 33633', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('17', '13', '2026-04-13', '14:10:00', NULL, NULL, 'Salida Irregular', '[Inasistencia] - 3333', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('18', '11', '2026-04-24', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('19', '11', '2026-04-17', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('20', '11', '2026-04-23', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('21', '11', '2026-04-22', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('22', '11', '2026-04-21', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('23', '11', '2026-04-20', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('24', '2', '2026-04-17', '00:00:00', NULL, NULL, 'Puntual y Salida Irregular', '', NULL, NULL, 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('25', '1', '2026-04-28', '14:10:00', '23:25:58', '23:26:11', 'Retraso', '[Llegada Tardía] - frfrrffrrfrrrfrffada', NULL, 'Aprobada', NULL);
INSERT INTO `asistencias` VALUES('26', '2', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('27', '3', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('28', '4', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('29', '5', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('30', '6', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('31', '7', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('32', '8', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('33', '9', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('34', '10', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('35', '11', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('36', '13', '2026-04-28', '14:10:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('37', '1', '2026-04-13', '14:10:00', NULL, NULL, 'Retraso y Salida Irregular', '[Llegada Tardía] - ededeedeedeedeedede', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('38', '1', '2026-04-08', '15:30:00', NULL, NULL, 'Salida Irregular', '[Inasistencia] - eefesfsefesfsfsfsef', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('40', '2', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('41', '3', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('42', '4', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('43', '5', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('44', '6', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('45', '7', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('46', '8', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('47', '9', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('48', '10', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('49', '11', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('50', '13', '2026-04-29', '15:30:00', NULL, NULL, 'Falta', NULL, NULL, NULL, NULL);
INSERT INTO `asistencias` VALUES('51', '1', '2026-04-09', '15:30:00', NULL, NULL, 'Retraso y Salida Irregular', '[Llegada Tardía] - opfkrepofkrpeokfpeorkfpekferpokferpokpofkefpoekepokferhutirhgiojmdojsklksdofjbgnijvdsudo apt borlorr andhgyrhjejerfiejuhe apex colabra toronte forule aldkapkdwpaokdwpaokdopwakdawpodkwaopdkwawaodpowakwapodkwpowkadpwakdopwkadhnuhgujifjweoufew', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('53', '1', '2026-04-29', '15:30:00', '22:23:37', '22:59:32', 'Retraso y Salida Irregular', '[Llegada Tardía] - ewwefwefwfwejpofwepjfewjewfopjew', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('54', '1', '2026-04-07', '15:30:00', NULL, NULL, 'Retraso y Salida Irregular', '[Llegada Tardía] - kkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkllllllllllllllllllllllllllllllllllkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('55', '1', '2026-03-30', '15:30:00', NULL, NULL, 'Salida Irregular', '[Inasistencia] - gggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');
INSERT INTO `asistencias` VALUES('56', '1', '2026-04-30', '12:50:00', '11:32:49', '11:36:52', 'Salida Temprana', '[Salida Temprana] - rgrgrdgrdgrdggggd', NULL, 'Aprobada', 'El sistema cerró la jornada automáticamente por omisión de salida.');


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
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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
INSERT INTO `bitacora` VALUES('30', '1', 'Asistencia', 'Registro de Entrada', 'El empleado Anthony Maita marcó su entrada a las 15:28:36.', '2026-04-16 15:28:36', '::1');
INSERT INTO `bitacora` VALUES('31', '2', 'Asistencia', 'Registro de Entrada', 'El empleado Lauris Viera marcó su entrada a las 15:28:41.', '2026-04-16 15:28:41', '::1');
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
INSERT INTO `bitacora` VALUES('42', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 03:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:37:33', '::1');
INSERT INTO `bitacora` VALUES('43', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 02:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:39:37', '::1');
INSERT INTO `bitacora` VALUES('44', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 02:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:39:47', '::1');
INSERT INTO `bitacora` VALUES('45', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 60 minutos.', '2026-04-24 14:39:52', '::1');
INSERT INTO `bitacora` VALUES('46', '1', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 14:45:07.', '2026-04-24 14:45:07', '::1');
INSERT INTO `bitacora` VALUES('47', '13', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado dedeeded dedeed marcó su entrada a las 19:20:22.', '2026-04-24 19:20:22', '::1');
INSERT INTO `bitacora` VALUES('48', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-24.', '2026-04-24 19:20:47', '::1');
INSERT INTO `bitacora` VALUES('49', '1', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Abril de 2026.', '2026-04-25 14:28:30', '::1');
INSERT INTO `bitacora` VALUES('50', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:23:41', '::1');
INSERT INTO `bitacora` VALUES('51', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:23:46', '::1');
INSERT INTO `bitacora` VALUES('52', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:23:51', '::1');
INSERT INTO `bitacora` VALUES('53', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:03', '::1');
INSERT INTO `bitacora` VALUES('54', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Temprana\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:08', '::1');
INSERT INTO `bitacora` VALUES('55', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso y Salida Temprana\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:14', '::1');
INSERT INTO `bitacora` VALUES('56', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-17.', '2026-04-25 21:24:21', '::1');
INSERT INTO `bitacora` VALUES('57', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-23.', '2026-04-25 21:24:25', '::1');
INSERT INTO `bitacora` VALUES('58', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Justificado\' para el empleado \'dede dede\' en la fecha: 2026-04-22.', '2026-04-25 21:24:30', '::1');
INSERT INTO `bitacora` VALUES('59', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-21.', '2026-04-25 21:24:36', '::1');
INSERT INTO `bitacora` VALUES('60', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-20.', '2026-04-25 21:24:40', '::1');
INSERT INTO `bitacora` VALUES('61', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Retraso\' para el empleado \'Lauris Viera\' en la fecha: 2026-04-17.', '2026-04-25 23:17:16', '::1');
INSERT INTO `bitacora` VALUES('62', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'Lauris Viera\' en la fecha: 2026-04-17.', '2026-04-25 23:17:20', '::1');
INSERT INTO `bitacora` VALUES('63', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual y Salida Temprana\' para el empleado \'Lauris Viera\' en la fecha: 2026-04-17.', '2026-04-25 23:17:28', '::1');
INSERT INTO `bitacora` VALUES('64', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 15:30:00, tolerancia: 10 minutos.', '2026-04-25 23:43:26', '::1');
INSERT INTO `bitacora` VALUES('65', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-25 23:43:55', '::1');
INSERT INTO `bitacora` VALUES('66', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-25 23:45:33', '::1');
INSERT INTO `bitacora` VALUES('67', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-25 23:47:41', '::1');
INSERT INTO `bitacora` VALUES('68', '1', 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'fff\' creada (ID: 3). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-26 22:34:42', '::1');
INSERT INTO `bitacora` VALUES('69', '1', 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'fff\' (ID: 3) eliminada.', '2026-04-26 22:34:53', '::1');
INSERT INTO `bitacora` VALUES('70', '1', 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'Llegada tardía\' (ID: 2) eliminada.', '2026-04-26 22:38:16', '::1');
INSERT INTO `bitacora` VALUES('71', '1', 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'frfrrf\' creada (ID: 4). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-26 22:39:41', '::1');
INSERT INTO `bitacora` VALUES('72', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-26 22:42:57', '::1');
INSERT INTO `bitacora` VALUES('73', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 minutos.', '2026-04-28 23:09:26', '::1');
INSERT INTO `bitacora` VALUES('74', '1', 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dededed\' creada (ID: 5). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:00', '::1');
INSERT INTO `bitacora` VALUES('75', '1', 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dedee\' creada (ID: 6). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:07', '::1');
INSERT INTO `bitacora` VALUES('76', '1', 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dededede\' creada (ID: 7). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:10', '::1');
INSERT INTO `bitacora` VALUES('77', '1', 'Configuracion', 'Nueva Plantilla Preestablecida', 'Plantilla \'dedeed\' creada (ID: 8). Entrada: 14:10:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:10:16', '::1');
INSERT INTO `bitacora` VALUES('78', '1', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 23:25:58.', '2026-04-28 23:25:58', '::1');
INSERT INTO `bitacora` VALUES('79', '1', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 23:26:11.', '2026-04-28 23:26:11', '::1');
INSERT INTO `bitacora` VALUES('80', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 14:03:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:28:04', '::1');
INSERT INTO `bitacora` VALUES('81', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:28:14', '::1');
INSERT INTO `bitacora` VALUES('82', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:28:24', '::1');
INSERT INTO `bitacora` VALUES('83', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 1 min.', '2026-04-28 23:28:29', '::1');
INSERT INTO `bitacora` VALUES('84', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 120 min.', '2026-04-28 23:28:43', '::1');
INSERT INTO `bitacora` VALUES('85', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 1 min.', '2026-04-28 23:28:48', '::1');
INSERT INTO `bitacora` VALUES('86', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 15:30:00, salida: 20:30:00, tolerancia: 30 min.', '2026-04-28 23:29:00', '::1');
INSERT INTO `bitacora` VALUES('87', '1', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 21:34:29.', '2026-04-29 21:34:29', '::1');
INSERT INTO `bitacora` VALUES('88', '1', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 21:57:31.', '2026-04-29 21:57:31', '::1');
INSERT INTO `bitacora` VALUES('89', '1', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 22:07:38.', '2026-04-29 22:07:38', '::1');
INSERT INTO `bitacora` VALUES('90', '1', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 22:23:37.', '2026-04-29 22:23:37', '::1');
INSERT INTO `bitacora` VALUES('91', '1', 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'dedeed\' (ID: 8) eliminada.', '2026-04-29 22:35:19', '::1');
INSERT INTO `bitacora` VALUES('92', '1', 'Configuracion', 'Eliminación de Plantilla Preestablecida', 'Plantilla \'dededede\' (ID: 7) eliminada.', '2026-04-29 22:35:21', '::1');
INSERT INTO `bitacora` VALUES('93', '1', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 22:59:32.', '2026-04-29 22:59:32', '::1');
INSERT INTO `bitacora` VALUES('94', '1', 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'Anthony Maita\'. Cédula: 30710894, Estado: Inactivo, Rol: Director.', '2026-04-29 23:45:10', '::1');
INSERT INTO `bitacora` VALUES('95', '1', 'Usuarios', 'Edición Completa de Perfil', 'Actualizó los datos de \'Anthony Maita\'. Cédula: 30710894, Estado: Activo, Rol: Director.', '2026-04-29 23:45:21', '::1');
INSERT INTO `bitacora` VALUES('96', '1', 'Asistencia', 'Registro de Entrada (Tras Justificar)', 'El empleado Anthony Maita marcó su entrada a las 11:32:49.', '2026-04-30 11:32:49', '::1');
INSERT INTO `bitacora` VALUES('97', '1', 'Asistencia', 'Registro de Salida', 'El empleado Anthony Maita marcó su salida a las 11:36:52.', '2026-04-30 11:36:52', '::1');
INSERT INTO `bitacora` VALUES('98', '1', 'Reportes', 'Descarga de Reporte PDF', 'Descargó Reporte General de Asistencia correspondiente a: Abril de 2026.', '2026-04-30 11:41:07', '::1');
INSERT INTO `bitacora` VALUES('99', '1', 'Configuracion', 'Modificación de Horarios del Sistema', 'Nueva entrada: 12:00:00, salida: 17:30:00, tolerancia: 30 min.', '2026-04-30 11:49:33', '::1');
INSERT INTO `bitacora` VALUES('100', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Salida Irregular\' para el empleado \'dede dede\' en la fecha: 2026-04-22.', '2026-04-30 13:16:07', '::1');
INSERT INTO `bitacora` VALUES('101', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-22.', '2026-04-30 13:16:17', '::1');
INSERT INTO `bitacora` VALUES('102', '1', 'Asistencia', 'Modificación de Asistencia', 'Cambió el estado a \'Puntual\' para el empleado \'dede dede\' en la fecha: 2026-04-23.', '2026-04-30 13:16:21', '::1');
INSERT INTO `bitacora` VALUES('103', '1', 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_30-04-2026_13-48-08.sql en el servidor.', '2026-04-30 13:48:08', '::1');
INSERT INTO `bitacora` VALUES('104', '1', 'Seguridad', 'Eliminación de Respaldo', 'Eliminó el archivo: respaldo_ceis_30-04-2026_13-48-08.sql del servidor.', '2026-04-30 13:48:25', '::1');
INSERT INTO `bitacora` VALUES('105', '1', 'Seguridad', 'Generación de Respaldo', 'Guardó el archivo: respaldo_ceis_30-04-2026_15-40-22.sql en el servidor.', '2026-04-30 15:40:22', '::1');


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

INSERT INTO `configuracion` VALUES('1', '12:00:00', '17:30:00', '30');


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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Plantillas horarias preestablecidas para carga rápida en el formulario de configuración';

INSERT INTO `configuraciones_preestablecidas` VALUES('5', 'dededed', '14:10:00', '20:30:00', '30', '0', '1', '2026-04-28 23:10:00', '2026-04-28 23:10:00');
INSERT INTO `configuraciones_preestablecidas` VALUES('6', 'dedee', '14:10:00', '20:30:00', '30', '0', '1', '2026-04-28 23:10:07', '2026-04-28 23:10:07');


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
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones` VALUES('1', '321', '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-07 00:54:19');
INSERT INTO `notificaciones` VALUES('2', '321', 'ATENCIÓN: Tu justificación del día 07-04-2026 ha sido RECHAZADA. Revisa las observaciones.', 'Alerta', '1', '2026-04-07 01:11:51');
INSERT INTO `notificaciones` VALUES('3', '321', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: Por gay', 'Alerta', '1', '2026-04-07 01:31:42');
INSERT INTO `notificaciones` VALUES('4', '321', 'Administración ha modificado tu perfil. Nuevo rol: Subdirector. Estado: Activo.', 'Informativa', '1', '2026-04-07 01:32:50');
INSERT INTO `notificaciones` VALUES('5', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: lololooooooooooooooooooooooooooookdsokfñskfñokesñfkñsekfñskefñokseñfkifgjerjfmtrtuoweugerkvrjhgoierkfkerpf\n\n\ndejiofjreifjerifoiergoijoerg\n\n\nekjfirjfirejfoirefoijefoijeriofjweijdfqjwfjdhe', 'Alerta', '1', '2026-04-07 01:36:57');
INSERT INTO `notificaciones` VALUES('6', '145', '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-07 01:39:37');
INSERT INTO `notificaciones` VALUES('7', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee', 'Alerta', '1', '2026-04-07 01:40:49');
INSERT INTO `notificaciones` VALUES('8', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Alerta', '1', '2026-04-07 01:45:47');
INSERT INTO `notificaciones` VALUES('9', '145', 'ATENCIÓN: Tu justificación del 07-04-2026 ha sido RECHAZADA. Motivo: dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', 'Alerta', '1', '2026-04-07 01:46:39');
INSERT INTO `notificaciones` VALUES('10', '145', '¡Tu justificación del día 15-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-16 15:11:15');
INSERT INTO `notificaciones` VALUES('11', '322', '¡Tu justificación del día 16-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-16 15:13:58');
INSERT INTO `notificaciones` VALUES('12', '322', '¡Tu justificación del día 16-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-16 15:14:32');
INSERT INTO `notificaciones` VALUES('13', '145', '¡Tu justificación del día 16-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-16 15:17:06');
INSERT INTO `notificaciones` VALUES('14', '13', '¡Tu justificación del día 01-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 14:37:23');
INSERT INTO `notificaciones` VALUES('15', '1', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 14:38:33');
INSERT INTO `notificaciones` VALUES('16', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 14:38:35');
INSERT INTO `notificaciones` VALUES('17', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:03:02');
INSERT INTO `notificaciones` VALUES('18', '13', '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:03:04');
INSERT INTO `notificaciones` VALUES('19', '13', 'ATENCIÓN: Tu justificación del 24-04-2026 ha sido RECHAZADA. Motivo: porquwe si', 'Alerta', '1', '2026-04-24 15:15:22');
INSERT INTO `notificaciones` VALUES('20', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:15:50');
INSERT INTO `notificaciones` VALUES('21', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:18:08');
INSERT INTO `notificaciones` VALUES('22', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:23:59');
INSERT INTO `notificaciones` VALUES('23', '13', 'ATENCIÓN: Tu justificación del 24-04-2026 ha sido RECHAZADA. Motivo: 33', 'Alerta', '1', '2026-04-24 15:25:08');
INSERT INTO `notificaciones` VALUES('24', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:29:18');
INSERT INTO `notificaciones` VALUES('25', '13', '¡Tu justificación del día 10-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:34:12');
INSERT INTO `notificaciones` VALUES('26', '13', '¡Tu justificación del día 13-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:36:14');
INSERT INTO `notificaciones` VALUES('27', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 15:38:51');
INSERT INTO `notificaciones` VALUES('28', '1', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-24 17:20:13');
INSERT INTO `notificaciones` VALUES('29', '1', 'ATENCIÓN: Tu justificación del 24-04-2026 ha sido RECHAZADA. Motivo: sffse', 'Alerta', '1', '2026-04-24 17:21:42');
INSERT INTO `notificaciones` VALUES('30', '13', '¡Tu justificación del día 24-04-2026 ha sido APROBADA!', 'Exito', '0', '2026-04-24 19:20:31');
INSERT INTO `notificaciones` VALUES('31', '1', '¡Tu justificación del día 13-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-28 23:19:02');
INSERT INTO `notificaciones` VALUES('32', '1', '¡Tu justificación del día 28-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-28 23:26:29');
INSERT INTO `notificaciones` VALUES('33', '1', '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-28 23:30:05');
INSERT INTO `notificaciones` VALUES('34', '1', '¡Tu justificación del día 29-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 21:34:46');
INSERT INTO `notificaciones` VALUES('35', '1', '¡Tu justificación del día 29-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 22:09:58');
INSERT INTO `notificaciones` VALUES('36', '1', '¡Tu justificación del día 09-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 22:10:00');
INSERT INTO `notificaciones` VALUES('37', '1', 'ATENCIÓN: Tu justificación del 29-04-2026 ha sido RECHAZADA. Motivo: gygihiuhuihuhiuhiuhuhihuihiuhihihuihiuhiuhiuh', 'Alerta', '1', '2026-04-29 22:13:20');
INSERT INTO `notificaciones` VALUES('38', '1', '¡Tu justificación del día 29-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 22:23:41');
INSERT INTO `notificaciones` VALUES('39', '1', '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 22:52:33');
INSERT INTO `notificaciones` VALUES('40', '1', '¡Tu justificación del día 07-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 23:29:16');
INSERT INTO `notificaciones` VALUES('41', '1', '¡Tu justificación del día 09-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 23:29:19');
INSERT INTO `notificaciones` VALUES('42', '1', '¡Tu justificación del día 30-03-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-29 23:44:23');
INSERT INTO `notificaciones` VALUES('43', '1', 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', '1', '2026-04-29 23:45:10');
INSERT INTO `notificaciones` VALUES('44', '1', 'Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.', 'Informativa', '1', '2026-04-29 23:45:21');
INSERT INTO `notificaciones` VALUES('45', '1', '¡Tu justificación del día 30-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-30 11:36:48');
INSERT INTO `notificaciones` VALUES('46', '1', '¡Tu justificación del día 30-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-30 11:37:24');
INSERT INTO `notificaciones` VALUES('47', '1', '¡Tu justificación del día 08-04-2026 ha sido APROBADA!', 'Exito', '1', '2026-04-30 11:38:17');


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

INSERT INTO `personal` VALUES('1', '30710894', 'Anthony', 'Maita', 'default.png', '04040404040', '1', '1', '12:50:00', '08:03:00', '2026-04-16');
INSERT INTO `personal` VALUES('2', '14669420', 'Lauris', 'Viera', 'default.png', '01010101010', '4', '2', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('3', '30303030', 'koli', 'vvv', 'default.png', '13213213213', '3', '3', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('4', '89798789', 'rita', 'man', 'default.png', '13165464545', '5', '4', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('5', '42342342', 'lolo', 'rerere', 'default.png', '43123212312', '6', '5', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('6', '13213543', 'kioki', 'lolo', 'default.png', '04128974532', '3', '6', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('7', '12331231', 'mobica', 'martin', 'default.png', '45654654654', '3', '7', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('8', '46848944', 'lolol', 'd13edwdwa', 'default.png', '65456456165', '4', '8', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('9', '12321312', 'for', 'mavi', 'default.png', '31231232131', '4', '9', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('10', '53454354', 'lovo', 'qeqwe', 'default.png', '12312313131', '5', '10', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('11', '21312312', 'dede', 'dede', 'default.png', '12323213123', '4', '11', NULL, NULL, '2026-04-16');
INSERT INTO `personal` VALUES('13', '12313123', 'dedeeded', 'dedeed', 'default.png', '12343242432', '4', '13', NULL, NULL, '2026-04-24');


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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` VALUES('1', 'anthony12', '$2y$10$DRWhHJmwnZRtbcyxXGpCyeGojFfPIddcj12gopj1x6ZwQs6M/8PbW', '1', 'Activo', '1', '$2y$10$BVyAkwrRzbBJ9tuAd2knsehVnQ8Qz6NXtpcJXU2GGggvUNtLU5Zt.', '2', '$2y$10$.bwdaZgtiZj38qIqFF43q.VdDmsObq6u40Qsr5AY2BLV4J8BOpZya', '3', '$2y$10$MrLRjTejXJVDLbswIiicBukk1CmRq1Wx1TXKo3RGoqofaOgNHKT8u');
INSERT INTO `usuarios` VALUES('2', 'lauri12', '$2y$10$jF3ZtGPxA9zaa8AiarONNueLijyAuwB0aI5P3ByVMJ1Nm1d1Ft8GK', '3', 'Activo', '3', '$2y$10$xbV.zubjzllTApaKNV3PWuyUdPoMhUDhswls0i4T8u/vVy3Z8iBVO', '1', '$2y$10$XexaOW3gdbHhzh308McX9OaIFOedZPGuA0uwurtNn9R2WruWhHth2', '4', '$2y$10$VIQGUc3MmqYLC7wqM48OPuQ3.TNNMn8.1Wb7iAoukAXSXzM0ErJTG');
INSERT INTO `usuarios` VALUES('3', 'ami', '$2y$10$gDwc4CDjCK0xMwJnP7USpOS4z9vaktD10y0J2cIfBClMvpjruZYWu', '3', 'Activo', '1', '$2y$10$Da3VYXXG2mClbVhRIfZ7VuVlIBuGzzv8NVRQZqZd4aLtK57pPK9dK', '2', '$2y$10$YrzcoyAPCyS48/nIoBdvzeseSSkWwTEqvwofTjXAAJlse3840sBrC', '3', '$2y$10$s1tgL9zNMWS3oiMK5RgZYeXbyW77NK7utl2K0xmZZVREho1JtTgie');
INSERT INTO `usuarios` VALUES('4', 'moon', '$2y$10$abtBC7ZvTeTF.SDrtdKRGe8ErWG3ZkzGLeFQocLxJ.A5pIIqqr3yO', '3', 'Activo', '1', '$2y$10$0L4O4R8mXguJcLvwbDSeNu.uBMRYiUbI.TKIqHMJ89ai/BpQL3huG', '2', '$2y$10$ivRwcqCx317Fy3PYbGMvS.qIydlVxlEDHTfzOKXJTcsCnEWs3FoKW', '4', '$2y$10$Z5hvOmU8Y4g7l/04EonN5OJGN55p3LBPoD023BSDJ6rfvKKJSzojm');
INSERT INTO `usuarios` VALUES('5', 'juan12', '$2y$10$v4CQMbEO9ycIyyiqbvH/pOCmKMidiSs1tIYojcWv7VN8WlUnjwdLy', '3', 'Activo', '1', '$2y$10$Ei.mjjIKBgb6xyQlIVV/q.czJBFC.NRL39EcZl/X.0lxbWn2yckry', '2', '$2y$10$owY7aZItWFGqFUTPtxVMTu4Cd7cXJB.a8/lcgw6emvo5wu.n0E.jK', '3', '$2y$10$3n6fKqmYI63BPLWioB2NdejGeHnk6ZLbTr7VeIsAMlfpf78nGm8Le');
INSERT INTO `usuarios` VALUES('6', 'bece', '$2y$10$B5rYIYfwkB5Loyav983ZE.EYcYcSxuM/otW2W9ij.7K5r8pAA/KlK', '3', 'Activo', '2', '$2y$10$LKDKp.0sTH6YfkB063Ir5ONNs/x.72iAX11WOORdxj5z05j9oEO2S', '3', '$2y$10$fA8valgK7mjUqBr8bpBCH.knVnvSJ/arPl7ckZL0EbEtKTpBL4IPm', '1', '$2y$10$JV0z3H91z93mophwnOkYBuI.7n2KxhEhjoj0wE8UQUzfA4LS8jzh.');
INSERT INTO `usuarios` VALUES('7', 'movi', '$2y$10$a00zM.TNxCXvm7fbzEasxOaF9thu2a1MznrLQzI86qY0bLY2Hleh.', '3', 'Activo', '1', '$2y$10$UP9GSTyxBf4Ub23npLox4OXKjK8jVoLD0nPyKKaUZ05FWlgVn9Jpq', '3', '$2y$10$/cjTepaPYyv.vzsdRAmDqOHhVUvb1QjaHZi1LI8vR/pGDlw8.tKEK', '2', '$2y$10$AZuT5ZpIyZSJla1WWjBPMOzyYgRcBabC/JwJKQOHGxrwV1drTqdFO');
INSERT INTO `usuarios` VALUES('8', 'ñopo', '$2y$10$Hk7z6LmWUNlEa/pFZQiRSeeJ9oBmowz0eicPERtF8Lp.OyzFLB5hu', '3', 'Activo', '1', '$2y$10$qRpfhY8QnAqK5ATaBub2O.HeK5pqnb7RvKau7cZ4SVj.RB1SjStCu', '2', '$2y$10$Uw4bU6tcXrsKs0qPN8WixOb1f3blRklOdpfAAnWNl48xuR7VqvTEW', '3', '$2y$10$EjXnUL6x.4er.p3V8DHQLueyUDwErhrlRK0YGSr2r6frstnna2KUq');
INSERT INTO `usuarios` VALUES('9', 'kilo', '$2y$10$7TxzFhUFLpzPtEa8aK4kuupWMjZ.BwOLZhyzF8bWo0gRu8v7.ZR36', '3', 'Activo', '1', '$2y$10$JLCzf3By/uCLyiORP5BCAOk9dfxZcXXvIMNu85UNSkQigMiN26M2W', '2', '$2y$10$PSdVNHNlIKLkOiXIdI8owuS2RA3YhSevns6K8GZmYbYH7f/CZ3gmi', '4', '$2y$10$VgD9H3bWsnkXokgmXF0G.uMNeoQvFVhJIR4iP//A612bP/4Dpfpk6');
INSERT INTO `usuarios` VALUES('10', 'polo', '$2y$10$bV3d1jbQ9PhApPxIErjWWOK7chkzvpRrrbKfnjBDli/Sy3BQz0Zei', '3', 'Activo', '1', '$2y$10$supjFoYp3.RS0A0cTBJX4O9Rm4fvHdEAPvipmD2WnojYCpjFLkbjq', '3', '$2y$10$70OVgjaeBVCqNIJRdYCW1uUXg2.Vfc2zwvBKlTxm3KLRKKtAL7EnW', '2', '$2y$10$2wukGQejN5n9gyWdvwuPu.OS./1ph0bM9XAp.N3Q73QRE4fOrjSY.');
INSERT INTO `usuarios` VALUES('11', 'jum', '$2y$10$Ep.hIzcFJB.CH8z9zoWoNuejx9rCiHsaV1ggH/bX3zfHVY1F2tscO', '3', 'Activo', '1', '$2y$10$.AB0icSJQkumkiK1Ct4H9u09c6y5EX57xUxr24N/py8Pih7K.mLRa', '2', '$2y$10$JjJk1L5w7pL5LRkaHrSEteDgLqJLQOoRnj/geyttY3sk1b3XqxkMK', '3', '$2y$10$jYRcGvrjMODzC027RGJYwuSbh3PbHrzk/7RgCIfOP9UI4QAsdwu7.');
INSERT INTO `usuarios` VALUES('13', 'meme', '$2y$10$5HO2n8qaANePkG/S.A.5cO02iDmT2UOPymhfqfaXmRFIWYtxm.dA.', '3', 'Activo', '2', '$2y$10$9HpLZQMzS16TnNsPXl8MMuJb91zHxC8ZaMGue9fU1O6oO3Yowt906', '1', '$2y$10$ISA.Uy331aXBIi8Nsy76Gu6wyLAud3vfkc02bXf2zssfr9I1Z9Qqy', '3', '$2y$10$4FfhXVHmFZuDpwyXxpglneiQgA8lETdj3.uNlbDEUQlGxF5RSh0L6');


SET FOREIGN_KEY_CHECKS=1;
