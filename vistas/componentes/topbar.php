<?php
require_once '../controladores/ControladorNotificaciones.php';
// Obtenemos las notificaciones del usuario actual
$notificaciones = [];
if (isset($_SESSION['id_usuario'])) {
    $notificaciones = ControladorNotificaciones::obtenerNoLeidas($conexion, $_SESSION['id_usuario']);
}
$cantidad_notificaciones = count($notificaciones);
?>
<header class="barra-superior">
    <h3><?php echo isset($titulo_pagina) ? $titulo_pagina : 'CEIS Julian Yánez'; ?></h3>
    
    <div class="acciones-superior">
        <div class="contenedor-iconos-top">
            
            <div class="contenedor-campana">
                <button class="btn-tema-top" id="btnNotificaciones" aria-label="Notificaciones">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <?php if ($cantidad_notificaciones > 0): ?>
                        <span class="badge-notificacion" id="badgeNotificacion"><?php echo $cantidad_notificaciones; ?></span>
                    <?php endif; ?>
                </button>
                
                <div class="dropdown-notificaciones" id="dropdownNotificaciones">
                    <div class="dropdown-header">
                        <h4>Notificaciones</h4>
                    </div>
                    <div class="dropdown-body">
                        <?php if ($cantidad_notificaciones > 0): ?>
                            <?php foreach ($notificaciones as $notif): 
                                $clase = ($notif['tipo'] == 'Exito') ? 'notif-exito' : (($notif['tipo'] == 'Alerta') ? 'notif-alerta' : 'notif-info');
                                $icono = ($notif['tipo'] == 'Exito') ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />' : (($notif['tipo'] == 'Alerta') ? '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />' : '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />');
                                
                                // Sanitizamos el texto para pasarlo al JavaScript sin romper las comillas
                                $mensaje_js = htmlspecialchars(addslashes($notif['mensaje']));
                                $fecha_js = date('d/m/Y h:i A', strtotime($notif['fecha_creacion']));
                            ?>
                                <div class="item-notificacion <?php echo $clase; ?>" style="cursor: pointer;" onclick="abrirNotificacion('<?php echo $mensaje_js; ?>', '<?php echo $notif['tipo']; ?>', '<?php echo $fecha_js; ?>')">
                                    <div class="icono-notif">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><?php echo $icono; ?></svg>
                                    </div>
                                    <div class="texto-notif">
                                        <p><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                        <span><?php echo $fecha_js; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="sin-notificaciones">No tienes notificaciones nuevas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button class="btn-tema-top" id="btnCambiarTema" aria-label="Cambiar Tema">
                <svg class="icono-sol" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg class="icono-luna" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>

        <div class="usuario-info">
            <h4><?php echo htmlspecialchars($nombre); ?></h4>
            <span><?php echo $rol; ?></span>
        </div>
    </div>
</header>

<script>
    const btnNotificaciones = document.getElementById('btnNotificaciones');
    const dropdownNotificaciones = document.getElementById('dropdownNotificaciones');
    const badgeNotificacion = document.getElementById('badgeNotificacion');

    if(btnNotificaciones) {
        btnNotificaciones.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownNotificaciones.classList.toggle('activo');
            
            if (dropdownNotificaciones.classList.contains('activo') && badgeNotificacion) {
                fetch('../controladores/ControladorNotificaciones.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'accion=marcar_leidas'
                }).then(response => response.text()).then(data => {
                    if(data.trim() === 'ok') {
                        badgeNotificacion.style.display = 'none'; 
                    }
                });
            }
        });

        document.addEventListener('click', function(e) {
            if (!btnNotificaciones.contains(e.target) && !dropdownNotificaciones.contains(e.target)) {
                dropdownNotificaciones.classList.remove('activo');
            }
        });
    }

    // MAGIA NUEVA: Función para expandir la notificación en un Modal
    function abrirNotificacion(mensaje, tipo, fecha) {
        let iconoAlerta = 'info';
        let tituloAlerta = 'Información';
        let colorBoton = '#3b82f6'; // Azul Info
        
        if (tipo === 'Exito') {
            iconoAlerta = 'success';
            tituloAlerta = '¡Aprobado!';
            colorBoton = '#10b981'; // Verde
        } else if (tipo === 'Alerta') {
            iconoAlerta = 'error'; // Error para los rechazos (Círculo con X)
            tituloAlerta = 'Denegado';
            colorBoton = '#ef4444'; // Rojo
        }

        Swal.fire({
            title: tituloAlerta,
            text: mensaje,
            icon: iconoAlerta,
            footer: '<span style="color: #94a3b8; font-size: 0.85rem;">Enviado el ' + fecha + '</span>',
            confirmButtonColor: colorBoton,
            confirmButtonText: 'Entendido',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
        });
    }
</script>