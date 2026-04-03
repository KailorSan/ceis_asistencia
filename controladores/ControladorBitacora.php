<?php
class ControladorBitacora {
    
    // Función para registrar una nueva acción en la bitácora
    public static function registrar($conexion, $id_usuario, $modulo, $accion, $detalles = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
        
        $sql = "INSERT INTO bitacora (id_usuario, modulo, accion, detalles, ip) 
                VALUES (:id_usuario, :modulo, :accion, :detalles, :ip)";
                
        try {
            $stmt = $conexion->prepare($sql);
            $exito = $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':modulo'     => $modulo,
                ':accion'     => $accion,
                ':detalles'   => $detalles,
                ':ip'         => $ip
            ]);
            return $exito;
        } catch (PDOException $e) {
            // MODO DEPURACIÓN: Esto detendrá la página y nos mostrará el error exacto si ocurre
            die("<h1>🛑 ERROR FATAL EN BITÁCORA:</h1> <p>" . $e->getMessage() . "</p><p>¿Verificaste si creaste la tabla 'bitacora' en phpMyAdmin?</p>");
        }
    }

    // Función para obtener todo el historial y mostrarlo en la tabla
    public static function obtenerHistorial($conexion) {
        // CORRECCIÓN APLICADA: Ahora extrae u.nombre_usuario
        $sql = "SELECT b.*, u.nombre_usuario 
                FROM bitacora b 
                INNER JOIN usuarios u ON b.id_usuario = u.id_usuario 
                ORDER BY b.fecha_hora DESC";
                
        try {
            $stmt = $conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>