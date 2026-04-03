<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; // NUEVO: Incluimos la bitácora

if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Protección anti-suicidio: No puedes borrarte a ti mismo
    if ($id_usuario == $_SESSION['id_usuario']) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No puedes eliminar tu propia cuenta.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    try {
        // CORRECCIÓN APLICADA: Obtenemos "nombre_usuario" antes de borrarlo
        $stmt_info = $conexion->prepare("SELECT nombre_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt_info->execute([$id_usuario]);
        $nombre_eliminado = $stmt_info->fetchColumn();
        if (!$nombre_eliminado) { $nombre_eliminado = "ID: " . $id_usuario; }

        // Obtenemos el nombre de la foto para borrarla del disco duro
        $stmt_foto = $conexion->prepare("SELECT foto_perfil FROM personal WHERE id_usuario = ?");
        $stmt_foto->execute([$id_usuario]);
        $foto = $stmt_foto->fetchColumn();

        if ($foto && $foto != 'default.png') {
            $ruta_foto = "../recursos/img/perfiles/" . $foto;
            if (file_exists($ruta_foto)) {
                unlink($ruta_foto); // Borra el archivo físico
            }
        }

        // Borramos al usuario. La base de datos (ON DELETE CASCADE) hará el resto
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        
        // NUEVO: Registrar en Bitácora la eliminación del usuario
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Usuarios', 'Eliminación de Personal', "Eliminó permanentemente del sistema al usuario: '$nombre_eliminado'.");

        $_SESSION['alerta_personal'] = ['tipo' => 'success', 'mensaje' => 'El empleado ha sido eliminado definitivamente.'];
    } catch (PDOException $e) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Error al intentar eliminar el registro.'];
    }
    
    header("Location: ../vistas/personal.php");
    exit;
}
?>