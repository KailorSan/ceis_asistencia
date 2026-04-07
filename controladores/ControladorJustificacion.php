<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

// 1. CONFIRMAMOS LA ZONA HORARIA A VENEZUELA
date_default_timezone_set('America/Caracas');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_personal = $_POST['id_personal'];
    $fecha = $_POST['fecha_justificacion'];
    
    // === BLOQUEO FIN DE SEMANA ===
    // date('N') nos da el día de la semana de la fecha enviada (6 = Sábado, 7 = Domingo)
    if (date('N', strtotime($fecha)) >= 6) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Operación denegada. No puedes registrar justificaciones para días de fin de semana.'];
        header("Location: ../vistas/principal.php");
        exit;
    }

    // RECIBIMOS LOS CAMPOS
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
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        $mimes_validos = [
            'image/jpeg', 
            'image/png', 
            'application/pdf'
        ];

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
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Archivo no seguro o formato inválido. Solo PDF, JPG o PNG reales.'];
            header("Location: ../vistas/principal.php");
            exit;
        }
    }
    // --- FIN LÓGICA DE ARCHIVO ---

    try {
        $check = $conexion->prepare("SELECT * FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $check->execute([$id_personal, $fecha]);
        $registro = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            // SI YA EXISTE EL REGISTRO (Ej: Ya marcó entrada en la mañana y ahora pide irse temprano)
            // SOLO actualizamos la excusa, NO TOCAMOS LAS HORAS (Por eso el código es más corto).
            $q = "UPDATE asistencias SET motivo_justificacion = ?, estado_justificacion = 'Pendiente'";
            $params = [$motivo_completo];

            if ($nombre_archivo_final) {
                $q .= ", archivo_evidencia = ?";
                $params[] = $nombre_archivo_final;
            }

            // Si la excusa es porque faltó todo el día, le cambiamos el estado directamente.
            if ($tipo == 'Inasistencia') {
                $q .= ", estado = 'Justificado (Pendiente)'";
            }

            $q .= " WHERE id_personal = ? AND fecha = ?";
            $params[] = $id_personal;
            $params[] = $fecha;

            $update = $conexion->prepare($q);
            $update->execute($params);
            
        } else {
            // SI NO EXISTE EL REGISTRO (Ej: Es temprano en la mañana y está justificando su retraso)
            $stmt_emp = $conexion->prepare("SELECT hora_entrada_personalizada FROM personal WHERE id_personal = ?");
            $stmt_emp->execute([$id_personal]);
            $emp = $stmt_emp->fetch(PDO::FETCH_ASSOC);
            
            $stmt_conf = $conexion->query("SELECT hora_entrada_general FROM configuracion WHERE id_config = 1");
            $conf = $stmt_conf->fetch(PDO::FETCH_ASSOC);
            
            $hora_esperada = !empty($emp['hora_entrada_personalizada']) ? $emp['hora_entrada_personalizada'] : ($conf['hora_entrada_general'] ?? '07:00:00');

            $estado_val = ($tipo == 'Inasistencia') ? 'Justificado (Pendiente)' : 'Pendiente';

            // Insertamos la fila con la excusa, pero dejamos las horas físicas en blanco para que el botón las marque después.
            $insert = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, estado, motivo_justificacion, estado_justificacion, archivo_evidencia) VALUES (?, ?, ?, ?, ?, 'Pendiente', ?)");
            $insert->execute([$id_personal, $fecha, $hora_esperada, $estado_val, $motivo_completo, $nombre_archivo_final]);
        }

        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Justificación enviada. Ahora debes registrar tu hora física en el panel.'];
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