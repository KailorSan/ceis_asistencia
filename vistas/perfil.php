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
                <form action="../controladores/ControladorEditarPerfil.php" method="POST" enctype="multipart/form-data" class="perfil-form" id="form-perfil" novalidate>

                    <!-- ══════════════════════════════════
                         COLUMNA IZQUIERDA — foto
                    ═══════════════════════════════════════ -->
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

                    <!-- ══════════════════════════════════
                         COLUMNA DERECHA — campos
                    ═══════════════════════════════════════ -->
                    <div class="perfil-col-campos">

                        <!-- ── Datos Personales ── -->
                        <p class="perfil-seccion-titulo">Datos Personales</p>
                        <div class="grid-formulario">

                            <div class="grupo-input">
                                <label>Nombres</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <input type="text" name="nombres" id="nombres"
                                           value="<?php echo htmlspecialchars($datos_usuario['nombres']); ?>"
                                           required maxlength="100"
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
                                           required maxlength="100"
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
                                           maxlength="8" required
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
                                           maxlength="11" required
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <span class="perfil-msg-ajax" id="msg_telefono"></span>
                            </div>

                        </div>

                        <!-- ── Datos de Acceso ── -->
                        <p class="perfil-seccion-titulo perfil-seccion-sep">Datos de Acceso</p>
                        <div class="grid-formulario">

                            <div class="grupo-input">
                                <label>Nombre de Usuario</label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zM12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                                    <input type="text" name="nombre_usuario" id="input_nombre_usuario"
                                           value="<?php echo htmlspecialchars($datos_usuario['nombre_usuario']); ?>"
                                           required maxlength="50"
                                           oninput="this.value=this.value.replace(/\s+/g,'').toLowerCase()">
                                </div>
                                <span id="mensaje_usuario_ajax" class="perfil-msg-ajax"></span>
                            </div>

                            <div class="grupo-input">
                                <label>Nueva Contraseña <small class="perfil-label-opcional">(opcional)</small></label>
                                <div class="input-con-icono">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <input type="password" name="nueva_password" id="nueva_password" placeholder="Mín. 6 caracteres" autocomplete="new-password">
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
                                </div>
                                <span id="msg_confirmar_pass" class="perfil-msg-ajax"></span>
                            </div>

                        </div>

                        <!-- ── Preguntas de Seguridad ── -->
                        <p class="perfil-seccion-titulo perfil-seccion-sep perfil-seccion-advertencia">
                            Preguntas de Seguridad
                            <small class="perfil-label-opcional">(deja en blanco para conservarlas)</small>
                        </p>
                        <div class="grid-formulario">

                            <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="grupo-input">
                                <label>Pregunta <?php echo $i; ?></label>
                                <div class="input-con-icono">
                                    <select name="pregunta_<?php echo $i; ?>" class="perfil-select">
                                        <?php foreach ($preguntas_seguridad as $k => $v): ?>
                                            <option value="<?php echo $k; ?>" <?php echo ($datos_usuario['pregunta_'.$i] == $k) ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="text" name="respuesta_<?php echo $i; ?>" class="perfil-input-respuesta" placeholder="Nueva respuesta">
                            </div>
                            <?php endfor; ?>

                        </div>

                        <!-- ── Confirmar identidad ── -->
                        <div class="perfil-bloque-confirmar">
                            <p class="perfil-confirmar-titulo">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Ingresa tu contraseña actual para guardar los cambios
                            </p>
                            <div class="input-con-icono perfil-confirmar-input">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                <input type="password" name="password_actual" id="password_actual" placeholder="Tu contraseña actual" required>
                            </div>
                            <span id="msg_pass_actual" class="perfil-msg-ajax"></span>
                        </div>

                        <!-- ── Botón guardar ── -->
                        <div class="botones-accion-formulario">
                            <button type="submit" class="btn-guardar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Guardar Cambios
                            </button>
                        </div>

                    </div><!-- /perfil-col-campos -->

                </form>
            </div><!-- /perfil-panel -->
        </main>
    </div>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
    // ── Previsualización de foto ──────────────────────────────────────────────
    document.getElementById('perfil_foto').addEventListener('change', function(e) {
        const archivo = e.target.files[0];
        document.getElementById('texto-foto').textContent = archivo ? archivo.name : 'Cambiar foto...';
        if (archivo) {
            const reader = new FileReader();
            reader.onload = ev => { document.getElementById('vista-previa-foto').src = ev.target.result; };
            reader.readAsDataURL(archivo);
        }
    });

    // ── Helper: muestra / limpia mensajes inline ──────────────────────────────
    function setMsg(id, texto, tipo) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = texto;
        el.className = 'perfil-msg-ajax' + (tipo ? ' perfil-msg-' + tipo : '');
    }
    function clearMsg(id) { setMsg(id, '', ''); }

    // ── Validación en tiempo real ─────────────────────────────────────────────
    document.getElementById('nombres').addEventListener('blur', function() {
        this.value.trim().length < 2
            ? setMsg('msg_nombres', '❌ Nombre inválido (mín. 2 letras).', 'error')
            : clearMsg('msg_nombres');
    });

    document.getElementById('apellidos').addEventListener('blur', function() {
        this.value.trim().length < 2
            ? setMsg('msg_apellidos', '❌ Apellido inválido (mín. 2 letras).', 'error')
            : clearMsg('msg_apellidos');
    });

    document.getElementById('cedula').addEventListener('input', function() {
        this.value.length > 0 && this.value.length < 6
            ? setMsg('msg_cedula', '❌ Mín. 6 dígitos.', 'error')
            : clearMsg('msg_cedula');
    });

    document.getElementById('telefono').addEventListener('input', function() {
        this.value.length > 0 && this.value.length < 11
            ? setMsg('msg_telefono', '❌ Debe tener 11 dígitos.', 'error')
            : clearMsg('msg_telefono');
    });

    document.getElementById('password_actual').addEventListener('input', function() {
        if (this.value.length > 0) clearMsg('msg_pass_actual');
    });

    // ── Validación AJAX del nombre de usuario ─────────────────────────────────
    const inputUsuario    = document.getElementById('input_nombre_usuario');
    const usuarioOriginal = inputUsuario.value;

    inputUsuario.addEventListener('keyup', function() {
        this.value = this.value.replace(/\s+/g, '').toLowerCase();
        const val = this.value;

        if (val === usuarioOriginal) {
            clearMsg('mensaje_usuario_ajax');
            inputUsuario.classList.remove('campo-error', 'input-exito');
            return;
        }
        if (val.length >= 3) {
            fetch('../controladores/validar_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'usuario=' + encodeURIComponent(val)
            })
            .then(r => r.json())
            .then(data => {
                inputUsuario.classList.remove('campo-error', 'input-exito');
                if (data.existe) {
                    setMsg('mensaje_usuario_ajax', '❌ Este usuario ya está en uso.', 'error');
                    inputUsuario.classList.add('campo-error');
                    inputUsuario.dataset.disponible = 'false';
                } else {
                    setMsg('mensaje_usuario_ajax', '✅ Usuario disponible.', 'exito');
                    inputUsuario.classList.add('input-exito');
                    inputUsuario.dataset.disponible = 'true';
                }
            })
            .catch(err => console.error(err));
        } else {
            clearMsg('mensaje_usuario_ajax');
            inputUsuario.classList.remove('campo-error', 'input-exito');
            inputUsuario.dataset.disponible = 'false';
        }
    });

    // ── Medidor de fuerza de contraseña ──────────────────────────────────────
    const inputNuevaPass = document.getElementById('nueva_password');
    const barraFuerza    = document.getElementById('barra_fuerza');
    const textoFuerza    = document.getElementById('texto_fuerza');

    inputNuevaPass.addEventListener('input', function() {
        const val           = this.value;
        const tieneLetras   = /[a-zA-Z]/.test(val);
        const tieneNumeros  = /[0-9]/.test(val);
        const tieneSimbolos = /[^a-zA-Z0-9]/.test(val);

        barraFuerza.className = 'barra-fuerza';
        barraFuerza.style.width = '';
        barraFuerza.style.inlineSize = '';
        textoFuerza.className = 'texto-fuerza perfil-texto-fuerza';

        if (val.length === 0) {
            textoFuerza.textContent = '';
        } else if (val.length < 6) {
            barraFuerza.classList.add('fuerza-mala');
            textoFuerza.textContent = 'Seguridad: Mala (mín. 6 caracteres)';
            textoFuerza.classList.add('perfil-fuerza-mala');
        } else if (tieneLetras && tieneNumeros && tieneSimbolos) {
            barraFuerza.classList.add('fuerza-excelente');
            textoFuerza.textContent = 'Seguridad: Excelente';
            textoFuerza.classList.add('perfil-fuerza-excelente');
        } else if ((tieneLetras && tieneNumeros) || (tieneLetras && tieneSimbolos) || (tieneNumeros && tieneSimbolos)) {
            barraFuerza.classList.add('fuerza-buena');
            textoFuerza.textContent = 'Seguridad: Buena';
            textoFuerza.classList.add('perfil-fuerza-buena');
        } else {
            barraFuerza.classList.add('fuerza-mala');
            textoFuerza.textContent = 'Seguridad: Mala (combina letras y números)';
            textoFuerza.classList.add('perfil-fuerza-mala');
        }
        verificarCoincidencia();
    });

    // ── Verificación de coincidencia de contraseñas ───────────────────────────
    const inputConfirmar = document.getElementById('confirmar_password');

    function verificarCoincidencia() {
        if (inputConfirmar.value === '') { clearMsg('msg_confirmar_pass'); return; }
        inputNuevaPass.value === inputConfirmar.value
            ? setMsg('msg_confirmar_pass', '✅ Las contraseñas coinciden.', 'exito')
            : setMsg('msg_confirmar_pass', '❌ Las contraseñas no coinciden.', 'error');
    }
    inputConfirmar.addEventListener('input', verificarCoincidencia);

    // ── Validación completa antes de enviar ───────────────────────────────────
    document.getElementById('form-perfil').addEventListener('submit', function(e) {
        let valido = true;

        if (document.getElementById('nombres').value.trim().length < 2) {
            setMsg('msg_nombres', '❌ Nombre inválido (mín. 2 letras).', 'error');
            valido = false;
        }
        if (document.getElementById('apellidos').value.trim().length < 2) {
            setMsg('msg_apellidos', '❌ Apellido inválido (mín. 2 letras).', 'error');
            valido = false;
        }
        if (document.getElementById('cedula').value.length < 6) {
            setMsg('msg_cedula', '❌ Mín. 6 dígitos.', 'error');
            valido = false;
        }
        if (document.getElementById('telefono').value.length < 11) {
            setMsg('msg_telefono', '❌ Debe tener 11 dígitos.', 'error');
            valido = false;
        }
        if (document.getElementById('password_actual').value === '') {
            setMsg('msg_pass_actual', '❌ Debes confirmar tu contraseña actual.', 'error');
            valido = false;
        }

        const nuevaPass    = inputNuevaPass.value;
        const confirmaPass = inputConfirmar.value;

        if (nuevaPass !== '' || confirmaPass !== '') {
            if (nuevaPass.length < 6 || !/[A-Za-z]/.test(nuevaPass) || !/[0-9]/.test(nuevaPass)) {
                e.preventDefault();
                Swal.fire({ title: 'Contraseña débil', text: 'Debe tener al menos 6 caracteres, letras y números.', icon: 'warning', confirmButtonColor: '#cc0000', heightAuto: false });
                return;
            }
            if (nuevaPass !== confirmaPass) {
                e.preventDefault();
                Swal.fire({ title: 'Error', text: 'Las contraseñas nuevas no coinciden.', icon: 'error', confirmButtonColor: '#cc0000', heightAuto: false });
                return;
            }
        }

        if (inputUsuario.value !== usuarioOriginal && inputUsuario.dataset.disponible === 'false') {
            e.preventDefault();
            Swal.fire({ title: 'Usuario no disponible', text: 'El nombre de usuario elegido ya está en uso.', icon: 'error', confirmButtonColor: '#cc0000', heightAuto: false });
            return;
        }

        if (!valido) {
            e.preventDefault();
            Swal.fire({ title: 'Campos inválidos', text: 'Revisa los campos marcados antes de guardar.', icon: 'warning', confirmButtonColor: '#f59e0b', heightAuto: false });
        }
    });

    // ── Tema ──────────────────────────────────────────────────────────────────
    const btnCambiarTema = document.getElementById('btnCambiarTema');
    const html = document.documentElement;
    const claveTema = 'tema_usuario_<?php echo $_SESSION['id_usuario']; ?>';

    if (btnCambiarTema) {
        btnCambiarTema.addEventListener('click', function(e) {
            e.preventDefault();
            const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', nuevoTema);
            localStorage.setItem(claveTema, nuevoTema);
        });
    }
    </script>

    <?php if (isset($_SESSION['alerta_principal'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_principal']['tipo'] == 'success' ? '¡Éxito!' : '¡Error!'; ?>',
                text: '<?php echo addslashes($_SESSION['alerta_principal']['mensaje']); ?>',
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