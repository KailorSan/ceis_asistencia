<?php
session_start();
require_once '../configuracion/conexion.php';

header('Content-Type: application/json');

// Medida de seguridad básica
if (!isset($_SESSION['logueado'])) {
    echo json_encode(['success' => false, 'msg' => 'No autorizado']);
    exit;
}

date_default_timezone_set('America/Caracas');
$hoy = date('Y-m-d');
$hora_actual_sec = date('H:i:s');

// Optimización: Si ya se ejecutó el cronjob hoy en esta sesión, no hacer nada para ahorrar recursos
if (isset($_SESSION['cron_ejecutado']) && $_SESSION['cron_ejecutado'] === $hoy) {
    echo json_encode(['success' => true, 'msg' => 'Mantenimiento ya realizado hoy.', 'procesado' => false]);
    exit;
}

$faltas_creadas = 0;
$salidas_cerradas = 0;

try {
    // 1. Obtener configuración general de horarios
    $stmt_conf = $conexion->query("SELECT hora_entrada_general, hora_salida_general FROM configuracion LIMIT 1");
    $config = $stmt_conf->fetch(PDO::FETCH_ASSOC);

    // =========================================================================================
    // FASE A: CERRAR SALIDAS OLVIDADAS (Salida Irregular)
    // =========================================================================================
    $sql_incompletos = "SELECT a.id_asistencia, a.estado, a.fecha, p.hora_salida_personalizada 
                        FROM asistencias a
                        INNER JOIN personal p ON a.id_personal = p.id_personal
                        WHERE a.hora_salida IS NULL 
                        AND a.hora_entrada IS NOT NULL /* 🛡️ REGLA 1: Solo si entraron físicamente */
                        AND a.estado != 'Falta'
                        AND a.estado != 'Justificado'  /* 🛡️ REGLA 2: Ignora días justificados por completo */
                        AND a.estado NOT LIKE '%Salida Irregular%'
                        AND a.estado NOT LIKE '%Salida Temprana%' /* 🛡️ REGLA 3: Ignora si ya le justificaron la salida */
                        AND a.estado NOT LIKE '%Feriado%'
                        AND (a.estado_justificacion IS NULL OR a.estado_justificacion != 'Pendiente')";
    
    $incompletos = $conexion->query($sql_incompletos)->fetchAll(PDO::FETCH_ASSOC);

    $upd_irr = $conexion->prepare("UPDATE asistencias SET estado = ?, observacion = 'El sistema cerró la jornada automáticamente por omisión de salida.' WHERE id_asistencia = ?");

    foreach ($incompletos as $inc) {
        $h_salida_p    = !empty($inc['hora_salida_personalizada']) ? $inc['hora_salida_personalizada'] : $config['hora_salida_general'];
        $limite_salida = date('H:i:s', strtotime("+60 minutes", strtotime($h_salida_p))); // Damos 1 hora de gracia para salir

        // Si la fecha es de ayer o días anteriores, O si es hoy pero ya pasó la hora límite
        if ($inc['fecha'] < $hoy || ($inc['fecha'] == $hoy && $hora_actual_sec > $limite_salida)) {
            $estado_actual = $inc['estado'];
            $nuevo_estado  = 'Salida Irregular';
            
            // Combinar con el estado de entrada
            if (strpos($estado_actual, 'Retraso') !== false) {
                $nuevo_estado = 'Retraso y Salida Irregular';
            } elseif (strpos($estado_actual, 'Puntual') !== false) {
                $nuevo_estado = 'Puntual y Salida Irregular';
            }

            $upd_irr->execute([$nuevo_estado, $inc['id_asistencia']]);
            $salidas_cerradas++;
        }
    }

    // =========================================================================================
    // FASE B: GENERAR FALTAS AUTOMÁTICAS (Empleados que no fueron a trabajar)
    // =========================================================================================
    
    // Obtenemos los feriados de los últimos 7 días para saltarlos
    $stmt_fer = $conexion->query("SELECT fecha FROM feriados WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $feriados = $stmt_fer->fetchAll(PDO::FETCH_COLUMN);

    // Obtenemos personal activo
    $sql_activos = "SELECT p.id_personal, p.fecha_ingreso, p.hora_entrada_personalizada 
                    FROM personal p 
                    INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                    WHERE u.estado = 'Activo'";
    $empleados = $conexion->query($sql_activos)->fetchAll(PDO::FETCH_ASSOC);

    // Fechas a evaluar: Últimos 5 días (sin contar hoy)
    $fechas_evaluar = [];
    for ($i = 1; $i <= 5; $i++) {
        $f = date('Y-m-d', strtotime("-$i days"));
        $dia_sem = date('N', strtotime($f));
        // Si no es fin de semana (1 al 5) y tampoco está en la lista de feriados
        if ($dia_sem < 6 && !in_array($f, $feriados)) {
            $fechas_evaluar[] = $f;
        }
    }

    $ins_falta = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, estado, observacion) VALUES (?, ?, ?, 'Falta', 'Falta generada automáticamente por inasistencia')");
    $check_asistencia = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");

    foreach ($empleados as $emp) {
        $f_ingreso = $emp['fecha_ingreso'] ?: '2000-01-01';
        $h_ent = $emp['hora_entrada_personalizada'] ?: $config['hora_entrada_general'];

        foreach ($fechas_evaluar as $fe) {
            // Solo evaluamos si el empleado ya había ingresado a la institución en esa fecha
            if ($fe >= $f_ingreso) {
                // Comprobamos si tiene algún registro ese día (Asistencia, Feriado manual o Justificación)
                $check_asistencia->execute([$emp['id_personal'], $fe]);
                if (!$check_asistencia->fetch()) {
                    // Si está totalmente vacío el día, le clavamos la falta
                    $ins_falta->execute([$emp['id_personal'], $fe, $h_ent]);
                    $faltas_creadas++;
                }
            }
        }
    }

    // Marcamos que ya se hizo el mantenimiento por hoy en esta sesión
    $_SESSION['cron_ejecutado'] = $hoy;

    echo json_encode([
        'success' => true, 
        'msg' => 'Mantenimiento completado.', 
        'procesado' => true,
        'salidas_cerradas' => $salidas_cerradas,
        'faltas_creadas' => $faltas_creadas
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'msg' => 'Error BD: ' . $e->getMessage()]);
}
?>