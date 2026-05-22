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
$id_contenedor = isset($_GET['contenedor']) ? htmlspecialchars($_GET['contenedor']) : 'contenedor-calendario-inline';

$meses_es = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$nombre_mes = $meses_es[$mes - 1];

$primer_dia = mktime(0, 0, 0, $mes, 1, $anio);
$dias_en_mes = date('t', $primer_dia);
$dia_semana_inicio = date('N', $primer_dia); // 1=Lunes, 7=Domingo

date_default_timezone_set('America/Caracas');
$fecha_hoy_str = date('Y-m-d');
$mes_actual_real = (int)date('n');
$anio_actual_real = (int)date('Y');

// Obtener fecha de ingreso del empleado
$stmt_ingreso = $conexion->prepare("SELECT fecha_ingreso FROM personal WHERE id_personal = ?");
$stmt_ingreso->execute([$id_personal]);
$fecha_ingreso_empleado = $stmt_ingreso->fetchColumn();
if (!$fecha_ingreso_empleado) {
    $fecha_ingreso_empleado = $fecha_hoy_str;
}
$anio_ingreso = (int)date('Y', strtotime($fecha_ingreso_empleado));
$mes_ingreso  = (int)date('n', strtotime($fecha_ingreso_empleado));

// Consultar asistencias del mes
$asistencias = [];
try {
    $sql = "SELECT fecha, estado, motivo_justificacion, archivo_evidencia 
            FROM asistencias 
            WHERE id_personal = :id AND MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_personal, ':mes' => $mes, ':anio' => $anio]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Eliminar sufijo " (Pendiente)" si existiera en el estado
        $estado_limpio = trim(str_replace(' (Pendiente)', '', $row['estado']));
        $asistencias[$row['fecha']] = [
            'estado'  => $estado_limpio,
            'motivo'  => $row['motivo_justificacion'] ?? '',
            'archivo' => $row['archivo_evidencia'] ?? ''
        ];
    }
} catch (PDOException $e) {
    die("<p style='text-align:center; color:red;'>Error al consultar la BD.</p>");
}

// -------------------------------------------------------------------------
// Función auxiliar: devuelve clase CSS + icono SVG + texto legible
// según el estado almacenado en la BD.
// -------------------------------------------------------------------------
function resolverEstado(string $est): array
{
    // Iconos reutilizables
    $ico_ok       = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    $ico_clock    = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    $ico_doc      = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
    $ico_x        = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    $ico_warn     = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
    $ico_mixed    = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

    // --- Estados simples ---
    switch ($est) {
        case 'Puntual':
            return ['estado-puntual',           $ico_ok,    'Puntual'];
        case 'Retraso':
            return ['estado-retraso',            $ico_clock, 'Retraso'];
        case 'Justificado':
            return ['estado-justificado',        $ico_doc,   'Justificado'];
        case 'Falta':
            return ['estado-falta',              $ico_x,     'Falta'];
        case 'Salida Irregular':
            return ['estado-salida-irregular',   $ico_warn,  'Salida Irregular'];
        case 'Salida Temprana':
            return ['estado-retraso',            $ico_mixed, 'Salida Temprana'];

        // --- Estados combinados: Retraso + salida ---
        case 'Retraso y Salida Temprana':
        case 'Retraso y Salida Irregular':
            return ['estado-retraso-salida',     $ico_mixed, $est];

        // --- Estados combinados: Puntual + salida ---
        case 'Puntual y Salida Temprana':
        case 'Puntual y Salida Irregular':
            return ['estado-puntual-salida',     $ico_mixed, $est];

        // --- Estados combinados: Justificado + salida ---
        case 'Justificado y Salida Temprana':
        case 'Justificado y Salida Irregular':
            return ['estado-justificado-salida', $ico_mixed, $est];

        // --- Estados combinados: Falta + salida ---
        case 'Falta y Salida Temprana':
        case 'Falta y Salida Irregular':
            return ['estado-falta-salida',       $ico_mixed, $est];
    }

    // --- Fallback: detectar combinaciones por contenido del string ---
    $tiene_retraso     = (stripos($est, 'Retraso')     !== false);
    $tiene_puntual     = (stripos($est, 'Puntual')     !== false);
    $tiene_justificado = (stripos($est, 'Justificado') !== false);
    $tiene_falta       = (stripos($est, 'Falta')       !== false);
    $tiene_sal_irreg   = (stripos($est, 'Salida Irregular') !== false);
    $tiene_sal_temp    = (stripos($est, 'Salida Temprana')  !== false);
    $tiene_salida      = ($tiene_sal_irreg || $tiene_sal_temp);

    if ($tiene_retraso     && $tiene_salida) return ['estado-retraso-salida',     $ico_mixed, $est];
    if ($tiene_puntual     && $tiene_salida) return ['estado-puntual-salida',     $ico_mixed, $est];
    if ($tiene_justificado && $tiene_salida) return ['estado-justificado-salida', $ico_mixed, $est];
    if ($tiene_falta       && $tiene_salida) return ['estado-falta-salida',       $ico_mixed, $est];
    if ($tiene_retraso)                      return ['estado-retraso',            $ico_clock, $est];
    if ($tiene_puntual)                      return ['estado-puntual',            $ico_ok,    $est];
    if ($tiene_justificado)                  return ['estado-justificado',        $ico_doc,   $est];
    if ($tiene_falta)                        return ['estado-falta',              $ico_x,     $est];

    // Estado desconocido: clase genérica
    return ['estado-mixto', $ico_mixed, $est];
}

