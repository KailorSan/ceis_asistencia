<?php
session_start();
require_once '../configuracion/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logueado'])) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$id_personal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 🛡️ BLINDAJE IDOR: Verificar que el rol 3 solo vea su propia información
if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 3) {
    $stmt_mi_id = $conexion->prepare("SELECT id_personal FROM personal WHERE id_usuario = ?");
    $stmt_mi_id->execute([$_SESSION['id_usuario']]);
    $mi_id = $stmt_mi_id->fetchColumn();

    if ($id_personal != $mi_id) {
        echo json_encode(['error' => 'Vulnerabilidad bloqueada. No puedes ver datos de otros empleados.']);
        exit;
    }
}

$mes = isset($_GET['mes']) ? $_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

$p = 0; $r = 0; $f = 0; $st = 0; $si = 0; $j = 0;
$feriados_omitidos = 0; 

try {
    // === OBTENER FECHA DE INGRESO PARA FRENAR FALTAS FANTASMAS ===
    $stmt_ingreso = $conexion->prepare("SELECT fecha_ingreso FROM personal WHERE id_personal = ?");
    $stmt_ingreso->execute([$id_personal]);
    $fecha_ingreso_empleado = $stmt_ingreso->fetchColumn() ?: '2000-01-01';

    // Obtener feriados hábiles (lunes a viernes) del período
    $feriados_array = [];
    if ($mes === 'todos') {
        $stmt_fer = $conexion->prepare("SELECT fecha FROM feriados WHERE YEAR(fecha) = ? AND DAYOFWEEK(fecha) NOT IN (1, 7)");
        $stmt_fer->execute([$anio]);
    } else {
        $stmt_fer = $conexion->prepare("SELECT fecha FROM feriados WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND DAYOFWEEK(fecha) NOT IN (1, 7)");
        $stmt_fer->execute([$mes, $anio]);
    }
    while ($rowF = $stmt_fer->fetch(PDO::FETCH_ASSOC)) {
        $feriados_array[] = $rowF['fecha'];
    }

    if ($mes === 'todos') {
        $stmt_stats = $conexion->prepare("SELECT fecha, estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND YEAR(fecha) = ?");
        $stmt_stats->execute([$id_personal, $anio]);
        
        $registros_reales = [];
        while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
            $registros_reales[$row['fecha']] = $row;
        }
        
        $fecha_hoy = date('Y-m-d');
        
        for($m = 1; $m <= 12; $m++) {
            $dias_del_mes = cal_days_in_month(CAL_GREGORIAN, $m, $anio);
            
            for ($d = 1; $d <= $dias_del_mes; $d++) {
                $fecha_ciclo = sprintf("%04d-%02d-%02d", $anio, $m, $d);
                
                $dia_semana = date('N', strtotime($fecha_ciclo)); 
                if ($dia_semana > 5 && !isset($registros_reales[$fecha_ciclo])) continue;

                $es_feriado = in_array($fecha_ciclo, $feriados_array);

                if (isset($registros_reales[$fecha_ciclo])) {
                    $estado = $registros_reales[$fecha_ciclo]['estado_justificacion'] == 'Aprobada' ? 'Justificado' : $registros_reales[$fecha_ciclo]['estado'];
                    
                    if (strpos($estado, 'Feriado') !== false) {
                        $feriados_omitidos++;
                        continue;
                    }

                    if (strpos($estado, 'Puntual') !== false) $p++;
                    if (strpos($estado, 'Retraso') !== false) $r++;
                    if (strpos($estado, 'Falta') !== false) $f++;
                    if (strpos($estado, 'Justificado') !== false) $j++;
                    if (strpos($estado, 'Salida Temprana') !== false) $st++;
                    if (strpos($estado, 'Salida Irregular') !== false) $si++;
                    
                } else {
                    if ($fecha_ciclo < $fecha_hoy && $fecha_ciclo >= $fecha_ingreso_empleado) {
                        if ($es_feriado) {
                            $feriados_omitidos++;
                        } else {
                            $f++; 
                        }
                    }
                }
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
            
            $dia_semana = date('N', strtotime($fecha_ciclo)); 
            if ($dia_semana > 5 && !isset($registros_reales[$fecha_ciclo])) continue;

            $es_feriado = in_array($fecha_ciclo, $feriados_array);

            if (isset($registros_reales[$fecha_ciclo])) {
                $estado = $registros_reales[$fecha_ciclo]['estado_justificacion'] == 'Aprobada' ? 'Justificado' : $registros_reales[$fecha_ciclo]['estado'];
                
                if (strpos($estado, 'Feriado') !== false) {
                    $feriados_omitidos++;
                    continue;
                }

                if (strpos($estado, 'Puntual') !== false) $p++;
                if (strpos($estado, 'Retraso') !== false) $r++;
                if (strpos($estado, 'Falta') !== false) $f++;
                if (strpos($estado, 'Justificado') !== false) $j++;
                if (strpos($estado, 'Salida Temprana') !== false) $st++;
                if (strpos($estado, 'Salida Irregular') !== false) $si++;
                
            } else {
                if ($fecha_ciclo < $fecha_hoy && $fecha_ciclo >= $fecha_ingreso_empleado) {
                    if ($es_feriado) {
                        $feriados_omitidos++;
                    } else {
                        $f++; 
                    }
                }
            }
        }
    }

    echo json_encode([
        'puntual' => $p,
        'retraso' => $r,
        'salida_temprana' => $st,
        'salida_irregular' => $si,
        'falta' => $f,
        'justificado' => $j,
        'feriados_omitidos' => $feriados_omitidos 
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de Base de Datos: ' . $e->getMessage()]);
}
?>