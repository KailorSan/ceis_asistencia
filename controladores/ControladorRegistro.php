<?php
session_start();
require_once '../configuracion/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recibir datos con seguridad (si no existen, asignamos un string vacío)
    $cedula = trim($_POST['cedula'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_cargo = $_POST['id_cargo'] ?? '';

    // 2. Recibir datos de usuario
    $nuevo_usuario = trim($_POST['nuevo_usuario'] ?? '');
    $nueva_password = $_POST['nueva_password'] ?? '';

    // 3. Recibir preguntas
    $pregunta_1 = $_POST['pregunta_1'] ?? '';
    $respuesta_1 = trim($_POST['respuesta_1'] ?? '');
    $pregunta_2 = $_POST['pregunta_2'] ?? '';
    $respuesta_2 = trim($_POST['respuesta_2'] ?? '');
    $pregunta_3 = $_POST['pregunta_3'] ?? '';
    $respuesta_3 = trim($_POST['respuesta_3'] ?? '');

    // --- EL ESCUDO BACKEND: Validar que lo esencial no esté vacío ---
    if (empty($cedula) || empty($nombres) || empty($apellidos) || empty($id_cargo) || 
        empty($nuevo_usuario) || empty($nueva_password) || 
        empty($respuesta_1) || empty($respuesta_2) || empty($respuesta_3)) {
        
        $_SESSION['error_login'] = "Error de seguridad: Faltan datos obligatorios. Inténtalo de nuevo.";
        header("Location: ../vistas/login.php");
        exit;
    }
    
    // Validar longitud mínima de la contraseña en el backend también
    if (strlen($nueva_password) < 6) {
        $_SESSION['error_login'] = "La contraseña debe tener al menos 6 caracteres.";
        header("Location: ../vistas/login.php");
        exit;
    }

    // --- Lógica para subir Foto de Perfil ---
    $nombre_foto = 'default.png'; // Valor por defecto si no suben nada
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png'];
        
        if (in_array($ext, $permitidas)) {
            // Renombramos la foto para evitar nombres duplicados
            $nombre_foto = 'perfil_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $ruta_destino = '../recursos/img/perfiles/' . $nombre_foto;
            
            // Movemos la foto de la memoria temporal a nuestra carpeta
            move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $ruta_destino);
        }
    }
    // ------------------------------------------------

    try {
        $conexion->beginTransaction();

        $sql_check = "SELECT id_rol FROM roles WHERE nombre_rol = 'Director'";
        $stmt_check = $conexion->query($sql_check);
        $rol_director_id = $stmt_check->fetchColumn();

        $sql_usuarios = "SELECT COUNT(*) FROM usuarios WHERE id_rol = :id_rol";
        $stmt_usuarios = $prepare = $conexion->prepare($sql_usuarios);
        $stmt_usuarios->execute([':id_rol' => $rol_director_id]);
        $existe_director = ($stmt_usuarios->fetchColumn() > 0);

        if (!$existe_director && $id_cargo == 1) { 
            $id_rol = $rol_director_id; 
        } else {
            $sql_rol = "SELECT id_rol FROM roles WHERE nombre_rol = 'Personal'";
            $stmt_rol = $conexion->query($sql_rol);
            $id_rol = $stmt_rol->fetchColumn();
        }

        // Encriptar la contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

        // --- SOLUCIÓN: Encriptar las respuestas de seguridad antes de guardarlas ---
        $hash_r1 = password_hash(strtolower($respuesta_1), PASSWORD_DEFAULT);
        $hash_r2 = password_hash(strtolower($respuesta_2), PASSWORD_DEFAULT);
        $hash_r3 = password_hash(strtolower($respuesta_3), PASSWORD_DEFAULT);

        // Insertar Usuario con las respuestas encriptadas
        $sql_insert_usuario = "INSERT INTO usuarios (nombre_usuario, password, id_rol, pregunta_1, respuesta_1, pregunta_2, respuesta_2, pregunta_3, respuesta_3) 
                               VALUES (:usuario, :pass, :rol, :p1, :r1, :p2, :r2, :p3, :r3)";
        $stmt_insert_usuario = $conexion->prepare($sql_insert_usuario);
        $stmt_insert_usuario->execute([
            ':usuario' => $nuevo_usuario,
            ':pass' => $password_hash,
            ':rol' => $id_rol,
            ':p1' => $pregunta_1, ':r1' => $hash_r1,
            ':p2' => $pregunta_2, ':r2' => $hash_r2,
            ':p3' => $pregunta_3, ':r3' => $hash_r3
        ]);

        $id_usuario_nuevo = $conexion->lastInsertId();

        // --- CORRECCIÓN MAGISTRAL: INYECTAR LA FECHA DE INGRESO EXACTA ---
        $sql_insert_personal = "INSERT INTO personal (cedula, nombres, apellidos, telefono, id_cargo, id_usuario, foto_perfil, fecha_ingreso) 
                                VALUES (:ced, :nom, :ape, :tel, :cargo, :user, :foto, CURDATE())";
        $stmt_insert_personal = $conexion->prepare($sql_insert_personal);
        $stmt_insert_personal->execute([
            ':ced' => $cedula,
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':tel' => $telefono,
            ':cargo' => $id_cargo,
            ':user' => $id_usuario_nuevo,
            ':foto' => $nombre_foto
        ]);

        $conexion->commit();
        $_SESSION['registro_exito'] = true;
        header("Location: ../vistas/login.php");
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        $_SESSION['error_login'] = "Error al registrar: " . $e->getMessage();
        header("Location: ../vistas/login.php");
        exit;
    }
} else {
    header("Location: ../vistas/login.php");
    exit;
}
?>