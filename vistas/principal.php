<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

// ¡VITAL! Configurar la zona horaria a Venezuela
date_default_timezone_set('America/Caracas');

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

$titulo_tarjeta_1 = "Cargando..."; $valor_tarjeta_1 = 0;
$titulo_tarjeta_2 = "Cargando..."; $valor_tarjeta_2 = 0;
$titulo_tarjeta_3 = "Cargando..."; $valor_tarjeta_3 = 0;

try {
    // ========================================================
    // === SCRIPT CENTINELA (AUTO-FALTA E IRREGULAR) ===
    // ========================================================
    
    $hora_actual_sec = date('H:i:s');
    $stmt_conf_limit = $conexion->query("SELECT hora_entrada_general, hora_salida_general FROM configuracion WHERE id_config = 1");
    $conf_limit = $stmt_conf_limit->fetch(PDO::FETCH_ASSOC);

    if ($conf_limit) {
        
        $dia_semana_hoy = date('N'); // 1 es Lunes, 5 es Viernes, 6 Sabado, 7 Domingo
        
        // TAREA 1: AUTO-FALTA (SOLO DE LUNES A VIERNES Y RESPETANDO FECHA DE INGRESO)
        if ($dia_semana_hoy <= 5) {
            $sql_ausentes = "SELECT p.id_personal, p.hora_entrada_personalizada, p.hora_salida_personalizada 
                             FROM personal p
                             INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                             WHERE u.estado = 'Activo' 
                             AND p.fecha_ingreso <= CURDATE()
                             AND p.id_personal NOT IN (SELECT id_personal FROM asistencias WHERE fecha = CURDATE())";
            $ausentes = $conexion->query($sql_ausentes)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ausentes as $aus) {
                $h_entrada_p = !empty($aus['hora_entrada_personalizada']) ? $aus['hora_entrada_personalizada'] : $conf_limit['hora_entrada_general'];
                $h_salida_p = !empty($aus['hora_salida_personalizada']) ? $aus['hora_salida_personalizada'] : $conf_limit['hora_salida_general'];

                if ($hora_actual_sec > $h_salida_p) {
                    $ins_falta = $conexion->prepare("INSERT INTO asistencias (id_personal, fecha, hora_esperada, estado) VALUES (?, CURDATE(), ?, 'Falta')");
                    $ins_falta->execute([$aus['id_personal'], $h_entrada_p]);
                }
            }
        }

        // TAREA 2: AUTO-SALIDA IRREGULAR (Se ejecuta todos los días por si alguien quedó colgado el viernes)
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
            $h_salida_p = !empty($inc['hora_salida_personalizada']) ? $inc['hora_salida_personalizada'] : $conf_limit['hora_salida_general'];
            $limite_salida = date('H:i:s', strtotime("+60 minutes", strtotime($h_salida_p)));

            if ($inc['fecha'] < $fecha_hoy_comparar || ($inc['fecha'] == $fecha_hoy_comparar && $hora_actual_sec > $limite_salida)) {
                $estado_actual = $inc['estado'];
                $nuevo_estado = 'Salida Irregular';
                
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
    // ========================================================
    // === FIN SCRIPT CENTINELA ===============================
    // ========================================================

    $stmt_emp = $conexion->prepare("SELECT id_personal, hora_entrada_personalizada, hora_salida_personalizada FROM personal WHERE id_usuario = :id_user");
    $stmt_emp->execute([':id_user' => $id_usuario]);
    $empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);
    $id_personal = $empleado ? $empleado['id_personal'] : null;

    $stmt_conf = $conexion->query("SELECT hora_entrada_general, hora_salida_general, minutos_tolerancia FROM configuracion WHERE id_config = 1");
    $config = $stmt_conf->fetch(PDO::FETCH_ASSOC) ?: ['hora_entrada_general' => '07:00:00', 'hora_salida_general' => '13:00:00', 'minutos_tolerancia' => 15];

    $asistencia_hoy = false;
    $ya_salio = false;
    $hora_entrada_registrada = "";

    $hora_esperada = (!empty($empleado['hora_entrada_personalizada'])) ? $empleado['hora_entrada_personalizada'] : $config['hora_entrada_general'];
    $hora_salida_esperada = (!empty($empleado['hora_salida_personalizada'])) ? $empleado['hora_salida_personalizada'] : $config['hora_salida_general'];
    $tolerancia = $config['minutos_tolerancia'];

    $hora_actual = date('H:i:s');
    $limite_entrada = date('H:i:s', strtotime("+$tolerancia minutes", strtotime($hora_esperada)));
    
    $es_tarde = ($hora_actual > $limite_entrada);
    $es_temprano_salida = ($hora_actual < $hora_salida_esperada);

    if ($id_personal) {
        $stmt_check = $conexion->prepare("SELECT hora_entrada, hora_salida FROM asistencias WHERE id_personal = :id AND fecha = CURDATE()");
        $stmt_check->execute([':id' => $id_personal]);
        $registro_hoy = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($registro_hoy) {
            $asistencia_hoy = true;
            $hora_entrada_registrada = $registro_hoy['hora_entrada'];
            if ($registro_hoy['hora_salida'] !== null) {
                $ya_salio = true; 
            }
        }
    }

    if ($id_rol == 1 || $id_rol == 2) {
        $titulo_tarjeta_1 = "Personal Registrado";
        $stmt1 = $conexion->query("SELECT COUNT(*) FROM personal");
        $valor_tarjeta_1 = $stmt1->fetchColumn();
        
        $titulo_tarjeta_2 = "Asistencias Hoy";
        $stmt2 = $conexion->query("SELECT COUNT(*) FROM asistencias WHERE fecha = CURDATE() AND hora_entrada IS NOT NULL");
        $valor_tarjeta_2 = $stmt2->fetchColumn();
        
        $titulo_tarjeta_3 = "Inasistencias Hoy";
        $valor_tarjeta_3 = $valor_tarjeta_1 - $valor_tarjeta_2; 
        if ($valor_tarjeta_3 < 0) $valor_tarjeta_3 = 0; 
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
        $stmt3 = $conexion->prepare("SELECT COUNT(*) FROM asistencias WHERE id_personal = :id AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) AND estado LIKE '%Retraso%'");
        $stmt3->execute([':id' => $id_personal]);
        $valor_tarjeta_3 = $stmt3->fetchColumn();
    }
} catch (PDOException $e) {
    $valor_tarjeta_1 = "-"; $valor_tarjeta_2 = "-"; $valor_tarjeta_3 = "-";
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
                    
                    <?php if (!$asistencia_hoy): ?>
                        
                        <?php if ($es_tarde): ?>
                            <button type="button" class="btn-marcar-entrada" style="background-color: #ef4444; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);" onclick="abrirModalJustificacion('Llegada Tardía')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Justificar Llegada Tardía
                            </button>
                        <?php else: ?>
                            <form action="../controladores/ControladorAsistencia.php" method="POST">
                                <input type="hidden" name="accion" value="marcar_entrada">
                                <button type="submit" class="btn-marcar-entrada" id="btnAsistencia">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Registrar Entrada
                                </button>
                            </form>
                        <?php endif; ?>

                    <?php elseif ($asistencia_hoy && !$ya_salio): ?>
                        
                        <?php if ($es_temprano_salida): ?>
                            <button type="button" class="btn-marcar-salida" disabled style="background-color: #94a3b8; cursor: not-allowed; box-shadow: none;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                ¡Espera a tu salida!
                            </button>
                        <?php else: ?>
                            <form action="../controladores/ControladorAsistencia.php" method="POST">
                                <input type="hidden" name="accion" value="marcar_salida">
                                <button type="submit" class="btn-marcar-salida" id="btnSalida">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Registrar Salida
                                </button>
                            </form>
                        <?php endif; ?>

                    <?php else: ?>
                        <div style="color: #10b981; font-size: 1.5rem; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Jornada Completada por hoy
                        </div>
                    <?php endif; ?>

                    <button type="button" class="btn-justificacion" onclick="abrirModalJustificacion()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Crear Justificación
                    </button>

                </div>
                
                <p class="texto-estado-asistencia">
                    <?php 
                        if (!$asistencia_hoy) {
                            if($es_tarde) {
                                echo "Has excedido tu tiempo límite de llegada. Por favor, <strong>justifica tu retraso</strong>.";
                            } else {
                                echo "Aún no has registrado tu entrada el día de hoy.";
                            }
                        } elseif ($asistencia_hoy && !$ya_salio) {
                            echo "Entrada registrada a las <strong>" . date('h:i A', strtotime($hora_entrada_registrada)) . "</strong>. ¡No olvides marcar tu salida!";
                        } else {
                            echo "Has completado tu registro de asistencia de hoy exitosamente.";
                        }
                    ?>
                </p>
            </div>
            
            <div class="grid-tarjetas">
                <div class="tarjeta">
                    <h3><?php echo htmlspecialchars($valor_tarjeta_1); ?></h3>
                    <p><?php echo htmlspecialchars($titulo_tarjeta_1); ?></p>
                </div>
                <div class="tarjeta">
                    <h3><?php echo htmlspecialchars($valor_tarjeta_2); ?></h3>
                    <p><?php echo htmlspecialchars($titulo_tarjeta_2); ?></p>
                </div>
                <div class="tarjeta" style="border-block-end-color: #ef4444;">
                    <h3><?php echo htmlspecialchars($valor_tarjeta_3); ?></h3>
                    <p><?php echo htmlspecialchars($titulo_tarjeta_3); ?></p>
                </div>
            </div>

        </main>
    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-contenido" id="modalJustificacion">
            <div class="modal-header">
                <h2 id="modal_j_titulo">Justificar Incidencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModales()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <form action="../controladores/ControladorJustificacion.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_personal" value="<?php echo $id_personal; ?>">
                
                <p style="font-size: 0.85rem; margin-block-end: 15px; color: var(--text-color);">
                    Detalla el motivo de tu incidencia y adjunta una prueba si es necesario.
                </p>

                <div class="grupo-input" style="margin-block-end: 15px;">
                    <label>Fecha de la Incidencia</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <input type="date" name="fecha_justificacion" id="modal_j_fecha" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="grupo-input" style="margin-block-end: 15px;">
                    <label>Tipo de Incidencia</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <select name="tipo_incidencia" id="modal_j_tipo" style="inline-size: 100%; padding: 12px 15px 12px 45px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat', sans-serif; font-size: 1.1rem; font-weight: 600; outline: none;" required>
                            <option value="" disabled selected>Selecciona una opción...</option>
                            <option value="Inasistencia">Falté todo el día</option>
                            <option value="Llegada Tardía">Llegué tarde</option>
                            <option value="Salida Temprana">Me fui antes de la hora</option>
                        </select>
                    </div>
                </div>

                <div class="grupo-input" style="margin-block-end: 15px;">
                    <label>Motivo / Explicación</label>
                    <div class="input-con-icono">
                        <textarea name="motivo" id="modal_j_motivo" placeholder="Escribe aquí los detalles..." required></textarea>
                    </div>
                </div>

                <div class="grupo-input" style="margin-block-end: 25px;">
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
    <script>
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        const html = document.documentElement;
        const claveTemaPersonalizado = 'tema_usuario_<?php echo $_SESSION['id_usuario']; ?>';

        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.add('girando');
                const temaActual = html.getAttribute('data-theme');
                const nuevoTema = temaActual === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem(claveTemaPersonalizado, nuevoTema);
                setTimeout(() => { this.classList.remove('girando'); }, 500);
            });
        }

        const modalOverlay = document.getElementById('modalOverlay');
        const modalJustificacion = document.getElementById('modalJustificacion');

        function abrirModalJustificacion(tipo = '') {
            document.getElementById('modal_j_fecha').valueAsDate = new Date();
            const selectTipo = document.getElementById('modal_j_tipo');
            if(tipo) { selectTipo.value = tipo; } else { selectTipo.selectedIndex = 0; }
            modalOverlay.classList.add('activo');
            modalJustificacion.classList.add('activo');
        }

        function cerrarModales() {
            modalOverlay.classList.remove('activo');
            modalJustificacion.classList.remove('activo');
        }

        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) cerrarModales();
        });

        const archivoInput = document.getElementById('modal_j_archivo');
        if (archivoInput) {
            archivoInput.addEventListener('change', function(e) {
                var nombreArchivo = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                document.getElementById('texto-archivo').textContent = nombreArchivo;
            });
        }
    </script>

    <?php if(isset($_SESSION['alerta_principal'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '¡Éxito!' : '¡Aviso!'; ?>',
                text: '<?php echo $_SESSION['alerta_principal']['mensaje']; ?>',
                icon: '<?php echo $_SESSION['alerta_principal']['tipo']; ?>',
                confirmButtonColor: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '#10b981' : '#ef4444'; ?>',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });
        </script>
        <?php unset($_SESSION['alerta_principal']); ?>
    <?php endif; ?>

</body>
</html>