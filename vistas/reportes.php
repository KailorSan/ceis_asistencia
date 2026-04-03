<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

$es_directivo = ($id_rol == 1 || $id_rol == 2);

// ==========================================================
// 1. OBTENER PERIODOS ACTIVOS DE LA BD
// ==========================================================
if ($es_directivo) {
    $stmt_fechas = $conexion->query("SELECT DISTINCT YEAR(fecha) as anio, MONTH(fecha) as mes FROM asistencias ORDER BY anio DESC, mes DESC");
} else {
    $stmt_mi_id = $conexion->prepare("SELECT id_personal FROM personal WHERE id_usuario = ?");
    $stmt_mi_id->execute([$id_usuario]);
    $mi_id_personal = $stmt_mi_id->fetchColumn();

    $stmt_fechas = $conexion->prepare("SELECT DISTINCT YEAR(fecha) as anio, MONTH(fecha) as mes FROM asistencias WHERE id_personal = ? ORDER BY anio DESC, mes DESC");
    $stmt_fechas->execute([$mi_id_personal]);
}

$fechas_bd = $stmt_fechas->fetchAll(PDO::FETCH_ASSOC);
$periodos_activos = [];
$anios_disponibles = [];
foreach ($fechas_bd as $f) {
    $periodos_activos[$f['anio']][] = $f['mes'];
    if (!in_array($f['anio'], $anios_disponibles)) {
        $anios_disponibles[] = $f['anio'];
    }
}
$periodos_json = json_encode($periodos_activos);

// ==========================================================
// 2. OBTENER DATOS DEL PERSONAL Y CARGOS
// ==========================================================
$stmt_cargos = $conexion->query("SELECT id_cargo, nombre_cargo FROM cargos ORDER BY id_cargo ASC");
$cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

