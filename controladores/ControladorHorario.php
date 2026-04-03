<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_personal = $_POST['id_personal'];
    
    // Si vienen vacíos, los convertimos en NULL para que usen el horario global
    $hora_entrada = !empty($_POST['hora_entrada']) ? $_POST['hora_entrada'] . ':00' : null;
    $hora_salida = !empty($_POST['hora_salida']) ? $_POST['hora_salida'] . ':00' : null;

    try {
        $stmt = $conexion->prepare("UPDATE personal SET hora_entrada_personalizada = ?, hora_salida_personalizada = ? WHERE id_personal = ?");
        $stmt->execute([$hora_entrada, $hora_salida, $id_personal]);
        
        $_SESSION['alerta_personal'] = ['tipo' => 'success', 'mensaje' => 'Horario actualizado correctamente.'];
    } catch (PDOException $e) {
        $_SESSION['alerta_personal'] = ['tipo' => 'error', 'mensaje' => 'Hubo un error al actualizar el horario.'];
    }
    
    header("Location: ../vistas/personal.php");
    exit;
}
?>