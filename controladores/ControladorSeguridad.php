<?php
session_start();
require_once '../configuracion/conexion.php';
require_once '../controladores/ControladorBitacora.php'; // NUEVO: Inclusión de la Bitácora

// 1. Verificación de seguridad
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../vistas/principal.php");
    exit();
}

date_default_timezone_set('America/Caracas');

// 2. Variables Principales
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$carpeta_respaldos = '../respaldos/';
$fecha_hoy = date('d-m-Y');
$archivo_limites = $carpeta_respaldos . 'limites_diarios.json';

if (!file_exists($carpeta_respaldos)) {
    mkdir($carpeta_respaldos, 0777, true);
}

// =======================================================
// FUNCIONES DE APOYO (LÍMITES Y LIMPIEZA)
// =======================================================
function limpiarHistorialAntiguo($ruta_carpeta) {
    $archivos = glob($ruta_carpeta . "*.sql");
    if (count($archivos) > 10) {
        usort($archivos, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        $excedente = count($archivos) - 10;
        for ($i = 0; $i < $excedente; $i++) {
            if (is_file($archivos[$i])) {
                unlink($archivos[$i]);
            }
        }
    }
}

function obtenerLimites($ruta_json, $hoy) {
    if (file_exists($ruta_json)) {
        $data = json_decode(file_get_contents($ruta_json), true);
        if ($data && isset($data['fecha']) && $data['fecha'] === $hoy) {
            if (!isset($data['restaurados'])) $data['restaurados'] = 0;
            return $data;
        }
    }
    return ['fecha' => $hoy, 'generados' => 0, 'subidos' => 0, 'restaurados' => 0];
}

$limites = obtenerLimites($archivo_limites, $fecha_hoy);

// =======================================================
// A. GENERAR RESPALDO (BLOQUEA AMBAS OPCIONES)
// =======================================================
if ($accion === 'generar') {
    $tipo = $_GET['tipo'] ?? 'local';
    
    // BLOQUEO DE SEGURIDAD BACKEND PARA AMBOS (LOCAL Y DESCARGAR)
    if ($limites['generados'] >= 4) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Seguridad: Límite diario de 4 respaldos alcanzado.'];
        header("Location: ../vistas/seguridad.php");
        exit();
    }

    $fecha_hora = date('d-m-Y_H-i-s');
    $nombre_archivo = "respaldo_ceis_" . $fecha_hora . ".sql";
    $ruta_completa = $carpeta_respaldos . $nombre_archivo;

    try {
        $tablas = [];
        $stmt = $conexion->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) { $tablas[] = $row[0]; }

        $sql_dump = "-- Respaldo del Sistema CEIS Julian Yánez\n";
        $sql_dump .= "-- Generado el: " . date('d/m/Y h:i:s A') . "\n";
        $sql_dump .= "-- Generado por: " . $_SESSION['usuario'] . "\n\n";
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tablas as $tabla) {
            $stmt = $conexion->query("SHOW CREATE TABLE `$tabla`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $sql_dump .= "DROP TABLE IF EXISTS `$tabla`;\n";
            $sql_dump .= $row[1] . ";\n\n";

            $stmt = $conexion->query("SELECT * FROM `$tabla`");
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as $fila) {
                $valores = array_map(function($val) use ($conexion) {
                    return is_null($val) ? "NULL" : $conexion->quote($val);
                }, array_values($fila));
                $sql_dump .= "INSERT INTO `$tabla` VALUES(" . implode(", ", $valores) . ");\n";
            }
            $sql_dump .= "\n\n";
        }
        $sql_dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Sumamos 1 a la bitácora de límites
        $limites['generados']++;
        file_put_contents($archivo_limites, json_encode($limites));

        if ($tipo === 'local') {
            file_put_contents($ruta_completa, $sql_dump);
            limpiarHistorialAntiguo($carpeta_respaldos);
            
            // NUEVO: Registrar en Bitácora (Local)
            ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Seguridad', 'Generación de Respaldo', "Guardó el archivo: $nombre_archivo en el servidor.");

            $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Respaldo guardado en el historial.'];
            header("Location: ../vistas/seguridad.php");
            exit();
        } else if ($tipo === 'descargar') {
            
            // NUEVO: Registrar en Bitácora (Descarga)
            ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Seguridad', 'Descarga de Respaldo Inmediata', "Generó y descargó: $nombre_archivo");

            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
            echo $sql_dump;
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error al generar: ' . $e->getMessage()];
        header("Location: ../vistas/seguridad.php");
        exit();
    }
}

// =======================================================
// B. ELIMINAR RESPALDO
// =======================================================
else if ($accion === 'eliminar') {
    $archivo = $_GET['archivo'] ?? '';
    $ruta_archivo = $carpeta_respaldos . basename($archivo);

    if (file_exists($ruta_archivo) && is_file($ruta_archivo)) {
        unlink($ruta_archivo);
        
        // NUEVO: Registrar en Bitácora
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Seguridad', 'Eliminación de Respaldo', "Eliminó el archivo: " . basename($archivo) . " del servidor.");

        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Respaldo eliminado del historial.'];
    } else {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'El archivo no existe.'];
    }
    header("Location: ../vistas/seguridad.php");
    exit();
}

