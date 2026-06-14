<?php
session_start();
require_once '../configuracion/conexion.php';

// Verificar autenticación
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'msg' => 'Acceso denegado.']);
    exit;
}

// Verificar permisos de administrador (id_rol 1 o 2)
$id_rol = $_SESSION['id_rol'];
if (!in_array($id_rol, [1, 2])) {
    echo json_encode(['success' => false, 'msg' => 'No tienes permisos para realizar justificaciones masivas.']);
    exit;
}

// Obtener datos del POST
$id_personal = isset($_POST['id_personal']) ? (int)$_POST['id_personal'] : 0;
$fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
$motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';

if (!$id_personal || !$fecha_inicio || !$fecha_fin || empty($motivo)) {
    echo json_encode(['success' => false, 'msg' => 'Todos los campos son obligatorios.']);
    exit;
}

if (strlen($motivo) < 10) {
    echo json_encode(['success' => false, 'msg' => 'El motivo debe tener al menos 10 caracteres.']);
    exit;
}

if ($fecha_fin < $fecha_inicio) {
    echo json_encode(['success' => false, 'msg' => 'La fecha fin debe ser mayor o igual a la fecha inicio.']);
    exit;
}

date_default_timezone_set('America/Caracas');
$hoy = date('Y-m-d');

// Obtener fecha de ingreso del empleado
$stmt = $conexion->prepare("SELECT fecha_ingreso FROM personal WHERE id_personal = ?");
$stmt->execute([$id_personal]);
$fechaIngreso = $stmt->fetchColumn();
if (!$fechaIngreso) {
    $fechaIngreso = $hoy; // fallback
}

// Validar que el rango no incluya fechas anteriores al ingreso
if ($fecha_fin < $fechaIngreso) {
    echo json_encode(['success' => false, 'msg' => "El rango seleccionado es completamente anterior a la fecha de ingreso del empleado ({$fechaIngreso})."]);
    exit;
}
if ($fecha_inicio < $fechaIngreso) {
    $fecha_inicio = $fechaIngreso; 
}

// 🛡️ PROTECCIÓN RCE FLEXIBILIZADA
$archivos = isset($_FILES['evidencias']) ? $_FILES['evidencias'] : null;
$rutas_archivos = [];

if ($archivos && isset($archivos['error'][0]) && $archivos['error'][0] !== UPLOAD_ERR_NO_FILE) {
    $carpeta_evidencias = '../recursos/evidencias/';
    if (!is_dir($carpeta_evidencias)) {
        mkdir($carpeta_evidencias, 0777, true);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
    
    // Variantes MIME para PDFs y Documentos
    $mimes_permitidos = [
        'image/jpeg', 'image/png', 
        'application/pdf', 'application/x-pdf', 'application/acrobat', 'text/pdf', 'text/x-pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $exts_permitidas = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

    for ($i = 0; $i < count($archivos['name']); $i++) {
        if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($archivos['name'][$i], PATHINFO_EXTENSION));
            $mime = finfo_file($finfo, $archivos['tmp_name'][$i]);

            // Misma regla de flexibilidad
            if (in_array($ext, $exts_permitidas) && (in_array($mime, $mimes_permitidos) || $mime === 'application/octet-stream')) {
                $nombre = uniqid() . '_' . basename($archivos['name'][$i]);
                if (move_uploaded_file($archivos['tmp_name'][$i], $carpeta_evidencias . $nombre)) {
                    $rutas_archivos[] = $nombre;
                }
            }
        }
    }
    finfo_close($finfo);
}
$archivos_guardados = implode(',', $rutas_archivos);

// Generar array de fechas entre inicio y fin
$fechas = [];
$current = strtotime($fecha_inicio);
$end = strtotime($fecha_fin);
while ($current <= $end) {
    $fecha = date('Y-m-d', $current);
    if (date('N', $current) < 6) { 
        $fechas[] = $fecha;
    }
    $current = strtotime('+1 day', $current);
}

$insertados = 0;
$errores = 0;

foreach ($fechas as $fecha) {
    if ($fecha < $fechaIngreso) continue;

    $stmt_check = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
    $stmt_check->execute([$id_personal, $fecha]);
    
    if ($stmt_check->fetch()) {
        $sql_upd = "UPDATE asistencias 
                    SET estado = 'Justificado', 
                        motivo_justificacion = CONCAT(IFNULL(motivo_justificacion, ''), '; ', ?), 
                        archivo_evidencia = IF(? != '', CONCAT(IFNULL(archivo_evidencia, ''), ',', ?), archivo_evidencia),
                        estado_justificacion = 'Aprobada'
                    WHERE id_personal = ? AND fecha = ?";
        $stmt_upd = $conexion->prepare($sql_upd);
        if ($stmt_upd->execute([$motivo, $archivos_guardados, $archivos_guardados, $id_personal, $fecha])) {
            $insertados++;
        } else {
            $errores++;
        }
    } else {
        $sql_ins = "INSERT INTO asistencias (id_personal, fecha, estado, motivo_justificacion, archivo_evidencia, estado_justificacion, hora_entrada) 
                    VALUES (?, ?, 'Justificado', ?, ?, 'Aprobada', NULL)";
        $stmt_ins = $conexion->prepare($sql_ins);
        if ($stmt_ins->execute([$id_personal, $fecha, $motivo, $archivos_guardados])) {
            $insertados++;
        } else {
            $errores++;
        }
    }
}

$msg = "Justificación completada. Días procesados: $insertados. Errores: $errores.";
if ($insertados === 0 && $errores === 0) {
    $msg = "No se procesó ningún día (posiblemente todas las fechas eran anteriores al ingreso o eran fines de semana).";
}
echo json_encode(['success' => true, 'msg' => $msg]);
?>