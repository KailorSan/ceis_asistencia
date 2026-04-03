<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
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

// Obtener datos actuales del usuario
try {
    $sql = "SELECT u.nombre_usuario, u.pregunta_1, u.pregunta_2, u.pregunta_3, 
                   p.telefono, p.foto_perfil, p.nombres, p.apellidos 
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
        
        <main class="contenido">
            <div class="tarjeta-formulario-config">
                <h2 style="margin-block-end: 20px; color: var(--primary-color); text-align: center;">Actualizar Datos de la Cuenta</h2>
                
                <form action="../controladores/ControladorEditarPerfil.php" method="POST" enctype="multipart/form-data">
                    
                    <div style="display: flex; flex-direction: column; align-items: center; margin-block-end: 20px;">
                        <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($datos_usuario['foto_perfil']); ?>" alt="Foto Perfil" style="inline-size: 100px; block-size: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color); margin-block-end: 10px;">
                        
                        <div class="contenedor-archivo" style="inline-size: 100%; max-inline-size: 300px;">
                            <input type="file" name="nueva_foto" id="perfil_foto" accept=".jpg, .jpeg, .png" class="input-file-oculto">
                            <label for="perfil_foto" class="btn-subir-archivo" style="justify-content: center; padding: 8px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /></svg>
                                <span id="texto-foto">Cambiar Foto...</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid-formulario">
                        <div class="grupo-input">
                            <label>Nombre de Usuario</label>
                            <div class="input-con-icono">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <input type="text" name="nombre_usuario" value="<?php echo htmlspecialchars($datos_usuario['nombre_usuario']); ?>" required>
                            </div>
                        </div>

                        <div class="grupo-input">
                            <label>Teléfono</label>
                            <div class="input-con-icono">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($datos_usuario['telefono']); ?>" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>
                        </div>

                        <div class="grupo-input campo-completo" style="margin-block-start: 15px; padding-block-start: 15px; border-block-start: 1px dashed #ccc;">
                            <label style="color: #f59e0b;">Actualizar Preguntas de Seguridad (Opcional)</label>
                            <small>Si no deseas cambiarlas, deja las respuestas en blanco.</small>
                        </div>

                        <?php for ($i = 1; $i <= 3; $i++): ?>
                        <div class="grupo-input">
                            <label>Pregunta <?php echo $i; ?></label>
                            <div class="input-con-icono">
                                <select name="pregunta_<?php echo $i; ?>" style="inline-size: 100%; padding: 12px 15px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat', sans-serif; outline: none;">
                                    <?php foreach ($preguntas_seguridad as $k => $v): ?>
                                        <option value="<?php echo $k; ?>" <?php echo ($datos_usuario['pregunta_'.$i] == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="text" name="respuesta_<?php echo $i; ?>" placeholder="Nueva respuesta (dejar en blanco para conservar actual)" style="padding: 10px; margin-block-start: 5px; border-radius: 8px; border: 1px solid var(--bg-light); font-family: 'Montserrat', sans-serif;">
                        </div>
                        <?php endfor; ?>

                        <div class="grupo-input campo-completo" style="margin-block-start: 20px; background: rgba(239, 68, 68, 0.05); padding: 20px; border-radius: 10px; border: 1px solid #ef4444;">
                            <label style="color: #ef4444; font-size: 1.1rem; text-align: center;">Seguridad: Confirma tu identidad</label>
                            <small style="text-align: center; display: block; margin-block-end: 10px;">Para guardar cualquier cambio, debes ingresar tu contraseña actual.</small>
                            <div class="input-con-icono" style="max-inline-size: 400px; margin: 0 auto;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                <input type="password" name="password_actual" placeholder="Tu Contraseña Actual" required>
                            </div>
                        </div>
                    </div>

                    <div class="botones-accion-formulario">
                        <button type="submit" class="btn-guardar"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg> Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
        const perfilFotoInput = document.getElementById('perfil_foto');
        if (perfilFotoInput) {
            perfilFotoInput.addEventListener('change', function(e) {
                var nombreArchivo = e.target.files[0] ? e.target.files[0].name : 'Cambiar Foto...';
                document.getElementById('texto-foto').textContent = nombreArchivo;
            });
        }

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
    </script>

    <?php if(isset($_SESSION['alerta_principal'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '¡Éxito!' : '¡Error!'; ?>',
                text: '<?php echo $_SESSION['alerta_principal']['mensaje']; ?>',
                icon: '<?php echo $_SESSION['alerta_principal']['tipo']; ?>',
                confirmButtonColor: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '#10b981' : '#ef4444'; ?>',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });
        </script>
        <?php unset($_SESSION['alerta_principal']); ?>
    <?php endif; ?>
</body>
</html>