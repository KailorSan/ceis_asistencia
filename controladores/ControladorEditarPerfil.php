<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $nuevo_usuario = trim($_POST['nombre_usuario']);
    $nuevo_telefono = trim($_POST['telefono']);
    $password_actual = $_POST['password_actual'];

    try {
        // 1. VERIFICAR LA CONTRASEÑA ACTUAL
        $stmt_verificar = $conexion->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
        $stmt_verificar->execute([$id_usuario]);
        $hash_db = $stmt_verificar->fetchColumn();

        if (!password_verify($password_actual, $hash_db)) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Contraseña incorrecta. No se guardaron los cambios.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }

        // 2. VERIFICAR QUE EL NOMBRE DE USUARIO NO EXISTA EN OTRA CUENTA
        $stmt_usuario = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = ? AND id_usuario != ?");
        $stmt_usuario->execute([$nuevo_usuario, $id_usuario]);
        if ($stmt_usuario->fetchColumn() > 0) {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Ese nombre de usuario ya está en uso.'];
            header("Location: ../vistas/perfil.php");
            exit;
        }

        $conexion->beginTransaction();

        // 3. ACTUALIZAR TABLA USUARIOS (Nombre y Preguntas si las llenó)
        $sql_update_user = "UPDATE usuarios SET nombre_usuario = :usr";
        $params_user = [':usr' => $nuevo_usuario, ':id' => $id_usuario];

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

        // 4. ACTUALIZAR FOTO DE PERFIL (SI SUBIÓ UNA)
        $sql_foto = "";
        $params_personal = [':tel' => $nuevo_telefono, ':id_user' => $id_usuario];

        if (isset($_FILES['nueva_foto']) && $_FILES['nueva_foto']['error'] == UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['nueva_foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png'];
            if (in_array($ext, $permitidas)) {
                $nombre_foto = 'perfil_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $ruta_destino = '../recursos/img/perfiles/' . $nombre_foto;
                move_uploaded_file($_FILES['nueva_foto']['tmp_name'], $ruta_destino);
                
                $sql_foto = ", foto_perfil = :foto";
                $params_personal[':foto'] = $nombre_foto;
            }
        }

        // 5. ACTUALIZAR TABLA PERSONAL
        $stmt_update_personal = $conexion->prepare("UPDATE personal SET telefono = :tel $sql_foto WHERE id_usuario = :id_user");
        $stmt_update_personal->execute($params_personal);

        $conexion->commit();

        // Actualizar la variable de sesión para que el nombre cambie arriba a la derecha
        $_SESSION['usuario'] = $nuevo_usuario;

        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Tu perfil ha sido actualizado correctamente.'];
        header("Location: ../vistas/perfil.php");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error al guardar los datos.'];
        header("Location: ../vistas/perfil.php");
        exit;
    }
} else {
    header("Location: ../vistas/perfil.php");
    exit;
}
?>