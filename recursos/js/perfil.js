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
});