<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; 

header('Content-Type: application/json');

// 1. Seguridad: solo administradores
if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    echo json_encode(['success' => false, 'msg' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_personal = (int)$_POST['id_personal'];
    $fecha = $_POST['fecha'];
    $motivo = trim($_POST['motivo']);
    $estado_primario = $_POST['estado'];
    
    date_default_timezone_set('America/Caracas');
    $hoy = date('Y-m-d');
    
    // Obtener la fecha de ingreso
    $stmt_ingreso = $conexion->prepare("SELECT fecha_ingreso FROM personal WHERE id_personal = ?");
    $stmt_ingreso->execute([$id_personal]);
    $fechaIngreso = $stmt_ingreso->fetchColumn();
    if (!$fechaIngreso) $fechaIngreso = $hoy;
    
    // Bloquear edición antes del ingreso
    if ($fecha < $fechaIngreso) {
        echo json_encode(['success' => false, 'msg' => "No se puede modificar una fecha anterior a la fecha de ingreso del empleado ($fechaIngreso)."]);
        exit;
    }

    // Bloquear creación de registros en días futuros sin registro previo
    if ($fecha > $hoy && $estado_primario !== 'Eliminar') {
        $stmt_future = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $stmt_future->execute([$id_personal, $fecha]);
        if (!$stmt_future->fetchColumn()) {
            echo json_encode(['success' => false, 'msg' => 'No se puede crear un registro en una fecha futura. Solo se pueden editar registros ya existentes (ej. justificaciones programadas).']);
            exit;
        }
    }

    $stmt_emp = $conexion->prepare("SELECT CONCAT(nombres, ' ', apellidos) FROM personal WHERE id_personal = ?");
    $stmt_emp->execute([$id_personal]);
    $nombre_empleado = $stmt_emp->fetchColumn() ?: "ID: " . $id_personal;

    try {
        // =========================================================
        // NUEVA LÓGICA: ELIMINAR UN REGISTRO (Deshacer justificación)
        // =========================================================
        if ($estado_primario === 'Eliminar') {
            $stmt_del = $conexion->prepare("DELETE FROM asistencias WHERE id_personal = ? AND fecha = ?");
            $stmt_del->execute([$id_personal, $fecha]);
            
            ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Asistencia', 'Eliminación de Registro', "Eliminó el registro de asistencia (limpió el día) para el empleado '$nombre_empleado' en la fecha: $fecha.");
            
            echo json_encode(['success' => true, 'msg' => 'La justificación/registro ha sido eliminada correctamente.']);
            exit;
        }

        // Lógica de estado combinado
        $estado_secundario = isset($_POST['estado_secundario']) ? trim($_POST['estado_secundario']) : '';
        if (!empty($estado_secundario) && $estado_secundario !== 'Ninguna') {
            $estado = $estado_primario . ' y ' . $estado_secundario;
        } else {
            $estado = $estado_primario;
        }

        $estado_justificacion = ($estado === 'Justificado') ? 'Aprobada' : NULL;
        
        // Lógica para subir archivos
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

        $stmt_check = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $stmt_check->execute([$id_personal, $fecha]);
        $existe = $stmt_check->fetchColumn();

        if ($existe) {
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
            $sql = "INSERT INTO asistencias (id_personal, fecha, estado, motivo_justificacion, estado_justificacion, archivo_evidencia) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$id_personal, $fecha, $estado, $motivo, $estado_justificacion, $nombre_archivo_final]);
        }

        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Asistencia', 'Modificación de Asistencia', "Cambió el estado a '$estado' para el empleado '$nombre_empleado' en la fecha: $fecha.");

        echo json_encode(['success' => true, 'msg' => 'La asistencia ha sido modificada correctamente.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'msg' => 'Error de Base de Datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'msg' => 'Petición inválida.']);
}
?>