// -------------------------------------------------------------------------
// Botones de navegación
// -------------------------------------------------------------------------
$btn_anterior = '<div style="inline-size: 34px;"></div>';
if ($anio > $anio_ingreso || ($anio == $anio_ingreso && $mes > $mes_ingreso)) {
    $btn_anterior = '<button class="btn-mes" onclick="cambiarMes(-1, \'' . $id_contenedor . '\')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                     </button>';
}

$btn_siguiente = '<button class="btn-mes" onclick="cambiarMes(1, \'' . $id_contenedor . '\')">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                 </button>';

// -------------------------------------------------------------------------
// Construcción del HTML
// -------------------------------------------------------------------------
$html  = '<div class="controles-calendario">';
$html .= $btn_anterior;
$html .= '<div class="mes-actual">' . $nombre_mes . ' ' . $anio . '</div>';
$html .= $btn_siguiente;
$html .= '</div>';

$html .= '<div class="calendario-grid">';

$dias_es = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
foreach ($dias_es as $d) {
    $html .= '<div class="dia-semana">' . $d . '</div>';
}

// Celdas vacías al inicio del mes
for ($i = 1; $i < $dia_semana_inicio; $i++) {
    $html .= '<div class="dia-celda vacio"></div>';
}

// Días del mes
for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
    $fecha_ciclo   = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    $fecha_display = sprintf("%02d/%02d/%04d", $dia, $mes, $anio);
    $dia_semana_num     = date('N', strtotime($fecha_ciclo));
    $es_fin_semana      = ($dia_semana_num == 6 || $dia_semana_num == 7);
    $es_anterior_ingreso = ($fecha_ciclo < $fecha_ingreso_empleado);

    $clase_estado = '';
    $icono        = '';
    $estado_texto = '';
    $motivo_texto = '';
    $archivo_texto = '';

    if ($es_fin_semana) {
        $clase_estado = 'estado-fin-semana';
        $estado_texto = 'Fin de Semana';

    } elseif ($es_anterior_ingreso) {
        $clase_estado = 'estado-no-aplica';
        $estado_texto = 'No aplica (antes de ingreso)';

    } elseif (isset($asistencias[$fecha_ciclo])) {
        $est           = $asistencias[$fecha_ciclo]['estado'];
        $motivo_texto  = htmlspecialchars(addslashes($asistencias[$fecha_ciclo]['motivo']), ENT_QUOTES);
        $archivo_texto = htmlspecialchars(addslashes($asistencias[$fecha_ciclo]['archivo']), ENT_QUOTES);

        [$clase_estado, $icono, $estado_texto] = resolverEstado($est);

    } else {
        // Sin registro en BD
        if ($fecha_ciclo < $fecha_hoy_str) {
            // Día laboral pasado sin registro → falta injustificada
            $clase_estado = 'estado-falta';
            $icono        = '<svg class="icono-estado" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            $estado_texto = 'Falta Injustificada';
        } else {
            $estado_texto = 'Sin registro';
        }
    }

    // Cliclable si es admin, no es fin de semana, no es anterior al ingreso,
    // Y además: si el día es futuro, solo se permite editar si YA tiene un registro en la BD.
    $attr_click      = '';
    $clase_clickable = '';
    $es_futuro        = ($fecha_ciclo > $fecha_hoy_str);
    $tiene_registro   = isset($asistencias[$fecha_ciclo]);

    if ($es_admin && !$es_fin_semana && !$es_anterior_ingreso) {
        if (!$es_futuro || $tiene_registro) {
            $clase_clickable = 'clickable';
            $attr_click = "onclick=\"editarDia('{$fecha_ciclo}', '{$fecha_display}', '{$estado_texto}', '{$motivo_texto}', '{$archivo_texto}')\"";
        }
    }

    $html .= "<div class=\"dia-celda {$clase_estado} {$clase_clickable}\" {$attr_click} title=\"{$estado_texto}\">";
    $html .= "<span class=\"numero-dia\">{$dia}</span>";
    $html .= $icono;
    $html .= "</div>";
}

$html .= '</div>';

$html = "<div id='{$id_contenedor}' data-fecha-ingreso='{$fecha_ingreso_empleado}'>" . $html . "</div>";
echo $html;
?>