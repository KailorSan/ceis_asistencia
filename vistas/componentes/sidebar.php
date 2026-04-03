<nav class="barra-lateral" role="navigation" aria-label="Menú principal">
    
    <div class="logo-sidebar">
        <div class="contenedor-logo-neon">
            <img src="../recursos/img/logo_ceis.jpg" alt="Escudo CEIS Julian Yánez" class="img-escudo-neon">
        </div>
    </div>

    <ul class="menu-navegacion">
        
        <li class="menu-item">
            <a href="principal.php" class="enlace-menu" aria-label="Inicio" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'inicio') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Inicio</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="../vistas/asistencia.php" class="enlace-menu" aria-label="Control de Asistencia" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'asistencia') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Asistencia</span>
            </a>
        </li>

        <?php if($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2): ?>
        <li class="menu-item">
            <a href="../vistas/ConfiguracionAsistencia.php" class="enlace-menu" aria-label="Configuración de asistencia" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'configuracion') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3l1.5 1.5" />
                </svg>
                <span>Configuración de asistencia</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if($_SESSION['id_rol'] == 1): ?>
        <li class="menu-item">
            <a href="../vistas/personal.php" class="enlace-menu" aria-label="Gestión de Personal" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'personal') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Personal</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2): ?>
        <li class="menu-item">
            <a href="../vistas/justificaciones.php" class="enlace-menu" aria-label="Bandeja de Justificaciones" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'justificaciones') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Buzón Justific.</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-item"><a href="../vistas/reportes.php" class="enlace-menu" aria-label="Reportes" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'reportes') ? 'color: var(--primary-color);' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg><span>Reportes</span></a>
        </li>

        <li class="menu-item">
            <a href="../vistas/perfil.php" class="enlace-menu" aria-label="Mi Perfil" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'perfil') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Mi Perfil</span>
            </a>
        </li>

        <?php if($_SESSION['id_rol'] == 1): ?>
        <li class="menu-item">
            <a href="../vistas/seguridad.php" class="enlace-menu" aria-label="Seguridad" style="<?php echo (isset($pagina_activa) && $pagina_activa == 'seguridad') ? 'color: var(--primary-color);' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Seguridad</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-item item-salir"> 
            <a href="../controladores/cerrar_sesion.php" class="enlace-menu" aria-label="Cerrar Sesión" style="color: #ef4444;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Cerrar Sesión</span>
            </a>
        </li>

    </ul>
</nav>