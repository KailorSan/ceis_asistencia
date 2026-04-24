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
    // Recibimos IDs ocultos
    $id_usuario = $_POST['id_usuario'];
    $id_personal = $_POST['id_personal'];
    
    // Recibimos los datos del formulario (Personal)
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $cedula = trim($_POST['cedula']);
    $telefono = trim($_POST['telefono']);
    $id_cargo = $_POST['id_cargo'];
    
    // Recibimos los datos del formulario (Accesos / Usuario)
    $usuario = trim($_POST['usuario']);
    $estado = $_POST['estado'];
    $id_rol = $_POST['id_rol'];

    // Evitar que el Director se quite los permisos a sí mismo por error
    if ($id_usuario == $_SESSION['id_usuario'] && $id_rol != 1) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No puedes quitarte el rango de Director a ti mismo.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    try {
        // Iniciamos transacción porque actualizaremos 2 tablas. Si una falla, no se guarda nada.
        $conexion->beginTransaction();

        // 1. Actualizar la tabla de usuarios usando "nombre_usuario"
        $stmt_u = $conexion->prepare("UPDATE usuarios SET nombre_usuario = ?, estado = ?, id_rol = ? WHERE id_usuario = ?");
        $stmt_u->execute([$usuario, $estado, $id_rol, $id_usuario]);

        // 2. Procesamiento de la Foto de Perfil (Si el usuario subió una nueva)
        $foto_actualizada = false;
        $nombre_foto = "";
        
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
            $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            // Generar nombre único para evitar que se sobrescriban o guarden en caché visual
            $nombre_foto = "perfil_" . $id_personal . "_" . time() . "." . $extension;
            $ruta_destino = "../recursos/img/perfiles/" . $nombre_foto;
            
            // Mover archivo
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino)) {
                $foto_actualizada = true;
            }
        }

        // 3. Actualizar la tabla personal (datos de perfil)
        if ($foto_actualizada) {
            // Si hay foto nueva, la actualizamos en la DB
            $stmt_p = $conexion->prepare("UPDATE personal SET nombres = ?, apellidos = ?, cedula = ?, telefono = ?, id_cargo = ?, foto_perfil = ? WHERE id_personal = ?");
            $stmt_p->execute([$nombres, $apellidos, $cedula, $telefono, $id_cargo, $nombre_foto, $id_personal]);
        } else {
            // Si NO hay foto, solo actualizamos los campos de texto y mantenemos la foto anterior
            $stmt_p = $conexion->prepare("UPDATE personal SET nombres = ?, apellidos = ?, cedula = ?, telefono = ?, id_cargo = ? WHERE id_personal = ?");
            $stmt_p->execute([$nombres, $apellidos, $cedula, $telefono, $id_cargo, $id_personal]);
        }
        
        // Obtenemos el nombre del nuevo rol asignado para dejarlo bonito en la bitácora
        $stmt_rol = $conexion->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");
        $stmt_rol->execute([$id_rol]);
        $nombre_rol = $stmt_rol->fetchColumn() ?: "ID Rol: $id_rol";

        // Registrar en bitácora
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Usuarios', 'Edición Completa de Perfil', "Actualizó los datos de '$nombres $apellidos'. Cédula: $cedula, Estado: $estado, Rol: $nombre_rol.");

        // DISPARAR NOTIFICACIÓN DE EDICIÓN DE PERFIL AL USUARIO EDITADO
        $mensaje_perfil = "Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.";
        ControladorNotificaciones::crear($conexion, $id_usuario, $mensaje_perfil, 'Informativa');

        // Confirmar transacción
        $conexion->commit();
        $_SESSION['alerta_personal'] = ['tipo' => 'success', 'mensaje' => 'Todos los datos del empleado han sido actualizados.'];

    } catch (PDOException $e) {
        // Si algo falla, revertimos todos los cambios
        $conexion->rollBack();
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Error al actualizar al empleado. Verifica que el nombre de usuario no esté en uso.'];
    }
    
    // Redirigir siempre de vuelta al panel
    header("Location: ../vistas/personal.php");
    exit;
}
?>