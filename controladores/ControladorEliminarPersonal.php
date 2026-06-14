<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php';

header('Content-Type: application/json');

// Validación de seguridad y rol (Solo Director y Subdirector)
if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    echo json_encode(['success' => false, 'msg' => 'Acceso denegado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- 🛡️ 1. PROTECCIÓN ANTI-FUERZA BRUTA ---
    if (!isset($_SESSION['intentos_eliminar'])) {
        $_SESSION['intentos_eliminar'] = 0;
        $_SESSION['ultimo_intento_eliminar'] = time();
    }
    
    if ($_SESSION['intentos_eliminar'] >= 5) {
        $tiempo_transcurrido = time() - $_SESSION['ultimo_intento_eliminar'];
        if ($tiempo_transcurrido < 180) { // 180 segundos = 3 minutos
            echo json_encode(['success' => false, 'msg' => 'Sistema bloqueado. Espera 3 minutos antes de volver a intentar.']);
            exit;
        } else {
            $_SESSION['intentos_eliminar'] = 0; // Reiniciamos si ya pasó el castigo
        }
    }

    $id_a_eliminar = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $password_proporcionada = $_POST['password'] ?? '';
    $id_admin_actual = $_SESSION['id_usuario'];
    $rol_admin_actual = $_SESSION['id_rol'];

    if (!$id_a_eliminar || empty($password_proporcionada)) {
        echo json_encode(['success' => false, 'msg' => 'Faltan datos obligatorios.']);
        exit;
    }

    // Protección anti-suicidio
    if ($id_a_eliminar == $id_admin_actual) {
        echo json_encode(['success' => false, 'msg' => 'No puedes eliminar tu propia cuenta desde aquí.']);
        exit;
    }

    try {
        // Obtener datos de la persona a eliminar (Rol y Nombre)
        $stmt_info = $conexion->prepare("SELECT id_rol, nombre_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt_info->execute([$id_a_eliminar]);
        $target_data = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if (!$target_data) {
            echo json_encode(['success' => false, 'msg' => 'El usuario no existe en la base de datos.']);
            exit;
        }

        $rol_target = $target_data['id_rol'];
        $nombre_eliminado = $target_data['nombre_usuario'];

        // --- 🛡️ 2. PROTECCIÓN DE JERARQUÍA (ESCALAMIENTO DE PRIVILEGIOS) ---
        if ($rol_admin_actual == 2 && $rol_target == 1) {
            // Castigamos el atrevimiento sumando un intento fallido
            $_SESSION['intentos_eliminar']++;
            $_SESSION['ultimo_intento_eliminar'] = time();
            
            // Registramos el intento de sabotaje en la bitácora
            ControladorBitacora::registrar($conexion, $id_admin_actual, 'Seguridad', 'Intento de Escalamiento', "Un Subdirector intentó eliminar la cuenta del Director ($nombre_eliminado).");
            
            echo json_encode(['success' => false, 'msg' => 'Privilegios insuficientes. Un Subdirector no puede eliminar a un Director bajo ninguna circunstancia.']);
            exit;
        }

        // --- 🛡️ 3. VERIFICACIÓN DINÁMICA DE CONTRASEÑA ---
        if ($rol_target == 1) {
            // Si el objetivo es otro DIRECTOR, verificamos la contraseña de EL OBJETIVO
            $stmt_pass = $conexion->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
            $stmt_pass->execute([$id_a_eliminar]);
            $hash_db = $stmt_pass->fetchColumn();

            if (!password_verify($password_proporcionada, $hash_db)) {
                $_SESSION['intentos_eliminar']++;
                $_SESSION['ultimo_intento_eliminar'] = time();
                echo json_encode(['success' => false, 'msg' => 'Contraseña incorrecta. Para eliminar a otro Administrador necesitas SU contraseña.']);
                exit;
            }
        } else {
            // Si el objetivo NO es Director, verificamos la contraseña de TU CUENTA
            $stmt_pass = $conexion->prepare("SELECT password FROM usuarios WHERE id_usuario = ?");
            $stmt_pass->execute([$id_admin_actual]);
            $hash_db = $stmt_pass->fetchColumn();

            if (!password_verify($password_proporcionada, $hash_db)) {
                $_SESSION['intentos_eliminar']++;
                $_SESSION['ultimo_intento_eliminar'] = time();
                echo json_encode(['success' => false, 'msg' => 'Tu contraseña de administrador es incorrecta.']);
                exit;
            }
        }

        // Si llegamos aquí, la contraseña es correcta y los roles son válidos
        // Limpiamos los intentos fallidos
        unset($_SESSION['intentos_eliminar']);
        unset($_SESSION['ultimo_intento_eliminar']);

        // Borramos la foto de perfil del servidor
        $stmt_foto = $conexion->prepare("SELECT foto_perfil FROM personal WHERE id_usuario = ?");
        $stmt_foto->execute([$id_a_eliminar]);
        $foto = $stmt_foto->fetchColumn();

        if ($foto && $foto != 'default.png') {
            $ruta_foto = "../recursos/img/perfiles/" . $foto;
            if (file_exists($ruta_foto)) {
                unlink($ruta_foto); 
            }
        }

        // Borramos al usuario (ON DELETE CASCADE hará el resto)
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_a_eliminar]);
        
        // Registramos en bitácora
        ControladorBitacora::registrar(
            $conexion, 
            $id_admin_actual, 
            'Usuarios', 
            'Eliminación de Personal', 
            "Eliminó permanentemente al usuario: '$nombre_eliminado' tras validación de seguridad."
        );

        echo json_encode(['success' => true, 'msg' => 'El usuario ha sido eliminado definitivamente.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'msg' => 'Error crítico en la base de datos al intentar eliminar.']);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Método de petición no permitido.']);
}
?>