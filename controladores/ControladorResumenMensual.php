<?php
// ob_start() absorbe cualquier espacio en blanco o error oculto para que no rompa el JSON
ob_start();
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1. Verificación básica de sesión
if (!isset($_SESSION['logueado'])) {
    ob_end_clean();
    echo json_encode(['error' => 'Acceso denegado. Inicie sesión.']);
    exit;
}

require_once '../configuracion/conexion.php';

// Recibir los parámetros de la URL
$id_personal_solicitado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

if ($id_personal_solicitado === 0) {
    ob_end_clean();
    echo json_encode(['error' => 'ID de personal inválido']);
    exit;
}

// 2. OBTENER EL ID DEL USUARIO QUE ESTÁ NAVEGANDO (RBAC)
$id_usuario_logueado = $_SESSION['id_usuario'];
$stmt_mi_per = $conexion->prepare("SELECT id_personal FROM personal WHERE id_usuario = ?");
$stmt_mi_per->execute([$id_usuario_logueado]);
$mi_id_personal = $stmt_mi_per->fetchColumn();

// 3. BARRERA DE SEGURIDAD INTELIGENTE
$id_rol = $_SESSION['id_rol'];
if ($id_rol != 1 && $id_rol != 2) { // Si NO es Administrador ni Directivo...
    if ($id_personal_solicitado != $mi_id_personal) { // ...y trata de ver a otra persona
        ob_end_clean();
        echo json_encode(['error' => 'ACCESO DENEGADO: Solo puedes ver tus propias estadísticas.']);
        exit;
    }
}

try {
    $puntual = 0;
    $retraso = 0;
    $falta = 0;
    $justificado = 0;

    if ($mes === 'todos') {
        // --- LÓGICA ANUAL ---
        $stmt_stats = $conexion->prepare("SELECT estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND YEAR(fecha) = ?");
        $stmt_stats->execute([$id_personal_solicitado, $anio]);
        
        while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
            if ($row['estado_justificacion'] == 'Aprobada') {
                $justificado++;
            } else {
                if ($row['estado'] == 'Puntual') $puntual++;
                if ($row['estado'] == 'Retraso') $retraso++;
                if ($row['estado'] == 'Falta') $falta++;
                if ($row['estado'] == 'Justificado') $justificado++;
            }
        }
    } else {
        // --- LÓGICA MENSUAL (CON RELLENO INTELIGENTE DE FALTAS) ---
        $stmt_stats = $conexion->prepare("SELECT fecha, estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?");
        $stmt_stats->execute([$id_personal_solicitado, $mes, $anio]);
        
        $registros_reales = [];
        while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
            $registros_reales[$row['fecha']] = $row;
        }

        $dias_del_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $fecha_hoy = date('Y-m-d'); // FECHA ACTUAL PARA BLOQUEAR EL FUTURO
        
        for ($d = 1; $d <= $dias_del_mes; $d++) {
            $fecha_ciclo = sprintf("%04d-%02d-%02d", $anio, $mes, $d);
            
            // Si la fecha del ciclo es mayor a la fecha de hoy, rompemos el bucle
            if ($fecha_ciclo > $fecha_hoy) {
                break;
            }

            $dia_semana = date('N', strtotime($fecha_ciclo)); 
            
            // Ignoramos fines de semana si no hay registros
            if ($dia_semana > 5 && !isset($registros_reales[$fecha_ciclo])) {
                continue; 
            }

            if (isset($registros_reales[$fecha_ciclo])) {
                $estado = $registros_reales[$fecha_ciclo]['estado_justificacion'] == 'Aprobada' ? 'Justificado' : $registros_reales[$fecha_ciclo]['estado'];
                
                if ($estado == 'Puntual') $puntual++;
                elseif ($estado == 'Retraso') $retraso++;
                elseif ($estado == 'Falta') $falta++;
                elseif ($estado == 'Justificado') $justificado++;
            } else {
                // Si es un día hábil y no hay registro, se cuenta como falta
                $falta++;
            }
        }
    }

    // Limpiamos el buffer para garantizar que solo salga el JSON
    ob_end_clean();
    echo json_encode([
        'puntual' => $puntual,
        'retraso' => $retraso,
        'falta' => $falta,
        'justificado' => $justificado
    ]);
    exit;

} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(['error' => 'Error de base de datos']);
    exit;
}
?>