<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; 

header('Content-Type: application/json');

// 1. Seguridad estricta
if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    echo json_encode(['success' => false, 'msg' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_personal = (int)$_POST['id_personal'];
    $fecha = $_POST['fecha'];
    $motivo = trim($_POST['motivo']);
    
    // OBTENEMOS EL ESTADO PRINCIPAL Y EL SECUNDARIO
    $estado_primario = $_POST['estado'];
    $estado_secundario = isset($_POST['estado_secundario']) ? trim($_POST['estado_secundario']) : '';
    
    // COMBINAMOS LA LÓGICA DE ESTADOS
    if (!empty($estado_secundario) && $estado_secundario !== 'Ninguna') {
        $estado = $estado_primario . ' y ' . $estado_secundario;
    } else {
        $estado = $estado_primario;
    }

    // CORRECCIÓN DEL BUG: Mantenemos el estado aprobado solo si se marca como Justificado explícitamente
    $estado_justificacion = ($estado === 'Justificado') ? 'Aprobada' : NULL;
    
    // 2. Lógica para subir una nueva evidencia
    $nombre_archivo_final = null;
    $actualizar_archivo = false;

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $directorio_destino = '../recursos/evidencias/';
        if (!file_exists($directorio_destino)) { mkdir($directorio_destino, 0777, true); }

        $archivo = $_FILES['archivo'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        $mimes_validos = ['image/jpeg', 'image/png', 'application/pdf'];

        if (in_array($extension, $extensiones_validas) && in_array($mime_type, $mimes_validos)) {
            if ($archivo['size'] <= 5000000) {
                $nombre_archivo_final = 'admin_mod_' . $id_personal . '_' . str_replace('-', '', $fecha) . '_' . time() . '.' . $extension;
                move_uploaded_file($archivo['tmp_name'], $directorio_destino . $nombre_archivo_final);
                $actualizar_archivo = true;
            } else {
                echo json_encode(['success' => false, 'msg' => 'El archivo supera los 5MB.']); exit;
            }
        } else {
            echo json_encode(['success' => false, 'msg' => 'Formato de archivo no válido.']); exit;
        }
    }

    try {
        // Obtenemos el nombre y apellido del empleado para la bitácora
        $stmt_emp = $conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) FROM personal WHERE id_personal = ?");
        $stmt_emp->execute([$id_personal]);
        $nombre_empleado = $stmt_emp->fetchColumn();
        if (!$nombre_empleado) { $nombre_empleado = "ID: " . $id_personal; }

        // 3. Revisar si ya existe un registro ese día
        $stmt_check = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $stmt_check->execute([$id_personal, $fecha]);
        $existe = $stmt_check->fetchColumn();

        if ($existe) {
            // UPDATE: Actualizamos el registro con el nuevo estado (simple o combinado)
            if ($actualizar_archivo) {
                $sql = "UPDATE asistencias SET estado = ?, motivo_justificacion = ?, archivo_evidencia = ?, estado_justificacion = ? WHERE id_personal = ? AND fecha = ?";
                $params = [$estado, $motivo, $nombre_archivo_final, $estado_justificacion, $id_personal, $fecha];
            } else {
                $sql = "UPDATE asistencias SET estado = ?, motivo_justificacion = ?, estado_justificacion = ? WHERE id_personal = ? AND fecha = ?";
                $params = [$estado, $motivo, $estado_justificacion, $id_personal, $fecha];
            }
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);

        } else {
            // INSERT: Creamos el registro desde cero
            $sql = "INSERT INTO asistencias (id_personal, fecha, estado, motivo_justificacion, estado_justificacion, archivo_evidencia) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_personal, $fecha, $estado, $motivo, $estado_justificacion, $nombre_archivo_final]);
        }

        // Registrar en Bitácora el evento de asistencia modificado
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Asistencia', 'Modificación de Asistencia', "Cambió el estado a '$estado' para el empleado '$nombre_empleado' en la fecha: $fecha.");

        echo json_encode(['success' => true, 'msg' => 'La asistencia ha sido modificada correctamente.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'msg' => 'Error de Base de Datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Petición inválida.']);
}
?>