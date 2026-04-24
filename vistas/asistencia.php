<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

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
        // 1. Obtener configuración general para el contador
        $stmt_config = $conexion->query("SELECT hora_entrada_general, minutos_tolerancia FROM configuracion LIMIT 1");
        if ($config_sys = $stmt_config->fetch(PDO::FETCH_ASSOC)) {
            $hora_entrada_sys = $config_sys['hora_entrada_general'];
            $tolerancia_sys = $config_sys['minutos_tolerancia'];
        }

        // 2. Obtener Justificaciones pendientes
        $stmt_just = $conexion->query("SELECT COUNT(*) FROM asistencias WHERE estado_justificacion = 'Pendiente'");
        $justificaciones_pendientes = $stmt_just->fetchColumn();

        // 3. Consulta del personal
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

        // 4. Calcular Ausentes
        $total_personal = count($lista_personal);
        $presentes_hoy = 0;
        foreach($lista_personal as $p) {
            if(!empty($p['asistio_hoy'])) $presentes_hoy++;
        }
        $ausentes_hoy = $total_personal - $presentes_hoy;

    } catch (PDOException $e) {}
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-block-end: 2rem; align-items: stretch; justify-content: center;">
                    
                    <div class="tarjeta-perfil" style="flex: 0 0 auto; width: 100%; max-inline-size: 320px; margin: 0 auto;"> 
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
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: white; inline-size: 18px; block-size: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Ver mi calendario
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="flex: 1 1 350px; max-inline-size: 500px; display: flex; flex-direction: column; justify-content: center; background: var(--navbar-bg); border-radius: var(--border-radius); box-shadow: var(--shadow-sm); padding: 1.5rem 2rem; position: relative; overflow: hidden; border-left: 5px solid var(--primary-color); margin: 0 auto;">
                        <svg style="position: absolute; right: -10px; bottom: 10px; width: 150px; height: 150px; color: var(--primary-color); opacity: 0.05; z-index: 0; pointer-events: none;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/><path d="M13 7h-2v5.414l3.293 3.293 1.414-1.414L13 11.586z"/></svg>

                        <div style="position: relative; z-index: 1;">
                            <p id="reloj-saludo" style="color: var(--text-color); font-weight: 600; font-size: 1.05rem; margin-bottom: 2px;">Cargando reloj...</p>
                            <h2 id="reloj-hora" style="font-size: clamp(2.2rem, 3.5vw, 3.4rem); font-weight: 800; color: var(--primary-color); letter-spacing: -2px; line-height: 1; margin-bottom: 8px; font-variant-numeric: tabular-nums;">--:--:--</h2>
                            <p style="color: var(--text-color); font-weight: 500; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span id="reloj-fecha">Cargando fecha...</span>
                            </p>
                        </div>
                    </div>

                    <div style="flex: 0 1 320px; width: 100%; display: flex; flex-direction: column; gap: 12px; justify-content: space-between; margin: 0 auto;">
                        
                        <div style="background: var(--navbar-bg); border-radius: 12px; padding: 12px 15px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #10b981; flex: 1;">
                            <div>
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-color); text-transform: uppercase;">Estado del Turno</span>
                                <div id="texto-estatus-turno" style="font-size: 1.1rem; font-weight: 700; margin-top: 2px;">Calculando...</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 28px; height: 28px; color: #10b981; opacity: 0.8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>

                        <div style="background: var(--navbar-bg); border-radius: 12px; padding: 12px 15px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #f59e0b; flex: 1;">
                            <div>
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-color); text-transform: uppercase;">Personal Ausente</span>
                                <div style="font-size: 1.3rem; font-weight: 900; color: #f59e0b; margin-top: 2px;"><?php echo $ausentes_hoy; ?> <span style="font-size:0.9rem; color:var(--text-color);">/ <?php echo $total_personal; ?></span></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 28px; height: 28px; color: #f59e0b; opacity: 0.8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>

                        <div style="background: var(--navbar-bg); border-radius: 12px; padding: 12px 15px; box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #ef4444; flex: 1;">
                            <div>
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-color); text-transform: uppercase;">Justif. Pendientes</span>
                                <div style="font-size: 1.3rem; font-weight: 900; color: #ef4444; margin-top: 2px;"><?php echo $justificaciones_pendientes; ?></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 28px; height: 28px; color: #ef4444; opacity: 0.8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>

                    </div>

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
                                        echo '<span class="indicador-estatus estatus-ausente" title="Aún no has marcado entrada hoy"></span>';
                                    }
                                ?>
                            </div>
                            <div class="info-perfil" style="padding-block-start: 10px;">
                                <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                                <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                                
                                <div class="acciones-perfil">
                                    <button class="btn-editar-horario" onclick="abrirCalendario(<?php echo $emp['id_personal']; ?>, '<?php echo addslashes($emp['nombres']); ?>', true)" style="inline-size: 100%;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="inline-size: 18px; block-size: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
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
    <style>
        #modalCalendario { max-inline-size: 450px; padding: 1.5rem; }
        #modalCalendario .dia-celda { min-block-size: 50px; padding: 4px; }
        #modalCalendario .numero-dia { font-size: 0.95rem; }
        #modalCalendario .icono-estado { inline-size: 16px; block-size: 16px; }
    </style>
    
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-contenido" id="modalCalendario">
            <div class="modal-header">
                <h2 id="titulo_modal_calendario" style="font-size: 1.2rem;">Asistencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <div id="contenedor-calendario-modal"></div>
        </div>
    </div>
    <?php endif; ?>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
        // ==========================================
        // LÓGICA DEL RELOJ Y ESTADO DEL TURNO
        // ==========================================
        const configHoraEntrada = "<?php echo htmlspecialchars($hora_entrada_sys); ?>";
        const configTolerancia = <?php echo (int)$tolerancia_sys; ?>;

        function actualizarReloj() {
            const elHora = document.getElementById('reloj-hora');
            if (!elHora) return;

            const ahora = new Date();
            let horas = ahora.getHours();
            const minutos = ahora.getMinutes().toString().padStart(2, '0');
            const ampm = horas >= 12 ? 'PM' : 'AM';

            let saludo = 'Buenas noches';
            if (horas >= 5 && horas < 12) saludo = 'Buenos días';
            else if (horas >= 12 && horas < 18) saludo = 'Buenas tardes';

            horas = horas % 12;
            horas = horas ? horas : 12; 

            const horaStr = `${horas.toString().padStart(2, '0')}:${minutos}<span style="color: var(--text-color); font-size: 1.8rem; font-weight: 600; margin-left: 8px;">${ampm}</span>`;
            
            const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const fechaStr = `${dias[ahora.getDay()]}, ${ahora.getDate()} de ${meses[ahora.getMonth()]} de ${ahora.getFullYear()}`;

            elHora.innerHTML = horaStr;
            document.getElementById('reloj-fecha').textContent = fechaStr;
            
            const nombreDirector = "<?php echo isset($mis_datos['nombres']) ? explode(' ', htmlspecialchars($mis_datos['nombres']))[0] : 'Director'; ?>";
            document.getElementById('reloj-saludo').textContent = `${saludo}, ${nombreDirector}.`;

            // === LÓGICA DE TOLERANCIA (Mini Tarjeta 1) ===
            const horaEntradaParts = configHoraEntrada.split(':');
            let fechaEntrada = new Date();
            fechaEntrada.setHours(parseInt(horaEntradaParts[0]), parseInt(horaEntradaParts[1]), 0, 0);
            
            let fechaTolerancia = new Date(fechaEntrada.getTime() + (configTolerancia * 60000));
            let estadoTurno = document.getElementById('texto-estatus-turno');
            
            if(estadoTurno) {
                if (ahora < fechaEntrada) {
                    estadoTurno.innerHTML = `<span style="color: #3b82f6;">Aún no inicia</span>`;
                    estadoTurno.parentElement.parentElement.style.borderLeftColor = '#3b82f6';
                    estadoTurno.parentElement.nextElementSibling.style.color = '#3b82f6';
                } else if (ahora >= fechaEntrada && ahora <= fechaTolerancia) {
                    let diffMs = fechaTolerancia - ahora;
                    let diffMins = Math.floor(diffMs / 60000);
                    estadoTurno.innerHTML = `<span style="color: #10b981;">Quedan ${diffMins} min</span>`;
                    estadoTurno.parentElement.parentElement.style.borderLeftColor = '#10b981';
                    estadoTurno.parentElement.nextElementSibling.style.color = '#10b981';
                } else {
                    estadoTurno.innerHTML = `<span style="color: #ef4444;">Turno cerrado</span>`;
                    estadoTurno.parentElement.parentElement.style.borderLeftColor = '#ef4444';
                    estadoTurno.parentElement.nextElementSibling.style.color = '#ef4444';
                }
            }
        }

        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        // ==========================================
        // SISTEMA DE FILTRADO Y PAGINACIÓN INFINITA
        // ==========================================
        const inputBuscadorUniv = document.getElementById('buscador-universal');
        let cargoActivoUniv = 'todos'; 
        
        const itemsPorCarga = 8;
        let limiteActual = itemsPorCarga; 
        
        function aplicarFiltroUniversal(idCargo = null, botonSeleccionado = null, reiniciarPaginacion = true) {
            
            if (reiniciarPaginacion) {
                limiteActual = itemsPorCarga;
            }

            if (idCargo !== null) {
                cargoActivoUniv = idCargo;
                document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
                if(botonSeleccionado) botonSeleccionado.classList.add('activo');
            }
            const textoBusqueda = inputBuscadorUniv ? inputBuscadorUniv.value.toLowerCase().trim() : '';
            
            let coincidentes = 0;
            
            document.querySelectorAll('.item-filtrable').forEach(item => {
                const coincideCargo = (cargoActivoUniv === 'todos') || (item.getAttribute('data-cargo') == cargoActivoUniv);
                const elNombre = item.querySelector('.nombre-empleado');
                const elCargo = item.querySelector('.cargo-empleado');
                const nombre = elNombre ? elNombre.innerText.toLowerCase() : '';
                const cargo = elCargo ? elCargo.innerText.toLowerCase() : '';
                const coincideTexto = nombre.includes(textoBusqueda) || cargo.includes(textoBusqueda);
                
                if (coincideCargo && coincideTexto) {
                    item.classList.remove('oculto-por-filtro');
                    coincidentes++;
                    
                    if (coincidentes > limiteActual) {
                        item.classList.add('oculto-por-paginacion');
                        item.classList.remove('animacion-aparecer');
                    } else {
                        if (item.classList.contains('oculto-por-paginacion')) {
                            item.classList.remove('oculto-por-paginacion');
                            void item.offsetWidth; 
                            item.classList.add('animacion-aparecer');
                        } 
                        else if (reiniciarPaginacion) {
                            item.classList.remove('animacion-aparecer');
                            void item.offsetWidth; 
                            item.classList.add('animacion-aparecer');
                        }
                    }
                } else {
                    item.classList.add('oculto-por-filtro');
                    item.classList.remove('oculto-por-paginacion');
                    item.classList.remove('animacion-aparecer');
                }
            });

            const contenedorVerMas = document.getElementById('contenedor-ver-mas');
            if (contenedorVerMas) {
                if (coincidentes > limiteActual) {
                    contenedorVerMas.style.display = 'block';
                } else {
                    contenedorVerMas.style.display = 'none';
                }
            }
        }

        if (inputBuscadorUniv) {
            inputBuscadorUniv.addEventListener('input', () => aplicarFiltroUniversal(null, null, true));
        }

        const btnVerMas = document.getElementById('btn-ver-mas');
        if (btnVerMas) {
            btnVerMas.addEventListener('click', () => {
                limiteActual += itemsPorCarga;
                aplicarFiltroUniversal(cargoActivoUniv, document.querySelector('.btn-filtro.activo'), false); 
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            aplicarFiltroUniversal(null, null, true);
        });

        // ==========================================
        // RESTO DEL CÓDIGO (TEMA Y CALENDARIO)
        // ==========================================
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                const html = document.documentElement;
                const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
            });
        }

        let idPersonalActual = <?php echo $mi_id_personal; ?>;
        let esModoAdmin = false;
        let mesActual = new Date().getMonth() + 1;
        let anioActual = new Date().getFullYear();

        function abrirCalendario(idPersonal, nombre, modoEdicion) {
            idPersonalActual = idPersonal;
            esModoAdmin = modoEdicion;
            mesActual = new Date().getMonth() + 1;
            anioActual = new Date().getFullYear();
            
            const titulo = document.getElementById('titulo_modal_calendario');
            if (titulo) titulo.innerText = 'Asistencia: ' + nombre;
            
            document.getElementById('modalOverlay').classList.add('activo');
            document.getElementById('modalCalendario').classList.add('activo');
            
            cargarCalendario('contenedor-calendario-modal');
        }

        function cerrarModal() {
            document.getElementById('modalOverlay').classList.remove('activo');
            document.getElementById('modalCalendario').classList.remove('activo');
        }

        <?php if (!$es_admin): ?>
            document.addEventListener('DOMContentLoaded', function() {
                cargarCalendario('contenedor-calendario-inline');
            });
        <?php endif; ?>

        function cargarCalendario(idContenedor) {
            const contenedor = document.getElementById(idContenedor);
            contenedor.innerHTML = '<div style="text-align:center; padding: 40px;"><svg class="animacion-vibrar" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--primary-color)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p>Cargando fechas...</p></div>';

            fetch(`../controladores/ControladorCalendario.php?id=${idPersonalActual}&mes=${mesActual}&anio=${anioActual}&admin=${esModoAdmin}&contenedor=${idContenedor}`)
                .then(response => response.text())
                .then(html => { contenedor.innerHTML = html; })
                .catch(error => { contenedor.innerHTML = '<p style="color:red; text-align:center;">Error al cargar el calendario.</p>'; });
        }

        function cambiarMes(direccion, idContenedor) {
            mesActual += direccion;
            if (mesActual > 12) { mesActual = 1; anioActual++; }
            if (mesActual < 1) { mesActual = 12; anioActual--; }
            cargarCalendario(idContenedor);
        }

        function editarDia(fechaBD, fechaVisual, estadoActual, motivo, archivo) {
            if (!esModoAdmin) return;

            let estadoPrimario = estadoActual;
            let estadoSecundario = '';
            
            if (estadoActual && estadoActual.includes(' y ')) {
                let partes = estadoActual.split(' y ');
                estadoPrimario = partes[0].trim();
                estadoSecundario = partes[1].trim();
            }

            let enlaceEvidencia = '';
            if (archivo !== '') {
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

            Swal.fire({
                title: 'Modificar Asistencia',
                html: `
                    <p style="margin-block-end:15px; font-weight:bold; color:var(--primary-color); font-size:1.1rem;">Fecha: ${fechaVisual}</p>
                    
                    <div style="text-align: start; margin-bottom: 5px;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color);">Estado Principal:</label>
                    </div>
                    <select id="swal-estado" style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:5px; border:1px solid #ccc; outline:none; font-family:'Montserrat';">
                        <option value="Puntual" ${estadoPrimario === 'Puntual' ? 'selected' : ''}>Puntual (Verde)</option>
                        <option value="Retraso" ${estadoPrimario === 'Retraso' ? 'selected' : ''}>Retraso (Naranja)</option>
                        <option value="Salida Irregular" ${estadoPrimario === 'Salida Irregular' ? 'selected' : ''}>Salida Irregular (Rojo Oscuro)</option>
                        <option value="Justificado" ${estadoPrimario === 'Justificado' ? 'selected' : ''}>Justificado (Gris)</option>
                        <option value="Falta" ${estadoPrimario.includes('Falta') ? 'selected' : ''}>Falta (Rojo)</option>
                    </select>

                    <div style="text-align: end; margin-block-end: 15px;">
                        <button type="button" id="btn-add-secundaria" style="background: none; border: none; color: var(--primary-color); font-weight: bold; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: transform 0.2s;">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Añadir Incidencia Secundaria
                        </button>
                    </div>

                    <div id="caja-secundaria" style="display: ${estadoSecundario ? 'block' : 'none'}; background: var(--bg-light); padding: 10px; border-radius: 8px; margin-block-end: 15px; border: 1px dashed var(--primary-color);">
                        <div style="text-align: start; margin-bottom: 5px;">
                            <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color);">Segunda Incidencia:</label>
                        </div>
                        <select id="swal-estado-secundario" style="inline-size:100%; padding:10px; border-radius:8px; border:1px solid #ccc; outline:none; font-family:'Montserrat';">
                            <option value="">Ninguna</option>
                            <option value="Salida Temprana" ${estadoSecundario === 'Salida Temprana' ? 'selected' : ''}>Salida Temprana</option>
                            <option value="Salida Irregular" ${estadoSecundario === 'Salida Irregular' ? 'selected' : ''}>Salida Irregular</option>
                        </select>
                    </div>

                    <textarea id="swal-motivo" placeholder="Escriba un motivo o nota de Dirección..." style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:15px; border:1px solid #ccc; min-block-size:80px; font-family:'Montserrat'; outline:none;">${motivo}</textarea>
                    ${enlaceEvidencia}
                    <div style="text-align: start; margin-block-start: 10px; overflow: hidden;">
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
                    document.getElementById('swal-archivo').addEventListener('change', function(e) {
                        const nombreArchivo = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                        document.getElementById('texto-swal-archivo').textContent = nombreArchivo;
                    });

                    const selectPrincipal = document.getElementById('swal-estado');
                    const btnSecundaria = document.getElementById('btn-add-secundaria');
                    const cajaSecundaria = document.getElementById('caja-secundaria');
                    const selectSecundario = document.getElementById('swal-estado-secundario');

                    // RESTRICCIONES DE INTERFAZ
                    function evaluarBloqueos() {
                        const estado = selectPrincipal.value;
                        
                        if (estado.includes('Falta') || estado === 'Salida Irregular') {
                            btnSecundaria.style.display = 'none';
                            cajaSecundaria.style.display = 'none';
                            selectSecundario.value = '';
                        } else {
                            btnSecundaria.style.display = 'inline-flex';
                        }
                    }

                    selectPrincipal.addEventListener('change', evaluarBloqueos);
                    evaluarBloqueos(); // Ejecutar al cargar el modal

                    btnSecundaria.addEventListener('click', function(e) {
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

                    if (cajaSecundaria.style.display === 'block') {
                        btnSecundaria.style.color = '#ef4444';
                        btnSecundaria.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> Quitar Incidencia Secundaria';
                    }
                },
                preConfirm: () => {
                    return {
                        fecha: fechaBD,
                        estado: document.getElementById('swal-estado').value,
                        estado_secundario: document.getElementById('swal-estado-secundario').value,
                        motivo: document.getElementById('swal-motivo').value,
                        archivo: document.getElementById('swal-archivo').files[0]
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
                        title: 'Guardando cambios...',
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
                                cargarCalendario('contenedor-calendario-modal');
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.msg,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error de Conexión', 'Hubo un problema al contactar al servidor.', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>