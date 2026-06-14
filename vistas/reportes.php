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
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            document.documentElement.setAttribute('data-theme', localStorage.getItem('tema_usuario_' + idUsr) || 'light');
        })();
    </script>
    <style>
        /* ================================================================
           AJUSTES DEL MODAL Y TARJETA FLOTANTE (SIN SCROLL)
           ================================================================ */
        
        /* Ocultamos el scroll del modal por completo y mostramos desbordamiento de la tarjeta si hace falta */
        #modalContenidoResumen {
            overflow: visible !important;
            max-height: max-content !important; 
            padding-bottom: 1.5rem !important;
        }

        /* Estilos del botón pequeño de Feriados */
        .btn-alerta-feriado {
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid #c084fc;
            color: #9333ea;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.2s ease;
            outline: none;
            margin-left: 8px;
        }

        .btn-alerta-feriado:hover {
            background: rgba(168, 85, 247, 0.2);
            transform: scale(1.1);
        }

        /* Tarjeta flotante que se despliega al hacer clic */
        .tarjeta-info-feriados {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(15px);
            width: 280px;
            background-color: var(--navbar-bg);
            border: 1px solid #c084fc;
            box-shadow: 0 10px 30px rgba(168, 85, 247, 0.25);
            padding: 15px;
            border-radius: 12px;
            font-size: 0.85rem;
            color: var(--text-color);
            text-align: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }

        /* Flechita superior de la tarjeta flotante */
        .tarjeta-info-feriados::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 10px;
            height: 10px;
            background-color: var(--navbar-bg);
            border-left: 1px solid #c084fc;
            border-top: 1px solid #c084fc;
        }

        /* Clase activa para mostrar la tarjeta */
        .tarjeta-info-feriados.activa {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(8px);
            pointer-events: auto;
        }

        [data-theme="dark"] .tarjeta-info-feriados {
            background-color: #1e293b;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        [data-theme="dark"] .tarjeta-info-feriados::before {
            background-color: #1e293b;
        }
    </style>
