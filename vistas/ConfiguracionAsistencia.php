<?php
/**
 * ============================================================
 * VISTA: Configuración de Asistencia
 * ARCHIVO: ConfiguracionAsistencia.php
 * PROYECTO: Sistema de Asistencia - CEIS Julian Yánez
 * ------------------------------------------------------------
 * Descripción:
 *   Permite a los administradores (rol 1 y 2) definir las
 *   reglas globales del sistema de asistencia. La vista se
 *   divide en dos columnas:
 *
 *   IZQUIERDA — Tarjetas de configuraciones preestablecidas:
 *     Plantillas horarias guardadas en BD que el usuario puede
 *     aplicar al formulario con un solo clic (con confirmación
 *     vía SweetAlert). Máximo 4 tarjetas.
 *
 *   DERECHA — Formulario principal:
 *     Edita y guarda la configuración global del sistema
 *     (hora entrada, hora salida, tolerancia).
 *
 * Reglas de negocio:
 *   - Solo roles 1 (Administrador) y 2 (Coordinador) acceden.
 *   - La jornada mínima es 1 hora y máxima 8 horas.
 *   - La tolerancia no puede superar la duración de la jornada.
 *   - Máximo 4 configuraciones preestablecidas en BD.
 *
 * Dependencias:
 *   - ../configuracion/seguridad.php
 *   - ../configuracion/conexion.php
 *   - ../recursos/css/principal.css
 *   - ../recursos/js/sweetalert2.all.min.js
 *   - componentes/sidebar.php
 *   - componentes/topbar.php
 *
 * Última modificación: 2025
 * ============================================================
 */

require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php';

// ============================================================
// CONTROL DE ACCESO
// Solo Administrador (1) y Coordinador (2) pueden acceder.
// ============================================================
if ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2) {
    header("Location: principal.php");
    exit;
}

$nombre = $_SESSION['usuario'];
$rol    = $_SESSION['rol'];

// ============================================================
// OBTENCIÓN DE CONFIGURACIÓN ACTIVA
// Registro único id_config = 1. Se usan defaults si falla BD.
// ============================================================
try {
    $stmt = $conexion->prepare("SELECT * FROM configuracion WHERE id_config = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        $config = [
            'hora_entrada_general' => '07:00',
            'hora_salida_general'  => '13:00',
            'minutos_tolerancia'   => 15,
        ];
    }
} catch (PDOException $e) {
    $config = [
        'hora_entrada_general' => '07:00',
        'hora_salida_general'  => '13:00',
        'minutos_tolerancia'   => 15,
    ];
}

// ============================================================
// OBTENCIÓN DE CONFIGURACIONES PREESTABLECIDAS
// Se traen todas (máx. 4) ordenadas por fecha de creación.
// Si la tabla aún no existe, la sección se muestra vacía.
// ============================================================
$preestablecidas       = [];
$total_preestablecidas = 0;

