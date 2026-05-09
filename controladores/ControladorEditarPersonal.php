<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; 
require_once 'ControladorNotificaciones.php';

if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_usuario  = filter_input(INPUT_POST, 'id_usuario',  FILTER_VALIDATE_INT);
    $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);

    if (!$id_usuario || !$id_personal || $id_usuario <= 0 || $id_personal <= 0) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Solicitud inválida. IDs incorrectos.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    $nombres   = htmlspecialchars(strip_tags(trim($_POST['nombres']   ?? '')), ENT_QUOTES, 'UTF-8');
    $apellidos = htmlspecialchars(strip_tags(trim($_POST['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8');
    $cedula    = trim($_POST['cedula']   ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $usuario   = htmlspecialchars(strip_tags(trim($_POST['usuario']   ?? '')), ENT_QUOTES, 'UTF-8');
    $id_cargo  = filter_input(INPUT_POST, 'id_cargo', FILTER_VALIDATE_INT);

    $estados_validos = ['Activo', 'Inactivo'];
    $estado = $_POST['estado'] ?? '';
    if (!in_array($estado, $estados_validos, true)) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Estado no válido.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    
    $id_rol_raw = $_POST['id_rol'] ?? null;
    $id_rol = filter_var($id_rol_raw, FILTER_VALIDATE_INT);
    if ($id_rol === false || $id_rol === null || $id_rol <= 0) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Rol no válido o no fue enviado correctamente.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if ($_SESSION['id_rol'] != 1 && $id_rol == 1) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No tienes permisos para asignar el rol de Administrador.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if (empty($nombres) || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $nombres)) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'El campo Nombres contiene caracteres no válidos.'];
        header("Location: ../vistas/personal.php");
        exit;
    }
    if (empty($apellidos) || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $apellidos)) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'El campo Apellidos contiene caracteres no válidos.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if (!preg_match('/^\d{6,12}$/', $cedula)) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'La cédula debe contener solo números (6 a 12 dígitos).'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if (!preg_match('/^[\d\s\-\+\(\)]{7,15}$/', $telefono)) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'El teléfono tiene un formato incorrecto.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if (empty($usuario) || !preg_match('/^[a-zA-Z0-9_\.]{4,30}$/', $usuario)) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'El usuario solo permite letras, números, puntos y guiones bajos (4-30 caracteres).'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if (!$id_cargo || $id_cargo <= 0) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Debes seleccionar un cargo válido.'];
        header("Location: ../vistas/personal.php");
        exit;
    }

    if ($id_usuario == $_SESSION['id_usuario']) {
        if ($estado == 'Inactivo') {
            $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No puedes desactivar tu propia cuenta.'];
            header("Location: ../vistas/personal.php");
            exit;
        }
        if ($id_rol != $_SESSION['id_rol']) {
            $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No puedes cambiar tu propio rol.'];
            header("Location: ../vistas/personal.php");
            exit;
        }
    }

    try {
        $conexion->beginTransaction();

        $stmt_u = $conexion->prepare("UPDATE usuarios SET nombre_usuario = ?, estado = ?, id_rol = ? WHERE id_usuario = ?");
        $stmt_u->execute([$usuario, $estado, $id_rol, $id_usuario]);

        // 2. Procesamiento de foto de perfil
        $foto_actualizada = false;
        $nombre_foto = "";
        
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {

            $tamano_absoluto = 10 * 1024 * 1024;
            if ($_FILES['foto_perfil']['size'] > $tamano_absoluto) {
                $conexion->rollBack();
                $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'La imagen es demasiado grande. Máximo permitido: 10 MB.'];
                header("Location: ../vistas/personal.php");
                exit;
            }

            $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo     = finfo_open(FILEINFO_MIME_TYPE);
            $tipo_real = finfo_file($finfo, $_FILES['foto_perfil']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($tipo_real, $tipos_permitidos, true)) {
                $conexion->rollBack();
                $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
                header("Location: ../vistas/personal.php");
                exit;
            }

            $nombre_foto  = "perfil_" . $id_personal . "_" . time() . ".jpg";
            $ruta_destino = "../recursos/img/perfiles/" . $nombre_foto;

            $tamano_objetivo = 2 * 1024 * 1024; // 2 MB
            $tmp             = $_FILES['foto_perfil']['tmp_name'];

            $imagen_gd = match($tipo_real) {
                'image/jpeg' => imagecreatefromjpeg($tmp),
                'image/png'  => imagecreatefrompng($tmp),
                'image/webp' => imagecreatefromwebp($tmp),
                'image/gif'  => imagecreatefromgif($tmp),
                default      => false
            };

            if (!$imagen_gd) {
                $conexion->rollBack();
                $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'No se pudo procesar la imagen. Intenta con otro archivo.'];
                header("Location: ../vistas/personal.php");
                exit;
            }

            $ancho_original = imagesx($imagen_gd);
            $alto_original  = imagesy($imagen_gd);
            $max_px         = 800;

            if ($ancho_original > $max_px || $alto_original > $max_px) {
                $ratio = min($max_px / $ancho_original, $max_px / $alto_original);
                $nuevo_ancho = (int)($ancho_original * $ratio);
                $nuevo_alto  = (int)($alto_original  * $ratio);

                $imagen_redim = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);

                imagealphablending($imagen_redim, false);
                imagesavealpha($imagen_redim, true);
                $transparente = imagecolorallocatealpha($imagen_redim, 0, 0, 0, 127);
                imagefill($imagen_redim, 0, 0, $transparente);

                imagecopyresampled($imagen_redim, $imagen_gd, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_original, $alto_original);
                imagedestroy($imagen_gd);
                $imagen_gd = $imagen_redim;
            }

            $calidad    = 85;
            $calidad_min = 30;
            $guardado   = false;

            while ($calidad >= $calidad_min) {
                ob_start();
                imagejpeg($imagen_gd, null, $calidad);
                $buffer = ob_get_clean();

                if (strlen($buffer) <= $tamano_objetivo || $calidad === $calidad_min) {
                    if (file_put_contents($ruta_destino, $buffer) !== false) {
                        $guardado = true;
                    }
                    break;
                }

                $calidad -= 5;
            }

            imagedestroy($imagen_gd);

            if ($guardado) {
                $foto_actualizada = true;
            }
        }

        if ($foto_actualizada) {
            $stmt_p = $conexion->prepare("UPDATE personal SET nombres = ?, apellidos = ?, cedula = ?, telefono = ?, id_cargo = ?, foto_perfil = ? WHERE id_personal = ?");
            $stmt_p->execute([$nombres, $apellidos, $cedula, $telefono, $id_cargo, $nombre_foto, $id_personal]);
        } else {
            $stmt_p = $conexion->prepare("UPDATE personal SET nombres = ?, apellidos = ?, cedula = ?, telefono = ?, id_cargo = ? WHERE id_personal = ?");
            $stmt_p->execute([$nombres, $apellidos, $cedula, $telefono, $id_cargo, $id_personal]);
        }
        
        $stmt_rol = $conexion->prepare("SELECT nombre_rol FROM roles WHERE id_rol = ?");
        $stmt_rol->execute([$id_rol]);
        $nombre_rol = $stmt_rol->fetchColumn() ?: "ID Rol: $id_rol";

        ControladorBitacora::registrar(
            $conexion,
            $_SESSION['id_usuario'],
            'Usuarios',
            'Edición Completa de Perfil',
            "Actualizó los datos de '$nombres $apellidos'. Cédula: $cedula, Estado: $estado, Rol: $nombre_rol."
        );

        $mensaje_perfil = "Administración ha actualizado tus datos personales y/o de acceso. Revisa tu perfil.";
        ControladorNotificaciones::crear($conexion, $id_usuario, $mensaje_perfil, 'Informativa');

        $conexion->commit();
        $_SESSION['alerta_personal'] = ['tipo' => 'success', 'mensaje' => 'Todos los datos del empleado han sido actualizados.'];

    } catch (PDOException $e) {
        $conexion->rollBack();
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Error al actualizar al empleado. Verifica que el nombre de usuario no esté en uso.'];
    }
    
    header("Location: ../vistas/personal.php");
    exit;
}
?>