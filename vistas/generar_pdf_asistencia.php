<?php
ob_start(); 
session_start();

if (!isset($_SESSION['logueado'])) {
    die("Acceso denegado. Inicie sesión.");
}

require_once '../configuracion/conexion.php';
require_once '../controladores/ControladorBitacora.php'; 
require_once '../recursos/librerias/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id_usuario_logueado = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];

$stmt_mi_per = $conexion->prepare("SELECT id_personal FROM personal WHERE id_usuario = ?");
$stmt_mi_per->execute([$id_usuario_logueado]);
$mi_id_personal = $stmt_mi_per->fetchColumn();

$id_personal = isset($_POST['id_personal']) ? $_POST['id_personal'] : (isset($_GET['id']) ? $_GET['id'] : 'todos');
$mes = isset($_POST['mes']) ? $_POST['mes'] : (isset($_GET['mes']) ? $_GET['mes'] : date('n'));
$anio = isset($_POST['anio']) ? (int)$_POST['anio'] : (isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y'));
$filtro_cargo = isset($_POST['id_cargo']) ? $_POST['id_cargo'] : 'todos'; 

if ($id_rol != 1 && $id_rol != 2) {
    if ($id_personal === 'todos' || $id_personal != $mi_id_personal) {
        die("<h2 style='color:red; text-align:center; margin-top:50px;'>ACCESO DENEGADO</h2>");
    }
}

$meses_es = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$nombre_mes = ($mes === 'todos') ? "TODO EL AÑO" : $meses_es[$mes - 1];

$cedula_log = $id_personal;
if ($id_personal != 'todos') {
    $stmt_ced = $conexion->prepare("SELECT cedula FROM personal WHERE id_personal = ?");
    $stmt_ced->execute([$id_personal]);
    $cedula_log = $stmt_ced->fetchColumn() ?: $id_personal;
}

$detalle_reporte = ($id_personal == 'todos') ? "Reporte General de Asistencia" : "Reporte Individual (C.I: {$cedula_log})";
if ($filtro_cargo !== 'todos' && $id_personal == 'todos') {
    $detalle_reporte .= " (Filtrado por cargo)";
}

$hash_pdf = md5($id_personal . $mes . $anio . $filtro_cargo);
$tiempo_actual = time();

if (!isset($_SESSION['bloqueo_pdf_'.$hash_pdf]) || ($tiempo_actual - $_SESSION['bloqueo_pdf_'.$hash_pdf]) > 15) {
    ControladorBitacora::registrar($conexion, $_SESSION['id_usuario'], 'Reportes', 'Descarga de Reporte PDF', "Descargó $detalle_reporte correspondiente a: $nombre_mes de $anio.");
    $_SESSION['bloqueo_pdf_'.$hash_pdf] = $tiempo_actual;
    session_write_close(); 
} else {
    session_write_close(); 
}

$stmt_config = $conexion->query("SELECT hora_entrada_general, hora_salida_general FROM configuracion LIMIT 1");
$configuracion = $stmt_config->fetch(PDO::FETCH_ASSOC);

$opciones = new Options();
$opciones->set('isRemoteEnabled', true);
$opciones->set('debugPng', false);
$dompdf = new Dompdf($opciones);

$ruta_simoncito = '../recursos/img/simoncito.jpg'; 
if(!file_exists($ruta_simoncito)) $ruta_simoncito = '../recursos/img/simoncito.png';
$base64_simoncito = file_exists($ruta_simoncito) ? 'data:image/' . pathinfo($ruta_simoncito, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($ruta_simoncito)) : '';

$ruta_escudo = '../recursos/img/logo mejorado.jpg'; 
$base64_escudo = file_exists($ruta_escudo) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($ruta_escudo)) : '';

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia CEIS Julian Yánez</title>
    <style>
        * { font-family: "Helvetica", "Arial", sans-serif; }
        body { font-size: 11px; color: #333; margin: 0; padding: 0; padding-bottom: 70px; }
        .encabezado { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .tabla-encabezado { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .col-logo-izq { width: 90px; text-align: left; vertical-align: middle; }
        .col-texto-central { width: auto; text-align: center; vertical-align: middle; }
        .col-logo-der { width: 90px; text-align: right; vertical-align: middle; }
        .logo-simoncito { width: 75px; }
        .logo-escudo { width: 70px; }
        .texto-ministerio { font-weight: bold; font-size: 12px; line-height: 1.4; white-space: nowrap; }
        .lema { font-size: 10px; font-style: italic; margin-top: 5px; }
        .titulo-reporte { text-align: center; font-size: 15px; font-weight: bold; margin: 15px 0; background-color: #f1f5f9; padding: 10px; border: 1px solid #cbd5e1; text-transform: uppercase; }
        .caja-datos { background: #fafafa; padding: 15px; border: 1px solid #e2e8f0; margin-bottom: 15px; border-radius: 4px; }
        .foto-perfil { width: 120px; height: 155px; border-radius: 6px; border: 1px solid #94a3b8; object-fit: cover; }
        .caja-evaluacion { font-family: "Helvetica", "Arial", sans-serif; padding: 12px; border: 1px solid #ccc; font-weight: bold; text-align: center; font-size: 13px; margin-bottom: 15px; border-radius: 5px; letter-spacing: 0.5px; }
        .grafico-contenedor { width: 100%; margin-bottom: 20px; border-collapse: collapse; table-layout: fixed; }
        .grafico-contenedor td { padding: 6px; font-size: 11px; font-weight: bold; vertical-align: middle; }
        .barra-fondo { width: 100%; background-color: #e2e8f0; height: 14px; border-radius: 7px; position: relative; overflow: hidden; }
        .barra-color { height: 14px; border-radius: 7px; text-align: right; color: white; font-size: 10px; line-height: 14px; padding-right: 5px; box-sizing: border-box; }
        .tabla-datos { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .tabla-datos th, .tabla-datos td { border: 1px solid #94a3b8; padding: 6px; text-align: center; }
        .tabla-datos th { background-color: #e2e8f0; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .texto-izq { text-align: left !important; }
        .alerta-roja { color: #dc2626; font-weight: bold; }
        .bloque-intocable { page-break-inside: avoid; }
        .caja-analitica { border: 1px solid #cbd5e1; border-radius: 6px; padding: 15px; margin-bottom: 20px; background-color: #ffffff; }
        .titulo-seccion { font-size: 12px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; text-transform: uppercase; color: #475569; }
        .comparativa-texto { font-size: 11px; margin-bottom: 5px; }
        .barra-comparativa-fondo { width: 100%; background-color: #f1f5f9; height: 20px; border-radius: 3px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
        .barra-comparativa-fill { height: 20px; border-radius: 3px; text-align: right; padding-right: 5px; color: white; font-weight: bold; line-height: 20px; }
        .firmas-footer { position: absolute; bottom: 5px; left: 0; right: 0; width: 100%; text-align: center; }
        .firmas { width: 100%; text-align: center; margin: 0; }
        .linea-firma { border-top: 1px solid #000; width: 220px; margin: 0 auto; padding-top: 5px; font-weight: bold;}
    </style>
</head>
<body>

<div class="encabezado">
    <table class="tabla-encabezado">
        <tr>
            <td class="col-logo-izq">' . ($base64_simoncito ? '<img src="' . $base64_simoncito . '" class="logo-simoncito">' : 'LOGO') . '</td>
            <td class="col-texto-central">
                <div class="texto-ministerio">
                    REPÚBLICA BOLIVARIANA DE VENEZUELA<br>
                    MINISTERIO DEL PODER POPULAR PARA LA EDUCACIÓN<br>
                    CENTRO DE EDUCACIÓN INICIAL<br>
                    SIMONCITO “JULIAN YANEZ”
                </div>
                <div class="lema">Educación Inicial Compromiso de Todos!</div>
            </td>
            <td class="col-logo-der">' . ($base64_escudo ? '<img src="' . $base64_escudo . '" class="logo-escudo">' : 'ESCUDO') . '</td>
        </tr>
    </table>
</div>';

if ($id_personal != 'todos') {
    $sql_emp = "SELECT p.nombres, p.apellidos, p.cedula, p.telefono, p.foto_perfil, p.fecha_ingreso, p.hora_entrada_personalizada, p.hora_salida_personalizada, c.nombre_cargo 
                FROM personal p INNER JOIN cargos c ON p.id_cargo = c.id_cargo WHERE p.id_personal = ?";
    $stmt_emp = $conexion->prepare($sql_emp);
    $stmt_emp->execute([$id_personal]);
    $emp = $stmt_emp->fetch(PDO::FETCH_ASSOC);

    $fecha_ing_emp = $emp['fecha_ingreso'] ?: '2000-01-01'; // Respaldo por si falla el SQL

    $ruta_foto = '../recursos/img/perfiles/' . $emp['foto_perfil'];
    if (!file_exists($ruta_foto) || empty($emp['foto_perfil'])) { $ruta_foto = '../recursos/img/perfiles/default.png'; }
    $base64_foto = file_exists($ruta_foto) ? 'data:image/' . pathinfo($ruta_foto, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($ruta_foto)) : '';

    $hora_ent = $emp['hora_entrada_personalizada'] ? $emp['hora_entrada_personalizada'] : $configuracion['hora_entrada_general'];
    $hora_sal = $emp['hora_salida_personalizada'] ? $emp['hora_salida_personalizada'] : $configuracion['hora_salida_general'];
    $horario_texto = date('h:i A', strtotime($hora_ent)) . ' a ' . date('h:i A', strtotime($hora_sal));

    $html .= '<div class="titulo-reporte">EXPEDIENTE DE ASISTENCIA - ' . $nombre_mes . ' ' . $anio . '</div>';
    
    $html .= '
    <div class="caja-datos">
        <table style="width: 100%; border: none; padding: 0; margin: 0; border-collapse: collapse;">
            <tr>
                <td style="width: 130px; text-align: center; vertical-align: middle; padding: 0;">' . ($base64_foto ? '<img src="' . $base64_foto . '" class="foto-perfil">' : '[FOTO]') . '</td>
                <td style="vertical-align: middle; padding-left: 20px;">
                    <table style="width: 100%; border: none; font-size: 13px; line-height: 2;">
                        <tr><td style="width: 50%;"><strong>Empleado:</strong> ' . htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']) . '</td><td style="width: 50%;"><strong>Cédula:</strong> ' . htmlspecialchars($emp['cedula']) . '</td></tr>
                        <tr><td><strong>Cargo:</strong> ' . htmlspecialchars($emp['nombre_cargo']) . '</td><td><strong>Teléfono:</strong> ' . htmlspecialchars($emp['telefono']) . '</td></tr>
                        <tr><td colspan="2"><strong>Horario Asignado:</strong> ' . $horario_texto . '</td></tr>
                        <tr><td colspan="2"><strong>Día de Emisión:</strong> ' . date('d/m/Y') . '</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>';

    if ($mes !== 'todos') {
        $stmt_asis = $conexion->prepare("SELECT fecha, estado, estado_justificacion, motivo_justificacion FROM asistencias WHERE id_personal = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?");
        $stmt_asis->execute([$id_personal, $mes, $anio]);
        
        $registros_reales = [];
        while($row = $stmt_asis->fetch(PDO::FETCH_ASSOC)) { $registros_reales[$row['fecha']] = $row; }

        $p = 0; $r = 0; $f = 0; $st = 0; $si = 0; $j = 0;
        $dias_del_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        $dias_evaluados = 0;
        $tabla_html = '';
        $fecha_hoy = date('Y-m-d'); 

        for ($d = 1; $d <= $dias_del_mes; $d++) {
            $fecha_ciclo = sprintf("%04d-%02d-%02d", $anio, $mes, $d);
            if ($fecha_ciclo > $fecha_hoy) break;

            $dia_semana = date('N', strtotime($fecha_ciclo)); 
            if ($dia_semana > 5 && !isset($registros_reales[$fecha_ciclo])) continue;

            $dias_evaluados++;
            $fecha_format = date('d/m/Y', strtotime($fecha_ciclo));

            if (isset($registros_reales[$fecha_ciclo])) {
                $a = $registros_reales[$fecha_ciclo];
                $estado = $a['estado_justificacion'] == 'Aprobada' ? 'Justificado' : $a['estado'];
                $motivo = !empty($a['motivo_justificacion']) ? htmlspecialchars($a['motivo_justificacion']) : '-';
                
                if (strpos($estado, 'Puntual') !== false) $p++;
                if (strpos($estado, 'Retraso') !== false) $r++;
                if (strpos($estado, 'Falta') !== false) $f++;
                if (strpos($estado, 'Justificado') !== false) $j++;
                if (strpos($estado, 'Salida Temprana') !== false) $st++;
                if (strpos($estado, 'Salida Irregular') !== false) $si++;

                $color = (strpos($estado, 'Falta') !== false || strpos($estado, 'Irregular') !== false) ? 'class="alerta-roja"' : '';
                $tabla_html .= "<tr><td>{$fecha_format}</td><td {$color}>{$estado}</td><td class='texto-izq'>{$motivo}</td></tr>";
            } else {
                // === CORRECCIÓN AQUÍ ===
                if ($fecha_ciclo < $fecha_hoy && $fecha_ciclo >= $fecha_ing_emp) {
                    $f++; $tabla_html .= "<tr><td>{$fecha_format}</td><td class='alerta-roja'>Falta</td><td class='texto-izq alerta-roja'>Inasistencia automática</td></tr>";
                }
            }
        }

        $porc_p = $dias_evaluados > 0 ? round(($p / $dias_evaluados) * 100) : 0;
        $porc_r = $dias_evaluados > 0 ? round(($r / $dias_evaluados) * 100) : 0;
        $porc_f = $dias_evaluados > 0 ? round(($f / $dias_evaluados) * 100) : 0;
        $porc_st = $dias_evaluados > 0 ? round(($st / $dias_evaluados) * 100) : 0;
        $porc_si = $dias_evaluados > 0 ? round(($si / $dias_evaluados) * 100) : 0;

        if ($f == 0 && $r == 0 && $si == 0) { $msg_eval = "Asistencia PERFECTA este mes. ¡Excelente!"; $bg_eval = "#dcfce7"; $col_eval = "#166534"; } 
        elseif ($f == 0 && ($r > 0 || $st > 0)) { $msg_eval = "Asistencia SATISFACTORIA, presentando incidencias menores."; $bg_eval = "#fef3c7"; $col_eval = "#92400e"; } 
        elseif ($f > 0 && $f <= 2) { $msg_eval = "Asistencia REGULAR. Se recomienda mejorar la constancia."; $bg_eval = "#ffedd5"; $col_eval = "#c2410c"; } 
        else { $msg_eval = "Requiere supervisión debido a múltiples faltas y/o salidas irregulares."; $bg_eval = "#fee2e2"; $col_eval = "#991b1b"; }

        $html .= "<div class='caja-evaluacion' style='background: {$bg_eval}; color: {$col_eval}; border-color: {$col_eval};'>ATENCIÓN: {$msg_eval}</div>";

        $html_barra_p = $porc_p > 0 ? '<div class="barra-color" style="width: '.$porc_p.'%; background-color: #10b981;">'.$porc_p.'%</div>' : '';
        $html_barra_r = $porc_r > 0 ? '<div class="barra-color" style="width: '.$porc_r.'%; background-color: #f59e0b;">'.$porc_r.'%</div>' : '';
        $html_barra_f = $porc_f > 0 ? '<div class="barra-color" style="width: '.$porc_f.'%; background-color: #ef4444;">'.$porc_f.'%</div>' : '';
        $html_barra_st = $porc_st > 0 ? '<div class="barra-color" style="width: '.$porc_st.'%; background-color: #3b82f6;">'.$porc_st.'%</div>' : '';
        $html_barra_si = $porc_si > 0 ? '<div class="barra-color" style="width: '.$porc_si.'%; background-color: #991b1b;">'.$porc_si.'%</div>' : '';

        $html .= '<table class="grafico-contenedor">
            <tr><td style="width: 30%;">Llegada Puntual ('.$p.')</td><td style="width: 70%;"><div class="barra-fondo">'.$html_barra_p.'</div></td></tr>
            <tr><td>Llegada Tardía ('.$r.')</td><td><div class="barra-fondo">'.$html_barra_r.'</div></td></tr>
            <tr><td>Salida Temprana ('.$st.')</td><td><div class="barra-fondo">'.$html_barra_st.'</div></td></tr>
            <tr><td>Salida Irregular ('.$si.')</td><td><div class="barra-fondo">'.$html_barra_si.'</div></td></tr>
            <tr><td>Falta Completa ('.$f.')</td><td><div class="barra-fondo">'.$html_barra_f.'</div></td></tr>
        </table>';

        $html .= '<table class="tabla-datos"><thead><tr><th style="width: 15%;">Fecha</th><th style="width: 25%;">Estado Exacto</th><th style="width: 60%;">Observación / Motivo</th></tr></thead><tbody>';
        $html .= $tabla_html;
        $html .= '</tbody></table>';

        $stmt_total_personal = $conexion->query("SELECT COUNT(*) FROM personal INNER JOIN usuarios ON personal.id_usuario = usuarios.id_usuario WHERE usuarios.estado = 'Activo'");
        $total_empleados = $stmt_total_personal->fetchColumn();
        $stmt_media = $conexion->prepare("SELECT COUNT(*) FROM asistencias WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND estado NOT LIKE '%Falta%'");
        $stmt_media->execute([$mes, $anio]);
        $total_asistencias_institucion = $stmt_media->fetchColumn();
        
        $media_institucional_asistencias = $total_empleados > 0 ? round($total_asistencias_institucion / $total_empleados) : 0;
        $porc_media_inst = $dias_evaluados > 0 ? round(($media_institucional_asistencias / $dias_evaluados) * 100) : 0;
        if($porc_media_inst > 100) $porc_media_inst = 100;
        
        $porc_emp_efectiva = round((($p + $r + $j) / $dias_evaluados) * 100);
        if($porc_emp_efectiva > 100) $porc_emp_efectiva = 100;
        
        $color_emp = $porc_emp_efectiva < $porc_media_inst ? "#ef4444" : ($porc_emp_efectiva == $porc_media_inst ? "#f59e0b" : "#10b981");
        $texto_ranking = $porc_emp_efectiva < $porc_media_inst ? "Por debajo de la media institucional." : ($porc_emp_efectiva == $porc_media_inst ? "Igual a la media de la institución." : "Superior a la media de la institución.");

        $html .= '<div class="bloque-intocable"><div class="titulo-reporte" style="margin-top: 10px;">ANÁLISIS DE DESEMPEÑO</div>
        <div class="caja-analitica">
            <div class="titulo-seccion">Comparativa de Asistencia Efectiva</div>
            <div class="comparativa-texto"><strong>Media de la Institución:</strong> '.$porc_media_inst.'% de asistencia promedio.</div>
            <div class="barra-comparativa-fondo">'.($porc_media_inst > 0 ? '<div class="barra-comparativa-fill" style="width: '.$porc_media_inst.'%; background-color: #64748b;">'.$porc_media_inst.'%</div>' : '').'</div>
            <div class="comparativa-texto" style="margin-top: 15px;"><strong>Desempeño de '.htmlspecialchars($emp['nombres']).':</strong> '.$porc_emp_efectiva.'% de asistencia.</div>
            <div class="barra-comparativa-fondo">'.($porc_emp_efectiva > 0 ? '<div class="barra-comparativa-fill" style="width: '.$porc_emp_efectiva.'%; background-color: '.$color_emp.';">'.$porc_emp_efectiva.'%</div>' : '').'</div>
            <div style="margin-top: 15px; font-style: italic; font-size: 11px; color: '.$color_emp.';"><strong>Conclusión del Sistema:</strong> '.$texto_ranking.'</div>
        </div></div>';

    } else {
        $html .= '<table class="tabla-datos"><thead><tr><th class="texto-izq">Mes</th><th>Puntual</th><th>Retraso</th><th>S.Temp</th><th>S.Irreg</th><th>Falta</th><th>Justif.</th></tr></thead><tbody>';
        for($m = 1; $m <= 12; $m++) {
            $stmt_stats = $conexion->prepare("SELECT estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?");
            $stmt_stats->execute([$id_personal, $m, $anio]);
            $p = 0; $r = 0; $f = 0; $j = 0; $st = 0; $si = 0;
            while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
                if ($row['estado_justificacion'] == 'Aprobada') { $j++; } 
                else {
                    $est = $row['estado'];
                    if (strpos($est, 'Puntual') !== false) $p++;
                    if (strpos($est, 'Retraso') !== false) $r++;
                    if (strpos($est, 'Falta') !== false) $f++;
                    if (strpos($est, 'Justificado') !== false) $j++;
                    if (strpos($est, 'Salida Temprana') !== false) $st++;
                    if (strpos($est, 'Salida Irregular') !== false) $si++;
                }
            }
            $html .= "<tr><td class='texto-izq'>{$meses_es[$m-1]}</td><td>{$p}</td><td>{$r}</td><td style='color:#3b82f6;font-weight:bold;'>{$st}</td><td style='color:#991b1b;font-weight:bold;'>{$si}</td><td class='alerta-roja'>{$f}</td><td>{$j}</td></tr>";
        }
        $html .= '</tbody></table>';
    }

} else {
    $nombre_cargo_filtro = "";
    $sql_personal = "SELECT p.id_personal, p.nombres, p.apellidos, p.fecha_ingreso, c.nombre_cargo 
                     FROM personal p 
                     INNER JOIN cargos c ON p.id_cargo = c.id_cargo 
                     INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
                     WHERE u.estado = 'Activo'";
    
    if ($filtro_cargo !== 'todos') {
        $sql_personal .= " AND p.id_cargo = " . (int)$filtro_cargo;
        $stmt_nom_cargo = $conexion->prepare("SELECT nombre_cargo FROM cargos WHERE id_cargo = ?");
        $stmt_nom_cargo->execute([$filtro_cargo]);
        $nombre_cargo_filtro = " - " . mb_strtoupper($stmt_nom_cargo->fetchColumn());
    }
    $sql_personal .= " ORDER BY p.nombres ASC";
    
    $stmt_personal = $conexion->query($sql_personal);
    $personal = $stmt_personal->fetchAll(PDO::FETCH_ASSOC);

    $html .= '<div class="titulo-reporte">REPORTE GENERAL' . $nombre_cargo_filtro . ' - ' . $nombre_mes . ' ' . $anio . '</div>';
    $html .= '<div style="margin-bottom: 15px; font-size: 12px; text-align: left;"><strong>Día de Emisión:</strong> ' . date('d/m/Y') . ' | <strong>Empleados Evaluados:</strong> ' . count($personal) . '</div>';
    
    $html .= '<table class="tabla-datos"><thead><tr><th class="texto-izq">Empleado</th><th>Puntual</th><th>Retraso</th><th>S.Temp</th><th>S.Irreg</th><th>Falta</th><th>Justif.</th></tr></thead><tbody>';

    $total_p_gen = 0; $total_r_gen = 0; $total_f_gen = 0; $total_st_gen = 0; $total_si_gen = 0; $total_j_gen = 0;
    $dias_del_mes = ($mes === 'todos') ? 0 : cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
    $fecha_hoy = date('Y-m-d'); 

    foreach ($personal as $per) {
        $id_p = $per['id_personal'];
        $fecha_ing_per = $per['fecha_ingreso'] ?: '2000-01-01'; // Respaldo seguro
        
        $p = 0; $r = 0; $f = 0; $st = 0; $si = 0; $j = 0;

        if($mes === 'todos') {
            $stmt_stats = $conexion->prepare("SELECT estado, estado_justificacion FROM asistencias WHERE id_personal = ? AND YEAR(fecha) = ?");
            $stmt_stats->execute([$id_p, $anio]);
            while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
                if ($row['estado_justificacion'] == 'Aprobada') { $j++; } 
                else {
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
            $stmt_stats->execute([$id_p, $mes, $anio]);
            
            $registros_reales = [];
            while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
                $registros_reales[$row['fecha']] = $row;
            }

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
                    // === CORRECCIÓN AQUÍ ===
                    if ($fecha_ciclo < $fecha_hoy && $fecha_ciclo >= $fecha_ing_per) {
                        $f++; 
                    }
                }
            }
        }
        
        $total_p_gen += $p; $total_r_gen += $r; $total_f_gen += $f; $total_st_gen += $st; $total_si_gen += $si; $total_j_gen += $j;

        $html .= "<tr><td class='texto-izq'>{$per['nombres']} {$per['apellidos']}</td><td>{$p}</td><td>{$r}</td><td style='color:#3b82f6;font-weight:bold;'>{$st}</td><td style='color:#991b1b;font-weight:bold;'>{$si}</td><td class='alerta-roja'>{$f}</td><td>{$j}</td></tr>";
    }
    $html .= '</tbody></table>';

    if ($mes !== 'todos' && count($personal) > 0) {
        $total_eventos = $total_p_gen + $total_r_gen + $total_f_gen + $total_j_gen; 
        if ($total_eventos > 0) {
            $porc_asistencia_gen = round((($total_p_gen + $total_r_gen + $total_j_gen) / $total_eventos) * 100);
            $porc_faltas_gen = round(($total_f_gen / $total_eventos) * 100);
            
            $color_gen = $porc_asistencia_gen >= 80 ? "#10b981" : ($porc_asistencia_gen >= 60 ? "#f59e0b" : "#ef4444");
            $texto_evaluacion = $porc_asistencia_gen >= 80 ? "Óptimo. El grupo mantiene un nivel de asistencia corporativa excelente." : 
                                ($porc_asistencia_gen >= 60 ? "Regular. Se requiere atención en la puntualidad y asistencia del grupo." : 
                                "Crítico. Alto índice de ausentismo en el grupo evaluado.");

            $html .= '<div class="bloque-intocable">';
            $html .= '<div class="titulo-reporte" style="margin-top: 10px;">ANÁLISIS DE DESEMPEÑO GRUPAL</div>';
            $html .= '
            <div class="caja-analitica">
                <div class="titulo-seccion">Métricas Globales del Segmento Evaluado</div>
                
                <div class="comparativa-texto"><strong>Asistencia Efectiva del Grupo:</strong> '.$porc_asistencia_gen.'%</div>
                <div class="barra-comparativa-fondo">
                    '.($porc_asistencia_gen > 0 ? '<div class="barra-comparativa-fill" style="width: '.$porc_asistencia_gen.'%; background-color: '.$color_gen.';">'.$porc_asistencia_gen.'%</div>' : '').'
                </div>
                
                <div class="comparativa-texto" style="margin-top: 15px;"><strong>Índice de Ausentismo (Faltas Completas):</strong> '.$porc_faltas_gen.'%</div>
                <div class="barra-comparativa-fondo">
                    '.($porc_faltas_gen > 0 ? '<div class="barra-comparativa-fill" style="width: '.$porc_faltas_gen.'%; background-color: #ef4444;">'.$porc_faltas_gen.'%</div>' : '').'
                </div>
                
                <div style="margin-top: 15px; font-style: italic; font-size: 11px; color: '.$color_gen.';">
                    <strong>Conclusión del Sistema:</strong> '.$texto_evaluacion.'
                </div>
            </div>';
            $html .= '</div>';
        }
    }
}

$html .= '
<div class="firmas-footer">
    <table class="firmas">
        <tr>
            <td style="width: 50%;">
                <div class="linea-firma">Firma del Director(a)</div>
                <span style="font-size: 10px; color: #666;">Sello de la Institución</span>
            </td>
            <td style="width: 50%;">
                <div class="linea-firma">Firma de la Subdirectora</div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

$identificador = ($id_personal == 'todos') ? ($filtro_cargo == 'todos' ? "General" : "Filtrado") : $emp['cedula'];

ob_end_clean(); 
$dompdf->stream("Reporte_{$identificador}_{$nombre_mes}_{$anio}.pdf", array("Attachment" => false));
?>