try {
    $stmtPre = $conexion->prepare(
        "SELECT * FROM configuraciones_preestablecidas ORDER BY fecha_creacion ASC LIMIT 4"
    );
    $stmtPre->execute();
    $preestablecidas       = $stmtPre->fetchAll(PDO::FETCH_ASSOC);
    $total_preestablecidas = count($preestablecidas);
} catch (PDOException $e) {
    // Tabla inexistente o error: se mostrará la sección vacía
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - CEIS Julian Yánez</title>
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">

    <script>
        /* Tema guardado en localStorage para evitar parpadeo (FOUC) */
        (function () {
            const idUsr        = "<?php echo $_SESSION['id_usuario']; ?>";
            const temaGuardado = localStorage.getItem('tema_usuario_' + idUsr) || 'light';
            document.documentElement.setAttribute('data-theme', temaGuardado);
        })();
    </script>
</head>
<body>

    <?php
        $pagina_activa = 'configuracion';
        require_once 'componentes/sidebar.php';
    ?>

    <div class="contenedor-principal">
        <?php
            $titulo_pagina = 'Configuración de Asistencias';
            require_once 'componentes/topbar.php';
        ?>

        <main class="contenido">
            <h1>Reglas de Asistencia</h1>
            <p style="margin-block-end: 25px;">
                Define el horario global y la tolerancia para el personal del plantel.
                Usa las plantillas guardadas para aplicar configuraciones frecuentes con un clic.
            </p>

            <!-- ════════════════════════════════════════════════════
                 LAYOUT DE DOS COLUMNAS
                 ════════════════════════════════════════════════════ -->
            <div class="contenido-configuracion">

                <!-- ── PANEL IZQUIERDO: Plantillas preestablecidas ── -->
                <div class="panel-plantillas" id="panelPlantillas">

                    <!-- ZONA 1: Cabecera fija -->
                    <div class="panel-plantillas-cabecera">
                        <span class="titulo-panel">Plantillas rápidas</span>
                        <span class="contador-plantillas" id="contadorPlantillas">
                            <?php echo $total_preestablecidas; ?> / 4
                        </span>
                    </div>

                    <!-- ZONA 2: Lista scrolleable -->
                    <div class="panel-plantillas-lista" id="listaPlantillas">

                    <p class="titulo-columna" style="display:none">Plantillas rápidas</p>

                    <?php if (empty($preestablecidas)): ?>
                        <!-- Estado vacío inicial -->
                        <p class="estado-vacio-plantillas">
                            Sin plantillas aún. Configura un horario en el formulario y guárdalo
                            como plantilla para acceder a él con un clic.
                        </p>
                    <?php else: ?>

                        <!-- ── Renderizado de tarjetas preestablecidas ── -->
                        <?php foreach ($preestablecidas as $pre): ?>
                            <div class="tarjeta-preestablecida <?php echo $pre['es_activa'] ? 'es-activa' : ''; ?>"
                                 data-id="<?php echo $pre['id_preestablecida']; ?>"
                                 id="tarjeta-<?php echo $pre['id_preestablecida']; ?>">

                                <!-- Cabecera: nombre editable + X eliminar -->
                                <div class="cabecera-tarjeta">
                                    <span class="nombre-preestablecida"
                                          title="Doble clic para renombrar"
                                          ondblclick="activarEdicionNombre(this, <?php echo $pre['id_preestablecida']; ?>)">
                                        <?php echo htmlspecialchars($pre['nombre']); ?>
                                    </span>
                                    <button class="btn-eliminar-tarjeta"
                                            title="Eliminar esta plantilla"
                                            onclick="confirmarEliminarTarjeta(
                                                <?php echo $pre['id_preestablecida']; ?>,
                                                '<?php echo htmlspecialchars(addslashes($pre['nombre'])); ?>'
                                            )">
                                        <!-- SVG: icono X cerrar -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Badge visible solo en la tarjeta actualmente aplicada -->
                                <?php if ($pre['es_activa']): ?>
                                    <span class="badge-activa">
                                        <!-- SVG: punto de estado activo -->
                                        <svg width="6" height="6" viewBox="0 0 6 6">
                                            <circle cx="3" cy="3" r="3" fill="currentColor"/>
                                        </svg>
                                        Aplicada al sistema
                                    </span>
                                <?php endif; ?>

                                <hr class="separador-tarjeta">

                                <!-- Datos del horario en tabla compacta -->
                                <table class="tabla-horario">
                                    <tr>
                                        <td class="etiqueta">Entrada</td>
                                        <td class="valor"><?php echo date('H:i', strtotime($pre['hora_entrada'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Salida</td>
                                        <td class="valor"><?php echo date('H:i', strtotime($pre['hora_salida'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="etiqueta">Tolerancia</td>
                                        <td class="valor"><?php echo $pre['minutos_tolerancia']; ?> min</td>
                                    </tr>
                                </table>

                                <!-- Botón que carga esta configuración en el formulario -->
                                <button class="btn-aplicar-preestablecida"
                                        onclick="confirmarAplicarPlantilla(
                                            <?php echo $pre['id_preestablecida']; ?>,
                                            '<?php echo htmlspecialchars(addslashes($pre['nombre'])); ?>',
                                            '<?php echo date('H:i', strtotime($pre['hora_entrada'])); ?>',
                                            '<?php echo date('H:i', strtotime($pre['hora_salida'])); ?>',
                                            <?php echo $pre['minutos_tolerancia']; ?>
                                        )">
                                    <!-- SVG: flecha circular aplicar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Aplicar al formulario
                                </button>

                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                    </div><!-- /panel-plantillas-lista -->

                    <!-- ZONA 3: Pie fijo con botón agregar -->
                    <div class="panel-plantillas-pie">

                        <button class="tarjeta-agregar-nueva <?php echo ($total_preestablecidas >= 4) ? 'oculto' : ''; ?>"
                                id="btnAgregarNueva"
                                onclick="guardarComoNueva()"
                                title="Guarda la configuración del formulario como nueva plantilla">
                            <span class="icono-mas">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                            Guardar como plantilla
                        </button>

                        <p class="aviso-limite <?php echo ($total_preestablecidas >= 4) ? 'visible' : ''; ?>"
                           id="avisosLimite">
                            Límite de 4 plantillas alcanzado.
                        </p>

                    </div><!-- /panel-plantillas-pie -->

                </div><!-- /panel-plantillas -->


                <!-- ── COLUMNA DERECHA: Formulario principal ── -->
                <div class="tarjeta-formulario-config" style="position: relative;">
                    
                    <!-- BOTÓN FLOTANTE DE ESCRITORIO (DÍAS LIBRES) -->
                    <button id="btnFlotanteRangoModal" onclick="abrirModalFeriados()" class="btn-flotante-rango-modal" title="Configurar Días Libres">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" class="ionicon" viewBox="0 0 512 512">
                            <rect fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" x="48" y="80" width="416" height="384" rx="48"/>
                            <circle cx="296" cy="232" r="24"/><circle cx="376" cy="232" r="24"/>
                            <circle cx="296" cy="312" r="24"/><circle cx="376" cy="312" r="24"/>
                            <circle cx="136" cy="312" r="24"/><circle cx="216" cy="312" r="24"/>
                            <circle cx="136" cy="392" r="24"/><circle cx="216" cy="392" r="24"/>
                            <circle cx="296" cy="392" r="24"/>
                            <path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" stroke-linecap="round" d="M128 48v32M384 48v32"/>
                            <path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" d="M464 160H48"/>
                        </svg>
                    </button>

                    <!--
                        id="formConfiguracion"         → validador JS
                        campoIdActivaOculto            → le indica al controlador
                                                         qué plantilla marcar como
                                                         es_activa=1 al guardar
                    -->
                    <form id="formConfiguracion"
                          action="../controladores/ControladorConfiguracion.php"
                          method="POST">

                        <input type="hidden" name="accion" value="guardar_config">
                        <input type="hidden" name="id_preestablecida_activa" id="campoIdActivaOculto" value="">

                        <div class="grid-formulario">

                            <!-- Campo: Hora de Entrada Oficial -->
                            <div class="grupo-input">
                                <label>Hora de Entrada Oficial</label>
                                <div class="input-con-icono">
                                    <!-- SVG: flecha entrando (login) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    <input type="time"
                                           id="hora_entrada"
                                           name="hora_entrada"
                                           value="<?php echo date('H:i', strtotime($config['hora_entrada_general'])); ?>"
                                           required>
                                </div>
                                <small>Momento exacto en que inicia la jornada laboral.</small>
                            </div>

                            <!-- Campo: Hora de Salida Oficial -->
                            <div class="grupo-input">
                                <label>Hora de Salida Oficial</label>
                                <div class="input-con-icono">
                                    <!-- SVG: flecha saliendo (logout) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    <input type="time"
                                           id="hora_salida"
                                           name="hora_salida"
                                           value="<?php echo date('H:i', strtotime($config['hora_salida_general'])); ?>"
                                           required>
                                </div>
                                <small>Momento en que finaliza la jornada laboral.</small>
                            </div>

                            <!-- Campo: Minutos de Tolerancia -->
                            <div class="grupo-input campo-completo">
                                <label>Minutos de Tolerancia (Gracia)</label>
                                <div class="input-con-icono">
                                    <!-- SVG: reloj con manecillas -->
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <input type="number"
                                           id="minutos_tolerancia"
                                           name="minutos_tolerancia"
                                           value="<?php echo $config['minutos_tolerancia']; ?>"
                                           min="0"
                                           max="120"
                                           required>
                                </div>
                                <small>Tiempo permitido después de la entrada antes de marcar "Retraso".</small>
                            </div>

                        </div><!-- /grid-formulario -->

                        <!-- Botones de acción del formulario -->
                        <div class="botones-accion-formulario">
                            <a href="principal.php" class="btn-cancelar">
                                <!-- SVG: flecha volver -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Volver al Panel
                            </a>
                            <button type="submit" class="btn-guardar">
                                <!-- SVG: disquete guardar -->
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                Guardar Cambios
                            </button>
                        </div>

                    </form>
                </div><!-- /tarjeta-formulario-config -->

            </div><!-- /contenido-configuracion -->

            <!-- ════════════════════════════════════════════════════
                 MODAL DE DÍAS LIBRES (FERIADOS)
                 ════════════════════════════════════════════════════ -->
            <div class="modal-calendario-overlay" id="modalFeriadosOverlay" onclick="cerrarModalFeriadosSiOverlay(event)">
                <div class="modal-calendario-contenido">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="color: var(--primary-color); margin: 0; font-size: 1.4rem;">Días libres</h2>
                        <button onclick="cerrarModalFeriados()" style="background:none; border:none; cursor:pointer; color:var(--text-color);" title="Cerrar">
                            <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <button type="button" class="btn-cancelar" onclick="cambiarMesFeriado(-1)" style="padding: 5px 15px; border-radius: 8px;">Anterior</button>
                        <strong id="mesAnioFeriado" style="font-size: 1.1rem; text-transform: capitalize; color: var(--text-color);"></strong>
                        <button type="button" class="btn-cancelar" onclick="cambiarMesFeriado(1)" style="padding: 5px 15px; border-radius: 8px;">Siguiente</button>
                    </div>
                    <div id="gridCalendarioFeriados" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; text-align: center;">
                    </div>
                </div>
            </div>

            <!-- ════════════════════════════════════════════════════
                 BOTONES FLOTANTES MÓVIL
                 ════════════════════════════════════════════════════ -->
            <!-- Botón flotante para días libres (móvil) -->
            <button class="btn-flotante-plantillas btn-flotante-feriados-movil" onclick="abrirModalFeriados()" title="Configurar Días Libres">
                <svg width="22" height="22" xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                    <rect fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" x="48" y="80" width="416" height="384" rx="48"/>
                    <circle cx="296" cy="232" r="24"/><circle cx="376" cy="232" r="24"/>
                    <circle cx="296" cy="312" r="24"/><circle cx="376" cy="312" r="24"/>
                    <circle cx="136" cy="312" r="24"/><circle cx="216" cy="312" r="24"/>
                    <circle cx="136" cy="392" r="24"/><circle cx="216" cy="392" r="24"/>
                    <circle cx="296" cy="392" r="24"/>
                    <path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" stroke-linecap="round" d="M128 48v32M384 48v32"/>
                    <path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" d="M464 160H48"/>
                </svg>
            </button>

            <!-- Botón flotante de plantillas (existente) -->
            <button class="btn-flotante-plantillas"
                    id="btnAbrirModalPlantillas"
                    onclick="abrirModalPlantillas()"
                    title="Ver plantillas rápidas"
                    aria-label="Abrir plantillas de configuración">

                <span class="badge-flotante" id="badgeFlotante">
                    <?php echo $total_preestablecidas; ?>
                </span>

                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 2L4.5 13.5H12L11 22L19.5 10.5H12L13 2Z"/>
                </svg>
            </button>

            <!-- Overlay + panel modal de plantillas (móvil) - existente -->
            <div class="modal-plantillas-overlay"
                 id="modalPlantillasOverlay"
                 onclick="cerrarModalPlantillasSiOverlay(event)">

                <div class="panel-plantillas" id="panelPlantillasModal">
                    <div class="tirador-modal"></div>
                    <div class="panel-plantillas-cabecera">
                        <span class="titulo-panel">Plantillas rápidas</span>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="contador-plantillas">
                                <?php echo $total_preestablecidas; ?> / 4
                            </span>
                            <button onclick="cerrarModalPlantillas()"
                                    style="background:none;border:none;cursor:pointer;
                                           color:var(--text-color);opacity:0.6;
                                           display:flex;align-items:center;padding:2px;"
                                    title="Cerrar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="panel-plantillas-lista">
                        <?php if (empty($preestablecidas)): ?>
                            <p class="estado-vacio-plantillas">
                                Sin plantillas aún. Configura un horario y guárdalo como plantilla.
                            </p>
                        <?php else: ?>
                            <?php foreach ($preestablecidas as $pre): ?>
                                <div class="tarjeta-preestablecida <?php echo $pre['es_activa'] ? 'es-activa' : ''; ?>">
                                    <div class="cabecera-tarjeta">
                                        <span class="nombre-preestablecida"
                                              title="Doble clic para renombrar"
                                              ondblclick="activarEdicionNombre(this, <?php echo $pre['id_preestablecida']; ?>)">
                                            <?php echo htmlspecialchars($pre['nombre']); ?>
                                        </span>
                                        <button class="btn-eliminar-tarjeta"
                                                title="Eliminar esta plantilla"
                                                onclick="confirmarEliminarTarjeta(
                                                    <?php echo $pre['id_preestablecida']; ?>,
                                                    '<?php echo htmlspecialchars(addslashes($pre['nombre'])); ?>'
                                                )">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <?php if ($pre['es_activa']): ?>
                                        <span class="badge-activa">
                                            <svg width="6" height="6" viewBox="0 0 6 6">
                                                <circle cx="3" cy="3" r="3" fill="currentColor"/>
                                            </svg>
                                            Aplicada al sistema
                                        </span>
                                    <?php endif; ?>
                                    <hr class="separador-tarjeta">
                                    <table class="tabla-horario">
                                        <tr>
                                            <td class="etiqueta">Entrada</td>
                                            <td class="valor"><?php echo date('H:i', strtotime($pre['hora_entrada'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="etiqueta">Salida</td>
                                            <td class="valor"><?php echo date('H:i', strtotime($pre['hora_salida'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="etiqueta">Tolerancia</td>
                                            <td class="valor"><?php echo $pre['minutos_tolerancia']; ?> min</td>
                                        </tr>
                                    </table>
                                    <button class="btn-aplicar-preestablecida"
                                            onclick="confirmarAplicarPlantilla(
                                                <?php echo $pre['id_preestablecida']; ?>,
                                                '<?php echo htmlspecialchars(addslashes($pre['nombre'])); ?>',
                                                '<?php echo date('H:i', strtotime($pre['hora_entrada'])); ?>',
                                                '<?php echo date('H:i', strtotime($pre['hora_salida'])); ?>',
                                                <?php echo $pre['minutos_tolerancia']; ?>
                                            )">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Aplicar al formulario
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="panel-plantillas-pie">
                        <button class="tarjeta-agregar-nueva <?php echo ($total_preestablecidas >= 4) ? 'oculto' : ''; ?>"
                                onclick="guardarComoNueva()"
                                title="Guardar configuración actual como plantilla">
                            <span class="icono-mas">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                            Guardar como plantilla
                        </button>
                        <p class="aviso-limite <?php echo ($total_preestablecidas >= 4) ? 'visible' : ''; ?>">
                            Límite de 4 plantillas alcanzado.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div><!-- /contenedor-principal -->

    <!-- Librería SweetAlert2 -->
    <script src="../recursos/js/sweetalert2.all.min.js"></script>

    <script>
        // ============================================================
        // VARIABLES Y CONSTANTES GLOBALES
        // ============================================================
        const html              = document.documentElement;
        const claveTema         = 'tema_usuario_<?php echo $_SESSION['id_usuario']; ?>';
        const MAXIMO_PLANTILLAS = 4;

        // Conteo inicial de plantillas desde PHP
        let totalPlantillas = <?php echo $total_preestablecidas; ?>;


        // ============================================================
        // HELPER: Parámetros de tema para SweetAlert2
        // Adapta el fondo y color de texto al tema claro/oscuro activo.
        // @returns {Object}
        // ============================================================
        function parametrosTema() {
            const esDark = html.getAttribute('data-theme') === 'dark';
            return {
                background: esDark ? '#1e293b' : '#fff',
                color:      esDark ? '#fff'    : '#333',
            };
        }


        // ============================================================
        // GESTIÓN DE TEMA (CLARO / OSCURO)
        // ============================================================
        const btnCambiarTema = document.getElementById('btnCambiarTema');

        if (btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function (e) {
                e.preventDefault();
                const temaActual = html.getAttribute('data-theme');
                const nuevoTema  = temaActual === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem(claveTema, nuevoTema);
            });
        }


        // ============================================================
        // ALERTAS DE RETROALIMENTACIÓN DEL SERVIDOR
        // Variables de sesión inyectadas por PHP tras cada operación.
        // Se consumen aquí una sola vez y no persisten en recargas.
        // ============================================================

        <?php if (isset($_SESSION['config_exito'])): ?>
            Swal.fire({
                title:              '¡Actualizado!',
                text:               'Las reglas de asistencia se guardaron correctamente.',
                icon:               'success',
                confirmButtonColor: '#10b981',
                ...parametrosTema(),
            });
            <?php unset($_SESSION['config_exito']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['config_error'])): ?>
            Swal.fire({
                title:              'Error de validación',
                text:               '<?php echo addslashes($_SESSION['config_error']); ?>',
                icon:               'error',
                confirmButtonColor: '#ef4444',
                ...parametrosTema(),
            });
            <?php unset($_SESSION['config_error']); ?>
        <?php endif; ?>


        // ============================================================
        // FUNCIÓN: Aplicar plantilla al formulario
        // Muestra un SweetAlert con los datos de la plantilla elegida.
        // Si el usuario confirma, carga los valores en los inputs.
        // NO guarda en BD — solo rellena el formulario visualmente.
        //
        // @param {number} idPlantilla
        // @param {string} nombrePlantilla
        // @param {string} entrada       - "HH:MM"
        // @param {string} salida        - "HH:MM"
        // @param {number} tolerancia
        // ============================================================
        function confirmarAplicarPlantilla(idPlantilla, nombrePlantilla, entrada, salida, tolerancia) {
            const t = parametrosTema();
            const colorSubtexto = t.color === '#fff' ? '#94a3b8' : '#6b7280';

            Swal.fire({
                title:             `Aplicar "${nombrePlantilla}"`,
                html:              `¿Quieres cargar esta configuración en el formulario?<br><br>
                                    <small style="color:${colorSubtexto}">
                                        Entrada: <strong>${entrada}</strong> &nbsp;·&nbsp;
                                        Salida: <strong>${salida}</strong> &nbsp;·&nbsp;
                                        Tolerancia: <strong>${tolerancia} min</strong>
                                    </small>`,
                icon:              'question',
                showCancelButton:   true,
                confirmButtonText:  'Sí, aplicar',
                cancelButtonText:   'Cancelar',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor:  '#6b7280',
                ...t,
            }).then(resultado => {
                if (!resultado.isConfirmed) return;

                // Cargar valores en los inputs del formulario
                document.getElementById('hora_entrada').value       = entrada;
                document.getElementById('hora_salida').value        = salida;
                document.getElementById('minutos_tolerancia').value = tolerancia;

                // Registrar el id de la plantilla para que el controlador
                // actualice su campo es_activa al guardar
                document.getElementById('campoIdActivaOculto').value = idPlantilla;

                // Feedback visual: resaltado azul temporal en los inputs cargados
                ['hora_entrada', 'hora_salida', 'minutos_tolerancia'].forEach(id => {
                    const campo = document.getElementById(id);
                    campo.style.borderColor = '#3b82f6';
                    campo.style.boxShadow   = '0 0 0 3px rgba(59,130,246,0.18)';
                    setTimeout(() => {
                        campo.style.borderColor = '';
                        campo.style.boxShadow   = '';
                    }, 2400);
                });

                // Scroll suave al formulario (útil en móvil)
                document.getElementById('formConfiguracion')
                        .scrollIntoView({ behavior: 'smooth', block: 'center' });

                Swal.fire({
                    title:             '¡Listo!',
                    text:              'Revisa los valores y haz clic en "Guardar Cambios".',
                    icon:              'info',
                    timer:             2400,
                    showConfirmButton:  false,
                    ...parametrosTema(),
                });
            });
        }


        // ============================================================
        // FUNCIÓN: Guardar configuración del formulario como plantilla
        // Lee los inputs actuales, pide nombre al usuario mediante
        // SweetAlert con input de texto y envía al controlador vía
        // fetch (sin recargar página). Al confirmar: reload.
        // ============================================================
        function guardarComoNueva() {
            if (totalPlantillas >= MAXIMO_PLANTILLAS) {
                Swal.fire({
                    title:              'Límite alcanzado',
                    text:               'Solo se permiten 4 plantillas. Elimina una antes de agregar otra.',
                    icon:               'warning',
                    confirmButtonColor: '#f59e0b',
                    ...parametrosTema(),
                });
                return;
            }

            const entrada    = document.getElementById('hora_entrada').value;
            const salida     = document.getElementById('hora_salida').value;
            const tolerancia = document.getElementById('minutos_tolerancia').value;

            if (!entrada || !salida) {
                Swal.fire({
                    title:              'Formulario incompleto',
                    text:               'Completa los campos de hora antes de guardar como plantilla.',
                    icon:               'warning',
                    confirmButtonColor: '#f59e0b',
                    ...parametrosTema(),
                });
                return;
            }

            Swal.fire({
                title:            'Nombre de la plantilla',
                input:            'text',
                inputPlaceholder: 'Ej: Jornada corta, Llegada tarde...',
                showCancelButton:   true,
                confirmButtonText:  'Guardar',
                cancelButtonText:   'Cancelar',
                confirmButtonColor: '#10b981',
                cancelButtonColor:  '#6b7280',
                inputAttributes: { maxlength: '60' },
                inputValidator: valor => {
                    if (!valor || !valor.trim()) return 'El nombre no puede estar vacío.';
                    if (valor.trim().length > 60) return 'Máximo 60 caracteres.';
                },
                ...parametrosTema(),
            }).then(resultado => {
                if (!resultado.isConfirmed) return;

                const nombre = resultado.value.trim();
                const datos  = new FormData();
                datos.append('accion',            'nueva_plantilla');
                datos.append('nombre',             nombre);
                datos.append('hora_entrada',       entrada);
                datos.append('hora_salida',        salida);
                datos.append('minutos_tolerancia', tolerancia);

                fetch('../controladores/ControladorConfiguracion.php', {
                    method: 'POST',
                    body:   datos,
                })
                .then(res => res.json())
                .then(json => {
                    if (json.exito) {
                        Swal.fire({
                            title:             '¡Plantilla guardada!',
                            text:              `"${nombre}" está disponible en las plantillas.`,
                            icon:              'success',
                            confirmButtonColor: '#10b981',
                            ...parametrosTema(),
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title:             'Error',
                            text:              json.mensaje || 'No se pudo guardar la plantilla.',
                            icon:              'error',
                            confirmButtonColor: '#ef4444',
                            ...parametrosTema(),
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        title:             'Error de red',
                        text:              'No se pudo comunicar con el servidor.',
                        icon:              'error',
                        confirmButtonColor: '#ef4444',
                        ...parametrosTema(),
                    });
                });
            });
        }


        // ============================================================
        // FUNCIÓN: Confirmar y eliminar una tarjeta preestablecida
        // Muestra SweetAlert de confirmación destructiva.
        // Si confirma: fetch al controlador, animación de salida,
        // eliminación del DOM y actualización del contador.
        //
        // @param {number} idPlantilla
        // @param {string} nombrePlantilla
        // ============================================================
        function confirmarEliminarTarjeta(idPlantilla, nombrePlantilla) {
            Swal.fire({
                title:              `¿Eliminar "${nombrePlantilla}"?`,
                text:               'Esta plantilla no se podrá recuperar.',
                icon:               'warning',
                showCancelButton:   true,
                confirmButtonText:  'Sí, eliminar',
                cancelButtonText:   'Cancelar',
                confirmButtonColor: '#ef4444',
                cancelButtonColor:  '#6b7280',
                ...parametrosTema(),
            }).then(resultado => {
                if (!resultado.isConfirmed) return;

                const datos = new FormData();
                datos.append('accion',             'eliminar_plantilla');
                datos.append('id_preestablecida',   idPlantilla);

                fetch('../controladores/ControladorConfiguracion.php', {
                    method: 'POST',
                    body:   datos,
                })
                .then(res => res.json())
                .then(json => {
                    if (json.exito) {
                        // Animación de salida suave
                        const tarjeta = document.getElementById('tarjeta-' + idPlantilla);
                        if (tarjeta) {
                            tarjeta.style.opacity   = '0';
                            tarjeta.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                tarjeta.remove();
                                totalPlantillas--;
                                actualizarBotonAgregar();
                            }, 300);
                        }
                        Swal.fire({
                            title:             'Eliminada',
                            text:              'La plantilla fue eliminada correctamente.',
                            icon:              'success',
                            timer:             1800,
                            showConfirmButton:  false,
                            ...parametrosTema(),
                        });
                    } else {
                        Swal.fire({
                            title:             'Error',
                            text:              json.mensaje || 'No se pudo eliminar la plantilla.',
                            icon:              'error',
                            confirmButtonColor: '#ef4444',
                            ...parametrosTema(),
                        });
                    }
                });
            });
        }


        // ============================================================
        // FUNCIÓN: Edición inline del nombre de la tarjeta
        // Reemplaza el <span> del nombre por un <input> de texto.
        // Al perder foco o presionar Enter → guarda vía fetch.
        // Escape → cancela sin guardar.
        //
        // @param {HTMLElement} span - Elemento con el nombre actual
        // @param {number}      id   - id_preestablecida
        // ============================================================
        function activarEdicionNombre(span, id) {
            const nombreOriginal = span.textContent.trim();

            const inputEditable     = document.createElement('input');
            inputEditable.type      = 'text';
            inputEditable.value     = nombreOriginal;
            inputEditable.maxLength = 60;
            inputEditable.className = 'input-nombre-editable';

            // Sustituir span por input
            span.parentNode.replaceChild(inputEditable, span);
            inputEditable.focus();
            inputEditable.select();

            let guardando = false; // Evitar doble submit por blur + enter

            function guardarNombre() {
                if (guardando) return;
                guardando = true;

                const nuevoNombre = inputEditable.value.trim();

                // Sin cambio: restaurar span sin petición
                if (!nuevoNombre || nuevoNombre === nombreOriginal) {
                    inputEditable.parentNode.replaceChild(span, inputEditable);
                    return;
                }

                const datos = new FormData();
                datos.append('accion',             'renombrar_plantilla');
                datos.append('id_preestablecida',   id);
                datos.append('nombre',              nuevoNombre);

                fetch('../controladores/ControladorConfiguracion.php', {
                    method: 'POST',
                    body:   datos,
                })
                .then(res => res.json())
                .then(json => {
                    if (json.exito) {
                        span.textContent = nuevoNombre;
                    }
                    // Restaurar el span (con nombre nuevo o el original si falló)
                    inputEditable.parentNode.replaceChild(span, inputEditable);
                })
                .catch(() => {
                    inputEditable.parentNode.replaceChild(span, inputEditable);
                });
            }

            inputEditable.addEventListener('blur', guardarNombre);

            inputEditable.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    inputEditable.blur(); // Dispara el blur que llama a guardarNombre
                }
                if (e.key === 'Escape') {
                    guardando = true; // Evitar que el blur guarde
                    inputEditable.removeEventListener('blur', guardarNombre);
                    inputEditable.parentNode.replaceChild(span, inputEditable);
                }
            });
        }


        // ============================================================
        // FUNCIÓN: Actualizar visibilidad del botón "Agregar nueva"
        // y el contador "X / 4" de la cabecera del panel.
        // Se llama tras crear o eliminar una plantilla vía fetch.
        // ============================================================
        function actualizarBotonAgregar() {
            const btn      = document.getElementById('btnAgregarNueva');
            const aviso    = document.getElementById('avisosLimite');
            const contador = document.getElementById('contadorPlantillas');
            const lleno    = totalPlantillas >= MAXIMO_PLANTILLAS;

            btn.classList.toggle('oculto', lleno);
            aviso.classList.toggle('visible', lleno);

            if (contador) contador.textContent = totalPlantillas + ' / ' + MAXIMO_PLANTILLAS;
        }


        // ============================================================
        // VALIDACIÓN FRONTEND DEL FORMULARIO PRINCIPAL
        // Primera capa de defensa ante datos inválidos.
        //
        // Reglas aplicadas:
        //   1. Salida > Entrada
        //   2. Jornada mínima: 60 min (1 hora)
        //   3. Jornada máxima: 480 min (8 horas)
        //   4. Tolerancia < duración total de la jornada
        // ============================================================
        document.getElementById('formConfiguracion').addEventListener('submit', function (e) {

            const inputEntrada    = document.getElementById('hora_entrada');
            const inputSalida     = document.getElementById('hora_salida');
            const inputTolerancia = document.getElementById('minutos_tolerancia');
            const tolerancia      = parseInt(inputTolerancia.value, 10);
            const t               = parametrosTema();

            /**
             * Convierte "HH:MM" a minutos totales desde las 00:00.
             * @param {string} hora
             * @returns {number}
             */
            function horaAMinutos(hora) {
                const [h, m] = hora.split(':').map(Number);
                return h * 60 + m;
            }

            /**
             * Resalta un campo con borde rojo durante 3 segundos.
             * @param {HTMLElement} campo
             */
            function marcarError(campo) {
                campo.style.borderColor = '#ef4444';
                campo.style.boxShadow   = '0 0 0 3px rgba(239,68,68,0.2)';
                setTimeout(() => {
                    campo.style.borderColor = '';
                    campo.style.boxShadow   = '';
                }, 3000);
            }

            /** Quita el estilo de error de todos los campos. */
            function limpiarErrores() {
                [inputEntrada, inputSalida, inputTolerancia].forEach(c => {
                    c.style.borderColor = '';
                    c.style.boxShadow   = '';
                });
            }

            limpiarErrores();

            const minEntrada = horaAMinutos(inputEntrada.value);
            const minSalida  = horaAMinutos(inputSalida.value);
            const duracion   = minSalida - minEntrada;

            // ── Regla 1: Salida posterior a la entrada ────────────
            if (minSalida <= minEntrada) {
                e.preventDefault();
                marcarError(inputEntrada);
                marcarError(inputSalida);
                Swal.fire({
                    title: 'Horario inválido',
                    text:  'La hora de salida debe ser posterior a la hora de entrada.',
                    icon:  'error', confirmButtonColor: '#ef4444', ...t,
                });
                return;
            }

            // ── Regla 2: Jornada mínima de 1 hora (60 min) ───────
            if (duracion < 60) {
                e.preventDefault();
                marcarError(inputEntrada);
                marcarError(inputSalida);
                Swal.fire({
                    title: 'Jornada muy corta',
                    text:  'La jornada debe ser de al menos 1 hora (60 minutos).',
                    icon:  'warning', confirmButtonColor: '#f59e0b', ...t,
                });
                return;
            }

            // ── Regla 3: Jornada máxima de 8 horas (480 min) ─────
            if (duracion > 480) {
                e.preventDefault();
                marcarError(inputEntrada);
                marcarError(inputSalida);
                Swal.fire({
                    title: 'Jornada excesiva',
                    html:  `La jornada no puede superar las <strong>8 horas</strong>.<br>Jornada actual: <strong>${duracion} min</strong>.`,
                    icon:  'warning', confirmButtonColor: '#f59e0b', ...t,
                });
                return;
            }

            // ── Regla 4: Tolerancia menor a la duración total ─────
            if (!isNaN(tolerancia) && tolerancia >= duracion) {
                e.preventDefault();
                marcarError(inputTolerancia);
                Swal.fire({
                    title: 'Tolerancia inválida',
                    html:  `Los minutos de tolerancia (<strong>${tolerancia} min</strong>) no pueden igualar o superar la duración de la jornada (<strong>${duracion} min</strong>).`,
                    icon:  'warning', confirmButtonColor: '#f59e0b', ...t,
                });
                return;
            }

            // Validaciones superadas: el formulario se envía normalmente.
        });


        // ============================================================
        // MODAL DE PLANTILLAS (MÓVIL)
        // El overlay y el panel del modal solo son funcionales en
        // pantallas < 860px. En escritorio el panel está en el layout
        // normal y estas funciones simplemente no se llaman.
        // ============================================================

        /**
         * Abre el modal de plantillas desde abajo.
         * Bloquea el scroll del body mientras está abierto.
         */
        function abrirModalPlantillas() {
            const overlay = document.getElementById('modalPlantillasOverlay');
            if (!overlay) return;
            overlay.classList.add('abierto');
            document.body.style.overflow = 'hidden'; // Evita scroll del fondo
        }

        /**
         * Cierra el modal de plantillas con animación de salida.
         * Restaura el scroll del body.
         */
        function cerrarModalPlantillas() {
            const overlay = document.getElementById('modalPlantillasOverlay');
            if (!overlay) return;
            overlay.classList.remove('abierto');
            document.body.style.overflow = '';
        }

        /**
         * Cierra el modal solo si el clic fue en el overlay oscuro,
         * no dentro del panel. Evita cerrar accidentalmente.
         * @param {MouseEvent} evento
         */
        function cerrarModalPlantillasSiOverlay(evento) {
            if (evento.target === document.getElementById('modalPlantillasOverlay')) {
                cerrarModalPlantillas();
            }
        }

        /**
         * Cierra el modal al presionar Escape (accesibilidad).
         */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarModalPlantillas();
        });

        /**
         * Cuando el usuario aplica una plantilla desde el modal,
         * el modal se cierra automáticamente para que vea el formulario.
         * Se sobreescribe confirmarAplicarPlantilla para añadir este paso.
         */
        const _confirmarAplicarPlantillaOriginal = confirmarAplicarPlantilla;
        confirmarAplicarPlantilla = function(idPlantilla, nombre, entrada, salida, tolerancia) {
            _confirmarAplicarPlantillaOriginal(idPlantilla, nombre, entrada, salida, tolerancia);
            cerrarModalPlantillas();
        };


        // ============================================================
        // MÓDULO: DÍAS LIBRES (FERIADOS Y FESTIVOS)
        // ============================================================
        let mesCal = new Date().getMonth();
        let anioCal = new Date().getFullYear();
        let feriadosActuales = [];

        function abrirModalFeriados() {
            const overlay = document.getElementById('modalFeriadosOverlay');
            if (overlay) {
                overlay.classList.add('abierto');
                document.body.style.overflow = 'hidden';
            }
            cargarFeriados();
        }

        function cerrarModalFeriados() {
            const overlay = document.getElementById('modalFeriadosOverlay');
            if (overlay) {
                overlay.classList.remove('abierto');
                document.body.style.overflow = '';
            }
        }

        function cerrarModalFeriadosSiOverlay(e) {
            if (e.target.id === 'modalFeriadosOverlay') cerrarModalFeriados();
        }

        // Cerrar con la tecla Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') cerrarModalFeriados();
        });

        function cambiarMesFeriado(dir) {
            mesCal += dir;
            if (mesCal < 0) { mesCal = 11; anioCal--; }
            else if (mesCal > 11) { mesCal = 0; anioCal++; }
            renderizarCalendarioFeriados();
        }

        function cargarFeriados() {
            fetch('../controladores/ControladorFeriados.php?accion=listar')
                .then(res => res.json())
                .then(data => {
                    if (data.success) feriadosActuales = data.data; 
                    renderizarCalendarioFeriados();
                }).catch(err => console.error('Error cargando feriados:', err));
        }

        function renderizarCalendarioFeriados() {
            const grid = document.getElementById('gridCalendarioFeriados');
            const labelMes = document.getElementById('mesAnioFeriado');
            const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            if(!grid) return;
            labelMes.textContent = `${nombresMeses[mesCal]} ${anioCal}`;
            grid.innerHTML = '';

            diasSemana.forEach(d => {
                const div = document.createElement('div');
                div.style.fontWeight = 'bold'; div.style.padding = '10px 0'; div.style.color = 'var(--text-color)'; div.textContent = d;
                grid.appendChild(div);
            });

            const primerDia = new Date(anioCal, mesCal, 1).getDay();
            const diasEnMes = new Date(anioCal, mesCal + 1, 0).getDate();

            for (let i = 0; i < primerDia; i++) grid.appendChild(document.createElement('div'));

            for (let i = 1; i <= diasEnMes; i++) {
                const div = document.createElement('div');
                const fechaFormat = `${anioCal}-${String(mesCal + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                const feriadoEncontrado = feriadosActuales.find(f => f.fecha === fechaFormat);
                
                div.textContent = i; div.style.padding = '15px 5px'; div.style.borderRadius = '8px';
                div.style.border = '1px solid var(--text-color)';
                div.style.transition = 'all 0.2s';
                
                const diaSem = new Date(anioCal, mesCal, i).getDay();
                const esFinde = (diaSem === 0 || diaSem === 6); // domingo o sábado
                
                if (feriadoEncontrado) {
                    // Día libre configurado (morado)
                    div.style.backgroundColor = '#e9d5ff'; // morado claro
                    div.style.color = '#6b21a8';
                    div.style.fontWeight = 'bold';
                    div.style.borderColor = '#c084fc';
                    div.title = feriadoEncontrado.descripcion;
                    div.style.cursor = 'pointer';
                    div.onclick = () => gestionarFeriado(fechaFormat, feriadoEncontrado);
                } else if (esFinde) {
                    // Fin de semana: no se puede modificar
                    div.style.backgroundColor = 'var(--bg-light)';
                    div.style.color = 'var(--text-color)';
                    div.style.opacity = '0.6';
                    div.style.cursor = 'default';
                    div.title = 'Día no laborable, no se puede modificar';
                    div.onclick = null;
                } else {
                    // Día laborable sin feriado
                    div.style.backgroundColor = 'var(--bg-card)';
                    div.style.color = 'var(--text-color)';
                    div.style.cursor = 'pointer';
                    div.onclick = () => gestionarFeriado(fechaFormat, null);
                }

                div.onmouseover = () => {
                    if (!esFinde) div.style.transform = 'scale(1.05)';
                };
                div.onmouseout = () => {
                    if (!esFinde) div.style.transform = 'scale(1)';
                };
                
                grid.appendChild(div);
            }
        }

        function gestionarFeriado(fechaStr, datosFeriado) {
            const t = parametrosTema();
            const fechaVisual = fechaStr.split('-').reverse().join('/');

            if (datosFeriado) {
                Swal.fire({
                    title: '¿Restaurar Día?',
                    html: `Día libre:<br><b style="color:var(--primary-color);">${datosFeriado.descripcion}</b><br><br>¿Volver a marcar como laborable?`,
                    icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, restaurar', cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#10b981', cancelButtonColor: '#6b7280', ...t
                }).then((res) => { if (res.isConfirmed) enviarFeriadoBD(fechaStr, null, null); });
            } else {
                Swal.fire({
                    title: 'Inhabilitar Día',
                    html: `
                        <div style="text-align: left; font-size: 0.95rem;">
                            <p style="margin-bottom: 10px; color: var(--primary-color); font-weight: bold;">Fecha: ${fechaVisual}</p>
                            <label style="font-weight: bold; margin-bottom: 5px; display: block; color: var(--text-color);">Clasificación:</label>
                            <select id="swal-feriado-tipo" style="width: 100%; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ccc; outline: none; font-family: inherit;">
                                <option value="Feriado">Feriado Nacional / Regional</option>
                                <option value="Día Festivo">Día Festivo del Plantel</option>
                            </select>
                            <label style="font-weight: bold; margin-bottom: 5px; display: block; color: var(--text-color);">Justificación (Mín. 10 caracteres):</label>
                            <input type="text" id="swal-feriado-motivo" placeholder="Ej: Aniversario del Plantel..." autocomplete="off" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; outline: none; box-sizing: border-box; font-family: inherit;">
                        </div>
                    `,
                    showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', ...t,
                    preConfirm: () => {
                        const tipo = document.getElementById('swal-feriado-tipo').value;
                        const motivo = document.getElementById('swal-feriado-motivo').value.trim();
                        if (motivo.length < 10) { Swal.showValidationMessage('La justificación debe tener al menos 10 caracteres.'); return false; }
                        return { tipo, motivo };
                    }
                }).then((res) => {
                    if (res.isConfirmed) { enviarFeriadoBD(fechaStr, res.value.tipo, res.value.motivo); }
                });
            }
        }

        function enviarFeriadoBD(fecha, tipo, motivo) {
            const payload = { fecha: fecha };
            if (tipo && motivo) { payload.tipo = tipo; payload.motivo = motivo; }

            fetch('../controladores/ControladorFeriados.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    Swal.fire({ title: '¡Éxito!', text: data.message || 'Actualizado.', icon: 'success', timer: 2000, showConfirmButton: false, ...parametrosTema() });
                    cargarFeriados(); 
                } else {
                    Swal.fire('Error', data.message || 'Error de validación', 'error');
                }
            }).catch(() => Swal.fire('Error', 'Fallo de comunicación', 'error'));
        }

    </script>

</body>
</html>