</head>
<body>

    <?php $pagina_activa = 'reportes'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = $es_directivo ? 'Panel de Reportes' : 'Mi Reporte de Asistencia'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            <div class="cabecera-personal cabecera-personal--reportes">
                <div>
                    <h1><?php echo $es_directivo ? 'Directorio de Reportes' : 'Mi Reporte Mensual'; ?></h1>
                    <p>Selecciona el mes o el año para generar el expediente.</p>
                </div>
            </div>

            <form id="formGenerarPDF" action="generar_pdf_asistencia.php" method="POST" target="_blank" class="form-pdf-oculto">
                <input type="hidden" name="id_personal" id="pdf_id_personal">
                <input type="hidden" name="mes" id="pdf_mes">
                <input type="hidden" name="anio" id="pdf_anio">
                <input type="hidden" name="id_cargo" id="pdf_id_cargo" value="todos">
            </form>

            <div class="contenedor-filtros-globales <?php if(!$es_directivo) echo 'contenedor-filtros-globales--personal'; ?>">
                <div class="grupo-selectores">
                    <span class="label-periodo">Evaluar Período:</span>
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
                        <button onclick="descargarPDF('todos')" class="btn-guardar btn-descarga-general">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Descargar General
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($es_directivo): ?>
                <div class="botones-filtro-cargo" id="contenedor-filtros">
                    <button class="btn-filtro activo" onclick="aplicarFiltrosCombinados('todos', this, true)">Todos</button>
                    <?php foreach($cargos as $c): ?>
                        <button class="btn-filtro" onclick="aplicarFiltrosCombinados(<?php echo $c['id_cargo']; ?>, this, true)">
                            <?php echo htmlspecialchars($c['nombre_cargo']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid-perfiles" id="directorio-personal">
                <?php foreach ($lista_personal as $emp): ?>
                    <div class="tarjeta-perfil item-filtrable" data-cargo="<?php echo $emp['id_cargo']; ?>">
                        <div class="banner-tarjeta banner-tarjeta--reportes"></div>
                        <div class="contenedor-avatar contenedor-avatar--reportes">
                            <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($emp['foto_perfil']); ?>" alt="Foto">
                        </div>
                        <div class="info-perfil info-perfil--reportes">
                            <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                            <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                            
                            <div class="acciones-perfil">
                                <button onclick="abrirResumen(<?php echo $emp['id_personal']; ?>, '<?php echo addslashes($emp['nombres'] . ' ' . $emp['apellidos']); ?>', '<?php echo addslashes($emp['nombre_cargo']); ?>')" class="btn-editar-horario btn-ver-resumen">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Ver Resumen Web
                                </button>
                                <button onclick="descargarPDF(<?php echo $emp['id_personal']; ?>)" class="btn-guardar btn-descargar-pdf-tarjeta">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Descargar PDF
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- CONTENEDOR DEL BOTÓN VER MÁS -->
            <?php if ($es_directivo): ?>
            <div id="contenedor-ver-mas-reportes" class="contenedor-ver-mas">
                <button id="btn-ver-mas-reportes" class="btn-guardar btn-ver-mas-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    Cargar más personal
                </button>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <div class="modal-overlay" id="modalOverlayResumen">
        <div class="modal-contenido modal-resumen-contenido" id="modalContenidoResumen">
            <div class="modal-header">
                <h2>Resumen de Asistencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarResumen()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <div class="resumen-encabezado">
                <h3 id="resumen-nombre" class="resumen-nombre">Cargando...</h3>
                <span id="resumen-cargo" class="resumen-cargo"></span>
                
                <div style="display: flex; align-items: center; justify-content: center; margin-top: 5px; position: relative;">
                    <p id="resumen-periodo" class="resumen-periodo" style="margin: 0;"></p>
                    
                    <!-- BOTÓN Y TARJETA FLOTANTE DE FERIADOS -->
                    <div id="btn-info-feriados" style="display: none; position: relative;">
                        <button type="button" class="btn-alerta-feriado" onclick="toggleTarjetaFeriados(event)" title="Información de feriados omitidos">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                            </svg>
                        </button>
                        <div class="tarjeta-info-feriados" id="tarjeta-info-feriados">
                            <strong style="color: #a855f7; display: block; margin-bottom: 5px;">Precisión Activada</strong>
                            El sistema omitió automáticamente <strong id="cantidad-feriados-omitidos" style="color: #a855f7; font-size: 1.1rem;">0</strong> día(s) feriado(s) para mantener el cálculo exacto de la asistencia.
                        </div>
                    </div>
                </div>

            </div>
            
            <div id="cargando-resumen" class="resumen-cargando">
                <svg class="animacion-vibrar" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--primary-color)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <div class="contenedor-grafico" id="contenedor-grafico-resumen"><canvas id="miGraficoDona"></canvas></div>
            
            <div class="grid-resumen grid-resumen--oculto" id="datos-resumen">
                <div class="caja-resumen caja-verde">Llegada<br>Puntual<span class="numero-resumen" id="num-puntual">0</span></div>
                <div class="caja-resumen caja-naranja">Llegada<br>Tardía<span class="numero-resumen" id="num-retraso">0</span></div>
                <div class="caja-resumen caja-azul">Salida<br>Temprana<span class="numero-resumen" id="num-salida-temp">0</span></div>
                <div class="caja-resumen caja-rojo-oscuro">Salida<br>Irregular<span class="numero-resumen" id="num-salida-irreg">0</span></div>
                <div class="caja-resumen caja-roja">Falta<br>Completa<span class="numero-resumen" id="num-falta">0</span></div>
                <div class="caja-resumen caja-gris">Falta<br>Justificada<span class="numero-resumen" id="num-justificado">0</span></div>
            </div>
        </div>
    </div>

    <!-- EL PUENTE: Transfiere configuración de PHP a JS -->
    <script>
        window.ReportesConfig = {
            idUsuario: "<?php echo $_SESSION['id_usuario']; ?>",
            esDirectivo: <?php echo $es_directivo ? 'true' : 'false'; ?>,
            periodosActivos: <?php echo $periodos_json; ?>
        };
    </script>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script src="../recursos/js/chart.min.js"></script>
    <script src="../recursos/js/reportes.js?v=<?php echo time(); ?>"></script>

</body>
</html>