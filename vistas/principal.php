<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

date_default_timezone_set('America/Caracas');

$nombre    = $_SESSION['usuario'];
$rol       = $_SESSION['rol'];
$id_rol    = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

$titulo_tarjeta_1 = "Cargando..."; $valor_tarjeta_1 = 0;
$titulo_tarjeta_2 = "Cargando..."; $valor_tarjeta_2 = 0;
$titulo_tarjeta_3 = "Cargando..."; $valor_tarjeta_3 = 0;

try {
    $hora_actual_sec = date('H:i:s');
    $dia_semana_hoy  = date('N');
    $stmt_conf_global = $conexion->query("SELECT hora_entrada_general, hora_salida_general, minutos_tolerancia FROM configuracion WHERE id_config = 1");
    $config_global    = $stmt_conf_global->fetch(PDO::FETCH_ASSOC) ?: [
        'hora_entrada_general' => '07:00:00',
        'hora_salida_general'  => '13:00:00',
        'minutos_tolerancia'   => 15
    ];

    if ($dia_semana_hoy <= 5) {

        $clave_flag_faltas = 'auto_faltas_ejecutado_' . date('Y-m-d');

        if (empty($_SESSION[$clave_flag_faltas])) {

            $sql_ausentes = "SELECT p.id_personal, p.hora_entrada_personalizada, p.hora_salida_personalizada 
                             FROM personal p
                             INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                             WHERE u.estado = 'Activo' 
                             AND p.fecha_ingreso <= CURDATE()
                             AND p.id_personal NOT IN (SELECT id_personal FROM asistencias WHERE fecha = CURDATE())";
            $ausentes = $conexion->query($sql_ausentes)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ausentes as $aus) {
                $h_entrada_p = !empty($aus['hora_entrada_personalizada']) ? $aus['hora_entrada_personalizada'] : $config_global['hora_entrada_general'];
                $h_salida_p  = !empty($aus['hora_salida_personalizada'])  ? $aus['hora_salida_personalizada']  : $config_global['hora_salida_general'];

                if ($hora_actual_sec > $h_salida_p) {
                    $ins_falta = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, estado) VALUES (?, CURDATE(), ?, 'Falta')");
                    $ins_falta->execute([$aus['id_personal'], $h_entrada_p]);
                }
            }

            $_SESSION[$clave_flag_faltas] = true;
        }

        $sql_incompletos = "SELECT a.id_asistencia, a.estado, a.fecha, p.hora_salida_personalizada 
                            FROM asistencias a
                            INNER JOIN personal p ON a.id_personal = p.id_personal
                            WHERE a.hora_salida IS NULL 
                            AND a.estado != 'Falta'
                            AND a.estado NOT LIKE '%Salida Irregular%'
                            AND (a.estado_justificacion IS NULL OR a.estado_justificacion != 'Pendiente')";
        $incompletos = $conexion->query($sql_incompletos)->fetchAll(PDO::FETCH_ASSOC);

        $fecha_hoy_comparar = date('Y-m-d');

        foreach ($incompletos as $inc) {
            $h_salida_p    = !empty($inc['hora_salida_personalizada']) ? $inc['hora_salida_personalizada'] : $config_global['hora_salida_general'];
            $limite_salida = date('H:i:s', strtotime("+60 minutes", strtotime($h_salida_p)));

            if ($inc['fecha'] < $fecha_hoy_comparar || ($inc['fecha'] == $fecha_hoy_comparar && $hora_actual_sec > $limite_salida)) {
                $estado_actual = $inc['estado'];
                $nuevo_estado  = 'Salida Irregular';
                
                if (strpos($estado_actual, 'Retraso') !== false) {
                    $nuevo_estado = 'Retraso y Salida Irregular';
                } elseif (strpos($estado_actual, 'Puntual') !== false) {
                    $nuevo_estado = 'Puntual y Salida Irregular';
                }

                $upd_irr = $conexion->prepare("UPDATE asistencias SET estado = ?, observacion = 'El sistema cerró la jornada automáticamente por omisión de salida.' WHERE id_asistencia = ?");
                $upd_irr->execute([$nuevo_estado, $inc['id_asistencia']]);
            }
        }
    }

    $stmt_emp = $conexion->prepare("SELECT id_personal, hora_entrada_personalizada, hora_salida_personalizada FROM personal WHERE id_usuario = :id_user");
    $stmt_emp->execute([':id_user' => $id_usuario]);
    $empleado   = $stmt_emp->fetch(PDO::FETCH_ASSOC);
    $id_personal = $empleado ? $empleado['id_personal'] : null;

    // Reutilizamos $config_global (no hay segunda query a configuracion)
    $config = $config_global;

    $asistencia_hoy           = false;
    $ya_salio                 = false;
    $hora_entrada_registrada  = "";
    $ya_justifico_entrada     = false;
    $ya_justifico_salida      = false;

    $es_fin_semana = ($dia_semana_hoy >= 6);

    $hora_esperada       = (!empty($empleado['hora_entrada_personalizada'])) ? $empleado['hora_entrada_personalizada'] : $config['hora_entrada_general'];
    $hora_salida_esperada = (!empty($empleado['hora_salida_personalizada'])) ? $empleado['hora_salida_personalizada']  : $config['hora_salida_general'];
    $tolerancia          = $config['minutos_tolerancia'];

    $hora_actual   = date('H:i:s');
    $limite_entrada = date('H:i:s', strtotime("+$tolerancia minutes", strtotime($hora_esperada)));
    
    $es_tarde           = (strtotime($hora_actual) > strtotime($limite_entrada));
    $es_temprano_salida = (strtotime($hora_actual) < strtotime($hora_salida_esperada));

    if ($id_personal) {
        $stmt_check = $conexion->prepare("SELECT hora_entrada, hora_salida, estado_justificacion, motivo_justificacion FROM asistencias WHERE id_personal = :id AND fecha = CURDATE()");
        $stmt_check->execute([':id' => $id_personal]);
        $registro_hoy = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($registro_hoy) {
            if ($registro_hoy['hora_entrada'] !== null) {
                $asistencia_hoy          = true;
                $hora_entrada_registrada = $registro_hoy['hora_entrada'];
                if ($registro_hoy['hora_salida'] !== null) {
                    $ya_salio = true; 
                }
            }
            if (!empty($registro_hoy['estado_justificacion'])) {
                if (strpos($registro_hoy['motivo_justificacion'], '[Llegada Tardía]') !== false) {
                    $ya_justifico_entrada = true;
                }
                if (strpos($registro_hoy['motivo_justificacion'], '[Salida Temprana]') !== false) {
                    $ya_justifico_salida = true;
                }
            }
        }
    }

    // === CÁLCULO DE DATOS PARA TARJETAS Y GRÁFICOS ===
    if ($id_rol == 1 || $id_rol == 2) {
        $titulo_tarjeta_1 = "Personal Registrado";
        $stmt1 = $conexion->query("SELECT COUNT(*) FROM personal");
        $valor_tarjeta_1 = $stmt1->fetchColumn();
        
        $titulo_tarjeta_2 = "Asistencias Hoy";
        $stmt2 = $conexion->query("SELECT COUNT(*) FROM asistencias WHERE fecha = CURDATE() AND hora_entrada IS NOT NULL");
        $valor_tarjeta_2 = $stmt2->fetchColumn();

        $titulo_tarjeta_3 = "Inasistencias Hoy";
        $stmt_total_activos = $conexion->query(
            "SELECT COUNT(*) FROM personal p 
             INNER JOIN usuarios u ON p.id_usuario = u.id_usuario 
             WHERE u.estado = 'Activo' AND p.fecha_ingreso <= CURDATE()"
        );
        $total_activos_hoy = (int) $stmt_total_activos->fetchColumn();

        $stmt_no_ausentes = $conexion->query(
            "SELECT COUNT(*) FROM asistencias 
             WHERE fecha = CURDATE() 
             AND (hora_entrada IS NOT NULL OR estado = 'Justificado')"
        );
        $no_ausentes_hoy = (int) $stmt_no_ausentes->fetchColumn();

        $valor_tarjeta_3 = max(0, $total_activos_hoy - $no_ausentes_hoy);

        $stmt_retrasos = $conexion->query(
            "SELECT COUNT(*) FROM asistencias 
             WHERE fecha = CURDATE() 
             AND (
                 estado LIKE '%Retraso%'
                 OR (
                     motivo_justificacion LIKE '%[Llegada Tardía]%' 
                     AND estado_justificacion = 'Pendiente'
                 )
             )"
        );
        $retrasos_hoy  = $stmt_retrasos->fetchColumn();
        $puntuales_hoy = max(0, (int)$valor_tarjeta_2 - (int)$retrasos_hoy);

        $stmt_pendientes = $conexion->query("SELECT COUNT(*) FROM asistencias WHERE estado_justificacion = 'Pendiente'");
        $just_pendientes = $stmt_pendientes->fetchColumn();

    } else {
        $titulo_tarjeta_1 = "Mis Asistencias (Mes)";
        $stmt1 = $conexion->prepare("SELECT COUNT(*) FROM asistencias WHERE id_personal = :id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND hora_entrada IS NOT NULL");
        $stmt1->execute([':id' => $id_personal]);
        $valor_tarjeta_1 = $stmt1->fetchColumn();
        
        $titulo_tarjeta_2 = "Faltas Justificadas";
        $stmt2 = $conexion->prepare("SELECT COUNT(*) FROM asistencias WHERE id_personal = :id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estado = 'Justificado'");
        $stmt2->execute([':id' => $id_personal]);
        $valor_tarjeta_2 = $stmt2->fetchColumn();
        
        $titulo_tarjeta_3 = "Mis Retrasos (Mes)";
        $stmt3 = $conexion->prepare(
            "SELECT COUNT(*) FROM asistencias 
             WHERE id_personal = :id 
             AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())
             AND (
                 estado LIKE '%Retraso%'
                 OR (
                     motivo_justificacion LIKE '%[Llegada Tardía]%' 
                     AND estado_justificacion = 'Pendiente'
                 )
             )"
        );
        $stmt3->execute([':id' => $id_personal]);
        $valor_tarjeta_3 = $stmt3->fetchColumn();

        $stmt_faltas = $conexion->prepare("SELECT COUNT(*) FROM asistencias WHERE id_personal = :id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estado = 'Falta'");
        $stmt_faltas->execute([':id' => $id_personal]);
        $faltas_injustificadas = $stmt_faltas->fetchColumn();
    }
} catch (PDOException $e) {
    $valor_tarjeta_1 = "-"; $valor_tarjeta_2 = "-"; $valor_tarjeta_3 = "-";
}

