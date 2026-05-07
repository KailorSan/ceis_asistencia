<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; 

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || empty($_SESSION['id_usuario'])) {
    header("Location: ../vistas/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Método de solicitud no permitido.'];
    header("Location: ../vistas/principal.php");
    exit;
}

date_default_timezone_set('America/Caracas');
$id_usuario = (int) $_SESSION['id_usuario'];

if (date('N') >= 6) {
    ControladorBitacora::registrar($conexion, $id_usuario, 'Seguridad', 'Intento de evasión (Fin de Semana)', "El usuario intentó forzar el registro de asistencia en un día no laborable.");
    
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Operación denegada. No se puede registrar asistencia los fines de semana.'];
    header("Location: ../vistas/principal.php");
    exit;
}

$acciones_permitidas = ['marcar_entrada', 'marcar_salida'];
$accion = htmlspecialchars(strip_tags(trim($_POST['accion'] ?? '')));

if (!in_array($accion, $acciones_permitidas)) {
    ControladorBitacora::registrar($conexion, $id_usuario, 'Seguridad', 'Acción manipulada', "El usuario intentó inyectar una acción no permitida: $accion");
    
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Acción no reconocida por el sistema de seguridad.'];
    header("Location: ../vistas/principal.php");
    exit;
}

$stmt_emp = $conexion->prepare("SELECT p.id_personal, p.nombres, p.apellidos, p.hora_entrada_personalizada, p.hora_salida_personalizada 
                                FROM personal p WHERE p.id_usuario = ? LIMIT 1");
$stmt_emp->execute([$id_usuario]);
$empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    ControladorBitacora::registrar($conexion, $id_usuario, 'Seguridad', 'Perfil fantasma', "Usuario intentó marcar asistencia sin estar vinculado a un perfil de empleado.");
    
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error: Tu usuario no está vinculado a un empleado.'];
    header("Location: ../vistas/principal.php");
    exit;
}

$id_personal = (int) $empleado['id_personal'];
$nombre_completo = trim($empleado['nombres'] . ' ' . $empleado['apellidos']);
$stmt_conf = $conexion->query("SELECT hora_entrada_general, hora_salida_general, minutos_tolerancia FROM configuracion LIMIT 1");
$config = $stmt_conf->fetch(PDO::FETCH_ASSOC);
$hora_esperada      = $empleado['hora_entrada_personalizada'] ?: $config['hora_entrada_general'];
$hora_salida_esperada = $empleado['hora_salida_personalizada'] ?: $config['hora_salida_general'];
$tolerancia         = (int) $config['minutos_tolerancia'];
$limite_entrada     = date('H:i:s', strtotime("+$tolerancia minutes", strtotime($hora_esperada)));
$hora_actual        = date('H:i:s');

// ==========================================
// PROCESAMIENTO DE ACCIONES VÁLIDAS
// ==========================================

if ($accion === 'marcar_entrada') {
    $estado_marcado = (strtotime($hora_actual) > strtotime($limite_entrada)) ? 'Retraso' : 'Puntual';

    $stmt_check = $conexion->prepare("SELECT id_asistencia, hora_entrada FROM asistencias WHERE id_personal = ? AND fecha = CURDATE() LIMIT 1");
    $stmt_check->execute([$id_personal]);
    $registro = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        if ($registro['hora_entrada'] === null) {
            $stmt_upd = $conexion->prepare("UPDATE asistencias SET hora_entrada = ?, estado = IF(estado_justificacion = 'Aprobada', estado, ?) WHERE id_asistencia = ?");
            $stmt_upd->execute([$hora_actual, $estado_marcado, $registro['id_asistencia']]);
            
            ControladorBitacora::registrar($conexion, $id_usuario, 'Asistencia', 'Registro de Entrada (Tras Justificar)', "El empleado $nombre_completo marcó su entrada a las $hora_actual.");
            $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Has registrado tu entrada física con éxito. ¡A trabajar!'];
        } else {
            $_SESSION['alerta_principal'] = ['tipo' => 'warning', 'mensaje' => 'Ya habías registrado tu entrada el día de hoy.'];
        }
    } else {
        $stmt_ins = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, hora_entrada, estado) VALUES (?, CURDATE(), ?, ?, ?)");
        $stmt_ins->execute([$id_personal, $hora_esperada, $hora_actual, $estado_marcado]);
        
        ControladorBitacora::registrar($conexion, $id_usuario, 'Asistencia', 'Registro de Entrada', "El empleado $nombre_completo marcó su entrada a las $hora_actual.");
        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Entrada registrada a tiempo. ¡Que tengas un excelente día!'];
    }

} elseif ($accion === 'marcar_salida') {

    $stmt_check = $conexion->prepare("SELECT id_asistencia, hora_salida FROM asistencias WHERE id_personal = ? AND fecha = CURDATE() LIMIT 1");
    $stmt_check->execute([$id_personal]);
    $registro = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        if ($registro['hora_salida'] === null) {
            // MEJORA: Verificamos si la salida es temprana y no tiene justificación aprobada.
            // Esto evita que alguien marque salida antes de tiempo sin haber justificado previamente.
            $es_salida_temprana = (strtotime($hora_actual) < strtotime($hora_salida_esperada));
            if ($es_salida_temprana) {
                // Verificamos si ya tiene una justificación de salida temprana pendiente o aprobada
                $stmt_just_salida = $conexion->prepare(
                    "SELECT id_asistencia FROM asistencias 
                     WHERE id_personal = ? AND fecha = CURDATE() 
                     AND motivo_justificacion LIKE '%[Salida Temprana]%'
                     AND estado_justificacion IN ('Pendiente', 'Aprobada') LIMIT 1"
                );
                $stmt_just_salida->execute([$id_personal]);
                $tiene_justificacion_salida = $stmt_just_salida->fetchColumn();

                if (!$tiene_justificacion_salida) {
                    ControladorBitacora::registrar($conexion, $id_usuario, 'Advertencia', 'Salida temprana sin justificación', "El empleado $nombre_completo intentó registrar salida antes de su hora ($hora_salida_esperada) sin justificación.");
                    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'No puedes registrar salida antes de tu hora establecida sin una justificación previa aprobada.'];
                    header("Location: ../vistas/principal.php");
                    exit;
                }
            }

            $stmt_upd = $conexion->prepare("UPDATE asistencias SET hora_salida = ? WHERE id_asistencia = ?");
            $stmt_upd->execute([$hora_actual, $registro['id_asistencia']]);
            
            ControladorBitacora::registrar($conexion, $id_usuario, 'Asistencia', 'Registro de Salida', "El empleado $nombre_completo marcó su salida a las $hora_actual.");
            $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Salida registrada correctamente. ¡Hasta mañana!'];
        } else {
            $_SESSION['alerta_principal'] = ['tipo' => 'warning', 'mensaje' => 'Ya habías registrado tu salida el día de hoy.'];
        }
    } else {
        ControladorBitacora::registrar($conexion, $id_usuario, 'Advertencia', 'Salida sin entrada previa', "El empleado $nombre_completo intentó registrar salida sin tener registro de entrada en el sistema hoy.");
        
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'No puedes marcar salida sin haber marcado entrada primero.'];
    }
}

header("Location: ../vistas/principal.php");
exit;
?>