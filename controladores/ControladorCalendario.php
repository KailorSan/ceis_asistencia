<?php
session_start();
require_once '../configuracion/conexion.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    die("<p style='color:red; text-align:center;'>Acceso denegado.</p>");
}

$id_personal = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$es_admin = isset($_GET['admin']) && $_GET['admin'] === 'true';
// CORRECCIÓN FLECHAS: Recibimos el ID exacto del contenedor desde JS
$id_contenedor = isset($_GET['contenedor']) ? htmlspecialchars($_GET['contenedor']) : 'contenedor-calendario-inline';

$meses_es = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$nombre_mes = $meses_es[$mes - 1];

$primer_dia = mktime(0, 0, 0, $mes, 1, $anio);
$dias_en_mes = date('t', $primer_dia);
$dia_semana_inicio = date('N', $primer_dia); 

date_default_timezone_set('America/Caracas');
$fecha_hoy_str = date('Y-m-d');
$mes_actual_real = (int)date('n');
$anio_actual_real = (int)date('Y');

// LÓGICA DEL PASADO: Buscar el "Día 1" del sistema
$stmt_inicio = $conexion->query("SELECT MIN(fecha) FROM asistencias");
$fecha_inicio_sistema = $stmt_inicio->fetchColumn();
if (!$fecha_inicio_sistema) {
    $fecha_inicio_sistema = $fecha_hoy_str; // Si está vacío, el Día 1 es hoy
}
$mes_inicio = (int)date('n', strtotime($fecha_inicio_sistema));
$anio_inicio = (int)date('Y', strtotime($fecha_inicio_sistema));

// Consultar asistencia
$asistencias = [];
try {
    $sql = "SELECT fecha, estado, estado_justificacion, motivo_justificacion, archivo_evidencia 
            FROM asistencias 
            WHERE id_personal = :id AND MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_personal, ':mes' => $mes, ':anio' => $anio]);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $estado_final = $row['estado'];
        if ($row['estado_justificacion'] == 'Aprobada') {
            $estado_final = 'Justificado';
        }
        $asistencias[$row['fecha']] = [
            'estado' => $estado_final,
            'motivo' => $row['motivo_justificacion'] ?? '',
            'archivo' => $row['archivo_evidencia'] ?? ''
        ];
    }
} catch (PDOException $e) {
    die("<p style='text-align:center; color:red;'>Error al consultar la BD.</p>");
}

// CONTROL DE FLECHAS (Ocultar si vamos más allá del Día 1 o del futuro)
$btn_anterior = '<div style="inline-size: 34px;"></div>';
if ($anio > $anio_inicio || ($anio == $anio_inicio && $mes > $mes_inicio)) {
    $btn_anterior = '<button class="btn-mes" onclick="cambiarMes(-1, \'' . $id_contenedor . '\')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg></button>';
}

$btn_siguiente = '<div style="inline-size: 34px;"></div>';
if ($anio < $anio_actual_real || ($anio == $anio_actual_real && $mes < $mes_actual_real)) {
    $btn_siguiente = '<button class="btn-mes" onclick="cambiarMes(1, \'' . $id_contenedor . '\')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></button>';
}

$html = '<div class="controles-calendario">';
$html .= $btn_anterior;
$html .= '<div class="mes-actual">' . $nombre_mes . ' ' . $anio . '</div>';
$html .= $btn_siguiente;
$html .= '</div>';

$html .= '<div class="calendario-grid">';

$dias_es = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
foreach ($dias_es as $d) {
    $html .= '<div class="dia-semana">' . $d . '</div>';
}

for ($i = 1; $i < $dia_semana_inicio; $i++) {
    $html .= '<div class="dia-celda vacio"></div>';
}

for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
    $fecha_ciclo = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    $fecha_display = sprintf("%02d/%02d/%04d", $dia, $mes, $anio);
    
    $dia_semana = date('N', strtotime($fecha_ciclo));
    $es_fin_semana = ($dia_semana == 6 || $dia_semana == 7);

    $clase_estado = '';
    $icono = '';
    $estado_texto = '';
    $motivo_texto = '';
    $archivo_texto = '';

    if ($es_fin_semana) {
        $clase_estado = 'estado-fin-semana';
        $estado_texto = 'Fin de Semana';
    } else {
        if (isset($asistencias[$fecha_ciclo])) {
            $est = $asistencias[$fecha_ciclo]['estado'];
            $motivo_texto = htmlspecialchars(addslashes($asistencias[$fecha_ciclo]['motivo']), ENT_QUOTES);
            $archivo_texto = htmlspecialchars(addslashes($asistencias[$fecha_ciclo]['archivo']), ENT_QUOTES);
            
            if ($est == 'Puntual') {
                $clase_estado = 'estado-puntual';
                $icono = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                $estado_texto = 'Puntual';
            } elseif ($est == 'Retraso') {
                $clase_estado = 'estado-retraso';
                $icono = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                $estado_texto = 'Retraso';
            } elseif ($est == 'Justificado') {
                $clase_estado = 'estado-justificado';
                $icono = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
                $estado_texto = 'Justificado';
            } elseif ($est == 'Falta') {
                $clase_estado = 'estado-falta';
                $icono = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                $estado_texto = 'Falta';
            }
        } else {
            // CORRECCIÓN: Solo es falta si ya pasó Y si pertenece a la "era del sistema"
            if ($fecha_ciclo < $fecha_hoy_str && $fecha_ciclo >= $fecha_inicio_sistema) {
                $clase_estado = 'estado-falta';
                $icono = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                $estado_texto = 'Falta Injustificada';
            } else {
                $estado_texto = 'Sin registro';
            }
        }
    }

    $attr_click = '';
    $clase_clickable = '';
    
    if ($es_admin && !$es_fin_semana && $fecha_ciclo <= $fecha_hoy_str) {
        $clase_clickable = 'clickable';
        $attr_click = "onclick=\"editarDia('{$fecha_ciclo}', '{$fecha_display}', '{$estado_texto}', '{$motivo_texto}', '{$archivo_texto}')\"";
    }

    $html .= "<div class=\"dia-celda {$clase_estado} {$clase_clickable}\" {$attr_click} title=\"{$estado_texto}\">";
    $html .= "<span class=\"numero-dia\">{$dia}</span>";
    $html .= $icono;
    $html .= "</div>";
}

$html .= '</div>';
echo $html;
?>