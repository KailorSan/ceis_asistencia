<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_personal = $_POST['id_personal'];
    $fecha = $_POST['fecha_justificacion'];
    
    // RECIBIMOS EL NUEVO CAMPO
    $tipo = isset($_POST['tipo_incidencia']) ? $_POST['tipo_incidencia'] : 'Otro';
    $motivo_texto = trim($_POST['motivo']);
    
    // UNIMOS EL TIPO CON EL MOTIVO PARA QUE DIRECCIÓN LO VEA CLARITO
    $motivo_completo = "[" . $tipo . "] - " . $motivo_texto;
    
    // --- LÓGICA DE SUBIDA DE ARCHIVO BLINDADA ---
    $nombre_archivo_final = null; 
    
    if (isset($_FILES['archivo_evidencia']) && $_FILES['archivo_evidencia']['error'] == 0) {
        $directorio_destino = '../recursos/evidencias/';
        
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $archivo = $_FILES['archivo_evidencia'];
        
        // 1. Validamos la extensión del nombre (como ya hacías)
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf'];

        // 2. NUEVO: Validamos el MIME Type real leyendo el contenido del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        $mimes_validos = [
            'image/jpeg', 
            'image/png', 
            'application/pdf'
        ];

        // Comprobamos que TANTO la extensión COMO el contenido interno sean válidos
        if (in_array($extension, $extensiones_validas) && in_array($mime_type, $mimes_validos)) {
            if ($archivo['size'] <= 5000000) {
                $nombre_archivo_final = $id_personal . '_' . str_replace('-', '', $fecha) . '_' . time() . '.' . $extension;
                $ruta_final = $directorio_destino . $nombre_archivo_final;
                move_uploaded_file($archivo['tmp_name'], $ruta_final);
            } else {
                $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'El archivo es muy pesado. Máximo 5MB.'];
                header("Location: ../vistas/principal.php");
                exit;
            }
        } else {
            // Mensaje de alerta si intentan subir un archivo camuflado
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Archivo no seguro o formato inválido. Solo PDF, JPG o PNG reales.'];
            header("Location: ../vistas/principal.php");
            exit;
        }
    }
    // --- FIN LÓGICA DE ARCHIVO ---

    try {
        $check = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $check->execute([$id_personal, $fecha]);
        
        if ($check->rowCount() > 0) {
            // USAMOS $motivo_completo AQUÍ
            $update = $conexion->prepare("UPDATE asistencias SET motivo_justificacion = ?, estado_justificacion = 'Pendiente', archivo_evidencia = ? WHERE id_personal = ? AND fecha = ?");
            $update->execute([$motivo_completo, $nombre_archivo_final, $id_personal, $fecha]);
        } else {
            // USAMOS $motivo_completo AQUÍ TAMBIÉN
            $insert = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, estado, motivo_justificacion, estado_justificacion, archivo_evidencia) VALUES (?, ?, 'Falta', ?, 'Pendiente', ?)");
            $insert->execute([$id_personal, $fecha, $motivo_completo, $nombre_archivo_final]);
        }

        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Tu justificación y evidencia han sido enviadas.'];
    } catch (PDOException $e) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error al procesar tu justificación en la base de datos.'];
    }

    header("Location: ../vistas/principal.php");
    exit;
} else {
    header("Location: ../vistas/principal.php");
    exit;
}
?>