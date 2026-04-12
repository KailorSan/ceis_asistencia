<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorNotificaciones.php';

if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id_asistencia = $_GET['id'];
    $accion = $_GET['accion'];

    try {
        $stmt = $conexion->prepare("SELECT * FROM asistencias WHERE id_asistencia = ?");
        $stmt->execute([$id_asistencia]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            $motivo = $registro['motivo_justificacion'];
            $estado_base = trim(str_replace(['(Pendiente)', ' (Pendiente)'], '', $registro['estado'])); 
            $observacion = $registro['observacion'];

            // OBTENER FECHA Y USUARIO PARA LA NOTIFICACIÓN
            $stmt_info = $conexion->prepare("SELECT a.fecha, p.id_usuario FROM asistencias a INNER JOIN personal p ON a.id_personal = p.id_personal WHERE a.id_asistencia = ?");
            $stmt_info->execute([$id_asistencia]);
            $info_asistencia = $stmt_info->fetch(PDO::FETCH_ASSOC);
            
            $fecha_incidencia = date('d-m-Y', strtotime($info_asistencia['fecha']));
            $id_usuario_destino = $info_asistencia['id_usuario'];

            if ($accion == 'aprobar') {
                $nuevo_estado = 'Justificado'; 
                $nueva_observacion = $observacion;

                if (strpos($motivo, '[Llegada Tardía]') !== false) {
                    $nuevo_estado = 'Retraso';
                } elseif (strpos($motivo, '[Salida Temprana]') !== false) {
                    if ($estado_base == 'Retraso') {
                        $nuevo_estado = 'Retraso y Salida Temprana';
                    } elseif ($estado_base == 'Puntual') {
                        $nuevo_estado = 'Puntual y Salida Temprana';
                    } else {
                        $nuevo_estado = 'Salida Temprana';
                    }
                    if ($observacion) {
                        $nueva_observacion = str_replace('[Salida Temprana Pendiente]', 'Salió a una hora más temprana', $observacion);
                        $nueva_observacion = str_replace('Registrada a las', 'mediante justificación aprobada a las', $nueva_observacion);
                    }
                } elseif (strpos($motivo, '[Inasistencia]') !== false) {
                    $nuevo_estado = 'Justificado';
                }

                $stmt_up = $conexion->prepare("UPDATE asistencias SET estado = ?, estado_justificacion = 'Aprobada', observacion = ? WHERE id_asistencia = ?");
                $stmt_up->execute([$nuevo_estado, $nueva_observacion, $id_asistencia]);
                $mensaje = "La justificación ha sido aprobada correctamente.";
                
                // DISPARAR NOTIFICACIÓN DE APROBACIÓN
                $mensaje_notif = "¡Tu justificación del día $fecha_incidencia ha sido APROBADA!";
                ControladorNotificaciones::crear($conexion, $id_usuario_destino, $mensaje_notif, 'Exito');
                
            } elseif ($accion == 'rechazar') {
                $nuevo_estado = $estado_base; 
                
                // ATRAPAMOS EL MOTIVO DEL RECHAZO
                $motivo_rechazo = isset($_GET['motivo_rechazo']) ? trim($_GET['motivo_rechazo']) : '';
                $texto_rechazo = $motivo_rechazo ? " [Denegada: $motivo_rechazo]" : " [Denegada por Dirección]";
                
                $nueva_observacion = $observacion ? $observacion . $texto_rechazo : ltrim($texto_rechazo);
                
                if (strpos($motivo, '[Llegada Tardía]') !== false || strpos($motivo, '[Inasistencia]') !== false) {
                    $nuevo_estado = 'Falta'; 
                } 
                elseif (strpos($motivo, '[Salida Temprana]') !== false) {
                    if ($estado_base == 'Retraso') {
                        $nuevo_estado = 'Retraso y Salida Irregular';
                    } elseif ($estado_base == 'Puntual') {
                        $nuevo_estado = 'Puntual y Salida Irregular';
                    } else {
                        $nuevo_estado = 'Salida Irregular';
                    }
                    if ($observacion) {
                        $nueva_observacion = str_replace('[Salida Temprana Pendiente]', 'Salida denegada', $nueva_observacion);
                    }
                }
                
                $stmt_up = $conexion->prepare("UPDATE asistencias SET estado = ?, estado_justificacion = 'Rechazada', observacion = ? WHERE id_asistencia = ?");
                $stmt_up->execute([$nuevo_estado, $nueva_observacion, $id_asistencia]);
                $mensaje = "La justificación ha sido rechazada.";

                // DISPARAR NOTIFICACIÓN DE RECHAZO CON EL MOTIVO INCLUIDO
                $mensaje_notif = "ATENCIÓN: Tu justificación del $fecha_incidencia ha sido RECHAZADA.";
                if ($motivo_rechazo) {
                    $mensaje_notif .= " Motivo: " . $motivo_rechazo;
                }
                ControladorNotificaciones::crear($conexion, $id_usuario_destino, $mensaje_notif, 'Alerta');
            }
            
            $_SESSION['alerta_justificacion'] = ['tipo' => 'success', 'mensaje' => $mensaje];
        }

    } catch (PDOException $e) {
        $_SESSION['alerta_justificacion'] = ['tipo' => 'error', 'mensaje' => 'Error en la base de datos al procesar la solicitud.'];
    }
}
header("Location: ../vistas/justificaciones.php");
exit;
?>