<?php
session_start();
require_once '../configuracion/conexion.php';

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
                
            } elseif ($accion == 'rechazar') {
                $nuevo_estado = $estado_base; 
                $nueva_observacion = $observacion;
                
                if (strpos($motivo, '[Llegada Tardía]') !== false || strpos($motivo, '[Inasistencia]') !== false) {
                    $nuevo_estado = 'Falta'; 
                } 
                // NUEVO: SI LE RECHAZAN IRSE TEMPRANO, ES UNA SALIDA IRREGULAR
                elseif (strpos($motivo, '[Salida Temprana]') !== false) {
                    if ($estado_base == 'Retraso') {
                        $nuevo_estado = 'Retraso y Salida Irregular';
                    } elseif ($estado_base == 'Puntual') {
                        $nuevo_estado = 'Puntual y Salida Irregular';
                    } else {
                        $nuevo_estado = 'Salida Irregular';
                    }
                    if ($observacion) {
                        $nueva_observacion = str_replace('[Salida Temprana Pendiente]', 'Salida denegada por Dirección', $observacion);
                    }
                }
                
                $stmt_up = $conexion->prepare("UPDATE asistencias SET estado = ?, estado_justificacion = 'Rechazada', observacion = ? WHERE id_asistencia = ?");
                $stmt_up->execute([$nuevo_estado, $nueva_observacion, $id_asistencia]);
                $mensaje = "La justificación ha sido rechazada.";
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