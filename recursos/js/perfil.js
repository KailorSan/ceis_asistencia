document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.PerfilConfig;
    const html = document.documentElement;
    const claveTema = 'tema_usuario_' + config.idUsuario;

    // =======================================================
    // 2. TEMAS Y ALERTAS
    // =======================================================
    function parametrosTema() {
        const esDark = html.getAttribute('data-theme') === 'dark';
        return {
            background: esDark ? '#1e293b' : '#fff',
            color:      esDark ? '#fff'    : '#333',
        };
    }

    const btnCambiarTema = document.getElementById('btnCambiarTema');
    if (btnCambiarTema) {
        btnCambiarTema.addEventListener('click', function(e) {
            e.preventDefault();
            const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', nuevoTema);
            localStorage.setItem(claveTema, nuevoTema);
        });
    }

    if (config.alerta && config.alerta.mostrar) {
        Swal.fire({
            title: config.alerta.titulo,
            text: config.alerta.mensaje,
            icon: config.alerta.tipo,
            confirmButtonColor: config.alerta.tipo === 'success' ? '#10b981' : '#ef4444',
            ...parametrosTema()
        });
    }

    // =======================================================
    // 3. DECLARACIÓN DE FUNCIONES (HOISTING)
    // =======================================================

    // Función para el ojito de la contraseña
    function alternarVisibilidad(idInput, contenedorIcono) {
        const input = document.getElementById(idInput);
        if (!input) return;
        
        const iconoVer = contenedorIcono.querySelector('.icono-ver');
        const iconoOcultar = contenedorIcono.querySelector('.icono-ocultar');

        if (input.type === "password") {
            input.type = "text";
            input.classList.add('clave-visible');
            iconoVer.classList.add('oculto');
            iconoOcultar.classList.remove('oculto');
        } else {
            input.type = "password";
            input.classList.remove('clave-visible');
            iconoVer.classList.remove('oculto');
            iconoOcultar.classList.add('oculto');
        }
    }

    // Helpers para validación visual inline
    function setMsg(id, texto, tipo) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = texto;
        el.className = 'perfil-msg-ajax' + (tipo ? ' perfil-msg-' + tipo : '');
    }
    function clearMsg(id) { setMsg(id, '', ''); }

    // Previsualización de foto
    const inputFoto = document.getElementById('perfil_foto');
    if (inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            const archivo = e.target.files[0];
            const textoFoto = document.getElementById('texto-foto');
            if (textoFoto) textoFoto.textContent = archivo ? archivo.name : 'Cambiar foto...';
            
            if (archivo) {
                const reader = new FileReader();
                reader.onload = ev => { 
                    const vistaPrevia = document.getElementById('vista-previa-foto');
                    if (vistaPrevia) vistaPrevia.src = ev.target.result; 
                };
                reader.readAsDataURL(archivo);
            }
        });
    }

    // Validaciones en tiempo real
    const inputNombres = document.getElementById('nombres');
    if (inputNombres) {
        inputNombres.addEventListener('blur', function() {
            this.value.trim().length < 2
                ? setMsg('msg_nombres', '❌ Nombre inválido (mín. 2 letras).', 'error')
                : clearMsg('msg_nombres');
        });
    }

    const inputApellidos = document.getElementById('apellidos');
    if (inputApellidos) {
        inputApellidos.addEventListener('blur', function() {
            this.value.trim().length < 2
                ? setMsg('msg_apellidos', '❌ Apellido inválido (mín. 2 letras).', 'error')
                : clearMsg('msg_apellidos');
        });
    }

    const inputCedula = document.getElementById('cedula');
    if (inputCedula) {
        inputCedula.addEventListener('input', function() {
            this.value.length > 0 && this.value.length < 6
                ? setMsg('msg_cedula', '❌ Mín. 6 dígitos.', 'error')
                : clearMsg('msg_cedula');
        });
    }

    const inputTelefono = document.getElementById('telefono');
    if (inputTelefono) {
        inputTelefono.addEventListener('input', function() {
            this.value.length > 0 && this.value.length < 11
                ? setMsg('msg_telefono', '❌ Debe tener 11 dígitos.', 'error')
                : clearMsg('msg_telefono');
        });
    }

    const inputPassActual = document.getElementById('password_actual');
    if (inputPassActual) {
        inputPassActual.addEventListener('input', function() {
            if (this.value.length > 0) clearMsg('msg_pass_actual');
        });
    }

    // Lógica Preguntas de Seguridad
    const selectP1 = document.getElementById('perfil_pregunta_1');
    const selectP2 = document.getElementById('perfil_pregunta_2');
    const selectP3 = document.getElementById('perfil_pregunta_3');
    const grupoSelects = [selectP1, selectP2, selectP3].filter(Boolean);

    function actualizarSelects() {
        if (grupoSelects.length === 0) return;
        const valoresSeleccionados = grupoSelects.map(s => s.value).filter(v => v !== "");
        grupoSelects.forEach(selectActual => {
            Array.from(selectActual.options).forEach(opcion => {
                if (opcion.value === "") return;
                if (valoresSeleccionados.includes(opcion.value) && selectActual.value !== opcion.value) {
                    opcion.disabled = true;
                } else {
                    opcion.disabled = false;
                }
            });
        });
    }

    if (grupoSelects.length > 0) {
        grupoSelects.forEach(select => { select.addEventListener('change', actualizarSelects); });
        actualizarSelects(); 
    }

    for (let i = 1; i <= 3; i++) {
        const inputResp = document.getElementById('perfil_respuesta_' + i);
        if (inputResp) {
            inputResp.addEventListener('input', function() {
                const val = this.value.trim();
                if (val.length > 0 && val.length < 3) {
                    setMsg('msg_respuesta_' + i, '❌ Mínimo 3 caracteres.', 'error');
                    this.classList.add('input-error');
                } else {
                    clearMsg('msg_respuesta_' + i);
                    this.classList.remove('input-error');
                }
            });
        }
    }

    // Validación AJAX nombre de usuario
    const inputUsuario = document.getElementById('input_nombre_usuario');
    if (inputUsuario) {
        inputUsuario.addEventListener('keyup', function() {
            this.value = this.value.replace(/\s+/g, '').toLowerCase();
            const val = this.value;

            if (val === config.usuarioOriginal) {
                clearMsg('mensaje_usuario_ajax');
                inputUsuario.classList.remove('campo-error', 'input-exito');
                inputUsuario.dataset.disponible = 'true';
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
    }

    // Fuerza de contraseña y coincidencia
    const inputNuevaPass = document.getElementById('nueva_password');
    const inputConfirmar = document.getElementById('confirmar_password');
    const barraFuerza    = document.getElementById('barra_fuerza');
    const textoFuerza    = document.getElementById('texto_fuerza');

    function verificarCoincidencia() {
        if (!inputConfirmar || !inputNuevaPass) return;
        if (inputConfirmar.value === '') { clearMsg('msg_confirmar_pass'); return; }
        
        inputNuevaPass.value === inputConfirmar.value
            ? setMsg('msg_confirmar_pass', '✅ Las contraseñas coinciden.', 'exito')
            : setMsg('msg_confirmar_pass', '❌ Las contraseñas no coinciden.', 'error');
    }

    if (inputNuevaPass && barraFuerza && textoFuerza) {
        inputNuevaPass.addEventListener('input', function() {
            const val           = this.value;
            const tieneLetras   = /[a-zA-Z]/.test(val);
            const tieneNumeros  = /[0-9]/.test(val);
            const tieneSimbolos = /[^a-zA-Z0-9]/.test(val);

            barraFuerza.className = 'barra-fuerza';
            barraFuerza.style.width = '';
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
    }

    if (inputConfirmar) {
        inputConfirmar.addEventListener('input', verificarCoincidencia);
    }

    // Validación Final Formulario
    const formPerfil = document.getElementById('form-perfil');
    if (formPerfil) {
        formPerfil.addEventListener('submit', function(e) {
            let valido = true;

            const n = document.getElementById('nombres');
            if (n && n.value.trim().length < 2) { setMsg('msg_nombres', '❌ Nombre inválido.', 'error'); valido = false; }
            
            const a = document.getElementById('apellidos');
            if (a && a.value.trim().length < 2) { setMsg('msg_apellidos', '❌ Apellido inválido.', 'error'); valido = false; }
            
            const c = document.getElementById('cedula');
            if (c && c.value.length < 6) { setMsg('msg_cedula', '❌ Mín. 6 dígitos.', 'error'); valido = false; }
            
            const t = document.getElementById('telefono');
            if (t && t.value.length < 11) { setMsg('msg_telefono', '❌ Debe tener 11 dígitos.', 'error'); valido = false; }
            
            const pActual = document.getElementById('password_actual');
            if (pActual && pActual.value === '') { setMsg('msg_pass_actual', '❌ Requerido.', 'error'); valido = false; }

            const nuevaPass = inputNuevaPass ? inputNuevaPass.value : '';
            const confirmaPass = inputConfirmar ? inputConfirmar.value : '';

            if (nuevaPass !== '' || confirmaPass !== '') {
                if (nuevaPass.length < 6 || !/[A-Za-z]/.test(nuevaPass) || !/[0-9]/.test(nuevaPass)) {
                    e.preventDefault();
                    Swal.fire({ title: 'Contraseña débil', text: 'Debe tener al menos 6 caracteres, letras y números.', icon: 'warning', confirmButtonColor: '#cc0000', ...parametrosTema() });
                    return;
                }
                if (nuevaPass !== confirmaPass) {
                    e.preventDefault();
                    Swal.fire({ title: 'Error', text: 'Las contraseñas nuevas no coinciden.', icon: 'error', confirmButtonColor: '#cc0000', ...parametrosTema() });
                    return;
                }
            }

            if (inputUsuario && inputUsuario.value !== config.usuarioOriginal && inputUsuario.dataset.disponible === 'false') {
                e.preventDefault();
                Swal.fire({ title: 'Usuario no disponible', text: 'El nombre de usuario elegido ya está en uso.', icon: 'error', confirmButtonColor: '#cc0000', ...parametrosTema() });
                return;
            }

            for (let i = 1; i <= 3; i++) {
                const respuestaInput = document.getElementById('perfil_respuesta_' + i);
                if(respuestaInput) {
                    const respuestaVal = respuestaInput.value.trim();
                    if (respuestaVal.length > 0 && respuestaVal.length < 3) {
                        e.preventDefault();
                        Swal.fire({ 
                            title: 'Respuesta muy corta', 
                            text: `La respuesta de seguridad ${i} debe tener al menos 3 caracteres.`, 
                            icon: 'warning', 
                            confirmButtonColor: '#f59e0b', 
                            ...parametrosTema()
                        });
                        return;
                    }
                }
            }

            if (!valido) {
                e.preventDefault();
                Swal.fire({ title: 'Campos inválidos', text: 'Revisa los campos marcados antes de guardar.', icon: 'warning', confirmButtonColor: '#f59e0b', ...parametrosTema() });
            }
        });
    }

    // =======================================================
    // 4. EXPORTACIÓN AL SCOPE GLOBAL
    // =======================================================
    window.alternarVisibilidad = alternarVisibilidad;

    // =======================================================
    // 5. INICIALIZACIÓN DE LA GUÍA DINÁMICA (MI PERFIL)
    // =======================================================

    let diccionarioPerfil = [
        
        { selector: '.perfil-avatar', titulo: 'Fotografía Actual', texto: 'Este es tu avatar del sistema. Se recomiendan imágenes cuadradas para mantener una estética uniforme.' },
        { selector: '.perfil-btn-foto', titulo: 'Cambiar Fotografía', texto: 'Haz clic aquí para seleccionar y subir una nueva imagen desde tu dispositivo (JPG, PNG o WEBP).' },
        { selector: '#nombres', titulo: 'Nombres', texto: 'Tus nombres de pila. Solo se permiten letras y espacios para mantener la integridad de los reportes.' },
        { selector: '#apellidos', titulo: 'Apellidos', texto: 'Tus apellidos. Forman parte de tu identificación legal dentro de la plataforma.' },
        { selector: '#cedula', titulo: 'Cédula de Identidad', texto: 'Tu documento de identidad. Es estrictamente numérico y debe tener un mínimo de 6 dígitos.' },
        { selector: '#telefono', titulo: 'Número Telefónico', texto: 'Información de contacto actualizada. Debe tener 11 dígitos sin guiones ni caracteres especiales.' },
        { selector: '#input_nombre_usuario', titulo: 'Usuario de Acceso', texto: 'El alias que usas para iniciar sesión. El sistema comprobará en tiempo real si el nuevo nombre está disponible.' },
        { selector: '#nueva_password', titulo: 'Nueva Contraseña', texto: 'Si deseas cambiar tu clave, escríbela aquí. Observa la barra de colores inferior para asegurar un nivel "Excelente".' },
        { selector: '#confirmar_password', titulo: 'Confirmar Contraseña', texto: 'Vuelve a escribir la nueva contraseña. El sistema verificará que ambas coincidan exactamente antes de guardarla.' },
        { selector: '#perfil_pregunta_1', titulo: 'Pregunta de Seguridad 1', texto: 'Elige una pregunta y establece su respuesta. Es fundamental por si algún día necesitas recuperar tu cuenta.' },
        { selector: '#perfil_pregunta_2', titulo: 'Pregunta de Seguridad 2', texto: 'Segunda validación de seguridad. Usa respuestas que sean fáciles de recordar para ti pero difíciles de adivinar para otros.' },
        { selector: '#perfil_pregunta_3', titulo: 'Pregunta de Seguridad 3', texto: 'Última pregunta de recuperación. Si las dejas en blanco, el sistema conservará las que ya tenías.' },
        { selector: '.perfil-bloque-confirmar', titulo: 'Validación Obligatoria', texto: '⚠️ ¡IMPORTANTE! Para procesar cualquier cambio que hayas hecho arriba, DEBES ingresar tu contraseña actual aquí.' },
        { selector: '.btn-guardar', titulo: 'Guardar Cambios', texto: 'Al hacer clic, el sistema revisará todos los campos. Si todo está en orden y tu clave actual es correcta, tu perfil se actualizará.' }
    ];

    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppPerfil = new window.GuiaDinamica(diccionarioPerfil);
    }

    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppPerfil = new window.GuiaDinamica(diccionarioPerfil);
    }

    // 1. Sección de foto de perfil
    if (document.querySelector('.perfil-col-foto')) {
        diccionarioPerfil.push({ 
            selector: '.perfil-col-foto', 
            titulo: 'Avatar del Sistema', 
            texto: 'Haz clic en "Cambiar foto..." para subir una nueva imagen. El sistema acepta formatos JPG, PNG y WEBP.' 
        });
    }

    // 2. Nombre de Usuario
    if (document.querySelector('#input_nombre_usuario')) {
        diccionarioPerfil.push({ 
            selector: '#input_nombre_usuario', 
            titulo: 'Nombre de Usuario', 
            texto: 'Es tu identificador para iniciar sesión. Si decides cambiarlo, el sistema verificará en tiempo real si el nuevo nombre está disponible.' 
        });
    }

    // 3. Nueva Contraseña
    if (document.querySelector('#nueva_password')) {
        diccionarioPerfil.push({ 
            selector: '#nueva_password', 
            titulo: 'Cambio de Contraseña', 
            texto: 'Déjalo en blanco si no quieres cambiar tu clave. Si escribes una nueva, el medidor inferior te indicará su nivel de seguridad.' 
        });
    }

    // 4. Preguntas de Seguridad
    if (document.querySelector('#perfil_pregunta_1')) {
        diccionarioPerfil.push({ 
            selector: '#perfil_pregunta_1', 
            titulo: 'Preguntas de Respaldo', 
            texto: 'Vitales para recuperar tu cuenta si olvidas tu contraseña. Puedes dejarlas en blanco para conservar las que ya tenías guardadas.' 
        });
    }

    // 5. Bloque de Confirmación (El más importante)
    if (document.querySelector('.perfil-bloque-confirmar')) {
        diccionarioPerfil.push({ 
            selector: '.perfil-bloque-confirmar', 
            titulo: 'Validación de Identidad', 
            texto: 'Como medida de máxima seguridad, el sistema NO aplicará ningún cambio en tu perfil a menos que ingreses tu contraseña actual aquí.' 
        });
    }

    // Instanciar el motor de la guía
    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppPerfil = new window.GuiaDinamica(diccionarioPerfil);
    }
    
});