<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../configuracion/conexion.php';

// 1. Verificación de Seguridad y Sesión
if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    echo json_encode(['success' => false, 'msg' => 'Acceso denegado. No tienes permisos para esta acción.']);
    exit;
}

// NOTA: Se ha omitido la validación CSRF aquí para no romper el funcionamiento 
// de tu archivo asistencia.js actual.

$id_admin = $_SESSION['id_usuario'];
$ip_usuario = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';

// 2. Recepción y Limpieza de Datos (POST)
$id_personal = $_POST['id_personal'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$estado_principal = trim($_POST['estado'] ?? '');
$estado_secundario = trim($_POST['estado_secundario'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');

// Capturar horas y convertirlas a NULL si vienen vacías
$hora_entrada = (!empty($_POST['hora_entrada']) && $_POST['hora_entrada'] !== '--:--' && $_POST['hora_entrada'] !== 'null') ? $_POST['hora_entrada'] : null;
$hora_salida = (!empty($_POST['hora_salida']) && $_POST['hora_salida'] !== '--:--' && $_POST['hora_salida'] !== 'null') ? $_POST['hora_salida'] : null;

// Validar campos obligatorios
if (!$id_personal || !$fecha || !$estado_principal) {
    echo json_encode(['success' => false, 'msg' => 'Faltan datos obligatorios para procesar la solicitud.']);
    exit;
}

// Lógica para combinar el estado (Ej: "Retraso y Salida Temprana")
$estado_final = $estado_principal;
if ($estado_principal !== 'Feriado' && $estado_principal !== 'Eliminar' && !empty($estado_secundario)) {
    $estado_final = $estado_principal . ' y ' . $estado_secundario;
}

try {
    // Iniciar transacción para garantizar la integridad de los datos
    $conexion->beginTransaction();

    // ========================================================================
    // CASO A: ELIMINAR EL REGISTRO
    // ========================================================================
    if ($estado_principal === 'Eliminar') {
        $stmt_del = $conexion->prepare("DELETE FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $stmt_del->execute([$id_personal, $fecha]);
        
        if ($stmt_del->rowCount() > 0) {
            $detalles_bitacora = "Eliminó el registro de asistencia del personal ID: $id_personal en la fecha: $fecha.";
            $stmt_bitacora = $conexion->prepare("INSERT INTO bitacora (id_usuario, modulo, accion, detalles, fecha_hora, ip) VALUES (?, 'Asistencia', 'Eliminación de Registro', ?, NOW(), ?)");
            $stmt_bitacora->execute([$id_admin, $detalles_bitacora, $ip_usuario]);
            
            $conexion->commit();
            echo json_encode(['success' => true, 'msg' => 'El registro ha sido eliminado correctamente.']);
        } else {
            $conexion->rollBack();
            echo json_encode(['success' => false, 'msg' => 'No se encontró ningún registro para eliminar en esta fecha.']);
        }
        exit;
    }

    // ========================================================================
    // CASO B: GESTIÓN DE ARCHIVOS DE EVIDENCIA
    // ========================================================================
    $archivo_nombre = null;
    
    // Bandera para saber si el admin quitó la justificación manualmente
    $borrar_archivo_viejo = false;
    if ($estado_principal !== 'Justificado' && empty($motivo)) {
        $borrar_archivo_viejo = true; 
    }

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $max_size = 5 * 1024 * 1024; // 5 MB
        
        if ($_FILES['archivo']['size'] > $max_size) {
            throw new Exception('El archivo adjunto supera el límite de tamaño permitido (5MB).');
        }

        // Validación de extensión
        $nombre_original = $_FILES['archivo']['name'];
        $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $exts_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $exts_permitidas)) {
            throw new Exception('El formato del archivo adjunto no es válido. Solo se admiten PDF, JPG o PNG.');
        }

        // Validación de ADN del archivo (Bloqueo de Mimes ejecutables)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_real = finfo_file($finfo, $_FILES['archivo']['tmp_name']);
        finfo_close($finfo);

        // Si el archivo dice ser PDF pero por dentro es un código PHP o script, lo bloqueamos.
        // Si es un "octet-stream" genérico pero la extensión es .pdf, lo dejamos pasar.
        if (strpos($mime_real, 'php') !== false || strpos($mime_real, 'javascript') !== false || strpos($mime_real, 'html') !== false) {
             throw new Exception('Se ha detectado contenido no seguro o corrupto en el archivo.');
        }

        $directorio = '../recursos/evidencias/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $archivo_nombre = 'evidencia_manual_' . $id_personal . '_' . str_replace('-', '', $fecha) . '_' . time() . '.' . $ext;
        
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $directorio . $archivo_nombre)) {
            throw new Exception('Ocurrió un problema interno al subir el archivo al servidor.');
        }
    }

    // ========================================================================
    // CASO C: INSERTAR O ACTUALIZAR
    // ========================================================================
    $stmt_check = $conexion->prepare("SELECT id_asistencia, archivo_evidencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
    $stmt_check->execute([$id_personal, $fecha]);
    $registro_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

    // Mantenimiento de archivos viejos
    if ($registro_existente) {
        if ($archivo_nombre === null) {
            // No subieron archivo nuevo. ¿Debemos borrar el viejo o conservarlo?
            if ($borrar_archivo_viejo) {
                $archivo_nombre = null; // Quedará nulo en la BD
                // Borramos la evidencia física para ahorrar espacio
                if (!empty($registro_existente['archivo_evidencia'])) {
                    $ruta_vieja = '../recursos/evidencias/' . $registro_existente['archivo_evidencia'];
                    if (file_exists($ruta_vieja)) { unlink($ruta_vieja); }
                }
            } else {
                // Conservamos el archivo que ya tenía
                $archivo_nombre = $registro_existente['archivo_evidencia'];
            }
        } else {
            // Subieron un archivo NUEVO, así que borramos el físico viejo para que no haga bulto
            if (!empty($registro_existente['archivo_evidencia'])) {
                $ruta_vieja = '../recursos/evidencias/' . $registro_existente['archivo_evidencia'];
                if (file_exists($ruta_vieja)) { unlink($ruta_vieja); }
            }
        }
    }

    // Si el administrador lo justifica directamente, pasa a estado 'Aprobada' automáticamente
    $estado_justificacion = ($estado_principal === 'Justificado') ? 'Aprobada' : null;

    if ($registro_existente) {
        // ACTUALIZAR REGISTRO EXISTENTE
        $sql_update = "UPDATE asistencias SET 
                        estado = ?, 
                        hora_entrada = ?, 
                        hora_salida = ?, 
                        motivo_justificacion = ?, 
                        archivo_evidencia = ?,
                        estado_justificacion = ?
                       WHERE id_personal = ? AND fecha = ?";
        
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->execute([
            $estado_final, $hora_entrada, $hora_salida, $motivo, 
            $archivo_nombre, $estado_justificacion, $id_personal, $fecha
        ]);
        $accion_log = "Modificación Manual";
        
    } else {
        // INSERTAR NUEVO REGISTRO
        $stmt_hora = $conexion->prepare("SELECT hora_entrada_personalizada FROM personal WHERE id_personal = ?");
        $stmt_hora->execute([$id_personal]);
        $hora_esperada = $stmt_hora->fetchColumn();

        if (!$hora_esperada) {
            $stmt_conf = $conexion->query("SELECT hora_entrada_general FROM configuracion LIMIT 1");
            $hora_esperada = $stmt_conf->fetchColumn() ?: '07:00:00';
        }

        $sql_insert = "INSERT INTO asistencias 
                        (id_personal, fecha, hora_esperada, hora_entrada, hora_salida, estado, motivo_justificacion, archivo_evidencia, estado_justificacion) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                       
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->execute([
            $id_personal, $fecha, $hora_esperada, $hora_entrada, $hora_salida, 
            $estado_final, $motivo, $archivo_nombre, $estado_justificacion
        ]);
        $accion_log = "Registro Manual";
    }

    // ========================================================================
    // REGISTRO EN BITÁCORA
    // ========================================================================
    $stmt_nombre = $conexion->prepare("SELECT nombres, apellidos FROM personal WHERE id_personal = ?");
    $stmt_nombre->execute([$id_personal]);
    $emp = $stmt_nombre->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $emp ? $emp['nombres'] . ' ' . $emp['apellidos'] : "ID $id_personal";

    $texto_horas = "";
    if ($hora_entrada || $hora_salida) {
        $texto_horas = " | Ent: " . ($hora_entrada ?: '--:--') . " - Sal: " . ($hora_salida ?: '--:--');
    }

    $detalles_bitacora = "Cambió el estado a '$estado_final' para el empleado '$nombre_completo' en la fecha: $fecha.$texto_horas";

    $stmt_bitacora = $conexion->prepare("INSERT INTO bitacora (id_usuario, modulo, accion, detalles, fecha_hora, ip) VALUES (?, 'Asistencia', ?, ?, NOW(), ?)");
    $stmt_bitacora->execute([$id_admin, $accion_log, $detalles_bitacora, $ip_usuario]);

    $conexion->commit();

    echo json_encode(['success' => true, 'msg' => 'Registro de asistencia actualizado correctamente.']);

} catch (Exception $e) {
    $conexion->rollBack();
    error_log("Error Modificar Asistencia: " . $e->getMessage());
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
?>