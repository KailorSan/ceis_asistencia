<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leido = 0");
    $stmt->execute([$id_usuario]);
    $cantidad = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'cantidad' => (int)$cantidad]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
}
?>