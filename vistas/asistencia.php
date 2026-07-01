<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

date_default_timezone_set('America/Caracas');
$dia_semana = date('N');
$es_fin_de_semana = ($dia_semana == 6 || $dia_semana == 7);

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

// Consulta de mis datos. Se agrega LEFT JOIN con asistencias filtrado por CURDATE() para el indicador visual.
$stmt_mi_id = $conexion->prepare("SELECT p.id_personal, p.foto_perfil, p.nombres, p.apellidos, c.nombre_cargo, 
                                         a.hora_entrada AS asistio_hoy
                                  FROM personal p 
                                  INNER JOIN cargos c ON p.id_cargo = c.id_cargo 
                                  LEFT JOIN asistencias a ON p.id_personal = a.id_personal AND a.fecha = CURDATE()
                                  WHERE p.id_usuario = ?");
$stmt_mi_id->execute([$id_usuario]);
$mis_datos = $stmt_mi_id->fetch(PDO::FETCH_ASSOC);
$mi_id_personal = $mis_datos ? $mis_datos['id_personal'] : 0;

$es_admin = ($id_rol == 1 || $id_rol == 2);

$lista_personal = [];
$cargos_activos_en_grid = [];

// Variables para los Widgets del Director
$ausentes_hoy = 0;
$justificaciones_pendientes = 0;
$hora_entrada_sys = '07:00:00';
$tolerancia_sys = 15;

if ($es_admin) {
    try {
        $stmt_config = $conexion->query("SELECT hora_entrada_general, minutos_tolerancia FROM configuracion LIMIT 1");
        if ($config_sys = $stmt_config->fetch(PDO::FETCH_ASSOC)) {
            $hora_entrada_sys = $config_sys['hora_entrada_general'];
            $tolerancia_sys = $config_sys['minutos_tolerancia'];
        }

        $stmt_just = $conexion->query("SELECT COUNT(*) FROM asistencias WHERE estado_justificacion = 'Pendiente'");
        $justificaciones_pendientes = $stmt_just->fetchColumn();

        $sql = "SELECT p.id_personal, p.cedula, p.nombres, p.apellidos, p.foto_perfil, p.id_cargo, c.nombre_cargo,
                       a.hora_entrada AS asistio_hoy
                FROM personal p
                INNER JOIN cargos c ON p.id_cargo = c.id_cargo
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                LEFT JOIN asistencias a ON p.id_personal = a.id_personal AND a.fecha = CURDATE()
                WHERE u.estado = 'Activo' AND p.id_personal != ?
                ORDER BY p.nombres ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$mi_id_personal]);
        $lista_personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cargos_activos_en_grid = array_unique(array_column($lista_personal, 'id_cargo'));

        $stmt_cargos = $conexion->query("SELECT id_cargo, nombre_cargo FROM cargos ORDER BY id_cargo ASC");
        $cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

        $total_personal = count($lista_personal);

        $stmt_no_ausentes_as = $conexion->query(
            "SELECT COUNT(*) FROM asistencias 
             WHERE fecha = CURDATE() 
             AND (hora_entrada IS NOT NULL OR estado = 'Justificado')"
        );
        $no_ausentes_hoy_as = (int) $stmt_no_ausentes_as->fetchColumn();

        $stmt_total_activos_as = $conexion->prepare(
            "SELECT COUNT(*) FROM personal p
             INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
             WHERE u.estado = 'Activo' AND p.id_personal != ?"
        );
        $stmt_total_activos_as->execute([$mi_id_personal]);
        $total_activos_as = (int) $stmt_total_activos_as->fetchColumn();

        $ausentes_hoy = max(0, $total_activos_as - $no_ausentes_hoy_as);

    } catch (PDOException $e) {}
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Asistencia - CEIS Julian Yánez</title>
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../recursos/css/guia_dinamica.css?v=<?php echo time(); ?>">
    
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            document.documentElement.setAttribute('data-theme', localStorage.getItem('tema_usuario_' + idUsr) || 'light');
        })();
    </script>
