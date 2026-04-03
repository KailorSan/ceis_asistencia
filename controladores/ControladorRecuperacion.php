<?php
session_start();
require_once '../configuracion/conexion.php';

$diccionario_preguntas = [
    1 => "¿Nombre de tu primera mascota?",
    2 => "¿Ciudad de nacimiento de tu padre?",
    3 => "¿Mejor amigo de la infancia?",
    4 => "¿Plato de comida favorito?",
    5 => "¿Marca de tu primer vehículo?",
    6 => "¿Nombre de tu escuela primaria?",
    7 => "¿Personaje histórico favorito?",
    8 => "¿Apellido de soltera de tu madre?"
];

if (isset($_POST['accion']) && $_POST['accion'] == 'buscar_usuario') {
    $usuario = trim($_POST['nombre_usuario']);

    try {
        $sql = "SELECT id_usuario, pregunta_1, pregunta_2, pregunta_3, 
                       respuesta_1, respuesta_2, respuesta_3 
                FROM usuarios WHERE nombre_usuario = :u AND estado = 'Activo'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':u' => $usuario]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($datos) {
            $_SESSION['recup_temp'] = [
                'id_usuario' => $datos['id_usuario'],
                'preguntas_texto' => [
                    1 => $diccionario_preguntas[$datos['pregunta_1']],
                    2 => $diccionario_preguntas[$datos['pregunta_2']],
                    3 => $diccionario_preguntas[$datos['pregunta_3']]
                ],
                'hashes' => [
                    1 => $datos['respuesta_1'],
                    2 => $datos['respuesta_2'],
                    3 => $datos['respuesta_3']
                ]
            ];
            $_SESSION['paso_recuperacion'] = 2;
            header("Location: ../vistas/recuperar_contraseña.php");
        } else {
            $_SESSION['error_recup'] = "El usuario no existe o está inactivo.";
            header("Location: ../vistas/recuperar_contraseña.php");
        }
    } catch (PDOException $e) {
        $_SESSION['error_recup'] = "Error de BD: " . $e->getMessage();
        header("Location: ../vistas/recuperar_contraseña.php");
    }
}

if (isset($_POST['accion']) && $_POST['accion'] == 'verificar_respuestas') {
    
    $r1 = mb_strtolower(trim($_POST['resp_1']), 'UTF-8');
    $r2 = mb_strtolower(trim($_POST['resp_2']), 'UTF-8');
    $r3 = mb_strtolower(trim($_POST['resp_3']), 'UTF-8');

    $hashes = $_SESSION['recup_temp']['hashes'];

    if (password_verify($r1, $hashes[1]) && 
        password_verify($r2, $hashes[2]) && 
        password_verify($r3, $hashes[3])) {
        
        $_SESSION['paso_recuperacion'] = 3; 
        header("Location: ../vistas/recuperar_contraseña.php");
    } else {
        $_SESSION['error_recup'] = "Las respuestas no coinciden con nuestros registros.";
        header("Location: ../vistas/recuperar_contraseña.php");
    }
}

if (isset($_POST['accion']) && $_POST['accion'] == 'cambiar_clave') {
    
    $p1 = $_POST['pass_1'];
    $p2 = $_POST['pass_2'];

    if ($p1 !== $p2) {
        $_SESSION['error_recup'] = "Las contraseñas no coinciden.";
        header("Location: ../vistas/recuperar_password.php");
        exit;
    }

    $nuevo_hash = password_hash($p1, PASSWORD_DEFAULT);
    $id_user = $_SESSION['recup_temp']['id_usuario'];

    try {
        $stmt = $conexion->prepare("UPDATE usuarios SET password = :p WHERE id_usuario = :id");
        $stmt->execute([':p' => $nuevo_hash, ':id' => $id_user]);

        session_destroy();
        session_start();
        $_SESSION['registro_exito'] = "Contraseña restablecida exitosamente.";
        header("Location: ../vistas/login.php");

    } catch (PDOException $e) {
        $_SESSION['error_recup'] = "Error al actualizar: " . $e->getMessage();
        header("Location: ../vistas/recuperar_password.php");
    }
}
?>