if ($es_directivo) {
    $sql_personal = "
        SELECT p.id_personal, p.nombres, p.apellidos, p.foto_perfil, p.id_cargo, c.nombre_cargo 
        FROM personal p
        INNER JOIN cargos c ON p.id_cargo = c.id_cargo
        INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE u.estado = 'Activo'
        ORDER BY p.nombres ASC";
    $stmt_personal = $conexion->query($sql_personal);
} else {
    $sql_personal = "
        SELECT p.id_personal, p.nombres, p.apellidos, p.foto_perfil, p.id_cargo, c.nombre_cargo 
        FROM personal p
        INNER JOIN cargos c ON p.id_cargo = c.id_cargo
        WHERE p.id_usuario = ?";
    $stmt_personal = $conexion->prepare($sql_personal);
    $stmt_personal->execute([$id_usuario]);
}
$lista_personal = $stmt_personal->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - CEIS Julian Yánez</title>
    <link rel="icon" href="../recursos/img/logo_ceis_transparente.png" type="image/png">
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    <script src="../recursos/js/chart.min.js"></script>
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            document.documentElement.setAttribute('data-theme', localStorage.getItem('tema_usuario_' + idUsr) || 'light');
        })();
    </script>
    <style>
        ::-webkit-scrollbar { inline-size: 8px; block-size: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4338ca; }
        [data-theme="light"] ::-webkit-scrollbar-track { background: #f1f5f9; }
        [data-theme="light"] ::-webkit-scrollbar-thumb { background: #cbd5e1; }
        [data-theme="light"] ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .contenedor-filtros-globales { 
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
            background: #ffffff; padding: 15px 20px; border-radius: 12px; margin-block-end: 20px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); color: #1e293b; 
        }
        .grupo-selectores { display: flex; align-items: center; gap: 15px; }
        .grupo-acciones-derecha { display: flex; justify-content: flex-end; }
        .contenedor-filtros-globales select { 
            padding: 8px 15px; border-radius: 8px; border: 2px solid #cbd5e1; font-family: 'Montserrat', sans-serif; 
            outline: none; background: #f8fafc; color: #0f172a; font-weight: 700; cursor: pointer; transition: 0.2s ease;
        }
        .contenedor-filtros-globales select:hover { border-color: #6366f1; background: #ffffff; }

        [data-theme="dark"] .contenedor-filtros-globales { background: #0f172a; border-color: #1e293b; color: #f8fafc; }
        [data-theme="dark"] .contenedor-filtros-globales select { background: #1e293b; border-color: #4f46e5; color: #ffffff !important; }
        [data-theme="dark"] .contenedor-filtros-globales select:hover { border-color: #818cf8; background: #334155; }

        .contenedor-busqueda-elegante { position: relative; display: flex; align-items: center; inline-size: 100%; max-inline-size: 350px; margin: 0 auto; }
        .campo-busqueda-elegante { inline-size: 100%; padding: 10px 40px 10px 18px; border-radius: 25px; border: 2px solid #e2e8f0; background: #f8fafc; color: #1e293b; font-size: 0.95rem; font-family: 'Montserrat', sans-serif; transition: 0.3s; outline: none; }
        .campo-busqueda-elegante:focus { border-color: #4f46e5; background: #ffffff; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15); }
        .icono-busqueda { position: absolute; inset-inline-end: 15px; color: #94a3b8; pointer-events: none; transition: 0.3s; }
        .campo-busqueda-elegante:focus + .icono-busqueda { color: #4f46e5; }
        [data-theme="dark"] .campo-busqueda-elegante { background: #1e293b; border-color: #334155; color: #f8fafc; }
        [data-theme="dark"] .campo-busqueda-elegante:focus { border-color: #818cf8; background: #0f172a; box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15); }

        .botones-filtro-cargo { display: flex; flex-wrap: wrap; gap: 10px; margin-block-end: 30px; }
        .btn-filtro { background: var(--bg-light); border: 2px solid transparent; color: var(--text-color); padding: 8px 15px; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 0.85rem; transition: 0.2s; }
        .btn-filtro:hover { border-color: var(--primary-color); }
        .btn-filtro.activo { background: var(--primary-color); color: white; border-color: var(--primary-color); box-shadow: 0 4px 6px -1px rgba(64, 111, 243, 0.4); }
        
        /* === CORRECCIÓN DE LA CUADRÍCULA (3x2 PERFECTO) === */
        .grid-resumen { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); /* Fuerza 3 columnas exactamente */
            gap: 15px; 
            margin-block-start: 20px; 
        }
        @media (max-inline-size: 600px) {
            .grid-resumen { grid-template-columns: repeat(2, 1fr); } /* 2 columnas en móviles */
        }
        /* =================================================== */

        .caja-resumen { padding: 15px; border-radius: 10px; text-align: center; color: white; font-weight: bold; font-size: 0.85rem;}
        .caja-verde { background: linear-gradient(135deg, #10b981, #059669); }
        .caja-naranja { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .caja-roja { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .caja-azul { background: linear-gradient(135deg, #3b82f6, #2563eb); } 
        .caja-rojo-oscuro { background: linear-gradient(135deg, #991b1b, #7f1d1d); } 
        .caja-gris { background: linear-gradient(135deg, #64748b, #475569); }
        .numero-resumen { font-size: 1.8rem; display: block; margin-block-start: 5px; }
        .contenedor-grafico { inline-size: 100%; max-inline-size: 280px; margin: 20px auto; display: none; }

        #directorio-personal .tarjeta-perfil { display: flex; flex-direction: column; block-size: 100%; }
        #directorio-personal .tarjeta-perfil.oculto { display: none !important; }
        #directorio-personal .info-perfil { display: flex; flex-direction: column; flex-grow: 1; padding-block-end: 15px; }
        #directorio-personal .acciones-perfil { margin-block-start: auto; display: flex; flex-direction: column; gap: 8px; }
        #directorio-personal .nombre-empleado { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-block-size: 2.4em; margin-block-end: 5px; }

        @media (max-inline-size: 1024px) {
            .contenedor-filtros-globales { justify-content: center; }
            .contenedor-busqueda-elegante { max-inline-size: 100%; order: 3; margin-block-start: 10px; }
        }
    </style>
</head>
<body>

    <?php $pagina_activa = 'reportes'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = $es_directivo ? 'Panel de Reportes' : 'Mi Reporte de Asistencia'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            <div class="cabecera-personal" style="margin-block-end: 15px;">
                <div>
                    <h1><?php echo $es_directivo ? 'Directorio de Reportes' : 'Mi Reporte Mensual'; ?></h1>
                    <p>Selecciona el mes o el año para generar el expediente.</p>
                </div>
            </div>

            <form id="formGenerarPDF" action="generar_pdf_asistencia.php" method="POST" target="_blank" style="display: none;">
                <input type="hidden" name="id_personal" id="pdf_id_personal">
                <input type="hidden" name="mes" id="pdf_mes">
                <input type="hidden" name="anio" id="pdf_anio">
                <input type="hidden" name="id_cargo" id="pdf_id_cargo" value="todos">
            </form>

            <div class="contenedor-filtros-globales" <?php if(!$es_directivo) echo 'style="justify-content: flex-start;"'; ?>>
                <div class="grupo-selectores">
                    <span style="font-weight: 600;">Evaluar Período:</span>
                    <select id="anio_global">
                        <?php if(empty($anios_disponibles)): ?>
                            <option value="<?php echo date('Y'); ?>"><?php echo date('Y'); ?></option>
                        <?php else: ?>
                            <?php foreach($anios_disponibles as $a): ?>
                                <option value="<?php echo $a; ?>"><?php echo $a; ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <select id="mes_global"></select>
                </div>

                <?php if ($es_directivo): ?>
                    <div class="contenedor-busqueda-elegante">
                        <input type="text" id="buscador-empleados" class="campo-busqueda-elegante" placeholder="Buscar por nombre o cargo...">
                        <svg class="icono-busqueda" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <div class="grupo-acciones-derecha">
                        <button onclick="descargarPDF('todos')" class="btn-guardar" style="display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 8px; padding: 10px 20px; background-color: #495b85; color: white; border: none; font-weight: bold; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: 0.2s; block-size: 46px; white-space: nowrap;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Descargar General
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($es_directivo): ?>
                <div class="botones-filtro-cargo" id="contenedor-filtros">
                    <button class="btn-filtro activo" onclick="aplicarFiltrosCombinados('todos', this)">Todos</button>
                    <?php foreach($cargos as $c): ?>
                        <button class="btn-filtro" onclick="aplicarFiltrosCombinados(<?php echo $c['id_cargo']; ?>, this)">
                            <?php echo htmlspecialchars($c['nombre_cargo']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid-perfiles" id="directorio-personal">
                <?php foreach ($lista_personal as $emp): ?>
                    <div class="tarjeta-perfil" data-cargo="<?php echo $emp['id_cargo']; ?>">
                        <div class="banner-tarjeta" style="block-size: 70px;"></div>
                        <div class="contenedor-avatar" style="margin-block-start: -40px; inline-size: 80px; block-size: 80px;">
                            <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($emp['foto_perfil']); ?>" alt="Foto">
                        </div>
                        <div class="info-perfil" style="padding-block-start: 10px;">
                            <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                            <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                            
                            <div class="acciones-perfil">
                                <button onclick="abrirResumen(<?php echo $emp['id_personal']; ?>, '<?php echo addslashes($emp['nombres'] . ' ' . $emp['apellidos']); ?>', '<?php echo addslashes($emp['nombre_cargo']); ?>')" class="btn-editar-horario" style="inline-size: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Ver Resumen Web
                                </button>
                                <button onclick="descargarPDF(<?php echo $emp['id_personal']; ?>)" class="btn-guardar" style="inline-size: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 8px; background-color: #e11d48; color: white; border: none; padding: 10px; font-weight: bold; cursor: pointer; transition: 0.2s;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Descargar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="modalOverlayResumen">
        <div class="modal-contenido" id="modalContenidoResumen" style="max-inline-size: 700px; padding: 2rem;">
            <div class="modal-header">
                <h2>Resumen de Asistencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarResumen()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <div style="text-align: center; margin-block-end: 5px;">
                <h3 id="resumen-nombre" style="color: var(--text-color); margin-block-end: 5px;">Cargando...</h3>
                <span id="resumen-cargo" style="color: var(--primary-color); font-weight: bold; font-size: 0.9rem;"></span>
                <p id="resumen-periodo" style="color: #64748b; font-size: 0.85rem; margin-block-start: 5px;"></p>
            </div>
            <div id="cargando-resumen" style="text-align: center; padding: 20px;">
                <svg class="animacion-vibrar" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--primary-color)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="contenedor-grafico" id="contenedor-grafico-resumen"><canvas id="miGraficoDona"></canvas></div>
            
            <div class="grid-resumen" id="datos-resumen" style="display: none;">
                <div class="caja-resumen caja-verde">Llegada<br>Puntual<span class="numero-resumen" id="num-puntual">0</span></div>
                <div class="caja-resumen caja-naranja">Llegada<br>Tardía<span class="numero-resumen" id="num-retraso">0</span></div>
                <div class="caja-resumen caja-azul">Salida<br>Temprana<span class="numero-resumen" id="num-salida-temp">0</span></div>
                <div class="caja-resumen caja-rojo-oscuro">Salida<br>Irregular<span class="numero-resumen" id="num-salida-irreg">0</span></div>
                <div class="caja-resumen caja-roja">Falta<br>Completa<span class="numero-resumen" id="num-falta">0</span></div>
                <div class="caja-resumen caja-gris">Falta<br>Justificada<span class="numero-resumen" id="num-justificado">0</span></div>
            </div>
        </div>
    </div>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
        const periodosActivos = <?php echo $periodos_json; ?>;
        const selectAnio = document.getElementById('anio_global');
        const selectMes = document.getElementById('mes_global');
        const nombresMeses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        const esDirectivo = <?php echo $es_directivo ? 'true' : 'false'; ?>;

        function actualizarMesesDisponibles() {
            const anio = selectAnio.value;
            const mesesDelAnio = periodosActivos[anio] || [];
            selectMes.innerHTML = '<option value="todos" style="font-weight:bold; color:var(--primary-color);">Todo el Año</option>';
            
            if (mesesDelAnio.length > 0) {
                mesesDelAnio.sort((a,b) => a - b);
                mesesDelAnio.forEach(mesNum => {
                    const opt = document.createElement('option');
                    opt.value = mesNum; opt.textContent = nombresMeses[mesNum];
                    selectMes.appendChild(opt);
                });
                selectMes.value = mesesDelAnio[mesesDelAnio.length - 1];
            } else {
                selectMes.innerHTML += '<option value="" disabled>Sin datos</option>';
            }
        }
        if(Object.keys(periodosActivos).length > 0) {
            selectAnio.addEventListener('change', actualizarMesesDisponibles);
            actualizarMesesDisponibles();
        }

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

        const inputBuscador = document.getElementById('buscador-empleados');
        let cargoActivo = 'todos'; 
        
        if (inputBuscador) {
            function aplicarFiltrosCombinados(idCargo = null, botonSeleccionado = null) {
                if (idCargo !== null) {
                    cargoActivo = idCargo;
                    document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
                    botonSeleccionado.classList.add('activo');
                }
                const textoBusqueda = inputBuscador.value.toLowerCase().trim();
                document.querySelectorAll('.tarjeta-perfil').forEach(tarjeta => {
                    const coincideCargo = cargoActivo === 'todos' || tarjeta.getAttribute('data-cargo') == cargoActivo;
                    const nombre = tarjeta.querySelector('.nombre-empleado').innerText.toLowerCase();
                    const cargo = tarjeta.querySelector('.cargo-empleado').innerText.toLowerCase();
                    const coincideTexto = nombre.includes(textoBusqueda) || cargo.includes(textoBusqueda);
                    
                    if (coincideCargo && coincideTexto) tarjeta.classList.remove('oculto');
                    else tarjeta.classList.add('oculto');
                });
            }
            inputBuscador.addEventListener('input', () => aplicarFiltrosCombinados());
        }

        let chartInstancia = null;
        function abrirResumen(idPersonal, nombre, cargo) {
            document.getElementById('modalOverlayResumen').classList.add('activo');
            document.getElementById('modalContenidoResumen').classList.add('activo'); 
            document.getElementById('resumen-nombre').innerText = nombre;
            document.getElementById('resumen-cargo').innerText = cargo;
            
            const mes = selectMes.value;
            const anio = selectAnio.value;
            const nombreMes = selectMes.options[selectMes.selectedIndex].text;
            
            document.getElementById('resumen-periodo').innerText = `Período: ${nombreMes} ${anio}`;
            document.getElementById('datos-resumen').style.display = 'none';
            document.getElementById('contenedor-grafico-resumen').style.display = 'none';
            document.getElementById('cargando-resumen').style.display = 'block';

            fetch(`../controladores/ControladorResumenMensual.php?id=${idPersonal}&mes=${mes}&anio=${anio}`)
                .then(async response => {
                    const texto = await response.text(); 
                    try { return JSON.parse(texto); } 
                    catch (err) { throw new Error("Error del servidor."); }
                })
                .then(data => {
                    if(data.error) throw new Error(data.error);
                    document.getElementById('num-puntual').innerText = data.puntual;
                    document.getElementById('num-retraso').innerText = data.retraso;
                    document.getElementById('num-salida-temp').innerText = data.salida_temprana;
                    document.getElementById('num-salida-irreg').innerText = data.salida_irregular;
                    document.getElementById('num-falta').innerText = data.falta;
                    document.getElementById('num-justificado').innerText = data.justificado;
                    
                    document.getElementById('cargando-resumen').style.display = 'none';
                    document.getElementById('datos-resumen').style.display = 'grid';
                    document.getElementById('contenedor-grafico-resumen').style.display = 'block';

                    if(chartInstancia) { chartInstancia.destroy(); } 
                    const ctx = document.getElementById('miGraficoDona').getContext('2d');
                    chartInstancia = new Chart(ctx, {
                        type: 'doughnut', 
                        data: {
                            labels: ['Puntuales', 'Retrasos', 'Salidas Tempranas', 'Salidas Irregulares', 'Faltas', 'Justificadas'],
                            datasets: [{
                                data: [data.puntual, data.retraso, data.salida_temprana, data.salida_irregular, data.falta, data.justificado],
                                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#991b1b', '#ef4444', '#64748b'], hoverOffset: 4
                            }]
                        }, options: { responsive: true, plugins: { legend: { display: false } } }
                    });
                })
                .catch(error => { document.getElementById('cargando-resumen').innerHTML = `<p style="color:#ef4444; font-weight:bold;">${error.message}</p>`; });
        }

        function cerrarResumen() {
            document.getElementById('modalOverlayResumen').classList.remove('activo');
            document.getElementById('modalContenidoResumen').classList.remove('activo');
        }

        function descargarPDF(idPersonal) {
            const mes = selectMes.value;
            const anio = selectAnio.value;
            if(!mes || mes === "") { Swal.fire('Atención', 'Seleccione un periodo válido.', 'warning'); return; }
            document.getElementById('pdf_id_personal').value = idPersonal;
            document.getElementById('pdf_mes').value = mes;
            document.getElementById('pdf_anio').value = anio;
            document.getElementById('pdf_id_cargo').value = cargoActivo; 
            
            document.getElementById('formGenerarPDF').submit();
        }
    </script>
</body>
</html>