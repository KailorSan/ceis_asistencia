<?php
// =======================================================
// 1. CONFIGURACIÓN INICIAL Y SEGURIDAD
// =======================================================
require_once '../configuracion/seguridad.php'; 
require_once '../configuracion/conexion.php';  
require_once '../controladores/ControladorBitacora.php'; // Controlador de la Bitácora

if ($_SESSION['id_rol'] != 1) {
    header("Location: principal.php");
    exit();
}

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

// -- LÓGICA PARA LÍMITES DIARIOS (CON BITÁCORA JSON) --
date_default_timezone_set('America/Caracas');
$fecha_hoy = date('d-m-Y');
$carpeta_respaldos = '../respaldos/';
$archivo_limites = $carpeta_respaldos . 'limites_diarios.json';

if (!file_exists($carpeta_respaldos)) {
    mkdir($carpeta_respaldos, 0777, true);
}

// Leemos la bitácora de límites
$limites = ['fecha' => $fecha_hoy, 'generados' => 0, 'subidos' => 0, 'restaurados' => 0];
if (file_exists($archivo_limites)) {
    $data = json_decode(file_get_contents($archivo_limites), true);
    if ($data && isset($data['fecha']) && $data['fecha'] === $fecha_hoy) {
        $limites = $data;
        if (!isset($limites['restaurados'])) $limites['restaurados'] = 0;
    }
}

// Límites por diseño del sistema
$limite_generar = 4;
$limite_subir = 2;
$limite_restaurar = 2;

$restantes_generar = max(0, $limite_generar - $limites['generados']);
$restantes_subir = max(0, $limite_subir - $limites['subidos']);
$restantes_restaurar = max(0, $limite_restaurar - $limites['restaurados']);

