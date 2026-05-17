<?php
// Validamos la sesión para evitar advertencias
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../configuracion/conexion.php';
require_once 'ControladorNotificaciones.php'; // Agregamos el controlador para usar su método

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

try {
    // Obtenemos los datos completos en lugar de solo el conteo
    $notificaciones = ControladorNotificaciones::obtenerNoLeidas($conexion, $id_usuario);
    $cantidad = count($notificaciones);

    // Devolvemos tanto la cantidad como la data
    echo json_encode([
        'success' => true, 
        'cantidad' => $cantidad,
        'notificaciones' => $notificaciones
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
}
?>