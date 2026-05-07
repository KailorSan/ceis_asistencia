<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario       = $_SESSION['id_usuario'];
    $nuevo_usuario    = strtolower(trim($_POST['nombre_usuario']));
    $nuevo_telefono   = trim($_POST['telefono']);
    $nuevos_nombres   = trim($_POST['nombres']);
    $nuevos_apellidos = trim($_POST['apellidos']);
    $nueva_cedula     = trim($_POST['cedula']);
    $nueva_password   = $_POST['nueva_password']   ?? '';
    $confirmar_pass   = $_POST['confirmar_password'] ?? '';
    $password_actual  = $_POST['password_actual'];

    // ── Validaciones básicas ──────────────────────────────────────────────────
    if (empty($nuevo_usuario) || empty($nuevo_telefono) || empty($nuevos_nombres) || empty($nuevos_apellidos) || empty($nueva_cedula)) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Todos los campos personales son obligatorios.'];
        header("Location: ../vistas/perfil.php");
        exit;
    }

    if (strlen($nueva_cedula) < 6) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'La cédula debe tener al menos 6 dígitos.'];
        header("Location: ../vistas/perfil.php");
        exit;
    }

    if (strlen($nuevo_telefono) < 11) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'El teléfono debe tener 11 dígitos.'];
        header("Location: ../vistas/perfil.php");
        exit;
    }

    // ── Validar contraseña nueva (solo si la llenó) ───────────────────────────
    $cambiar_password = false;
    if ($nueva_password !== '') {
        if (strlen($nueva_password) < 6 || !preg_match('/[A-Za-z]/', $nueva_password) || !preg_match('/[0-9]/', $nueva_password)) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'La nueva contraseña debe tener al menos 6 caracteres e incluir letras y números.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }
        if ($nueva_password !== $confirmar_pass) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Las contraseñas nuevas no coinciden.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }
        $cambiar_password = true;
    }

    try {
        // ── 1. Verificar contraseña actual ────────────────────────────────────
        $stmt_verificar = $conexion->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
        $stmt_verificar->execute([$id_usuario]);
        $hash_db = $stmt_verificar->fetchColumn();

        if (!password_verify($password_actual, $hash_db)) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Contraseña incorrecta. No se guardaron los cambios.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }

        // ── 2. Verificar que el nombre de usuario no exista en otra cuenta ────
        $stmt_usuario = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
        $stmt_usuario->execute([$nuevo_usuario, $id_usuario]);
        if ($stmt_usuario->fetchColumn() > 0) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Ese nombre de usuario ya está en uso.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }

        // ── 3. Verificar que la cédula no exista en otro registro ─────────────
        $stmt_cedula = $conexion->prepare("SELECT COUNT(*) FROM personal WHERE cedula = ? AND id_usuario != ?");
        $stmt_cedula->execute([$nueva_cedula, $id_usuario]);
        if ($stmt_cedula->fetchColumn() > 0) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Esa cédula ya está registrada en el sistema.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }

        $conexion->beginTransaction();

        // ── 4. Actualizar tabla USUARIOS ──────────────────────────────────────
        $sql_update_user = "UPDATE usuarios SET nombre_usuario = :usr";
        $params_user = [':usr' => $nuevo_usuario, ':id' => $id_usuario];

        if ($cambiar_password) {
            $sql_update_user .= ", password = :nuevapass";
            $params_user[':nuevapass'] = password_hash($nueva_password, PASSWORD_DEFAULT);
        }

        for ($i = 1; $i <= 3; $i++) {
            if (!empty(trim($_POST['respuesta_'.$i]))) {
                $sql_update_user .= ", pregunta_$i = :p$i, respuesta_$i = :r$i";
                $params_user[":p$i"] = $_POST['pregunta_'.$i];
                $params_user[":r$i"] = password_hash(strtolower(trim($_POST['respuesta_'.$i])), PASSWORD_DEFAULT);
            }
        }

        $sql_update_user .= " WHERE id_usuario = :id";
        $stmt_update_user = $conexion->prepare($sql_update_user);
        $stmt_update_user->execute($params_user);

        // ── 5. Procesar foto de perfil (si subió una) ─────────────────────────
        $sql_foto = "";
        $params_personal = [
            ':tel'       => $nuevo_telefono,
            ':nombres'   => $nuevos_nombres,
            ':apellidos' => $nuevos_apellidos,
            ':cedula'    => $nueva_cedula,
            ':id_user'   => $id_usuario
        ];

        if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] == UPLOAD_ERR_OK) {
            $ext        = strtolower(pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png'];
            if (in_array($ext, $permitidas)) {
                $nombre_foto              = 'perfil_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $ruta_destino             = '../recursos/img/perfiles/' . $nombre_foto;
                move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $ruta_destino);
                $sql_foto                  = ", foto_perfil = :foto";
                $params_personal[':foto']  = $nombre_foto;
            }
        }

        // ── 6. Actualizar tabla PERSONAL ──────────────────────────────────────
        $stmt_update_personal = $conexion->prepare(
            "UPDATE personal
             SET telefono = :tel, nombres = :nombres, apellidos = :apellidos, cedula = :cedula $sql_foto
             WHERE id_usuario = :id_user"
        );
        $stmt_update_personal->execute($params_personal);

        $conexion->commit();

        // Actualizar sesión
        $_SESSION['usuario'] = $nuevo_usuario;

        $msg = 'Tu perfil ha sido actualizado correctamente.';
        if ($cambiar_password) {
            $msg = 'Perfil actualizado. Tu contraseña ha sido cambiada.';
        }

        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => $msg];
        header("Location: ../vistas/perfil.php");
        exit;

    } catch (PDOException $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error interno al guardar los datos. Intenta de nuevo.'];
        header("Location: ../vistas/perfil.php");
        exit;
    }

} else {
    header("Location: ../vistas/perfil.php");
    exit;
}
?>