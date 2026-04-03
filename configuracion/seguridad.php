<?php
// 1. Forzar a PHP a bloquear el caché ANTES de iniciar sesión
session_cache_limiter('nocache');
session_start();

// 2. Destruir el caché del navegador (Solución al botón "Atrás")
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 3. Verificar si el usuario NO ha iniciado sesión
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    // Si no está logueado, lo pateamos de vuelta al login
    header("Location: ../vistas/login.php");
    exit;
}
?>