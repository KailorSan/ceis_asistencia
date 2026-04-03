<?php
$servidor = 'localhost';
$nombre_bd = 'ceis_asistencia';
$usuario_bd = 'root';
$clave_bd = '';
$juego_caracteres = 'utf8mb4';

try {
    $origen_datos = "mysql:host=$servidor;dbname=$nombre_bd;charset=$juego_caracteres";
    
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    $conexion = new PDO($origen_datos, $usuario_bd, $clave_bd, $opciones);

} catch (PDOException $error) {
    die("Error de conexión a la Base de Datos: " . $error->getMessage());
}
?>