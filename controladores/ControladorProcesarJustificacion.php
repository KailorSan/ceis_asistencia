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
        if ($accion == 'aprobar') {
            $stmt = $conexion->prepare("UPDATE asistencias SET estado = 'Justificado', estado_justificacion = 'Aprobada' WHERE id_asistencia = ?");
            $mensaje = "La justificación ha sido aprobada correctamente.";
        } elseif ($accion == 'rechazar') {
            $stmt = $conexion->prepare("UPDATE asistencias SET estado_justificacion = 'Rechazada' WHERE id_asistencia = ?");
            $mensaje = "La justificación ha sido rechazada.";
        }
        $stmt->execute([$id_asistencia]);
        
        // Todo salió bien, guardamos el mensaje de éxito
        $_SESSION['alerta_justificacion'] = ['tipo' => 'success', 'mensaje' => $mensaje];
        
    } catch (PDOException $e) {
        // ¡Capturamos el error! Ya no fallará en silencio
        $_SESSION['alerta_justificacion'] = ['tipo' => 'error', 'mensaje' => 'Error en la base de datos al procesar la solicitud.'];
    }
}
header("Location: ../vistas/justificaciones.php");
exit;
?>