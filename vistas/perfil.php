<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php';

$nombre     = $_SESSION['usuario'];
$rol        = $_SESSION['rol'];
$id_usuario = $_SESSION['id_usuario'];

$preguntas_seguridad = [
    1 => "¿Nombre de tu primera mascota?",
    2 => "¿Ciudad de nacimiento de tu padre?",
    3 => "¿Mejor amigo de la infancia?",
    4 => "¿Plato de comida favorito?",
    5 => "¿Marca de tu primer vehículo?",
    6 => "¿Nombre de tu escuela primaria?",
    7 => "¿Personaje histórico favorito?",
    8 => "¿Apellido de soltera de tu madre?"
];

try {
    $sql = "SELECT u.nombre_usuario, u.pregunta_1, u.pregunta_2, u.pregunta_3,
                   p.cedula, p.telefono, p.foto_perfil, p.nombres, p.apellidos
            FROM usuarios u
            INNER JOIN personal p ON u.id_usuario = p.id_usuario
            WHERE u.id_usuario = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
    $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar los datos.");
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - CEIS Julian Yánez</title>
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../recursos/css/guia_dinamica.css?v=<?php echo time(); ?>">

    <style>
        /* Efecto hover para el ojito */
        .icono-alternar:hover { color: #0f172a !important; }
        html[data-theme='dark'] .icono-alternar:hover { color: #f8fafc !important; }
    </style>
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            const temaGuardado = localStorage.getItem('tema_usuario_' + idUsr) || 'light';
            document.documentElement.setAttribute('data-theme', temaGuardado);
        })();
    </script>
</head>
<body>

    <?php $pagina_activa = 'perfil'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Mi Perfil'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido-perfil">
            <div class="perfil-panel">
                <form action="../controladores/ControladorEditarPerfil.php" method="POST" enctype="multipart/form-data" class="perfil-form" id="form-perfil" autocomplete="off" novalidate>

                    <aside class="perfil-col-foto">
                        <img
                            src="../recursos/img/perfiles/<?php echo htmlspecialchars($datos_usuario['foto_perfil']); ?>"
                            alt="Foto de perfil"
                            id="vista-previa-foto"
                            class="perfil-avatar">

                        <div class="contenedor-archivo">
                            <input type="file" name="nueva_foto" id="perfil_foto" accept=".jpg,.jpeg,.png" class="input-file-oculto">
                            <label for="perfil_foto" class="btn-subir-archivo perfil-btn-foto">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                                <span id="texto-foto">Cambiar foto...</span>
                            </label>
                        </div>
                    </aside>

                    <div class="perfil-col-campos">

                        <p class="perfil-seccion-titulo">Datos Personales</p>
                        <div class="grid-formulario">

                            <div class="grupo-input">
                                <label>Nombres</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <input type="text" name="nombres" id="nombres"
                                           value="<?php echo htmlspecialchars($datos_usuario['nombres']); ?>"
                                           required maxlength="100" autocomplete="off"
                                           oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g,'')">
                                </div>
                                <span class="perfil-msg-ajax" id="msg_nombres"></span>
                            </div>

                            <div class="grupo-input">
                                <label>Apellidos</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <input type="text" name="apellidos" id="apellidos"
                                           value="<?php echo htmlspecialchars($datos_usuario['apellidos']); ?>"
                                           required maxlength="100" autocomplete="off"
                                           oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g,'')">
                                </div>
                                <span class="perfil-msg-ajax" id="msg_apellidos"></span>
                            </div>

                            <div class="grupo-input">
                                <label>Cédula</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                                    <input type="text" name="cedula" id="cedula"
                                           value="<?php echo htmlspecialchars($datos_usuario['cedula']); ?>"
                                           maxlength="8" required autocomplete="off"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <span class="perfil-msg-ajax" id="msg_cedula"></span>
                            </div>

                            <div class="grupo-input">
                                <label>Teléfono</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <input type="text" name="telefono" id="telefono"
                                           value="<?php echo htmlspecialchars($datos_usuario['telefono']); ?>"
                                           maxlength="11" required autocomplete="off"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <span class="perfil-msg-ajax" id="msg_telefono"></span>
                            </div>

                        </div>

                        <p class="perfil-seccion-titulo perfil-seccion-sep">Datos de Acceso</p>
                        <div class="grid-formulario">

                            <div class="grupo-input">
                                <label>Nombre de Usuario</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zM12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                                    <input type="text" name="nombre_usuario" id="input_nombre_usuario"
                                           value="<?php echo htmlspecialchars($datos_usuario['nombre_usuario']); ?>"
                                           required maxlength="50" autocomplete="off"
                                           oninput="this.value=this.value.replace(/\s+/g,'').toLowerCase()">
                                </div>
                                <span id="mensaje_usuario_ajax" class="perfil-msg-ajax"></span>
                            </div>

                            <div class="grupo-input">
                                <label>Nueva Contraseña <small class="perfil-label-opcional">(opcional)</small></label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <input type="password" name="nueva_password" id="nueva_password" placeholder="Mín. 6 caracteres" autocomplete="new-password">
                                    <span class="icono-alternar" onclick="alternarVisibilidad('nueva_password', this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar oculto" viewBox="0 0 512 512"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                                    </span>
                                </div>
                                <div class="medidor-fuerza-contenedor">
                                    <div class="barra-fuerza" id="barra_fuerza"></div>
                                </div>
                                <span id="texto_fuerza" class="texto-fuerza perfil-texto-fuerza"></span>
                            </div>

                            <div class="grupo-input">
                                <label>Confirmar Contraseña</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <input type="password" name="confirmar_password" id="confirmar_password" placeholder="Repite la nueva contraseña" autocomplete="new-password">
                                    <span class="icono-alternar" onclick="alternarVisibilidad('confirmar_password', this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar oculto" viewBox="0 0 512 512"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                                    </span>
                                </div>
                                <span id="msg_confirmar_pass" class="perfil-msg-ajax"></span>
                            </div>

                        </div>

                        <p class="perfil-seccion-titulo perfil-seccion-sep perfil-seccion-advertencia">
                            Preguntas de Seguridad
                            <small class="perfil-label-opcional">(deja en blanco para conservarlas)</small>
                        </p>
                        <div class="grid-formulario">

                            <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="grupo-input">
                                <label>Pregunta <?php echo $i; ?></label>
                                <div class="input-con-icono">
                                    <select name="pregunta_<?php echo $i; ?>" id="perfil_pregunta_<?php echo $i; ?>" class="perfil-select">
                                        <?php foreach ($preguntas_seguridad as $k => $v): ?>
                                            <option value="<?php echo $k; ?>" <?php echo ($datos_usuario['pregunta_'.$i] == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="text" name="respuesta_<?php echo $i; ?>" id="perfil_respuesta_<?php echo $i; ?>" class="perfil-input-respuesta" placeholder="Nueva respuesta" autocomplete="off">
                                <span class="perfil-msg-ajax" id="msg_respuesta_<?php echo $i; ?>"></span>
                            </div>
                            <?php endfor; ?>

                        </div>

                        <div class="perfil-bloque-confirmar">
                            <p class="perfil-confirmar-titulo">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Ingresa tu contraseña actual para guardar los cambios
                            </p>
                            <div class="input-con-icono perfil-confirmar-input">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                <input type="password" name="password_actual" id="password_actual" placeholder="Tu contraseña actual" autocomplete="new-password" required>
                                <span class="icono-alternar" onclick="alternarVisibilidad('password_actual', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar oculto" viewBox="0 0 512 512"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                                </span>
                            </div>
                            <span id="msg_pass_actual" class="perfil-msg-ajax"></span>
                        </div>

                        <div class="botones-accion-formulario">
                            <button type="submit" class="btn-guardar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Guardar Cambios
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        window.PerfilConfig = {
            idUsuario: "<?php echo $_SESSION['id_usuario']; ?>",
            usuarioOriginal: "<?php echo htmlspecialchars($datos_usuario['nombre_usuario']); ?>",
            alerta: <?php
                if (isset($_SESSION['alerta_principal'])) {
                    echo json_encode([
                        'mostrar' => true,
                        'tipo'    => $_SESSION['alerta_principal']['tipo'] == 'success' ? 'success' : 'error',
                        'titulo'  => $_SESSION['alerta_principal']['tipo'] == 'success' ? '¡Éxito!' : '¡Error!',
                        'mensaje' => addslashes($_SESSION['alerta_principal']['mensaje'])
                    ]);
                    unset($_SESSION['alerta_principal']);
                } else {
                    echo json_encode(['mostrar' => false]);
                }
            ?>
        };
    </script>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script src="../recursos/js/perfil.js?v=<?php echo time(); ?>"></script>
    <script src="../recursos/js/guia_dinamica.js"></script>

</body>
</html>