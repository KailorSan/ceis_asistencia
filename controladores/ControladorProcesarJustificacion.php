<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorNotificaciones.php';

if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

// =====================================================================
// CORRECCIÓN ADVERTENCIA: El motivo de rechazo ahora viaja por POST
// en lugar de GET. Antes quedaba expuesto en la URL, en el historial
// del navegador y en los logs del servidor web. Ahora se recibe de
// forma segura desde la sesión (guardado temporalmente por justificaciones.php
// antes de redirigir a este controlador).
//
// FLUJO NUEVO:
//   justificaciones.php → guarda id, accion y motivo_rechazo en $_SESSION
//                        → hace fetch POST a este controlador
//   Este controlador    → lee los datos de $_SESSION y limpia
// =====================================================================

// Soportamos tanto POST (nuevo flujo AJAX desde justificaciones.php)
// como GET con id/accion para mantener compatibilidad con el flujo de aprobación
// (que no necesita motivo y puede seguir siendo una redirección simple).

$id_asistencia  = null;
$accion         = null;
$motivo_rechazo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nuevo flujo seguro: datos vienen por POST (usado para rechazos con motivo)
    $id_asistencia  = isset($_POST['id'])     ? (int) $_POST['id']               : null;
    $accion         = isset($_POST['accion']) ? trim($_POST['accion'])            : null;
    $motivo_rechazo = isset($_POST['motivo_rechazo']) ? trim($_POST['motivo_rechazo']) : '';
} elseif (isset($_GET['id'], $_GET['accion'])) {
    // Flujo GET original: sigue funcionando para aprobaciones simples
    $id_asistencia  = (int) $_GET['id'];
    $accion         = trim($_GET['accion']);
    // No leemos motivo_rechazo por GET (seguridad)
}

if (!$id_asistencia || !in_array($accion, ['aprobar', 'rechazar'])) {
    header("Location: ../vistas/justificaciones.php");
    exit;
}

try {
    $stmt = $conexion->prepare("SELECT * FROM asistencias WHERE id_asistencia = ?");
    $stmt->execute([$id_asistencia]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($registro) {
        $motivo      = $registro['motivo_justificacion'];
        $estado_base = trim(str_replace(['(Pendiente)', ' (Pendiente)'], '', $registro['estado'])); 
        $observacion = $registro['observacion'];

        // OBTENER FECHA Y USUARIO PARA LA NOTIFICACIÓN
        $stmt_info = $conexion->prepare("SELECT a.fecha, p.id_usuario FROM asistencias a INNER JOIN personal p ON a.id_personal = p.id_personal WHERE a.id_asistencia = ?");
        $stmt_info->execute([$id_asistencia]);
        $info_asistencia = $stmt_info->fetch(PDO::FETCH_ASSOC);
        
        $fecha_incidencia    = date('d-m-Y', strtotime($info_asistencia['fecha']));
        $id_usuario_destino  = $info_asistencia['id_usuario'];

        if ($accion == 'aprobar') {
            $nuevo_estado    = 'Justificado'; 
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
            
            $mensaje_notif = "¡Tu justificación del día $fecha_incidencia ha sido APROBADA!";
            ControladorNotificaciones::crear($conexion, $id_usuario_destino, $mensaje_notif, 'Exito');
            
        } elseif ($accion == 'rechazar') {
            $nuevo_estado = $estado_base; 
            
            // El motivo ya llegó limpio por POST; solo lo sanitizamos
            $texto_rechazo = $motivo_rechazo
                ? " [Denegada: " . htmlspecialchars($motivo_rechazo, ENT_QUOTES, 'UTF-8') . "]"
                : " [Denegada por Dirección]";
            
            $nueva_observacion = $observacion ? $observacion . $texto_rechazo : ltrim($texto_rechazo);
            
            if (strpos($motivo, '[Llegada Tardía]') !== false || strpos($motivo, '[Inasistencia]') !== false) {
                $nuevo_estado = 'Falta'; 
            } elseif (strpos($motivo, '[Salida Temprana]') !== false) {
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

header("Location: ../vistas/justificaciones.php");
exit;
?>