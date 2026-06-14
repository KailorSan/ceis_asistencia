<?php
// 1. Forzar a PHP a bloquear el caché ANTES de iniciar sesión
session_cache_limiter('nocache');
session_start();

header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

require_once 'conexion.php'; // Asegúrate de que esta ruta apunte bien a tu conexion.php

$stmt_check_vivo = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE id_usuario = ? AND estado = 'Activo'");
$stmt_check_vivo->execute([$_SESSION['id_usuario']]);

if ($stmt_check_vivo->fetchColumn() == 0) {
    // Vaciamos y destruimos la sesión por completo
    $_SESSION = array();
    session_destroy();
    
    header("Location: ../vistas/login.php?error=cuenta_eliminada");
    exit;
}
?>