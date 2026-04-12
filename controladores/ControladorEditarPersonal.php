<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; 
require_once 'ControladorNotificaciones.php'; // <-- INCLUIMOS EL SISTEMA DE NOTIFICACIONES

if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $estado = $_POST['estado'];
    $id_rol = $_POST['id_rol'];

    // Evitar que el Director se quite los permisos a sí mismo por error
    if ($id_usuario == $_SESSION['id_usuario'] && $id_rol != 1) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No puedes quitarte el rango de Director a ti mismo.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    try {
        // Obtenemos el nombre del usuario para que el registro sea claro
        $stmt_info = $conexion->prepare("SELECT nombre_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt_info->execute([$id_usuario]);
        $nombre_editado = $stmt_info->fetchColumn() ?: "ID: $id_usuario";

        // Obtenemos el nombre del nuevo rol asignado
        $stmt_rol = $conexion->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");
        $stmt_rol->execute([$id_rol]);
        $nombre_rol = $stmt_rol->fetchColumn() ?: "ID Rol: $id_rol";

        $stmt = $conexion->prepare("UPDATE usuarios SET estado = ?, id_rol = ? WHERE id_usuario = ?");
        $stmt->execute([$estado, $id_rol, $id_usuario]);
        
        // Registrar en bitácora
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Usuarios', 'Edición de Perfil y Accesos', "Modificó al usuario '$nombre_editado'. Nuevo estado: $estado, Nuevo rol: $nombre_rol.");

        // DISPARAR NOTIFICACIÓN DE EDICIÓN DE PERFIL
        $mensaje_perfil = "Administración ha modificado tu perfil. Nuevo rol: $nombre_rol. Estado: $estado.";
        ControladorNotificaciones::crear($conexion, $id_usuario, $mensaje_perfil, 'Informativa');

        $_SESSION['alerta_personal'] = ['tipo' => 'success', 'mensaje' => 'Datos del empleado actualizados.'];
    } catch (PDOException $e) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Error al actualizar al empleado.'];
    }
    
    header("Location: ../vistas/personal.php");
    exit;
}
?>