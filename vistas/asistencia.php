<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

// Validar si es fin de semana (Sábado = 6, Domingo = 7)
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

                <div class="tarjeta-formulario-config" style="max-inline-size: 100%;">
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

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
      /**
         * ============================================================================
         * 1. CONSTANTES Y VARIABLES GLOBALES DE ESTADO
         * ============================================================================
         */
        
        // Configuración del Sistema (Reloj y Tolerancia)
        const configHoraEntrada = "<?php echo htmlspecialchars($hora_entrada_sys); ?>";
        const configTolerancia = <?php echo (int)$tolerancia_sys; ?>;

        // Estado de Paginación y Filtrado
        const ITEMS_POR_CARGA = 8;
        let limitePaginacionActual = ITEMS_POR_CARGA; 
        let cargoActivoGlobal = 'todos'; 

        // Estado del Modal de Justificación Múltiple (Archivos)
        let arrayArchivosRango = [];

        // Estado del Calendario Individual
        let idPersonalActual = <?php echo $mi_id_personal; ?>;
        let nombrePersonalActual = "";
        let esModoAdmin = false;
        let mesActual = new Date().getMonth() + 1;
        let anioActual = new Date().getFullYear();
        let fechaIngresoActual = ""; 

        /**
         * ============================================================================
         * 2. INICIALIZACIÓN (Event Listeners Principales)
         * ============================================================================
         */
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar el reloj si el elemento existe
            if (document.getElementById('reloj-hora')) {
                setInterval(actualizarRelojSistema, 1000);
                actualizarRelojSistema();
            }

            // Inicializar filtro universal
            aplicarFiltroUniversal(null, null, true);
            
            // Carga de calendario inline (Vista Empleado)
            <?php if (!$es_admin): ?>
                cargarCalendarioHtml('contenedor-calendario-inline');
            <?php endif; ?>

            // Listeners UI Globales
            configurarListenersUI();
        });

        /**
         * Configura los escuchadores de eventos para elementos estáticos del DOM.
         */
        function configurarListenersUI() {
            // Tema Oscuro/Claro
            const btnCambiarTema = document.getElementById('btnCambiarTema');
            if(btnCambiarTema) {
                btnCambiarTema.addEventListener('click', (e) => {
                    e.preventDefault();
                    const html = document.documentElement;
                    const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                    html.setAttribute('data-theme', nuevoTema);
                    localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
                });
            }

            // Buscador Universal
            const inputBuscador = document.getElementById('buscador-universal');
            if (inputBuscador) {
                inputBuscador.addEventListener('input', () => aplicarFiltroUniversal(null, null, true));
            }

            // Botón "Cargar Más"
            const btnVerMas = document.getElementById('btn-ver-mas');
            if (btnVerMas) {
                btnVerMas.addEventListener('click', () => {
                    limitePaginacionActual += ITEMS_POR_CARGA;
                    aplicarFiltroUniversal(cargoActivoGlobal, document.querySelector('.btn-filtro.activo'), false); 
                });
            }

            // Input File de Justificación Múltiple
            const inputEvidenciasRango = document.getElementById('rango_evidencias');
            if (inputEvidenciasRango) {
                inputEvidenciasRango.addEventListener('change', (e) => {
                    const nuevosArchivos = Array.from(e.target.files);
                    arrayArchivosRango = arrayArchivosRango.concat(nuevosArchivos);
                    renderizarPreviewArchivosRango();
                    inputEvidenciasRango.value = ''; // Resetear para permitir reselección
                });
            }

            // Envío del Formulario de Rango
            const formRango = document.getElementById('formJustificacionRango');
            if (formRango) {
                formRango.addEventListener('submit', procesarEnvioJustificacionRango);
            }
        }

        /**
         * ============================================================================
         * 3. MÓDULO: RELOJ Y ESTADO DEL TURNO
         * ============================================================================
         */

        /**
         * Calcula y renderiza la hora actual, la fecha y el estado del turno 
         * evaluando la tolerancia configurada en el sistema.
         */
        function actualizarRelojSistema() {
            const elHora = document.getElementById('reloj-hora');
            if (!elHora) return;

            const ahora = new Date();
            let horas = ahora.getHours();
            const minutos = ahora.getMinutes().toString().padStart(2, '0');
            const ampm = horas >= 12 ? 'PM' : 'AM';

            let saludo = 'Buenas noches';
            if (horas >= 5 && horas < 12) saludo = 'Buenos días';
            else if (horas >= 12 && horas < 18) saludo = 'Buenas tardes';

            horas = horas % 12 || 12; 

            // Actualizar DOM UI Reloj
            elHora.innerHTML = `${horas.toString().padStart(2, '0')}:${minutos}<span>${ampm}</span>`;
            
            const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            document.getElementById('reloj-fecha').textContent = `${dias[ahora.getDay()]}, ${ahora.getDate()} de ${meses[ahora.getMonth()]} de ${ahora.getFullYear()}`;
            
            const nombreDirector = "<?php echo isset($mis_datos['nombres']) ? explode(' ', htmlspecialchars($mis_datos['nombres']))[0] : 'Director'; ?>";
            document.getElementById('reloj-saludo').textContent = `${saludo}, ${nombreDirector}.`;

            // Lógica de cálculo de Tolerancia y Estado de Turno
            const horaEntradaParts = configHoraEntrada.split(':');
            let fechaEntrada = new Date();
            fechaEntrada.setHours(parseInt(horaEntradaParts[0]), parseInt(horaEntradaParts[1]), 0, 0);
            
            let fechaTolerancia = new Date(fechaEntrada.getTime() + (configTolerancia * 60000));
            
            const estadoTurno = document.getElementById('texto-estatus-turno');
            const tarjetaTurno = document.getElementById('tarjeta-estado-turno');
            const iconoTurno = document.getElementById('icono-estado-turno');
            
            if (estadoTurno && tarjetaTurno && iconoTurno) {
                if (ahora < fechaEntrada) {
                    estadoTurno.innerHTML = `<span style="color: #3b82f6;">Aún no inicia</span>`;
                    tarjetaTurno.style.borderLeftColor = '#3b82f6';
                    iconoTurno.style.color = '#3b82f6';
                } else if (ahora >= fechaEntrada && ahora <= fechaTolerancia) {
                    let diffMins = Math.floor((fechaTolerancia - ahora) / 60000);
                    estadoTurno.innerHTML = `<span style="color: #10b981;">Quedan ${diffMins} min</span>`;
                    tarjetaTurno.style.borderLeftColor = '#10b981';
                    iconoTurno.style.color = '#10b981';
                } else {
                    estadoTurno.innerHTML = `<span style="color: #ef4444;">Turno cerrado</span>`;
                    tarjetaTurno.style.borderLeftColor = '#ef4444';
                    iconoTurno.style.color = '#ef4444';
                }
            }
        }

        /**
         * ============================================================================
         * 4. MÓDULO: FILTRADO Y BÚSQUEDA DE PERSONAL
         * ============================================================================
         */

        /**
         * Filtra las tarjetas de personal combinando el filtro de cargo y la búsqueda de texto.
         * Incorpora un sistema de pseudo-paginación ("Cargar más").
         * * @param {number|string|null} idCargo - ID del cargo a filtrar (o 'todos').
         * @param {HTMLElement|null} botonSeleccionado - Referencia al nodo del botón clicado.
         * @param {boolean} reiniciarPaginacion - Indica si se debe resetear el límite de paginación.
         */
        function aplicarFiltroUniversal(idCargo = null, botonSeleccionado = null, reiniciarPaginacion = true) {
            if (reiniciarPaginacion) limitePaginacionActual = ITEMS_POR_CARGA;
            
            if (idCargo !== null) {
                cargoActivoGlobal = idCargo;
                document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
                if(botonSeleccionado) botonSeleccionado.classList.add('activo');
            }
            
            const inputVal = document.getElementById('buscador-universal');
            const textoBusqueda = inputVal ? inputVal.value.toLowerCase().trim() : '';
            let coincidentes = 0;
            
            document.querySelectorAll('.item-filtrable').forEach(item => {
                const coincideCargo = (cargoActivoGlobal === 'todos') || (item.getAttribute('data-cargo') == cargoActivoGlobal);
                const elNombre = item.querySelector('.nombre-empleado');
                const elCargo = item.querySelector('.cargo-empleado');
                
                const nombreStr = elNombre ? elNombre.innerText.toLowerCase() : '';
                const cargoStr = elCargo ? elCargo.innerText.toLowerCase() : '';
                const coincideTexto = nombreStr.includes(textoBusqueda) || cargoStr.includes(textoBusqueda);
                
                if (coincideCargo && coincideTexto) {
                    item.classList.remove('oculto-por-filtro');
                    coincidentes++;
                    
                    if (coincidentes > limitePaginacionActual) {
                        item.classList.add('oculto-por-paginacion');
                        item.classList.remove('animacion-aparecer');
                    } else {
                        // Forzar repintado para disparar animación
                        if (item.classList.contains('oculto-por-paginacion') || reiniciarPaginacion) {
                            item.classList.remove('oculto-por-paginacion', 'animacion-aparecer');
                            void item.offsetWidth; 
                            item.classList.add('animacion-aparecer');
                        }
                    }
                } else {
                    item.classList.add('oculto-por-filtro');
                    item.classList.remove('oculto-por-paginacion', 'animacion-aparecer');
                }
            });

            // Control de visualización del botón "Cargar Más"
            const contenedorVerMas = document.getElementById('contenedor-ver-mas');
            if (contenedorVerMas) {
                contenedorVerMas.style.display = (coincidentes > limitePaginacionActual) ? 'block' : 'none';
            }
        }

        /**
         * ============================================================================
         * 5. MÓDULO: MODAL JUSTIFICACIÓN MÚLTIPLE (POR RANGO)
         * ============================================================================
         */

        /**
         * Renderiza el DOM con la vista previa (imágenes o iconos) de los archivos seleccionados.
         */
        function renderizarPreviewArchivosRango() {
            const listaPreview = document.getElementById('lista_preview_archivos_rango');
            if(!listaPreview) return;
            listaPreview.innerHTML = '';
            
            arrayArchivosRango.forEach((archivo, index) => {
                const div = document.createElement('div');
                div.className = 'item-archivo-preview';
                
                let iconHtml = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>`;
                
                if (archivo.type && archivo.type.startsWith('image/')) {
                    const objUrl = URL.createObjectURL(archivo);
                    iconHtml = `<img src="${objUrl}" class="img-preview-mini" onload="URL.revokeObjectURL(this.src)">`;
                }

                div.innerHTML = `
                    ${iconHtml}
                    <span class="nombre-archivo" title="${archivo.name}">${archivo.name}</span>
                    <button type="button" class="btn-eliminar-preview" onclick="eliminarArchivoRango(${index})" title="Quitar archivo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                `;
                listaPreview.appendChild(div);
            });
        }

        /**
         * Elimina un archivo específico del array de evidencias y re-renderiza la interfaz.
         * @param {number} index - Índice del archivo en el array arrayArchivosRango.
         */
        function eliminarArchivoRango(index) {
            arrayArchivosRango.splice(index, 1);
            renderizarPreviewArchivosRango();
        }

        /**
         * Prepara y abre el Modal de Justificación Múltiple. Extrae datos del calendario abierto.
         */
        function ejecutarModalRango() {
            // Extracción preventiva de la fecha de ingreso almacenada en el dataset
            if (!fechaIngresoActual) {
                const contenedor = document.getElementById('contenedor-calendario-modal');
                if (contenedor) fechaIngresoActual = contenedor.getAttribute('data-fecha-ingreso') || "";
            }

            if (fechaIngresoActual) {
                const hoy = new Date().toISOString().slice(0,10);
                if (fechaIngresoActual > hoy) {
                    Swal.fire('Aviso', 'La fecha de ingreso del empleado es futura. No se puede procesar justificación.', 'warning');
                    return;
                }
            }

            cerrarModal(); // Cierra el modal individual
            
            setTimeout(() => {
                const inputId = document.getElementById('rango_id_personal');
                const inputNombre = document.getElementById('rango_nombre_personal');
                if (inputId && inputNombre) {
                    inputId.value = idPersonalActual;
                    inputNombre.value = nombrePersonalActual;
                }
                document.getElementById('modalRango').classList.add('activo');
                document.getElementById('contenidoModalRango').classList.add('activo');
            }, 150);
        }

        /**
         * Cierra y resetea completamente el Modal de Justificación Múltiple.
         */
        function cerrarModalRango() {
            const modal = document.getElementById('modalRango');
            const contenido = document.getElementById('contenidoModalRango');
            const form = document.getElementById('formJustificacionRango');
            
            if (modal) modal.classList.remove('activo');
            if (contenido) contenido.classList.remove('activo');
            if (form) form.reset();
            
            // Limpieza de clases de validación visual
            document.querySelectorAll('.input-modal-rango').forEach(el => el.classList.remove('input-error', 'sacudir'));
            document.querySelectorAll('.error-inline').forEach(el => el.style.display = 'none');
            
            arrayArchivosRango = [];
            renderizarPreviewArchivosRango();
        }

        /**
         * Manejador asíncrono para validar y enviar el formulario de Justificación Múltiple vía Fetch API.
         * @param {Event} e - Evento Submit.
         */
        function procesarEnvioJustificacionRango(e) {
            e.preventDefault();
            
            const inpInicio = document.getElementById('rango_fecha_inicio');
            const inpFin = document.getElementById('rango_fecha_fin');
            const inpMotivo = document.getElementById('rango_motivo');
            
            const errInicio = document.getElementById('err_rango_inicio');
            const errFin = document.getElementById('err_rango_fin');
            const errMotivo = document.getElementById('err_rango_motivo');

            // Reset UI states
            [inpInicio, inpFin, inpMotivo].forEach(el => el.classList.remove('input-error', 'sacudir'));
            [errInicio, errFin, errMotivo].forEach(el => el.style.display = 'none');

            let hasError = false;

            // Bloque de Validación
            if (!inpInicio.value) {
                inpInicio.classList.add('input-error', 'sacudir');
                errInicio.style.display = 'block';
                hasError = true;
            }
            
            if (!inpFin.value) {
                inpFin.classList.add('input-error', 'sacudir');
                errFin.innerText = 'Seleccione una fecha final.';
                errFin.style.display = 'block';
                hasError = true;
            } else if (inpInicio.value && inpFin.value < inpInicio.value) {
                inpFin.classList.add('input-error', 'sacudir');
                errFin.innerText = 'La fecha fin no puede ser anterior al inicio.';
                errFin.style.display = 'block';
                hasError = true;
            }

            if (!inpMotivo.value || inpMotivo.value.trim().length < 10) {
                inpMotivo.classList.add('input-error', 'sacudir');
                errMotivo.style.display = 'block';
                hasError = true;
            }

            if (hasError) return;

            // Construcción del FormData
            const formData = new FormData();
            formData.append('id_personal', document.getElementById('rango_id_personal').value);
            formData.append('fecha_inicio', inpInicio.value);
            formData.append('fecha_fin', inpFin.value);
            formData.append('motivo', inpMotivo.value);
            
            arrayArchivosRango.forEach((archivo) => {
                formData.append('evidencias[]', archivo);
            });

            // UI Feedback
            Swal.fire({
                title: 'Procesando...',
                text: 'Validando archivos y registrando en el sistema...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); },
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });

            // Petición AJAX
            fetch('../controladores/ControladorJustificacionRango.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Completado!',
                        text: data.msg,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                    }).then(() => {
                        cerrarModalRango();
                        location.reload(); 
                    });
                } else {
                    Swal.fire('Error de Validación', data.msg, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error de Conexión', 'Hubo un problema al procesar la solicitud.', 'error');
            });
        }

        /**
         * ============================================================================
         * 6. MÓDULO: CALENDARIO INDIVIDUAL Y EDICIÓN DE DÍAS
         * ============================================================================
         */

        /**
         * Abre el Modal del Calendario e inicializa las variables de estado.
         * * @param {number} idPersonal - ID del empleado seleccionado.
         * @param {string} nombre - Nombre completo del empleado.
         * @param {boolean} modoEdicion - True si el usuario tiene privilegios de Admin.
         */
        function abrirCalendario(idPersonal, nombre, modoEdicion) {
            idPersonalActual = idPersonal;
            nombrePersonalActual = nombre;
            esModoAdmin = modoEdicion;
            mesActual = new Date().getMonth() + 1;
            anioActual = new Date().getFullYear();
            
            const titulo = document.getElementById('titulo_modal_calendario');
            if (titulo) titulo.innerText = 'Asistencia: ' + nombre;
            
            const btnFab = document.getElementById('btnFlotanteRangoModal');
            if(btnFab) btnFab.style.display = modoEdicion ? 'flex' : 'none';

            document.getElementById('modalOverlay').classList.add('activo');
            document.getElementById('modalCalendario').classList.add('activo');
            
            cargarCalendarioHtml('contenedor-calendario-modal');
        }

        /**
         * Cierra el modal individual de calendario.
         */
        function cerrarModal() {
            const overlay = document.getElementById('modalOverlay');
            const modal = document.getElementById('modalCalendario');
            if(overlay) overlay.classList.remove('activo');
            if(modal) modal.classList.remove('activo');
        }

        /**
         * Realiza una petición AJAX para obtener y pintar la vista HTML del mes del calendario.
         * @param {string} idContenedor - El ID del nodo DOM donde se inyectará el calendario.
         */
        function cargarCalendarioHtml(idContenedor) {
            const contenedor = document.getElementById(idContenedor);
            if (!contenedor) return;
            
            // Render spinner inicial
            contenedor.innerHTML = '<div style="text-align:center; padding: 40px;"><svg class="animacion-vibrar" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--primary-color)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p>Cargando fechas...</p></div>';

            fetch(`../controladores/ControladorCalendario.php?id=${idPersonalActual}&mes=${mesActual}&anio=${anioActual}&admin=${esModoAdmin}&contenedor=${idContenedor}`)
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                    const contPrincipal = document.getElementById(idContenedor);
                    if (contPrincipal) {
                        fechaIngresoActual = contPrincipal.getAttribute('data-fecha-ingreso') || "";
                    }
                })
                .catch(() => {
                    contenedor.innerHTML = '<p style="color:red; text-align:center;">Error al cargar el calendario.</p>';
                });
        }

        /**
         * Interfaz de navegación para mover los meses hacia adelante o atrás.
         * @param {number} direccion - (+1 para siguiente, -1 para anterior).
         * @param {string} idContenedor - ID destino de renderizado.
         */
        function cambiarMes(direccion, idContenedor) {
            mesActual += direccion;
            if (mesActual > 12) { mesActual = 1; anioActual++; }
            if (mesActual < 1) { mesActual = 12; anioActual--; }
            cargarCalendarioHtml(idContenedor);
        }

        /**
         * Lanza SweetAlert para la edición individual de un día (Modifica/Agrega/Elimina el estado).
         * * @param {string} fechaBD - Formato YYYY-MM-DD.
         * @param {string} fechaVisual - Formato DD/MM/YYYY.
         * @param {string} estadoActual - Estado textual (Ej: "Retraso y Salida Temprana").
         * @param {string} motivo - Motivo actual (si existe).
         * @param {string} archivo - Nombre de archivo adjunto (si existe).
         */
        function editarDia(fechaBD, fechaVisual, estadoActual, motivo, archivo) {
            if (!esModoAdmin) return;
            
            if (fechaIngresoActual && fechaBD < fechaIngresoActual) {
                Swal.fire('Operación no permitida', 'No se puede modificar una fecha anterior a la fecha de ingreso del empleado.', 'error');
                return;
            }

            // Identificación de contextos cronológicos
            const hoy = new Date().toISOString().slice(0,10);
            const esFuturo = fechaBD > hoy;

            // Parsear estados combinados
            let estadoPrimario = estadoActual;
            let estadoSecundario = '';
            
            if (estadoActual && estadoActual.includes(' y ')) {
                const partes = estadoActual.split(' y ');
                estadoPrimario = partes[0].trim();
                estadoSecundario = partes[1].trim();
            }

            // Generación Dinámica de Opciones (Futuro vs Pasado/Presente)
            let opcionesEstado = esFuturo ? `
                <option value="Justificado" selected>Justificado (Gris)</option>
                <option value="Eliminar">Eliminar Registro (Deshacer Justificación)</option>
            ` : `
                <option value="Puntual" ${estadoPrimario === 'Puntual' ? 'selected' : ''}>Puntual (Verde)</option>
                <option value="Retraso" ${estadoPrimario === 'Retraso' ? 'selected' : ''}>Retraso (Naranja)</option>
                <option value="Salida Irregular" ${estadoPrimario === 'Salida Irregular' ? 'selected' : ''}>Salida Irregular (Rojo Oscuro)</option>
                <option value="Justificado" ${estadoPrimario === 'Justificado' ? 'selected' : ''}>Justificado (Gris)</option>
                <option value="Falta" ${estadoPrimario.includes('Falta') ? 'selected' : ''}>Falta (Rojo)</option>
            `;

            // Construcción del HTML de Evidencia adjunta
            let enlaceEvidencia = '';
            if (archivo && archivo !== 'undefined' && archivo !== 'null') {
                enlaceEvidencia = `
                    <div style="margin-block-end: 15px; text-align: start; background: var(--bg-light); padding: 10px; border-radius: 8px;">
                        <span style="font-size:0.85rem; color:var(--text-color); display:block; margin-block-end:5px;">Evidencia adjunta:</span>
                        <a href="../recursos/evidencias/${archivo}" target="_blank" style="color: var(--primary-color); font-weight: bold; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                            Ver Documento / Imagen
                        </a>
                    </div>
                `;
            }

            // Ejecución del Modal Dinámico
            Swal.fire({
                title: esFuturo ? 'Gestionar Justificación Futura' : 'Modificar Asistencia',
                html: `
                    <p style="margin-block-end:15px; font-weight:bold; color:var(--primary-color); font-size:1.1rem;">Fecha: ${fechaVisual}</p>
                    
                    <div style="text-align: start; margin-block-end: 5px;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color);">Estado Principal:</label>
                    </div>
                    <select id="swal-estado" style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:5px; border:1px solid #ccc; outline:none; font-family:'Montserrat';">
                        ${opcionesEstado}
                    </select>

                    <div style="text-align: end; margin-block-end: 15px; display: ${esFuturo ? 'none' : 'block'};">
                        <button type="button" id="btn-add-secundaria" style="background: none; border: none; color: var(--primary-color); font-weight: bold; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: transform 0.2s;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Añadir Incidencia Secundaria
                        </button>
                    </div>

                    <div id="caja-secundaria" style="display: ${estadoSecundario && !esFuturo ? 'block' : 'none'}; background: var(--bg-light); padding: 10px; border-radius: 8px; margin-block-end: 15px; border: 1px solid var(--primary-color);">
                        <div style="text-align: start; margin-block-end: 5px;">
                            <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color);">Segunda Incidencia:</label>
                        </div>
                        <select id="swal-estado-secundario" style="inline-size:100%; padding:10px; border-radius:8px; border:1px solid #ccc; outline:none; font-family:'Montserrat';">
                            <option value="">Ninguna</option>
                            <option value="Salida Temprana" ${estadoSecundario === 'Salida Temprana' ? 'selected' : ''}>Salida Temprana</option>
                            <option value="Salida Irregular" ${estadoSecundario === 'Salida Irregular' ? 'selected' : ''}>Salida Irregular</option>
                        </select>
                    </div>

                    <textarea id="swal-motivo" placeholder="${esFuturo ? 'Motivo de la justificación...' : 'Escriba un motivo o nota de Dirección...'}" style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:15px; border:1px solid #ccc; min-block-size:80px; font-family:'Montserrat'; outline:none;">${motivo}</textarea>
                    ${enlaceEvidencia}
                    
                    <div style="text-align: start; margin-block-start: 10px; overflow: hidden; display: ${esFuturo ? 'none' : 'block'};">
                        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color); display: block; margin-block-end: 8px;">${archivo ? 'Reemplazar evidencia (Opcional):' : 'Subir evidencia (Opcional):'}</label>
                        <div style="position: relative; display: block; inline-size: 100%;">
                            <input type="file" id="swal-archivo" accept=".pdf, .jpg, .jpeg, .png" style="position: absolute; inset-inline-start: -9999px;">
                            <label for="swal-archivo" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; background-color: var(--bg-light); border: 2px dashed var(--primary-color); border-radius: 10px; color: var(--text-color); font-weight: 600; cursor: pointer; transition: all 0.3s ease; inline-size: 100%; box-sizing: border-box;">
                                <svg xmlns="http://www.w3.org/2000/svg" style="inline-size: 24px; block-size: 24px; color: var(--primary-color); flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <span id="texto-swal-archivo" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display:block; inline-size:100%; text-align:start;">Seleccionar archivo...</span>
                            </label>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar Cambios',
                confirmButtonColor: '#406ff3',
                cancelButtonText: 'Cancelar',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333',
                
                didOpen: () => {
                    // Prevenir error si se oculta en contexto futuro
                    if (!esFuturo) {
                        document.getElementById('swal-archivo').addEventListener('change', function(e) {
                            const name = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                            document.getElementById('texto-swal-archivo').textContent = name;
                        });
                    }

                    const selectPrincipal = document.getElementById('swal-estado');
                    const btnSecundaria = document.getElementById('btn-add-secundaria');
                    const cajaSecundaria = document.getElementById('caja-secundaria');
                    const selectSecundario = document.getElementById('swal-estado-secundario');
                    const textareaMotivo = document.getElementById('swal-motivo');

                    function evaluarBloqueosUI() {
                        const estado = selectPrincipal.value;
                        
                        if (estado === 'Eliminar') {
                            textareaMotivo.style.display = 'none';
                            btnSecundaria.style.display = 'none';
                            cajaSecundaria.style.display = 'none';
                            selectSecundario.value = '';
                        } else {
                            textareaMotivo.style.display = 'block';
                            if (esFuturo) {
                                btnSecundaria.style.display = 'none';
                            } else {
                                if (estado.includes('Falta') || estado === 'Salida Irregular') {
                                    btnSecundaria.style.display = 'none';
                                    cajaSecundaria.style.display = 'none';
                                    selectSecundario.value = '';
                                } else {
                                    btnSecundaria.style.display = 'inline-flex';
                                }
                            }
                        }
                    }

                    selectPrincipal.addEventListener('change', evaluarBloqueosUI);
                    evaluarBloqueosUI(); 

                    if(btnSecundaria) {
                        btnSecundaria.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (cajaSecundaria.style.display === 'none') {
                                cajaSecundaria.style.display = 'block';
                                selectSecundario.value = 'Salida Temprana'; 
                                btnSecundaria.style.color = '#ef4444';
                                btnSecundaria.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> Quitar Incidencia Secundaria';
                            } else {
                                cajaSecundaria.style.display = 'none';
                                selectSecundario.value = '';
                                btnSecundaria.style.color = 'var(--primary-color)';
                                btnSecundaria.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> Añadir Incidencia Secundaria';
                            }
                        });
                    }
                },
                preConfirm: () => {
                    let archivoVal = null;
                    if (!esFuturo && document.getElementById('swal-archivo')) {
                        archivoVal = document.getElementById('swal-archivo').files[0];
                    }
                    return {
                        fecha: fechaBD,
                        estado: document.getElementById('swal-estado').value,
                        estado_secundario: document.getElementById('swal-estado-secundario').value,
                        motivo: document.getElementById('swal-motivo').value,
                        archivo: archivoVal
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_personal', idPersonalActual); 
                    formData.append('fecha', result.value.fecha);
                    formData.append('estado', result.value.estado);
                    formData.append('estado_secundario', result.value.estado_secundario);
                    formData.append('motivo', result.value.motivo);
                    
                    if (result.value.archivo) {
                        formData.append('archivo', result.value.archivo);
                    }

                    Swal.fire({
                        title: 'Procesando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); },
                        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                    });

                    fetch('../controladores/ControladorModificarAsistencia.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: data.msg,
                                icon: 'success',
                                confirmButtonColor: '#10b981',
                                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                            }).then(() => {
                                cargarCalendarioHtml('contenedor-calendario-modal');
                            });
                        } else {
                            Swal.fire('Error', data.msg, 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error de Conexión', 'Hubo un problema al contactar al servidor.', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>