// =======================================================
// C. DESCARGAR DEL HISTORIAL
// =======================================================
else if ($accion === 'descargar_historial') {
    $archivo = $_GET['archivo'] ?? '';
    $ruta_archivo = $carpeta_respaldos . basename($archivo);

    if (file_exists($ruta_archivo) && is_file($ruta_archivo)) {
        
        // NUEVO: Registrar en Bitácora
        ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Seguridad', 'Descarga desde Historial', "Descargó el archivo: " . basename($archivo));

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($ruta_archivo).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($ruta_archivo));
        readfile($ruta_archivo);
        exit();
    } else {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Archivo no encontrado.'];
        header("Location: ../vistas/seguridad.php");
        exit();
    }
}

// =======================================================
// D. RESTAURAR BASE DE DATOS
// =======================================================
else if ($accion === 'restaurar') {
    if ($limites['restaurados'] >= 2) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Seguridad: Has alcanzado el límite de 2 restauraciones por día.'];
        header("Location: ../vistas/seguridad.php");
        exit();
    }

    $archivo = $_POST['archivo'] ?? '';
    $password_recibida = $_POST['password_admin'] ?? '';
    $id_usuario_actual = $_SESSION['id_usuario'];
    
    try {
        $stmt_pass = $conexion->prepare("SELECT password FROM usuarios WHERE id_usuario = :id");
        $stmt_pass->execute([':id' => $id_usuario_actual]);
        $hash_db = $stmt_pass->fetchColumn();

        $es_valida = false;
        if (password_verify($password_recibida, $hash_db)) { $es_valida = true; } 
        else if ($hash_db === md5($password_recibida)) { $es_valida = true; } 
        else if ($hash_db === $password_recibida) { $es_valida = true; }

        if (!$hash_db || !$es_valida) {
            
            // NUEVO: Registrar INTENTO FALLIDO en Bitácora
            ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Seguridad', 'Intento de Restauración Fallido', "Contraseña incorrecta al intentar restaurar: " . basename($archivo));

            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Contraseña incorrecta. Restauración cancelada por seguridad.'];
            header("Location: ../vistas/seguridad.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error al verificar credenciales.'];
        header("Location: ../vistas/seguridad.php");
        exit();
    }

    $ruta_archivo = $carpeta_respaldos . basename($archivo);

    if (file_exists($ruta_archivo) && is_file($ruta_archivo)) {
        try {
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = file_get_contents($ruta_archivo);
            
            $conexion->exec("SET FOREIGN_KEY_CHECKS=0;");
            $conexion->exec($sql);
            $conexion->exec("SET FOREIGN_KEY_CHECKS=1;");

            $limites['restaurados']++;
            file_put_contents($archivo_limites, json_encode($limites));

            $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Autenticación exitosa. Sistema restaurado correctamente.'];
        } catch (Exception $e) {
            $conexion->exec("SET FOREIGN_KEY_CHECKS=1;");
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error SQL al restaurar: ' . $e->getMessage()];
        }
    } else {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'No se encontró el archivo seleccionado.'];
    }
    header("Location: ../vistas/seguridad.php");
    exit();
}

// =======================================================
// E. SUBIR ARCHIVO SQL EXTERNO
// =======================================================
else if ($accion === 'subir_externo') {
    if ($limites['subidos'] >= 2) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Seguridad: Has alcanzado el límite de 2 cargas externas por día.'];
        header("Location: ../vistas/seguridad.php");
        exit();
    }

    if (isset($_FILES['archivo_sql']) && $_FILES['archivo_sql']['error'] === UPLOAD_ERR_OK) {
        $nombre_tmp = $_FILES['archivo_sql']['tmp_name'];
        $nombre_original = $_FILES['archivo_sql']['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

        if ($extension === 'sql') {
            $nuevo_nombre = "respaldo_externo_" . date('d-m-Y_H-i-s') . ".sql";
            $destino = $carpeta_respaldos . $nuevo_nombre;

            if (move_uploaded_file($nombre_tmp, $destino)) {
                
                $limites['subidos']++;
                file_put_contents($archivo_limites, json_encode($limites));

                limpiarHistorialAntiguo($carpeta_respaldos);
                
                // NUEVO: Registrar en Bitácora
                ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Seguridad', 'Subida de Archivo SQL', "Subió el archivo externo: $nombre_original y se renombró a: $nuevo_nombre");

                $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Archivo cargado al historial. Usa el botón de restaurar cuando desees aplicarlo.'];
            } else {
                $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error de permisos al guardar el archivo.'];
            }
        } else {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Seguridad: Solo se permiten subir archivos .sql'];
        }
    } else {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error en el archivo. Verifica el peso.'];
    }
    header("Location: ../vistas/seguridad.php");
    exit();
}
else {
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Acción desconocida.'];
    header("Location: ../vistas/seguridad.php");
    exit();
}
?>