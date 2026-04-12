<?php
session_start();

if (!isset($_SESSION['paso_recuperacion'])) {
    $_SESSION['paso_recuperacion'] = 1;
}
$paso = $_SESSION['paso_recuperacion'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acceso</title>
    <link rel="stylesheet" href="../recursos/css/login.css?v=<?php echo time(); ?>">
</head>
<body class="modo-recuperacion">

<div class="tarjeta-recuperacion">
    <img src="../recursos/img/logo_ceis.jpg" alt="Logo CEIS" class="logo-login-flotante">
    
    <?php if ($paso == 1): ?>
        <h1>Recuperar Acceso</h1>
        <p>Ingresa tu nombre de usuario para localizar tus preguntas de seguridad.</p>
        
        <form action="../controladores/ControladorRecuperacion.php" method="POST" id="formularioBuscar" autocomplete="off">
            <input type="hidden" name="accion" value="buscar_usuario">
            
            <input type="text" name="nombre_usuario" id="input_usuario" class="input-gris" 
                   placeholder="Usuario (Ej: admin)" autocomplete="off">
            
            <button type="submit" class="boton-completo">Buscar Usuario</button>
            <a href="login.php" class="enlace-cancelar">Cancelar y volver</a>
        </form>
    
    <?php elseif ($paso == 2): ?>
        <h1>Seguridad</h1>
        <p style="margin-block-end: 15px;">Responde las preguntas para verificar tu identidad.</p>
        
        <form action="../controladores/ControladorRecuperacion.php" method="POST" id="formularioRespuestas" autocomplete="off">
            <input type="hidden" name="accion" value="verificar_respuestas">
            
            <?php $preguntas = $_SESSION['recup_temp']['preguntas_texto']; ?>

            <label class="etiqueta-pregunta">1. <?php echo $preguntas[1]; ?></label>
            <input type="text" name="resp_1" id="respuesta_1" class="input-gris" placeholder="Tu respuesta..." autocomplete="off">

            <label class="etiqueta-pregunta">2. <?php echo $preguntas[2]; ?></label>
            <input type="text" name="resp_2" id="respuesta_2" class="input-gris" placeholder="Tu respuesta..." autocomplete="off">

            <label class="etiqueta-pregunta">3. <?php echo $preguntas[3]; ?></label>
            <input type="text" name="resp_3" id="respuesta_3" class="input-gris" placeholder="Tu respuesta..." autocomplete="off">

            <button type="submit" class="boton-completo">Verificar Respuestas</button>
            <a href="../controladores/destruir_recuperacion.php" class="enlace-cancelar" style="color:#cc0000;">Cancelar operación</a>
        </form>

    <?php elseif ($paso == 3): ?>
        
        <span class="icono-exito">&#10003;</span>
        
        <h1>Identidad Verificada</h1>
        <p>Crea una nueva contraseña segura para tu cuenta.</p>
        
        <form action="../controladores/ControladorRecuperacion.php" method="POST" id="formularioClave" autocomplete="off">
            <input type="hidden" name="accion" value="cambiar_clave">
            
            <div class="contenedor-clave">
                <input type="password" name="pass_1" id="nueva_clave_1" placeholder="Nueva Contraseña (Mín. 6 chars)" autocomplete="new-password">
                <span class="icono-alternar" onclick="alternarVisibilidad('nueva_clave_1', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar" viewBox="0 0 512 512" style="display:none;"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                </span>
            </div>

            <div class="contenedor-clave">
                <input type="password" name="pass_2" id="nueva_clave_2" placeholder="Repetir Contraseña" autocomplete="new-password">
                <span class="icono-alternar" onclick="alternarVisibilidad('nueva_clave_2', this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar" viewBox="0 0 512 512" style="display:none;"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                </span>
            </div>

            <button type="submit" class="boton-completo">Actualizar Contraseña</button>
        </form>
    <?php endif; ?>

</div>

<script src="../recursos/js/sweetalert2.all.min.js"></script>
<script>
    // === FUNCIONES DE ERROR VISUAL ===
    function mostrarError(input) {
        input.classList.add('campo-error', 'animacion-vibrar');
        setTimeout(() => {
            input.classList.remove('animacion-vibrar');
        }, 500);
    }
    function limpiarError(input) { input.classList.remove('campo-error'); }

    // === VALIDACIÓN: PASO 1 (BUSCAR USUARIO) ===
    const formularioBuscar = document.getElementById('formularioBuscar');
    if (formularioBuscar) {
        formularioBuscar.addEventListener('submit', function(e) {
            const input = document.getElementById('input_usuario');
            limpiarError(input);
            
            if (input.value.trim() === '') {
                e.preventDefault();
                mostrarError(input);
            }
        });
    }

    // === VALIDACIÓN: PASO 2 (RESPUESTAS) ===
    const formularioRespuestas = document.getElementById('formularioRespuestas');
    if (formularioRespuestas) {
        formularioRespuestas.addEventListener('submit', function(e) {
            let esValido = true;
            const r1 = document.getElementById('respuesta_1');
            const r2 = document.getElementById('respuesta_2');
            const r3 = document.getElementById('respuesta_3');
            
            [r1, r2, r3].forEach(input => {
                limpiarError(input);
                if (input.value.trim() === '') {
                    mostrarError(input);
                    esValido = false;
                }
            });

            if (!esValido) e.preventDefault();
        });
    }

    // === VALIDACIÓN: PASO 3 (NUEVA CLAVE) ===
    const formularioClave = document.getElementById('formularioClave');
    if (formularioClave) {
        formularioClave.addEventListener('submit', function(e) {
            let esValido = true;
            const c1 = document.getElementById('nueva_clave_1');
            const c2 = document.getElementById('nueva_clave_2');
            
            [c1, c2].forEach(limpiarError);

            // 1. Vacíos
            if (c1.value.trim() === '') { mostrarError(c1); esValido = false; }
            if (c2.value.trim() === '') { mostrarError(c2); esValido = false; }

            if (!esValido) { e.preventDefault(); return; }

            // 2. Longitud
            if (c1.value.length < 6) {
                e.preventDefault();
                Swal.fire({ 
                    title: 'Contraseña Corta', 
                    text: 'Mínimo 6 caracteres.', 
                    icon: 'warning', confirmButtonColor: '#cc0000', heightAuto: false 
                });
                mostrarError(c1);
                return;
            }

            // 3. Coincidencia
            if (c1.value !== c2.value) {
                e.preventDefault();
                Swal.fire({ 
                    title: 'Error', 
                    text: 'Las contraseñas no coinciden.', 
                    icon: 'warning', confirmButtonColor: '#cc0000', heightAuto: false 
                });
                mostrarError(c2);
            }
        });
    }

    // === FUNCIÓN PARA MOSTRAR/OCULTAR CONTRASEÑA ===
    function alternarVisibilidad(idInput, contenedorIcono) {
        const input = document.getElementById(idInput);
        const iconoVer = contenedorIcono.querySelector('.icono-ver');
        const iconoOcultar = contenedorIcono.querySelector('.icono-ocultar');

        if (input.type === "password") {
            input.type = "text";
            iconoVer.style.display = 'none';
            iconoOcultar.style.display = 'block'; 
        } else {
            input.type = "password";
            iconoVer.style.display = 'block';
            iconoOcultar.style.display = 'none';
        }
    }
</script>

<?php if(isset($_SESSION['error_recup'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'Atención',
            text: '<?php echo addslashes($_SESSION['error_recup']); ?>',
            icon: 'error',
            confirmButtonColor: '#003366',
            heightAuto: false,
            width: '350px' /* Corregido: se usa width en lugar de inline-size */
        });
    });
</script>
<?php unset($_SESSION['error_recup']); endif; ?>

</body>
</html>