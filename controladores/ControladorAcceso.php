<?php
session_start();
require_once '../configuracion/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibir datos con seguridad (si no existen, asignamos un string vacío '')
    $usuario = trim($_POST['nombre_usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 2. EL ESCUDO BACKEND: Validar que no estén vacíos
    if (empty($usuario) || empty($password)) {
        $_SESSION['error_login'] = "Por favor, ingresa tu usuario y contraseña.";
        header("Location: ../vistas/login.php");
        exit;
    }

    try {
        // Buscamos al usuario y su rol en la base de datos
        $sql = "SELECT u.id_usuario, u.password, u.estado, u.id_rol, r.nombre_rol 
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.nombre_usuario = :usuario";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            
            // Verificamos si el usuario fue desactivado por el Director
            if ($resultado['estado'] !== 'Activo') {
                $_SESSION['error_login'] = "Este usuario está desactivado. Contacte a Dirección.";
                header("Location: ../vistas/login.php");
                exit;
            }

            // Verificamos que la contraseña coincida con el hash de la base de datos
            if (password_verify($password, $resultado['password'])) {
                
                // Regenerar el ID de sesión para prevenir ataques de Session Fixation (¡Excelente práctica que ya tenías!)
                session_regenerate_id(true);

                // Guardamos los datos en la sesión
                $_SESSION['id_usuario'] = $resultado['id_usuario'];
                $_SESSION['usuario'] = $usuario;
                $_SESSION['id_rol'] = $resultado['id_rol'];
                $_SESSION['rol'] = $resultado['nombre_rol'];
                $_SESSION['logueado'] = true;

                header("Location: ../vistas/principal.php"); 
                exit;

            } else {
                $_SESSION['error_login'] = "La contraseña es incorrecta.";
                header("Location: ../vistas/login.php");
                exit;
            }

        } else {
            $_SESSION['error_login'] = "El usuario no existe.";
            header("Location: ../vistas/login.php");
            exit;
        }

    } catch (PDOException $e) {
        // En caso de que se caiga la base de datos
        $_SESSION['error_login'] = "Error del sistema: " . $e->getMessage();
        header("Location: ../vistas/login.php");
        exit;
    }
} else {
    header("Location: ../vistas/login.php");
    exit;
}
?>