</head>
<body>

   <?php $pagina_activa = 'asistencia'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Control de Asistencia'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            
            <?php if ($es_admin): ?>
                <div class="cabecera-personal" style="margin-block-end: 15px;">
                    <div>
                        <h1>Mi Asistencia</h1>
                        <p>Revisa tu historial de entradas y salidas.</p>
                    </div>
                </div>

    <div class="contenedor-widgets-asistencia">
    
    <div class="tarjeta-perfil perfil-widget-min"> 
        <div class="banner-tarjeta" style="block-size: 70px;"></div>
        <div class="contenedor-avatar" style="margin-block-start: -40px; inline-size: 80px; block-size: 80px;">
            <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($mis_datos['foto_perfil']); ?>" alt="Mi Foto">
            <?php 
                if (!empty($mis_datos['asistio_hoy'])) {
                    echo '<span class="indicador-estatus estatus-presente" title="Asistencia marcada (' . date('h:i A', strtotime($mis_datos['asistio_hoy'])) . ')"></span>';
                } else {
                    echo '<span class="indicador-estatus estatus-ausente" title="Aún no has marcado entrada hoy"></span>';
                }
            ?>
        </div>
        <div class="info-perfil" style="padding-block-start: 10px;">
            <h3 class="nombre-empleado"><?php echo htmlspecialchars($mis_datos['nombres'] . ' ' . $mis_datos['apellidos']); ?></h3>
            <span class="cargo-empleado"><?php echo htmlspecialchars($mis_datos['nombre_cargo']); ?></span>
            
            <div class="acciones-perfil">
                <button class="btn-editar-horario" onclick="abrirCalendario(<?php echo $mi_id_personal; ?>, 'Mi Asistencia', false)" style="inline-size: 100%; background-color: var(--primary-color); color: white;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Ver mi calendario
                </button>
            </div>
        </div>
    </div>

    <?php if ($es_fin_de_semana): ?>
        <div class="fin-semana-widget">
            <svg xmlns="http://www.w3.org/2000/svg" class="fin-semana-icono" width="60" height="60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="13" r="8"></circle>
                <polyline points="11 9 11 13 14 15"></polyline>
                <path d="M15 3h4l-4 5h4"></path>
                <path d="M21 7h3l-3 4h3" stroke-width="1.5"></path>
            </svg>
            <h2 class="fin-semana-titulo">¡Feliz Fin de Semana!</h2>
            <p class="fin-semana-texto">El módulo de asistencia en tiempo real está en pausa hasta el lunes.</p>
        </div>
    <?php else: ?>
        <div class="reloj-widget">
            <svg class="reloj-bg-icon" width="150" height="150" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/><path d="M13 7h-2v5.414l3.293 3.293 1.414-1.414L13 11.586z"/></svg>

            <div class="reloj-contenido">
                <p id="reloj-saludo" class="reloj-saludo">Cargando reloj...</p>
                <h2 id="reloj-hora" class="reloj-hora">--:--:--</h2>
                <p class="reloj-fecha">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="inline-size: 16px; block-size: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span id="reloj-fecha">Cargando fecha...</span>
                </p>
            </div>
        </div>

        <div class="stats-widget">
            
            <div class="stat-tarjeta verde" id="tarjeta-estado-turno">
                <div class="stat-info">
                    <span>Estado del Turno</span>
                    <div id="texto-estatus-turno" class="stat-valor" style="font-size: 1.1rem; font-weight: 700;">Calculando...</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono verde" id="icono-estado-turno" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <div class="stat-tarjeta naranja">
                <div class="stat-info">
                    <span>Personal Ausente</span>
                    <div class="stat-valor naranja"><?php echo $ausentes_hoy; ?> <span class="limite">/ <?php echo $total_personal; ?></span></div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono naranja" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>

            <div class="stat-tarjeta rojo">
                <div class="stat-info">
                    <span>Justif. Pendientes</span>
                    <div class="stat-valor rojo"><?php echo $justificaciones_pendientes; ?></div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono rojo" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>

        </div>
    <?php endif; ?>

            </div>

                <div class="separador-personal" style="margin-block-end: 20px;">Asistencia del Personal</div>

                <div class="contenedor-filtros-globales" style="justify-content: center; margin-inline: auto; max-inline-size: 500px; margin-block-end: 25px; padding: 15px;">
                    <div class="contenedor-busqueda-elegante" style="margin: 0; inline-size: 100%;">
                      <input type="text" id="buscador-universal" class="campo-busqueda-elegante" placeholder="Buscar por nombre o cargo...">
                        <svg class="icono-busqueda" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <div class="botones-filtro-cargo" style="margin-block-end: 30px;">
                    <button class="btn-filtro activo" onclick="aplicarFiltroUniversal('todos', this, true)">Todos</button>
                    <?php foreach($cargos as $c): ?>
                        <?php if(in_array($c['id_cargo'], $cargos_activos_en_grid)): ?>
                            <button class="btn-filtro" onclick="aplicarFiltroUniversal(<?php echo $c['id_cargo']; ?>, this, true)">
                                <?php echo htmlspecialchars($c['nombre_cargo']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="grid-perfiles">
                    <?php foreach ($lista_personal as $emp): ?>
                        <div class="tarjeta-perfil item-filtrable" data-cargo="<?php echo $emp['id_cargo']; ?>">
                            <div class="banner-tarjeta" style="block-size: 70px;"></div>
                            <div class="contenedor-avatar" style="margin-block-start: -40px; inline-size: 80px; block-size: 80px;">
                                <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($emp['foto_perfil']); ?>" alt="Foto">
                                <?php 
                                    if (!empty($emp['asistio_hoy'])) {
                                        echo '<span class="indicador-estatus estatus-presente" title="Asistió hoy (' . date('h:i A', strtotime($emp['asistio_hoy'])) . ')"></span>';
                                    } else {
                                        echo '<span class="indicador-estatus estatus-ausente" title="Aún no ha marcado entrada hoy"></span>';
                                    }
                                ?>
                            </div>
                            <div class="info-perfil" style="padding-block-start: 10px;">
                                <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                                <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                                
                                <div class="acciones-perfil">
                                    <button class="btn-editar-horario" onclick="abrirCalendario(<?php echo $emp['id_personal']; ?>, '<?php echo addslashes($emp['nombres']); ?>', true)" style="inline-size: 100%;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                            <line x1="12" y1="11" x2="16" y2="11"/>
                                            <line x1="12" y1="15" x2="16" y2="15"/>
                                            <line x1="8" y1="11" x2="8.01" y2="11"/>
                                            <line x1="8" y1="15" x2="8.01" y2="15"/>
                                        </svg>
                                        Revisar Asistencia
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="contenedor-ver-mas" style="text-align: center; margin-block-start: 10px; margin-block-end: 30px; display: none;">
                    <button id="btn-ver-mas" class="btn-guardar" style="background-color: var(--primary-color); border-radius: 25px; padding: 0.8rem 2rem; font-size: 0.95rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="inline-size: 20px; block-size: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        Cargar más personal
                    </button>
                </div>

            <?php else: ?>
                <div class="cabecera-personal">
                    <div>
                        <h1>Mi Historial de Asistencia</h1>
                        <p>Calendario de tus entradas, retrasos y justificaciones.</p>
                    </div>
                </div>

               <div class="tarjeta-formulario-config" style="max-inline-size: 900px; margin: 0 auto; padding: 1.5rem 2rem;">
                 <div id="contenedor-calendario-inline"></div>
              </div>
            <?php endif; ?>

        </main>
    </div>

    <?php if ($es_admin): ?>
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-contenido" id="modalCalendario">
            <button id="btnFlotanteRangoModal" onclick="ejecutarModalRango()" class="btn-flotante-rango-modal" style="display: none;" title="Justificar Múltiples Días">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v6m-3-3h6" />
                </svg>
            </button>

            <div class="modal-header">
                <h2 id="titulo_modal_calendario" style="font-size: 1.2rem;">Asistencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <div id="contenedor-calendario-modal"></div>
        </div>
    </div>

    <div class="modal-overlay" id="modalRango">
        <div id="contenidoModalRango" class="modal-contenido" style="max-width: 550px; padding: 2.5rem; border-radius: 16px; z-index: 1001;">
            <div class="modal-header" style="margin-bottom: 25px;">
                <h2 style="font-size: 1.4rem; color: var(--primary-color);">Justificación Múltiple</h2>
                <button type="button" class="btn-cerrar-modal" onclick="cerrarModalRango()"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <form id="formJustificacionRango" enctype="multipart/form-data" novalidate>
                
                <div class="grupo-input-modal">
                    <label>Personal a Justificar:</label>
                    <input type="hidden" id="rango_id_personal">
                    <input type="text" id="rango_nombre_personal" class="input-modal-rango disabled" readonly>
                </div>

                <div class="grid-formulario" style="gap: 15px; margin-bottom: 15px;">
                    <div class="grupo-input-modal" style="margin-bottom: 0;">
                        <label>Fecha Inicio:</label>
                        <input type="date" id="rango_fecha_inicio" class="input-modal-rango">
                        <span class="error-inline" id="err_rango_inicio">Seleccione una fecha de inicio.</span>
                    </div>
                    <div class="grupo-input-modal" style="margin-bottom: 0;">
                        <label>Fecha Fin:</label>
                        <input type="date" id="rango_fecha_fin" class="input-modal-rango">
                        <span class="error-inline" id="err_rango_fin">Seleccione una fecha final válida.</span>
                    </div>
                </div>

                <div class="grupo-input-modal">
                    <label>Motivo (Reposo médico, Vacaciones, etc.):</label>
                    <textarea id="rango_motivo" class="input-modal-rango" rows="3" placeholder="Detalle el motivo (Mín. 10 caracteres)..."></textarea>
                    <span class="error-inline" id="err_rango_motivo">El motivo debe tener al menos 10 caracteres.</span>
                </div>

                <div class="grupo-input-modal">
                    <label>Adjuntar Evidencias (Opcional):</label>
                    <div class="zona-upload-multiple">
                        <input type="file" id="rango_evidencias" multiple class="input-file-oculto" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                        <label for="rango_evidencias" class="btn-subir-archivo">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span style="font-size: 0.9rem;">Cargar archivos...</span>
                        </label>
                    </div>
                    <div id="lista_preview_archivos_rango" class="lista-archivos-preview"></div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-guardar" style="width: 100%; justify-content: center; border-radius: 10px; padding: 1rem;">
                        Procesar Justificación
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- EL PUENTE: Transfiere variables de PHP al entorno JS -->
    <script>
        window.AsistenciaConfig = {
            idUsuario: "<?php echo $_SESSION['id_usuario']; ?>",
            miIdPersonal: <?php echo $mi_id_personal; ?>,
            esAdmin: <?php echo $es_admin ? 'true' : 'false'; ?>,
            horaEntradaSys: "<?php echo htmlspecialchars($hora_entrada_sys); ?>",
            toleranciaSys: <?php echo (int)$tolerancia_sys; ?>,
            nombreDirector: "<?php echo isset($mis_datos['nombres']) ? addslashes(explode(' ', $mis_datos['nombres'])[0]) : 'Director'; ?>"
        };
    </script>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script src="../recursos/js/asistencia.js?v=<?php echo time(); ?>"></script>
    <script src="../recursos/js/guia_dinamica.js"></script>

</body>
</html>