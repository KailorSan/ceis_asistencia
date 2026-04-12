<?php
class ControladorNotificaciones {
    // Función para crear una nueva notificación
    public static function crear($conexion, $id_usuario, $mensaje, $tipo = 'Informativa') {
        try {
            $stmt = $conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, tipo) VALUES (?, ?, ?)");
            return $stmt->execute([$id_usuario, $mensaje, $tipo]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Función para obtener las notificaciones no leídas del usuario
    public static function obtenerNoLeidas($conexion, $id_usuario) {
        $stmt = $conexion->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? AND leido = 0 ORDER BY fecha_creacion DESC");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Función para marcar como leídas
    public static function marcarLeidas($conexion, $id_usuario) {
        $stmt = $conexion->prepare("UPDATE notificaciones SET leido = 1 WHERE id_usuario = ?");
        return $stmt->execute([$id_usuario]);
    }
}

// Bloque AJAX: Si el JS llama a este archivo directamente para marcar como leídas
if (isset($_POST['accion']) && $_POST['accion'] == 'marcar_leidas') {
    session_start();
    require_once '../configuracion/conexion.php';
    if (isset($_SESSION['id_usuario'])) {
        ControladorNotificaciones::marcarLeidas($conexion, $_SESSION['id_usuario']);
        echo "ok";
    }
    exit;
}
?>