// -- EXTRAER HISTORIAL DE LA BITÁCORA INEXPUGNABLE --
$registros_bitacora = ControladorBitacora::obtenerHistorial($conexion);
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad y Respaldos - CEIS Julian Yánez</title>
    
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

    <?php $pagina_activa = 'seguridad'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Seguridad y Respaldos'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            
            <div style="margin-block-end: 25px; display: flex; justify-content: flex-start;">
                <button type="button" class="btn-guardar" style="padding: 12px 30px; font-size: 1.05rem; border-radius: 50px; box-shadow: 0 4px 15px rgba(64, 111, 243, 0.3); display: flex; align-items: center; gap: 10px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="abrirModalBitacora()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ver Registro de Auditoría
                </button>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-block-end: 25px; flex-wrap: wrap; gap: 15px;">
                
                <div style="flex: 1; min-inline-size: 250px;">
                    <h1 style="margin-block-end: 5px;">Gestión de Base de Datos</h1>
                    <p style="margin: 0; font-size: 0.95rem; color: var(--text-color);">Administra los respaldos del sistema CEIS.</p>
                </div>
                
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    
                    <div style="background: var(--navbar-bg); padding: 8px 15px; border-radius: 10px; box-shadow: var(--shadow-sm); border-inline-start: 4px solid <?php echo $restantes_generar > 0 ? '#10b981' : '#ef4444'; ?>; display: flex; align-items: center; gap: 10px;">
                        <div style="color: <?php echo $restantes_generar > 0 ? '#10b981' : '#ef4444'; ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: var(--text-color); font-size: 0.75rem; text-transform: uppercase;">Generar Disp.</h4>
                            <strong style="font-size: 1.1rem; color: <?php echo $restantes_generar > 0 ? 'var(--primary-color)' : '#ef4444'; ?>;"><?php echo $restantes_generar; ?> <span style="font-size: 0.8rem; color: var(--text-color);">Restantes</span></strong>
                        </div>
                    </div>

                    <div style="background: var(--navbar-bg); padding: 8px 15px; border-radius: 10px; box-shadow: var(--shadow-sm); border-inline-start: 4px solid <?php echo $restantes_subir > 0 ? '#f59e0b' : '#ef4444'; ?>; display: flex; align-items: center; gap: 10px;">
                        <div style="color: <?php echo $restantes_subir > 0 ? '#f59e0b' : '#ef4444'; ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: var(--text-color); font-size: 0.75rem; text-transform: uppercase;">Cargas Externas</h4>
                            <strong style="font-size: 1.1rem; color: <?php echo $restantes_subir > 0 ? 'var(--primary-color)' : '#ef4444'; ?>;"><?php echo $restantes_subir; ?> <span style="font-size: 0.8rem; color: var(--text-color);">Restantes</span></strong>
                        </div>
                    </div>

                    <div style="background: var(--navbar-bg); padding: 8px 15px; border-radius: 10px; box-shadow: var(--shadow-sm); border-inline-start: 4px solid <?php echo $restantes_restaurar > 0 ? '#8b5cf6' : '#ef4444'; ?>; display: flex; align-items: center; gap: 10px;">
                        <div style="color: <?php echo $restantes_restaurar > 0 ? '#8b5cf6' : '#ef4444'; ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: var(--text-color); font-size: 0.75rem; text-transform: uppercase;">Restauraciones</h4>
                            <strong style="font-size: 1.1rem; color: <?php echo $restantes_restaurar > 0 ? 'var(--primary-color)' : '#ef4444'; ?>;"><?php echo $restantes_restaurar; ?> <span style="font-size: 0.8rem; color: var(--text-color);">Restantes</span></strong>
                        </div>
                    </div>

                </div>
            </div>

            <div class="grid-seguridad">
                
                <div class="panel-seguridad" style="padding: 2rem;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-block-end: 15px;">
                        <svg style="inline-size: 28px; block-size: 28px; color: var(--primary-color);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" /></svg>
                        <h2 style="color: var(--primary-color); margin:0; font-size: 1.4rem;">Crear Respaldo</h2>
                    </div>
                    <p style="font-size: 0.9rem; margin-block-end: 20px;">Selecciona el método de generación o sube una copia externa.</p>

                    <div class="botones-generar" style="display: flex; flex-direction: column; gap: 12px;">
                        
                        <button type="button" class="btn-guardar btn-completo" style="padding: 12px; font-size: 0.95rem; <?php echo $restantes_generar == 0 ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>" onclick="generarRespaldo('local', <?php echo $restantes_generar; ?>)">
                            <svg style="inline-size: 20px; block-size: 20px; margin-inline-end: 8px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            <?php echo $restantes_generar > 0 ? 'Generar y Guardar en Historial' : 'Límite de generación alcanzado'; ?>
                        </button>

                        <button type="button" class="btn-cancelar btn-completo" style="padding: 12px; font-size: 0.95rem; <?php echo $restantes_generar == 0 ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>" onclick="generarRespaldo('descargar', <?php echo $restantes_generar; ?>)">
                            <svg style="inline-size: 20px; block-size: 20px; margin-inline-end: 8px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            <?php echo $restantes_generar > 0 ? 'Generar y Descargar Inmediata' : 'Límite de generación alcanzado'; ?>
                        </button>

                        <div class="separador-personal" style="margin: 10px 0; font-size: 0.8rem;">O SUBIR ARCHIVO EXTERNO</div>

                        <form id="form-subir" action="../controladores/ControladorSeguridad.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="subir_externo">
                            <div class="contenedor-archivo">
                                <input type="file" name="archivo_sql" id="archivo_sql" accept=".sql" class="input-file-oculto" onchange="document.getElementById('form-subir').submit();" <?php echo $restantes_subir == 0 ? 'disabled' : ''; ?>>
                                <label for="archivo_sql" class="btn-subir-archivo" style="justify-content: center; padding: 12px; font-size: 0.95rem; <?php echo $restantes_subir == 0 ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                                    <svg style="inline-size: 20px; block-size: 20px; margin-inline-end: 8px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                    <span><?php echo $restantes_subir > 0 ? 'Cargar .SQL Externo' : 'Límite de cargas alcanzado'; ?></span>
                                </label>
                            </div>
                        </form>

                        <div style="background: rgba(64, 111, 243, 0.05); border: 1px solid rgba(64, 111, 243, 0.2); border-radius: 10px; padding: 12px; margin-block-start: 5px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-block-end: 6px; color: var(--primary-color);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <h4 style="margin: 0; font-size: 0.9rem;">Información de Seguridad</h4>
                            </div>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-color); line-height: 1.4; text-align: justify;">
                                El sistema almacenará un máximo de <strong>10 archivos</strong> en el historial. Al generar uno nuevo, el más antiguo será reemplazado automáticamente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="panel-seguridad panel-alto" style="padding: 2rem;">
                    <?php
                        $archivos_sql = glob($carpeta_respaldos . "*.sql");
                        if ($archivos_sql) {
                            usort($archivos_sql, function($a, $b) { return filemtime($b) - filemtime($a); });
                        }
                        $total_respaldos = $archivos_sql ? count($archivos_sql) : 0;
                    ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-block-end: 20px;">
                        <h2 style="color: var(--primary-color); margin:0; font-size: 1.4rem;">Historial</h2>
                        <span style="background: var(--bg-light); padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; color: var(--text-color);">Total: <?php echo $total_respaldos; ?> / 10</span>
                    </div>

                    <div class="lista-historial" style="max-block-size: 380px; padding-inline-end: 5px;">
                        <?php if($total_respaldos > 0): ?>
                            <?php foreach($archivos_sql as $ruta_archivo): 
                                $nombre_archivo = basename($ruta_archivo);
                                $fecha_archivo = date("d/m/Y h:i A", filemtime($ruta_archivo));
                                $peso_bytes = filesize($ruta_archivo);
                                
                                $peso_mb = number_format($peso_bytes / 1048576, 2) . ' MB';
                                if($peso_bytes < 1048576) {
                                    $peso_mb = number_format($peso_bytes / 1024, 2) . ' KB';
                                }
                            ?>
                            <div class="item-respaldo" style="padding: 12px 18px;">
                                <div class="info-respaldo">
                                    <div class="icono-bd" style="padding: 8px;">
                                        <svg style="inline-size: 20px; block-size: 20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                    </div>
                                    <div>
                                        <h4 style="word-break: break-all; padding-inline-end: 10px; font-size: 0.95rem; margin-block-end: 2px;"><?php echo htmlspecialchars($nombre_archivo); ?></h4>
                                        <small style="font-size: 0.8rem;"><?php echo $peso_mb; ?> • Creado: <?php echo $fecha_archivo; ?></small>
                                    </div>
                                </div>
                                <div class="acciones-respaldo">
                                    <button class="btn-icono-accion btn-restaurar" title="Restaurar este respaldo" onclick="pedirPasswordRestaurar('<?php echo $nombre_archivo; ?>', <?php echo $restantes_restaurar; ?>)">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    </button>
                                    <button class="btn-icono-accion btn-descargar" title="Descargar archivo" onclick="window.location.href='../controladores/ControladorSeguridad.php?accion=descargar_historial&archivo=<?php echo urlencode($nombre_archivo); ?>'">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    </button>
                                    <button class="btn-icono-accion btn-eliminar" title="Eliminar del historial" onclick="eliminarRespaldo('<?php echo $nombre_archivo; ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: var(--text-color);">
                                <svg xmlns="http://www.w3.org/2000/svg" style="inline-size: 45px; block-size: 45px; opacity: 0.5; margin-block-end: 10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                <p style="font-size: 0.9rem;">No hay respaldos guardados en el historial.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </main>

        <div id="modalBitacora" style="display: none; position: fixed; inset-block-start: 0; inset-inline-start: 0; inline-size: 100vw; block-size: 100vh; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
            
            <div style="background: var(--bg-color); inline-size: 95%; block-size: 95%; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); display: flex; flex-direction: column; overflow: hidden; animation: zoomIn 0.3s ease-out;">
                
                <div style="padding: 15px 30px; background: var(--navbar-bg); border-block-end: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; z-index: 2;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="background: rgba(64, 111, 243, 0.1); padding: 10px; border-radius: 10px; color: var(--primary-color);">
                            <svg style="inline-size: 28px; block-size: 28px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h2 style="margin: 0; color: var(--text-color); font-size: 1.4rem;">Registro Inexpugnable (Bitácora)</h2>
                        </div>
                    </div>
                    
                    <button onclick="cerrarModalBitacora()" style="background: rgba(239, 68, 68, 0.1); border: none; cursor: pointer; color: #ef4444; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff';" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='#ef4444';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div style="display: flex; flex: 1; overflow: hidden;">
                    
                    <div style="inline-size: 260px; background: var(--navbar-bg); border-inline-end: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 20px 0; overflow-y: auto;">
                        <h3 style="padding: 0 25px; font-size: 0.8rem; text-transform: uppercase; color: var(--text-color); opacity: 0.5; margin-block-end: 15px; letter-spacing: 1px;">Filtrar por Módulo</h3>
                        
                        <button class="btn-tab-bitacora activo" onclick="cambiarTabBitacora(this, 'Todos')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                            Todos los Registros
                        </button>
                        
                        <button class="btn-tab-bitacora" onclick="cambiarTabBitacora(this, 'Asistencia')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            Asistencia
                        </button>

                        <button class="btn-tab-bitacora" onclick="cambiarTabBitacora(this, 'Usuarios')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Usuarios y Perfiles
                        </button>

                        <button class="btn-tab-bitacora" onclick="cambiarTabBitacora(this, 'Reportes')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            Descarga de Reportes
                        </button>
                        
                        <button class="btn-tab-bitacora" onclick="cambiarTabBitacora(this, 'Configuracion')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Configuración
                        </button>

                        <button class="btn-tab-bitacora" onclick="cambiarTabBitacora(this, 'Seguridad')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            Base de Datos (BD)
                        </button>
                    </div>

                    <div style="flex: 1; padding: 25px 30px; background: var(--bg-light); display: flex; flex-direction: column; overflow: hidden;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-block-end: 20px; flex-wrap: wrap; gap: 15px; background: var(--navbar-bg); padding: 15px; border-radius: 12px; box-shadow: var(--shadow-sm);">
                            
                            <div style="position: relative; flex: 1; min-inline-size: 250px; max-inline-size: 400px;">
                                <svg style="position: absolute; inset-inline-start: 15px; inset-block-start: 50%; transform: translateY(-50%); color: var(--text-color); opacity: 0.5; inline-size: 20px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                <input type="text" placeholder="Buscar registro específico..." style="inline-size: 100%; padding: 10px 15px 10px 45px; border-radius: 50px; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); font-size: 0.95rem; outline: none;">
                            </div>

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <svg style="color: var(--text-color); opacity: 0.6; inline-size: 20px; margin-inline-end: 5px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                
                                <select class="select-filtro-bitacora">
                                    <option value="">Día</option>
                                    <?php for($i=1; $i<=31; $i++) echo "<option value='$i'>".str_pad($i,2,'0',STR_PAD_LEFT)."</option>"; ?>
                                </select>
                                
                                <select class="select-filtro-bitacora">
                                    <option value="">Mes</option>
                                    <option value="01">Enero</option><option value="02">Febrero</option><option value="03">Marzo</option>
                                    <option value="04">Abril</option><option value="05">Mayo</option><option value="06">Junio</option>
                                    <option value="07">Julio</option><option value="08">Agosto</option><option value="09">Septiembre</option>
                                    <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                                </select>
                                
                                <select class="select-filtro-bitacora">
                                    <option value="">Año</option>
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                </select>

                                <button style="padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 50px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: bold; margin-inline-start: 5px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                    Filtrar
                                </button>
                            </div>
                        </div>

                        <div style="flex: 1; overflow-y: auto; padding-inline-end: 5px;">
                            <table style="inline-size: 100%; border-collapse: separate; border-spacing: 0 10px;">
                                <thead>
                                    <tr style="text-align: start; color: var(--text-color); font-size: 0.85rem; text-transform: uppercase; opacity: 0.7;">
                                        <th style="padding: 0 15px 10px 15px;">Fecha / Hora</th>
                                        <th style="padding: 0 15px 10px 15px;">Usuario Ejecutor</th>
                                        <th style="padding: 0 15px 10px 15px;">Módulo</th>
                                        <th style="padding: 0 15px 10px 15px;">Detalles Exactos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($registros_bitacora)): ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-color); opacity: 0.6;">
                                                No hay registros en la bitácora aún.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($registros_bitacora as $reg): 
                                            // Formatear Fecha y Hora
                                            $fecha = date("d/m/Y", strtotime($reg['fecha_hora']));
                                            $hora = date("h:i A", strtotime($reg['fecha_hora']));
                                            
                                            // Configurar Estilos Visuales según el Módulo
                                            $modulo = $reg['modulo'];
                                            $estilo = [];
                                            switch($modulo) {
                                                case 'Asistencia': 
                                                    $estilo = ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b']; break;
                                                case 'Usuarios': 
                                                    $estilo = ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444']; break;
                                                case 'Reportes': 
                                                    $estilo = ['bg' => 'rgba(64, 111, 243, 0.1)', 'color' => '#406ff3']; break;
                                                // NUEVO: Color morado para Configuración
                                                case 'Configuracion': 
                                                    $estilo = ['bg' => 'rgba(139, 92, 246, 0.1)', 'color' => '#8b5cf6']; break;
                                                case 'Seguridad': 
                                                    $estilo = ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => '#10b981']; break;
                                                default:
                                                    $estilo = ['bg' => 'rgba(100, 116, 139, 0.1)', 'color' => '#64748b']; break;
                                            }
                                        ?>
                                        <tr class="fila-bitacora" data-modulo="<?php echo htmlspecialchars($modulo); ?>" style="background: var(--navbar-bg); box-shadow: var(--shadow-sm); transition: transform 0.2s;">
                                            <td style="padding: 15px; border-radius: 10px 0 0 10px; color: var(--text-color); inline-size: 140px;">
                                                <strong><?php echo $fecha; ?></strong><br>
                                                <small style="opacity: 0.7;"><?php echo $hora; ?></small>
                                            </td>
                                            <td style="padding: 15px; color: var(--text-color);">
                                                <div style="display:flex; align-items:center; gap:10px;">
                                                    <img src="../recursos/img/perfiles/default.png" style="inline-size:30px; block-size:30px; border-radius:50%;"> 
                                                    <strong><?php echo htmlspecialchars($reg['nombre_usuario']); ?></strong>
                                                </div>
                                            </td>
                                            <td style="padding: 15px; color: var(--text-color);">
                                                <span style="background: <?php echo $estilo['bg']; ?>; color: <?php echo $estilo['color']; ?>; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                                    <?php echo htmlspecialchars($modulo); ?>
                                                </span>
                                            </td>
                                            <td style="padding: 15px; border-radius: 0 10px 10px 0; color: var(--text-color); font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($reg['accion']); ?>
                                                <?php if(!empty($reg['detalles'])): ?>
                                                    <br><small style="color: var(--primary-color); opacity: 0.8;"><?php echo htmlspecialchars($reg['detalles']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    
    <script>
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        const html = document.documentElement;
        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.add('girando');
                const temaActual = html.getAttribute('data-theme');
                const nuevoTema = temaActual === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
                setTimeout(() => { this.classList.remove('girando'); }, 500);
            });
        }

        function generarRespaldo(tipo, restantes) {
            if (restantes <= 0) {
                Swal.fire('Límite alcanzado', 'Ya has generado el máximo de respaldos permitidos por hoy (4).', 'warning');
                return;
            }

            let textoMensaje = tipo === 'local' 
                ? 'El respaldo se guardará en el servidor y aparecerá en tu historial.'
                : 'El respaldo se generará y se descargará automáticamente a tu equipo.';
                
            Swal.fire({
                title: '¿Generar copia de seguridad?', text: textoMensaje, icon: 'info',
                showCancelButton: true, confirmButtonColor: '#406ff3', cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, generar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (tipo === 'local') {
                        Swal.fire({
                            title: 'Procesando...', text: 'Generando y guardando archivo SQL.',
                            allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }
                        });
                        window.location.href = '../controladores/ControladorSeguridad.php?accion=generar&tipo=local';
                    } else {
                        Swal.fire({
                            title: '¡Preparando Descarga!', text: 'El archivo SQL se descargará en breve.',
                            icon: 'success', timer: 3000, showConfirmButton: false
                        });
                        setTimeout(() => { window.location.href = '../controladores/ControladorSeguridad.php?accion=generar&tipo=descargar'; }, 800);
                    }
                }
            });
        }

        function pedirPasswordRestaurar(nombreArchivo, restantes) {
            if (restantes <= 0) {
                Swal.fire('Límite alcanzado', 'Ya has utilizado el máximo de 2 restauraciones permitidas por hoy.', 'warning');
                return;
            }

            Swal.fire({
                title: '¡ADVERTENCIA CRÍTICA!',
                html: "Estás a punto de restaurar la base de datos.<br><br><b>Por favor, ingrese su contraseña para confirmar:</b>",
                icon: 'warning',
                input: 'password',
                inputAttributes: { autocapitalize: 'off', autocorrect: 'off', placeholder: 'Escribe tu contraseña...' },
                showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
                confirmButtonText: 'CONFIRMAR RESTAURACIÓN', cancelButtonText: 'Cancelar',
                preConfirm: (password) => {
                    if (!password) { Swal.showValidationMessage('La contraseña es obligatoria.'); }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST'; form.action = '../controladores/ControladorSeguridad.php';

                    const inputAccion = document.createElement('input');
                    inputAccion.type = 'hidden'; inputAccion.name = 'accion'; inputAccion.value = 'restaurar';
                    
                    const inputArchivo = document.createElement('input');
                    inputArchivo.type = 'hidden'; inputArchivo.name = 'archivo'; inputArchivo.value = nombreArchivo;
                    
                    const inputPass = document.createElement('input');
                    inputPass.type = 'hidden'; inputPass.name = 'password_admin'; inputPass.value = result.value;

                    form.appendChild(inputAccion); form.appendChild(inputArchivo); form.appendChild(inputPass);
                    document.body.appendChild(form);

                    Swal.fire({
                        title: 'Restaurando...', text: 'Verificando seguridad e importando base de datos.',
                        allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }
                    });
                    form.submit();
                }
            });
        }

        function eliminarRespaldo(nombreArchivo) {
            Swal.fire({
                title: '¿Eliminar respaldo?', text: "Ya no podrás utilizar este archivo para restaurar el sistema.", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = '../controladores/ControladorSeguridad.php?accion=eliminar&archivo=' + encodeURIComponent(nombreArchivo); }
            });
        }

        // ==========================================
        // FUNCIONES PARA EL MODAL DE BITÁCORA REDISEÑADO
        // ==========================================
        function abrirModalBitacora() {
            const modal = document.getElementById('modalBitacora');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; 
        }

        function cerrarModalBitacora() {
            const modal = document.getElementById('modalBitacora');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; 
        }
        
        // Función para filtrar dinámicamente por módulo en la barra lateral
        function cambiarTabBitacora(btn, moduloFiltro) {
            // Cambiar el diseño del botón presionado
            document.querySelectorAll('.btn-tab-bitacora').forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
            
            // Mostrar/Ocultar filas basándose en el data-modulo
            const filas = document.querySelectorAll('.fila-bitacora');
            filas.forEach(fila => {
                const moduloFila = fila.getAttribute('data-modulo');
                
                if (moduloFiltro === 'Todos' || moduloFila === moduloFiltro) {
                    fila.style.display = 'table-row';
                } else {
                    fila.style.display = 'none';
                }
            });
        }
        
    </script>

    <?php if(isset($_SESSION['alerta_principal'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '¡Éxito!' : '¡Error!'; ?>',
                text: '<?php echo $_SESSION['alerta_principal']['mensaje']; ?>',
                icon: '<?php echo $_SESSION['alerta_principal']['tipo']; ?>',
                confirmButtonColor: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '#10b981' : '#ef4444'; ?>',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });
        </script>
        <?php unset($_SESSION['alerta_principal']); ?>
    <?php endif; ?>

</body>
</html>