<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

if ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2) {
    header("Location: principal.php");
    exit;
}

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

// Obtenemos la configuración actual para llenar el formulario
try {
    $stmt = $conexion->prepare("SELECT * FROM configuracion WHERE id_config = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$config) {
        $config = ['hora_entrada_general' => '07:00', 'hora_salida_general' => '13:00', 'minutos_tolerancia' => 15];
    }
} catch (PDOException $e) {
    $config = ['hora_entrada_general' => '07:00', 'hora_salida_general' => '13:00', 'minutos_tolerancia' => 15];
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
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            const temaGuardado = localStorage.getItem('tema_usuario_' + idUsr) || 'light';
            document.documentElement.setAttribute('data-theme', temaGuardado);
        })();
    </script>
</head>
<body>

   <?php $pagina_activa = 'configuracion'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Configuración de Asistencias'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            <h1>Reglas de Asistencia</h1>
            <p style="margin-block-end: 25px;">Define el horario global y la tolerancia para el personal del plantel.</p>

            <div class="tarjeta-formulario-config">
                <form action="../controladores/ControladorConfiguracion.php" method="POST">
                    
                    <div class="grid-formulario">
                        
                        <div class="grupo-input">
                            <label>Hora de Entrada Oficial</label>
                            <div class="input-con-icono">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                <input type="time" name="hora_entrada" value="<?php echo date('H:i', strtotime($config['hora_entrada_general'])); ?>" required>
                            </div>
                            <small>Momento exacto en que inicia la jornada laboral.</small>
                        </div>

                        <div class="grupo-input">
                            <label>Hora de Salida Oficial</label>
                            <div class="input-con-icono">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                <input type="time" name="hora_salida" value="<?php echo date('H:i', strtotime($config['hora_salida_general'])); ?>" required>
                            </div>
                            <small>Momento en que finaliza la jornada laboral.</small>
                        </div>

                        <div class="grupo-input campo-completo">
                            <label>Minutos de Tolerancia (Gracia)</label>
                            <div class="input-con-icono">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <input type="number" name="minutos_tolerancia" value="<?php echo $config['minutos_tolerancia']; ?>" min="0" max="120" required>
                            </div>
                            <small>Tiempo permitido después de la entrada antes de marcar "Retraso".</small>
                        </div>

                    </div>

                    <div class="botones-accion-formulario">
                        <a href="principal.php" class="btn-cancelar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Volver al Panel
                        </a>
                        <button type="submit" class="btn-guardar">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    
    <script>
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        const html = document.documentElement;
        const claveTemaPersonalizado = 'tema_usuario_<?php echo $_SESSION['id_usuario']; ?>';

        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                const temaActual = html.getAttribute('data-theme');
                const nuevoTema = temaActual === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem(claveTemaPersonalizado, nuevoTema);
            });
        }

        // Alerta de Éxito
        <?php if(isset($_SESSION['config_exito'])): ?>
            Swal.fire({
                title: '¡Actualizado!',
                text: 'Las reglas de asistencia se han guardado correctamente.',
                icon: 'success',
                confirmButtonColor: '#10b981',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });
            <?php unset($_SESSION['config_exito']); ?>
        <?php endif; ?>
    </script>

</body>
</html>