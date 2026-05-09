<?php
session_start();
require_once '../configuracion/conexion.php';

// =====================================================================
// CORRECCIÓN BUG: Este controlador recibe el formulario de justificación
// de CUALQUIER empleado (rol 1, 2 o 3). La validación debe permitir el
// acceso a todos los usuarios autenticados, NO solo a Director/Subdirector.
// El error anterior (copiar el check de ControladorProcesarJustificacion.php)
// bloqueaba a los empleados con rol 3 y los redirigía a principal.php
// sin procesar nada, dando la ilusión de que el formulario "no hacía nada".
// =====================================================================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: ../vistas/login.php");
    exit;
}

// 1. CONFIRMAMOS LA ZONA HORARIA A VENEZUELA
date_default_timezone_set('America/Caracas');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // =====================================================================
    // PROTECCIÓN CSRF
    // Verificamos que el token del formulario coincida con el de la sesión.
    // Sin esto, cualquier sitio externo podría enviar justificaciones en
    // nombre del empleado autenticado (ataque Cross-Site Request Forgery).
    // =====================================================================
    if (
        empty($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Solicitud inválida. Por favor, recarga la página e intenta de nuevo.'];
        header("Location: ../vistas/principal.php");
        exit;
    }
    // Invalidamos el token después de usarlo (token de un solo uso)
    unset($_SESSION['csrf_token']);

    $id_personal = $_POST['id_personal'];
    $fecha = $_POST['fecha_justificacion'];
    
    // === BLOQUEO FIN DE SEMANA ===
    if (date('N', strtotime($fecha)) >= 6) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Operación denegada. No puedes registrar justificaciones para días de fin de semana.'];
        header("Location: ../vistas/principal.php");
        exit;
    }

    $tipo = isset($_POST['tipo_incidencia']) ? trim($_POST['tipo_incidencia']) : '';
    $motivo_texto = trim($_POST['motivo']);
    
    // ==========================================
    // ESCUDO DE VALIDACIÓN ESTRICTA (BACKEND)
    // ==========================================
    $errores = [];

    if (empty($fecha)) {
        $errores[] = "• La fecha de la incidencia es obligatoria.";
    } elseif ($fecha > date('Y-m-d')) {
        $errores[] = "• No puedes justificar una incidencia en una fecha futura.";
    }

    $tipos_permitidos = ['Inasistencia', 'Llegada Tardía', 'Salida Temprana'];
    if (!in_array($tipo, $tipos_permitidos)) {
        $errores[] = "• El tipo de incidencia seleccionado no es válido.";
    }

    if (empty($motivo_texto)) {
        $errores[] = "• Debes explicar el motivo de la incidencia.";
    } elseif (strlen($motivo_texto) < 15) {
        $errores[] = "• El motivo es muy corto. Por favor, sé más detallado (mínimo 15 caracteres).";
    }

    if (!empty($errores)) {
        $_SESSION['alerta_principal'] = [
            'tipo' => 'error', 
            'mensaje' => '<strong>Revisa los siguientes errores:</strong><br>' . implode("<br>", $errores)
        ];
        header("Location: ../vistas/principal.php");
        exit;
    }
    // ==========================================
    // FIN DEL ESCUDO DE VALIDACIÓN
    // ==========================================

    $motivo_completo = "[" . $tipo . "] - " . htmlspecialchars($motivo_texto, ENT_QUOTES, 'UTF-8');
    
    // --- LÓGICA DE SUBIDA DE ARCHIVO BLINDADA ---
    $nombre_archivo_final = null; 
    
    if (isset($_FILES['archivo_evidencia']) && $_FILES['archivo_evidencia']['error'] == 0) {
        $directorio_destino = '../recursos/evidencias/';
        
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }

        $archivo = $_FILES['archivo_evidencia'];
        
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'pdf'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        $mimes_validos = ['image/jpeg', 'image/png', 'application/pdf'];

        if (in_array($extension, $extensiones_validas) && in_array($mime_type, $mimes_validos)) {
            if ($archivo['size'] <= 5000000) {
                $nombre_archivo_final = $id_personal . '_' . str_replace('-', '', $fecha) . '_' . time() . '.' . $extension;
                $ruta_final = $directorio_destino . $nombre_archivo_final;
                move_uploaded_file($archivo['tmp_name'], $ruta_final);
            } else {
                $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'El archivo adjunto es muy pesado. El límite máximo es de 5MB.'];
                header("Location: ../vistas/principal.php");
                exit;
            }
        } else {
            $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Archivo no seguro o formato inválido. Solo se admiten documentos PDF e imágenes JPG o PNG reales.'];
            header("Location: ../vistas/principal.php");
            exit;
        }
    }
    // --- FIN LÓGICA DE ARCHIVO ---

    try {
        $check = $conexion->prepare("SELECT * FROM asistencias WHERE id_personal = ? AND fecha = ?");
        $check->execute([$id_personal, $fecha]);
        $registro = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            $q = "UPDATE asistencias SET motivo_justificacion = ?, estado_justificacion = 'Pendiente'";
            $params = [$motivo_completo];

            if ($nombre_archivo_final) {
                $q .= ", archivo_evidencia = ?";
                $params[] = $nombre_archivo_final;
            }

            if ($tipo == 'Inasistencia') {
                $q .= ", estado = 'Justificado (Pendiente)'";
            }

            $q .= " WHERE id_personal = ? AND fecha = ?";
            $params[] = $id_personal;
            $params[] = $fecha;

            $update = $conexion->prepare($q);
            $update->execute($params);
            
        } else {
            $stmt_emp = $conexion->prepare("SELECT hora_entrada_personalizada FROM personal WHERE id_personal = ?");
            $stmt_emp->execute([$id_personal]);
            $emp = $stmt_emp->fetch(PDO::FETCH_ASSOC);
            
            $stmt_conf = $conexion->query("SELECT hora_entrada_general FROM configuracion WHERE id_config = 1");
            $conf = $stmt_conf->fetch(PDO::FETCH_ASSOC);
            
            $hora_esperada = !empty($emp['hora_entrada_personalizada']) ? $emp['hora_entrada_personalizada'] : ($conf['hora_entrada_general'] ?? '07:00:00');

            $estado_val = ($tipo == 'Inasistencia') ? 'Justificado (Pendiente)' : 'Pendiente';

            $insert = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, estado, motivo_justificacion, estado_justificacion, archivo_evidencia) VALUES (?, ?, ?, ?, ?, 'Pendiente', ?)");
            $insert->execute([$id_personal, $fecha, $hora_esperada, $estado_val, $motivo_completo, $nombre_archivo_final]);
        }

        $msg_exito = ($tipo == 'Inasistencia') 
            ? 'Justificación de Inasistencia enviada correctamente a Dirección.' 
            : 'Justificación enviada. <strong>Recuerda registrar tu hora física en el panel.</strong>';

        $_SESSION['alerta_principal'] = ['tipo' => 'success', 'mensaje' => $msg_exito];

    } catch (PDOException $e) {
        $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Ocurrió un error en la base de datos al procesar tu justificación. Intenta nuevamente.'];
    }

    header("Location: ../vistas/principal.php");
    exit;
} else {
    header("Location: ../vistas/principal.php");
    exit;
}
?>