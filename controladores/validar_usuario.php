<?php
require_once '../configuracion/conexion.php';

// Verificamos que sea una petición POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['usuario'])) {
    // Idea 2: Sanitización (quitamos espacios)
    $usuario = preg_replace('/\s+/', '', $_POST['usuario']); 

    try {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':usuario' => $usuario]);
        
        $existe = ($stmt->fetchColumn() > 0);
        
        // Devolvemos la respuesta en formato JSON
        echo json_encode(['existe' => $existe]);
    } catch (PDOException $e) {
        echo json_encode(['error' => true]);
    }
}
?>