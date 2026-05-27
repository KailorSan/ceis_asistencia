<?php
session_start();
require_once '../configuracion/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// 1. LISTAR FERIADOS (Para pintar el calendario JS)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] == 'listar') {
    try {
        $stmt = $conexion->query("SELECT fecha, descripcion FROM feriados");
        $feriados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $feriados]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al consultar BD']);
    }
    exit;
}

// 2. GESTIONAR FERIADO (Agregar o Quitar)
$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['fecha'])) {
    $fecha = $data['fecha'];
    $id_usuario = $_SESSION['id_usuario'];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido.']);
        exit;
    }

    try {
        $stmt = $conexion->prepare("SELECT id_feriado FROM feriados WHERE fecha = :fecha");
        $stmt->execute([':fecha' => $fecha]);
        
        if ($stmt->rowCount() > 0) {
            // Ya existe -> Lo eliminamos (restauramos el día como laborable)
            $stmtDel = $conexion->prepare("DELETE FROM feriados WHERE fecha = :fecha");
            $stmtDel->execute([':fecha' => $fecha]);
            echo json_encode(['success' => true, 'accion' => 'eliminado', 'message' => 'Día restaurado como laborable.']);
        } else {
            // No existe -> Lo agregamos
            $tipo = isset($data['tipo']) ? trim($data['tipo']) : '';
            $motivo = isset($data['motivo']) ? trim($data['motivo']) : '';

            // Validaciones Backend
            if (!in_array($tipo, ['Feriado', 'Día Festivo'])) {
                echo json_encode(['success' => false, 'message' => 'El tipo seleccionado es inválido.']);
                exit;
            }
            if (strlen($motivo) < 10) {
                echo json_encode(['success' => false, 'message' => 'La justificación debe tener un mínimo de 10 caracteres.']);
                exit;
            }

            // Concatenamos para guardar en la BD actual
            $descripcion = $tipo . ': ' . $motivo;

            $stmtIns = $conexion->prepare("INSERT INTO feriados (fecha, descripcion, id_usuario) VALUES (:fecha, :descripcion, :id_usuario)");
            $stmtIns->execute([
                ':fecha' => $fecha, 
                ':descripcion' => $descripcion, 
                ':id_usuario' => $id_usuario
            ]);
            echo json_encode(['success' => true, 'accion' => 'agregado', 'message' => 'Día inhabilitado correctamente.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la BD.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Petición inválida']);
?>