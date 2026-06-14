<?php
/**
 * ============================================================
 * VISTA: Configuración de Asistencia
 * ARCHIVO: ConfiguracionAsistencia.php
 * PROYECTO: Sistema de Asistencia - CEIS Julian Yánez
 * ============================================================
 */

require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php';

if ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2) {
    $_SESSION['alerta_principal'] = ['tipo' => 'error', 'mensaje' => 'Acceso denegado. Configuración exclusiva para directivos.'];
    header("Location: principal.php");
    exit;
}

$nombre = $_SESSION['usuario'];
$rol    = $_SESSION['rol'];

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

            <div class="contenido-configuracion">

                <div class="panel-plantillas" id="panelPlantillas">

                    <div class="panel-plantillas-cabecera">
                        <span class="titulo-panel">Plantillas rápidas</span>
                        <span class="contador-plantillas" id="contadorPlantillas">
                            <?php echo $total_preestablecidas; ?> / 4
                        </span>
                    </div>

                    <div class="panel-plantillas-lista" id="listaPlantillas">

                    <p class="titulo-columna" style="display:none">Plantillas rápidas</p>

                    <?php if (empty($preestablecidas)): ?>
                        <p class="estado-vacio-plantillas">
                            Sin plantillas aún. Configura un horario en el formulario y guárdalo
                            como plantilla para acceder a él con un clic.
                        </p>
                    <?php else: ?>

                        <?php foreach ($preestablecidas as $pre): ?>
                            <div class="tarjeta-preestablecida <?php echo $pre['es_activa'] ? 'es-activa' : ''; ?>"
                                 data-id="<?php echo $pre['id_preestablecida']; ?>"
                                 id="tarjeta-<?php echo $pre['id_preestablecida']; ?>">

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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Aplicar al formulario
                                </button>

                            </div>
                        <?php endforeach; ?>

                    <?php endif; ?>

                    </div>

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
                    </div>

                </div>

                <div class="tarjeta-formulario-config" style="position: relative;">
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

                    <form id="formConfiguracion"
                          action="../controladores/ControladorConfiguracion.php"
                          method="POST">

                        <input type="hidden" name="accion" value="guardar_config">
                        <input type="hidden" name="id_preestablecida_activa" id="campoIdActivaOculto" value="">

                        <div class="grid-formulario">
                            <div class="grupo-input">
                                <label>Hora de Entrada Oficial</label>
                                <div class="input-con-icono">
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

                            <div class="grupo-input">
                                <label>Hora de Salida Oficial</label>
                                <div class="input-con-icono">
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

                            <div class="grupo-input campo-completo">
                                <label>Minutos de Tolerancia</label>
                                <div class="input-con-icono">
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

                        </div>

                        <div class="botones-accion-formulario">
                            <a href="principal.php" class="btn-cancelar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Volver al Panel
                            </a>
                            <button type="submit" class="btn-guardar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

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
    </div>

    <script>
        window.ConfiguracionConfig = {
            idUsuario: "<?php echo $_SESSION['id_usuario']; ?>",
            totalPlantillas: <?php echo $total_preestablecidas; ?>,
            maximoPlantillas: 4,
            alertas: [
                <?php if (isset($_SESSION['config_exito'])): ?>
                {
                    tipo: 'success',
                    titulo: '¡Actualizado!',
                    mensaje: 'Las reglas de asistencia se guardaron correctamente.'
                },
                <?php unset($_SESSION['config_exito']); endif; ?>
                
                <?php if (isset($_SESSION['config_error'])): ?>
                {
                    tipo: 'error',
                    titulo: 'Error de validación',
                    mensaje: '<?php echo addslashes($_SESSION['config_error']); ?>'
                }
                <?php unset($_SESSION['config_error']); endif; ?>
            ]
        };
    </script>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>

    <script src="../recursos/js/configuracionAsistencia.js?v=<?php echo time(); ?>"></script>

</body>
</html>