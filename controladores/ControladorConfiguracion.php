<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; // NUEVO: Incluimos la bitácora

// Verificación estricta de seguridad
if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    header("Location: ../vistas/principal.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hora_entrada = $_POST['hora_entrada'] . ':00'; // Formato de BD
    $hora_salida = $_POST['hora_salida'] . ':00';
    $tolerancia = (int)$_POST['minutos_tolerancia'];

    try {
        $stmt = $conexion->prepare("UPDATE configuracion SET hora_entrada_general = ?, hora_salida_general = ?, minutos_tolerancia = ? WHERE id_config = 1");
        $stmt->execute([$hora_entrada, $hora_salida, $tolerancia]);

        // NUEVO: Registrar en Bitácora
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Configuracion', 'Modificación de Horarios del Sistema', "Nueva entrada: $hora_entrada, salida: $hora_salida, tolerancia: $tolerancia minutos.");

        // Guardamos una variable de sesión para que la vista muestre la alerta verde
        $_SESSION['config_exito'] = true;

    } catch (PDOException $e) {
        // En caso de error
        $_SESSION['config_error'] = "Error al actualizar la base de datos.";
    }

    // Regresamos a la vista
    header("Location: ../vistas/configuracionAsistencia.php");
    exit;
} else {
    header("Location: ../vistas/configuracionAsistencia.php");
    exit;
}
?>