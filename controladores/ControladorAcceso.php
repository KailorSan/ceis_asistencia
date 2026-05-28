<?php
session_start();
require_once '../configuracion/conexion.php';

// Cambiamos el header para que el navegador entienda que respondemos JSON
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- PROTECCIÓN CSRF ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['exito' => false, 'mensaje' => "Error de seguridad (CSRF). Por favor, recargue la página."]);
        exit;
    }
    
    // --- PROTECCIÓN ANTI-FUERZA BRUTA ---
    if (!isset($_SESSION['intentos_login'])) {
        $_SESSION['intentos_login'] = 0;
        $_SESSION['ultimo_intento_login'] = time();
    }
    
    if ($_SESSION['intentos_login'] >= 5) {
        $tiempo_transcurrido = time() - $_SESSION['ultimo_intento_login'];
        if ($tiempo_transcurrido < 180) { // 3 minutos
            echo json_encode(['exito' => false, 'mensaje' => "Sistema bloqueado. Espera 3 minutos antes de intentar de nuevo."]);
            exit;
        } else {
            $_SESSION['intentos_login'] = 0; 
        }
    }
    
    $usuario = strtolower(trim($_POST['nombre_usuario'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        echo json_encode(['exito' => false, 'mensaje' => "Por favor, ingresa tu usuario y contraseña."]);
        exit;
    }

    try {
        $sql = "SELECT u.id_usuario, u.password, u.estado, u.id_rol, r.nombre_rol 
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.nombre_usuario = :usuario";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            
            if ($resultado['estado'] !== 'Activo') {
                echo json_encode(['exito' => false, 'mensaje' => "Este usuario está desactivado. Contacte a Dirección."]);
                exit;
            }

            if (password_verify($password, $resultado['password'])) {
                
                unset($_SESSION['intentos_login']);
                session_regenerate_id(true);

                $_SESSION['id_usuario'] = $resultado['id_usuario'];
                $_SESSION['usuario'] = $usuario;
                $_SESSION['id_rol'] = $resultado['id_rol'];
                $_SESSION['rol'] = $resultado['nombre_rol'];
                $_SESSION['logueado'] = true;

                // Respondemos con ÉXITO, y pasamos el nombre del usuario
                echo json_encode([
                    'exito' => true, 
                    'nombre' => ucfirst($usuario), // Capitalizamos la primera letra
                    'id_usuario' => $resultado['id_usuario'] // Lo pasaremos para consultar el localStorage
                ]);
                exit;

            } else {
                $_SESSION['intentos_login']++;
                $_SESSION['ultimo_intento_login'] = time();
                echo json_encode(['exito' => false, 'mensaje' => "La contraseña es incorrecta."]);
                exit;
            }

        } else {
            $_SESSION['intentos_login']++;
            $_SESSION['ultimo_intento_login'] = time();
            echo json_encode(['exito' => false, 'mensaje' => "El usuario no existe."]);
            exit;
        }

    } catch (PDOException $e) {
        echo json_encode(['exito' => false, 'mensaje' => "Error del sistema: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => "Petición inválida."]);
    exit;
}
?>