// =====================================================================
// CORRECCIÓN BUG 4 (parte PHP): Generamos el token CSRF aquí.
// Se genera una sola vez por sesión (o se regenera si no existe).
// El mismo token se inyecta como campo oculto en el formulario de
// justificación más abajo.
// =====================================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - CEIS Julian Yánez</title>
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            const temaGuardado = localStorage.getItem('tema_usuario_' + idUsr) || 'light';
            document.documentElement.setAttribute('data-theme', temaGuardado);
        })();
    </script>
</head>
<body>

    <?php $pagina_activa = 'inicio'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Panel de Control'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            <h1>Bienvenido al Sistema</h1>
            <p style="margin-block-end: 15px;">Seleccione una opción del menú para comenzar.</p>

            <div class="panel-asistencia">
                <h1>Registro Diario</h1>
                <div class="botones-asistencia">
                    <?php if ($es_fin_semana): ?>
                        <div class="mensaje-fin-semana">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: block; margin: 0 auto 10px auto;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            ¡Feliz Fin de Semana!<br>
                            <span>El sistema de registros y justificaciones está deshabilitado hasta el lunes.</span>
                        </div>
                    <?php else: ?>
                        <?php if (!$asistencia_hoy): ?>
                            <?php if ($es_tarde && !$ya_justifico_entrada): ?>
                                <button type="button" class="btn-marcar-entrada" style="background-color: #ef4444; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);" onclick="abrirModalJustificacion('Llegada Tardía')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    Justificar Llegada Tardía
                                </button>
                            <?php else: ?>
                                <form action="../controladores/ControladorAsistencia.php" method="POST">
                                    <input type="hidden" name="accion" value="marcar_entrada">
                                    <button type="submit" class="btn-marcar-entrada" id="btnAsistencia" <?php echo ($es_tarde && $ya_justifico_entrada) ? 'style="background-color: #f59e0b; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);"' : ''; ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <?php echo ($es_tarde && $ya_justifico_entrada) ? 'Registrar Entrada (Retraso)' : 'Registrar Entrada'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php elseif ($asistencia_hoy && !$ya_salio): ?>
                            <?php if ($es_temprano_salida && !$ya_justifico_salida): ?>
                                <button type="button" class="btn-marcar-salida" style="background-color: #94a3b8; cursor: not-allowed; box-shadow: none;" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    ¡Espera a tu salida!
                                </button>
                            <?php else: ?>

                                <form action="../controladores/ControladorAsistencia.php" method="POST">
                                    <input type="hidden" name="accion" value="marcar_salida">
                                    <button type="submit" class="btn-marcar-salida" id="btnSalida" <?php echo ($es_temprano_salida && $ya_justifico_salida) ? 'style="background-color: #3b82f6; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);"' : ''; ?>>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        <?php echo ($es_temprano_salida && $ya_justifico_salida) ? 'Registrar Salida Temprana' : 'Registrar Salida'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="mensaje-jornada-completada">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="texto-jornada">Jornada Completada<br>¡Hasta la próxima!</span>
                            </div>
                        <?php endif; ?>

                        <button type="button" class="btn-justificacion" onclick="abrirModalJustificacion()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Crear Justificación
                        </button>
                    <?php endif; ?>
                </div>
                
                <p class="texto-estado-asistencia">
                    <?php 
                        if ($es_fin_semana) {
                            echo "Los reportes y los demas módulos están habilitados, pero el registro de asistencia y justificaciones se encuentra pausado.";
                        } else {
                            if (!$asistencia_hoy) {
                                if ($es_tarde && !$ya_justifico_entrada) {
                                    echo "Has excedido tu tiempo límite de llegada. Por favor, <strong>justifica tu retraso</strong> para habilitar el botón de entrada.";
                                } elseif ($es_tarde && $ya_justifico_entrada) {
                                    echo "Justificación enviada a la Dirección. <strong>Ahora debes registrar tu entrada físicamente.</strong>";
                                } else {
                                    echo "Aún no has registrado tu entrada el día de hoy.";
                                }
                            } elseif ($asistencia_hoy && !$ya_salio) {
                                if ($es_temprano_salida && !$ya_justifico_salida) {
                                    echo "Entrada registrada a las <strong>" . date('h:i A', strtotime($hora_entrada_registrada)) . "</strong>. Tu botón de salida se habilitará a las " . date('h:i A', strtotime($hora_salida_esperada)) . ".";
                                } elseif ($es_temprano_salida && $ya_justifico_salida) {
                                    echo "Entrada: <strong>" . date('h:i A', strtotime($hora_entrada_registrada)) . "</strong>. Permiso procesado, <strong>ya puedes registrar tu salida temprana.</strong>";
                                } else {
                                    echo "Entrada registrada a las <strong>" . date('h:i A', strtotime($hora_entrada_registrada)) . "</strong>. ¡Es hora de ir a casa, no olvides marcar tu salida!";
                                }
                            } else {
                                echo "Has completado tu registro de asistencia de hoy exitosamente.";
                            }
                        }
                    ?>
                </p>
            </div>
            
            <div class="grid-tarjetas">
                <div class="tarjeta"><h3><?php echo htmlspecialchars($valor_tarjeta_1); ?></h3><p><?php echo htmlspecialchars($titulo_tarjeta_1); ?></p></div>
                <div class="tarjeta"><h3><?php echo htmlspecialchars($valor_tarjeta_2); ?></h3><p><?php echo htmlspecialchars($titulo_tarjeta_2); ?></p></div>
                <div class="tarjeta" style="border-block-end-color: #ef4444;"><h3><?php echo htmlspecialchars($valor_tarjeta_3); ?></h3><p><?php echo htmlspecialchars($titulo_tarjeta_3); ?></p></div>
            </div>

            <?php if ($es_fin_semana): ?>
                <div class="banner-pausa">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="banner-pausa-icono">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM9 8.25a.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75h.75a.75.75 0 0 0 .75-.75V9a.75.75 0 0 0-.75-.75H9Zm5.25 0a.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75H15a.75.75 0 0 0 .75-.75V9a.75.75 0 0 0-.75-.75h-.75Z" clip-rule="evenodd" />
                    </svg>
                    <div class="banner-pausa-texto">
                        <h3>Estadísticas en Pausa</h3>
                        <p>El monitoreo gráfico en tiempo real se reactivará el próximo día hábil.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid-graficos">
                    <div class="tarjeta-grafico"><h3 id="tituloGrafico1">Cargando...</h3><div class="contenedor-canvas"><canvas id="grafico1"></canvas></div></div>
                    <div class="tarjeta-grafico"><h3 id="tituloGrafico2">Cargando...</h3><div class="contenedor-canvas"><canvas id="grafico2"></canvas></div></div>
                    <div class="tarjeta-grafico"><h3 id="tituloGrafico3">Cargando...</h3><div class="contenedor-canvas"><canvas id="grafico3"></canvas></div></div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-contenido" id="modalJustificacion">
            <div class="modal-header">
                <h2 id="modal_j_titulo">Justificar Incidencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModales()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <form action="../controladores/ControladorJustificacion.php" method="POST" enctype="multipart/form-data" id="formJustificacion" novalidate>
                <input type="hidden" name="id_personal" value="<?php echo $id_personal; ?>">

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <p style="font-size: 0.85rem; margin-block-end: 15px; color: var(--text-color);">Detalla el motivo de tu incidencia y adjunta una prueba si es necesario.</p>

                <div class="grupo-input grupo-input-modal">
                    <label>Fecha de la Incidencia</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <input type="date" name="fecha_justificacion" id="modal_j_fecha" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="grupo-input grupo-input-modal">
                    <label>Tipo de Incidencia</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <select name="tipo_incidencia" id="modal_j_tipo" required class="select-justificacion">
                            <option value="" disabled selected>Selecciona una opción...</option>
                            <option value="Inasistencia">Falté todo el día</option>
                            <option value="Llegada Tardía">Llegué tarde</option>
                            <option value="Salida Temprana">Me fui antes de la hora</option>
                        </select>
                    </div>
                </div>

                <div class="grupo-input grupo-input-modal">
                    <label>Motivo / Explicación</label>
                    <div class="input-con-icono">
                        <textarea name="motivo" id="modal_j_motivo" placeholder="Escribe aquí los detalles..." required></textarea>
                    </div>
                    <div id="error-motivo" class="error-inline">El motivo debe tener al menos 15 caracteres.</div>
                </div>

                <div class="grupo-input grupo-input-modal ultimo">
                    <label>Evidencia (Opcional - PDF, JPG, PNG)</label>
                    <div class="contenedor-archivo">
                        <input type="file" name="archivo_evidencia" id="modal_j_archivo" accept=".pdf, .jpg, .jpeg, .png" class="input-file-oculto">
                        <label for="modal_j_archivo" class="btn-subir-archivo">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span id="texto-archivo">Seleccionar archivo...</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-guardar" style="inline-size: 100%; justify-content: center;">Enviar a Dirección</button>
            </form>
        </div>
    </div>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script src="../recursos/js/chart.min.js"></script>

    <script>
        const html = document.documentElement;

        // --- 1. LÓGICA DE NOTIFICACIÓN SWEETALERT2 (TOP BAR) ---
        const ToastSwal = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        function mostrarToast(tipo, titulo, mensaje) {
            ToastSwal.fire({
                icon: tipo === 'success' ? 'success' : 'error',
                title: titulo,
                html: mensaje
            });
        }

        <?php if(isset($_SESSION['alerta_principal'])): 
            $tipo_t   = $_SESSION['alerta_principal']['tipo'] == 'success' ? 'success' : 'error';
            $titulo_t = $_SESSION['alerta_principal']['tipo'] == 'success' ? '¡Éxito!' : 'Aviso';
        ?>
            mostrarToast('<?php echo $tipo_t; ?>', '<?php echo $titulo_t; ?>', '<?php echo $_SESSION['alerta_principal']['mensaje']; ?>');
            <?php unset($_SESSION['alerta_principal']); ?>
        <?php endif; ?>

        // --- 2. VALIDACIÓN FRONTEND FORMULARIO ---
        const inputMotivo = document.getElementById('modal_j_motivo');
        const errorMotivo = document.getElementById('error-motivo');
        const formJ       = document.getElementById('formJustificacion');

        if(inputMotivo) {
            inputMotivo.addEventListener('input', function() {
                if (this.value.trim().length > 0 && this.value.trim().length < 15) {
                    this.classList.add('input-error');
                    errorMotivo.style.display = 'block';
                } else {
                    this.classList.remove('input-error');
                    errorMotivo.style.display = 'none';
                }
            });
        }

        if(formJ) {
            formJ.addEventListener('submit', function(e) {
                let errores = [];
                const valFecha = document.getElementById('modal_j_fecha').value;
                const valTipo  = document.getElementById('modal_j_tipo').value;
                
                inputMotivo.classList.remove('input-error');
                errorMotivo.style.display = 'none';

                if (!valFecha) errores.push("• Debes seleccionar la fecha de la incidencia.");
                if (!valTipo)  errores.push("• Debes seleccionar un tipo de incidencia.");
                if (inputMotivo.value.trim().length < 15) {
                    inputMotivo.classList.add('input-error');
                    errorMotivo.style.display = 'block';
                    errores.push("• El motivo debe tener al menos 15 caracteres.");
                }

                if (errores.length > 0) {
                    e.preventDefault(); 
                    mostrarToast('error', 'Datos Incompletos', errores.join('<br>'));
                }
            });
        }

        // --- 3. MODALES ---
        const modalOverlay      = document.getElementById('modalOverlay');
        const modalJustificacion = document.getElementById('modalJustificacion');
        const fechaInput        = document.getElementById('modal_j_fecha');

        window.abrirModalJustificacion = function(tipo = '') {
            const hoy = new Date();
            if(hoy.getDay() !== 0 && hoy.getDay() !== 6) {
                fechaInput.valueAsDate = hoy;
            } else {
                fechaInput.value = '';
            }
            const selectTipo = document.getElementById('modal_j_tipo');
            if(tipo) { selectTipo.value = tipo; } else { selectTipo.selectedIndex = 0; }

            const motivo   = document.getElementById('modal_j_motivo');
            const errorMot = document.getElementById('error-motivo');
            if(motivo)   { motivo.value = ''; motivo.classList.remove('input-error'); }
            if(errorMot) { errorMot.style.display = 'none'; }
            document.getElementById('texto-archivo').textContent = 'Seleccionar archivo...';
            const archivoInp = document.getElementById('modal_j_archivo');
            if(archivoInp) archivoInp.value = '';

            modalJustificacion.scrollTop = 0;
            modalJustificacion.classList.remove('cerrando');
            modalOverlay.classList.remove('cerrando');
            modalOverlay.classList.add('activo');
            modalJustificacion.classList.add('activo');
        };

        window.cerrarModales = function() {
            modalJustificacion.classList.add('cerrando');
            modalOverlay.classList.add('cerrando');
            setTimeout(function() {
                modalOverlay.classList.remove('activo', 'cerrando');
                modalJustificacion.classList.remove('activo', 'cerrando');
            }, 220);
        };

        if(modalOverlay) {
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === modalOverlay) cerrarModales();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalOverlay.classList.contains('activo')) {
                cerrarModales();
            }
        });

        const archivoInput = document.getElementById('modal_j_archivo');
        if (archivoInput) {
            archivoInput.addEventListener('change', function(e) {
                var nombreArchivo = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                document.getElementById('texto-archivo').textContent = nombreArchivo;
            });
        }

        // --- 4. GRÁFICAS Y MODO OSCURO (DOM LISTO) ---
        document.addEventListener('DOMContentLoaded', function() {
            
            const btnCambiarTema = document.getElementById('btnCambiarTema');
            
            if (btnCambiarTema) {
                btnCambiarTema.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    this.classList.add('girando'); 
                    
                    const temaActual = html.getAttribute('data-theme');
                    const nuevoTema  = temaActual === 'light' ? 'dark' : 'light';
                    html.setAttribute('data-theme', nuevoTema);
                    localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);

                    const nuevoColorTexto = nuevoTema === 'dark' ? '#cbd5e1' : '#64748b';
                    const nuevoColorGrid  = nuevoTema === 'dark' ? '#334155' : '#e2e8f0';

                    if (typeof Chart !== 'undefined') {
                        for (let id in Chart.instances) {
                            let chart = Chart.instances[id];
                            
                            if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                                chart.options.plugins.legend.labels.color = nuevoColorTexto;
                            }
                            if (chart.options.scales) {
                                if (chart.options.scales.x) chart.options.scales.x.ticks.color = nuevoColorTexto;
                                if (chart.options.scales.y) {
                                    chart.options.scales.y.ticks.color = nuevoColorTexto;
                                    if(chart.options.scales.y.grid) chart.options.scales.y.grid.color = nuevoColorGrid;
                                }
                                if (chart.options.scales.r) { 
                                    if(chart.options.scales.r.grid) chart.options.scales.r.grid.color = nuevoColorGrid;
                                }
                            }
                            chart.update();
                        }
                    }

                    setTimeout(() => { this.classList.remove('girando'); }, 500);
                });
            }

            // B. RENDERIZADO DE LAS GRÁFICAS
            try {
                const esFinSemana = <?php echo $es_fin_semana ? 'true' : 'false'; ?>;
                if (esFinSemana) return; 

                const rolUser     = <?php echo $id_rol; ?>;
                const colorTexto  = html.getAttribute('data-theme') === 'dark' ? '#cbd5e1' : '#64748b';
                const colorGrid   = html.getAttribute('data-theme') === 'dark' ? '#334155' : '#e2e8f0';
                
                const t1 = parseInt("<?php echo is_numeric($valor_tarjeta_1) ? $valor_tarjeta_1 : 0; ?>") || 0; 
                const t2 = parseInt("<?php echo is_numeric($valor_tarjeta_2) ? $valor_tarjeta_2 : 0; ?>") || 0; 
                const t3 = parseInt("<?php echo is_numeric($valor_tarjeta_3) ? $valor_tarjeta_3 : 0; ?>") || 0; 

                const retrasosHoy        = parseInt("<?php echo isset($retrasos_hoy) && is_numeric($retrasos_hoy) ? $retrasos_hoy : 0; ?>") || 0;
                const puntualesHoy       = parseInt("<?php echo isset($puntuales_hoy) && is_numeric($puntuales_hoy) ? $puntuales_hoy : 0; ?>") || 0;
                const justPendientes     = parseInt("<?php echo isset($just_pendientes) && is_numeric($just_pendientes) ? $just_pendientes : 0; ?>") || 0;
                const faltasInjustificadas = parseInt("<?php echo isset($faltas_injustificadas) && is_numeric($faltas_injustificadas) ? $faltas_injustificadas : 0; ?>") || 0;

                let d_labels_1 = [], d_data_1 = [], d_colors_1 = [];
                let d_labels_2 = [], d_data_2 = [], d_colors_2 = [];
                let d_labels_3 = [], d_data_3 = [], d_colors_3 = [];

                if (rolUser == 1 || rolUser == 2) {
                    document.getElementById('tituloGrafico1').innerText = "Asistencia General Hoy";
                    document.getElementById('tituloGrafico2').innerText = "Bandeja de Justificaciones";
                    document.getElementById('tituloGrafico3').innerText = "Calidad de Llegada Hoy";

                    d_labels_1 = ['Presentes', 'Ausentes/Faltas'];
                    d_data_1   = [t2, t3];
                    d_colors_1 = ['#3b82f6', '#ef4444'];

                    d_labels_2 = ['Pendientes (Revisar)', 'Aprobadas'];
                    d_data_2   = [justPendientes, t2]; 
                    d_colors_2 = ['#f59e0b', '#10b981'];

                    d_labels_3 = ['Llegaron Puntuales', 'Llegaron Tarde'];
                    d_data_3   = [puntualesHoy, retrasosHoy];
                    d_colors_3 = ['#10b981', '#f59e0b'];
                } else {
                    document.getElementById('tituloGrafico1').innerText = "Mis Llegadas (Mes)";
                    document.getElementById('tituloGrafico2').innerText = "Balance del Mes";
                    document.getElementById('tituloGrafico3').innerText = "Mis Inasistencias (Mes)";

                    const puntuales   = (t1 - t3) > 0 ? (t1 - t3) : 0;
                    const totalFaltas = t2 + faltasInjustificadas;

                    d_labels_1 = ['Llegadas Puntuales', 'Retrasos'];
                    d_data_1   = [puntuales, t3];
                    d_colors_1 = ['#10b981', '#f59e0b'];

                    d_labels_2 = ['Días Asistidos', 'Total Faltas'];
                    d_data_2   = [t1, totalFaltas];
                    d_colors_2 = ['#3b82f6', '#ef4444'];

                    d_labels_3 = ['Faltas Justificadas', 'Faltas Sin Justificar'];
                    d_data_3   = [t2, faltasInjustificadas];
                    d_colors_3 = ['#8b5cf6', '#ef4444'];
                }

                const opcionesComunes = {
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: colorTexto, font: { size: 11, family: "'Montserrat', sans-serif" } } } }
                };

                const hayDatos = d_data_1.reduce((a, b) => a + b, 0) > 0 || d_data_2.reduce((a, b) => a + b, 0) > 0 || d_data_3.reduce((a, b) => a + b, 0) > 0;

                if (hayDatos) {
                    new Chart(document.getElementById('grafico1').getContext('2d'), {
                        type: 'doughnut',
                        data: { labels: d_labels_1, datasets: [{ data: d_data_1, backgroundColor: d_colors_1, borderWidth: 0, hoverOffset: 4 }] },
                        options: { ...opcionesComunes, cutout: '70%' }
                    });

                    new Chart(document.getElementById('grafico2').getContext('2d'), {
                        type: 'bar',
                        data: { labels: d_labels_2, datasets: [{ label: 'Cantidad', data: d_data_2, backgroundColor: d_colors_2, borderRadius: 6 }] },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                y: { beginAtZero: true, grid: { color: colorGrid }, ticks: { color: colorTexto, stepSize: 1 } },
                                x: { grid: { display: false }, ticks: { color: colorTexto } }
                            }
                        }
                    });

                    new Chart(document.getElementById('grafico3').getContext('2d'), {
                        type: 'polarArea',
                        data: { labels: d_labels_3, datasets: [{ data: d_data_3, backgroundColor: d_colors_3, borderWidth: 0 }] },
                        options: {
                            ...opcionesComunes,
                            scales: { r: { ticks: { display: false }, grid: { color: colorGrid } } }
                        }
                    });
                } else {
                    document.querySelector('.grid-graficos').innerHTML = '<div style="width: 100%; text-align: center; padding: 40px; color: var(--text-color); font-weight: bold;">Aún no hay suficientes datos registrados para generar las gráficas.</div>';
                }

            } catch(e) {
                console.error("No se pudieron cargar las gráficas: ", e);
            }
        });
    </script>
</body>
</html>