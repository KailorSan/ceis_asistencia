<?php
session_start();
require_once '../configuracion/conexion.php';

// Si alguien intenta entrar aquí directamente por URL sin iniciar sesión, lo echamos
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

// 1. CONFIRMAMOS LA ZONA HORARIA A VENEZUELA
date_default_timezone_set('America/Caracas');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];
    $id_usuario = $_SESSION['id_usuario'];
    
    try {
        // ==========================================
        // OBTENER DATOS DEL EMPLEADO Y SU HORARIO
        // ==========================================
        $stmt_emp = $conexion->prepare("SELECT id_personal, hora_entrada_personalizada, hora_salida_personalizada FROM personal WHERE id_usuario = ?");
        $stmt_emp->execute([$id_usuario]);
        $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) throw new Exception("Tu usuario no está vinculado a un empleado.");
        $id_personal = $empleado['id_personal'];

        // ==========================================
        // OBTENER LA CONFIGURACIÓN GLOBAL
        // ==========================================
        $stmt_conf = $conexion->prepare("SELECT hora_entrada_general, hora_salida_general, minutos_tolerancia FROM configuracion WHERE id_config = 1");
        $stmt_conf->execute();
        $config = $stmt_conf->fetch(PDO::FETCH_ASSOC);

        // Si no existe la configuración, ponemos valores por defecto para evitar errores
        if(!$config) {
            $config = ['hora_entrada_general' => '07:00:00', 'minutos_tolerancia' => 15];
        }

        // ==========================================
        // DEFINIR CUÁL HORARIO APLICA (Personalizado o General)
        // ==========================================
        $hora_esperada = !empty($empleado['hora_entrada_personalizada']) ? $empleado['hora_entrada_personalizada'] : $config['hora_entrada_general'];
        $tolerancia = $config['minutos_tolerancia'];

        // Tomamos la fecha y hora EXACTA del momento del clic
        $fecha_hoy = date('Y-m-d');
        $hora_actual = date('H:i:s');

        // ==========================================
        // ACCIÓN 1: MARCAR ENTRADA
        // ==========================================
        if ($accion === 'marcar_entrada') {
            
            // Verificamos que no haya un registro previo hoy (Protección doble clic)
            $check = $conexion->prepare("SELECT id_asistencia FROM asistencias WHERE id_personal = ? AND fecha = ?");
            $check->execute([$id_personal, $fecha_hoy]);
            
            if ($check->rowCount() == 0) {
                // Cálculo matemático: Hora Esperada + Minutos de Tolerancia
                $hora_maxima_permitida = date('H:i:s', strtotime("+$tolerancia minutes", strtotime($hora_esperada)));
                
                // Determinamos el estado comparando las horas
                $estado = ($hora_actual > $hora_maxima_permitida) ? 'Retraso' : 'Puntual';

                $insert = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, hora_entrada, estado) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$id_personal, $fecha_hoy, $hora_esperada, $hora_actual, $estado]);
            }
        } 
        
        // ==========================================
        // ACCIÓN 2: MARCAR SALIDA
        // ==========================================
        elseif ($accion === 'marcar_salida') {
            
            // Actualizamos la hora de salida SOLO si está vacía
            $update = $conexion->prepare("UPDATE asistencias SET hora_salida = ? WHERE id_personal = ? AND fecha = ? AND hora_salida IS NULL");
            $update->execute([$hora_actual, $id_personal, $fecha_hoy]);
        }

    } catch (Exception $e) {
        // En un futuro podemos mostrar el error en pantalla con un SweetAlert, por ahora es silencioso
    }

    // Sin importar lo que pase, devolvemos al panel principal
    header("Location: ../vistas/principal.php");
    exit;
} else {
    header("Location: ../vistas/principal.php");
    exit;
}
?>