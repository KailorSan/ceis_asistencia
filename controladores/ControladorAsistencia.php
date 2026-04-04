<?php
session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php'; 

if (!isset($_SESSION['logueado'])) {
    header("Location: ../vistas/login.php");
    exit;
}

date_default_timezone_set('America/Caracas');

$id_usuario = $_SESSION['id_usuario'];
$accion = $_POST['accion'] ?? '';

// 1. Obtener los datos del empleado
$stmt_emp = $conexion->prepare("SELECT p.id_personal, p.nombres, p.apellidos, p.hora_entrada_personalizada, p.hora_salida_personalizada 
                                FROM personal p WHERE p.id_usuario = ?");
$stmt_emp->execute([$id_usuario]);
$empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Error: Tu usuario no está vinculado a un empleado.'];
    header("Location: ../vistas/principal.php");
    exit;
}

$id_personal = $empleado['id_personal'];
$nombre_completo = $empleado['nombres'] . ' ' . $empleado['apellidos'];

// 2. Obtener horarios de configuración
$stmt_conf = $conexion->query("SELECT hora_entrada_general, hora_salida_general, minutos_tolerancia FROM configuracion LIMIT 1");
$config = $stmt_conf->fetch(PDO::FETCH_ASSOC);

$hora_esperada = $empleado['hora_entrada_personalizada'] ?: $config['hora_entrada_general'];
$tolerancia = $config['minutos_tolerancia'];
$limite_entrada = date('H:i:s', strtotime("+$tolerancia minutes", strtotime($hora_esperada)));
$hora_actual = date('H:i:s');

if ($accion === 'marcar_entrada') {
    
    $estado_marcado = ($hora_actual > $limite_entrada) ? 'Retraso' : 'Puntual';

    // Verificamos si ya existe una fila para hoy (creada por una justificación previa, por ejemplo)
    $stmt_check = $conexion->prepare("SELECT id_asistencia, hora_entrada FROM asistencias WHERE id_personal = ? AND fecha = CURDATE()");
    $stmt_check->execute([$id_personal]);
    $registro = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        // La fila ya existía (Justificó primero, como es debido)
        if ($registro['hora_entrada'] === null) {
            
            // Hacemos UPDATE para no causar error de duplicidad
            $stmt_upd = $conexion->prepare("UPDATE asistencias SET hora_entrada = ?, estado = IF(estado_justificacion IS NOT NULL, estado, ?) WHERE id_asistencia = ?");
            $stmt_upd->execute([$hora_actual, $estado_marcado, $registro['id_asistencia']]);
            
            ControladorBitacora::registrar($conexion, $id_usuario, 'Asistencia', 'Registro de Entrada (Tras Justificar)', "El empleado $nombre_completo marcó su entrada a las $hora_actual.");
            $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Has registrado tu entrada física con éxito. ¡A trabajar!'];
        
        } else {
            $_SESSION['alerta_principal'] = ['tipo' => 'warning', 'mensaje' => 'Ya habías registrado tu entrada el día de hoy.'];
        }
    } else {
        // Es un registro normal (Puntual, sin justificación previa)
        $stmt_ins = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, hora_entrada, estado) VALUES (?, CURDATE(), ?, ?, ?)");
        $stmt_ins->execute([$id_personal, $hora_esperada, $hora_actual, $estado_marcado]);
        
        ControladorBitacora::registrar($conexion, $id_usuario, 'Asistencia', 'Registro de Entrada', "El empleado $nombre_completo marcó su entrada a las $hora_actual.");
        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Entrada registrada a tiempo. ¡Que tengas un excelente día!'];
    }

} elseif ($accion === 'marcar_salida') {
    
    $stmt_check = $conexion->prepare("SELECT id_asistencia, hora_salida FROM asistencias WHERE id_personal = ? AND fecha = CURDATE()");
    $stmt_check->execute([$id_personal]);
    $registro = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        if ($registro['hora_salida'] === null) {
            $stmt_upd = $conexion->prepare("UPDATE asistencias SET hora_salida = ? WHERE id_asistencia = ?");
            $stmt_upd->execute([$hora_actual, $registro['id_asistencia']]);
            
            ControladorBitacora::registrar($conexion, $id_usuario, 'Asistencia', 'Registro de Salida', "El empleado $nombre_completo marcó su salida a las $hora_actual.");
            $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => 'Salida registrada correctamente. ¡Hasta mañana!'];
        } else {
            $_SESSION['alerta_principal'] = ['tipo' => 'warning', 'mensaje' => 'Ya habías registrado tu salida el día de hoy.'];
        }
    } else {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'No puedes marcar salida sin haber marcado entrada primero.'];
    }
}

header("Location: ../vistas/principal.php");
exit;
?>