<?php
session_start();
require_once '../configuracion/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logueado'])) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$id_personal = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

// Variables contador (Ahora son 6)
$p = 0; $r = 0; $f = 0; $st = 0; $si = 0; $j = 0;

try {
    if ($mes === 'todos') {
        $stmt_stats = $conexion->prepare("SELECT estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND YEAR(fecha) = ?");
        $stmt_stats->execute([$id_personal, $anio]);
        
        while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
            if ($row['estado_justificacion'] == 'Aprobada') {
                $j++;
            } else {
                $est = $row['estado'];
                if (strpos($est, 'Puntual') !== false) $p++;
                if (strpos($est, 'Retraso') !== false) $r++;
                if (strpos($est, 'Falta') !== false) $f++;
                if (strpos($est, 'Justificado') !== false) $j++;
                if (strpos($est, 'Salida Temprana') !== false) $st++;
                if (strpos($est, 'Salida Irregular') !== false) $si++;
            }
        }
    } else {
        $stmt_stats = $conexion->prepare("SELECT fecha, estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?");
        $stmt_stats->execute([$id_personal, $mes, $anio]);
        
        $registros_reales = [];
        while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
            $registros_reales[$row['fecha']] = $row;
        }

        $dias_del_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $fecha_hoy = date('Y-m-d'); 

        for ($d = 1; $d <= $dias_del_mes; $d++) {
            $fecha_ciclo = sprintf("%04d-%02d-%02d", $anio, $mes, $d);
            if ($fecha_ciclo > $fecha_hoy) break;

            $dia_semana = date('N', strtotime($fecha_ciclo)); 
            if ($dia_semana > 5 && !isset($registros_reales[$fecha_ciclo])) continue;

            if (isset($registros_reales[$fecha_ciclo])) {
                $estado = $registros_reales[$fecha_ciclo]['estado_justificacion'] == 'Aprobada' ? 'Justificado' : $registros_reales[$fecha_ciclo]['estado'];
                
                if (strpos($estado, 'Puntual') !== false) $p++;
                if (strpos($estado, 'Retraso') !== false) $r++;
                if (strpos($estado, 'Falta') !== false) $f++;
                if (strpos($estado, 'Justificado') !== false) $j++;
                if (strpos($estado, 'Salida Temprana') !== false) $st++;
                if (strpos($estado, 'Salida Irregular') !== false) $si++;
                
            } else {
                $f++; 
            }
        }
    }

    echo json_encode([
        'puntual' => $p,
        'retraso' => $r,
        'falta' => $f,
        'salida_temprana' => $st,
        'salida_irregular' => $si,
        'justificado' => $j
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de Base de Datos']);
}
?>