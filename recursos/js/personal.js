/**
 * ============================================================================
 * ARCHIVO: personal.js
 * MÓDULO: Gestión de Personal (Directivos y Administradores)
 * DESCRIPCIÓN: Maneja la lógica de interfaz, filtros de búsqueda, 
 * validaciones de formularios y operaciones críticas (como la eliminación 
 * segura de usuarios) en la vista de personal.
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.PersonalConfig; 
    const html = document.documentElement;

    const inputBuscadorUniv = document.getElementById('buscador-universal');
    let cargoActivoUniv = 'todos'; 
    const ITEMS_POR_CARGA = 8;
    let limiteActual = ITEMS_POR_CARGA;

    // =======================================================
    // 2. MANEJO DE TEMAS (DARK/LIGHT) Y ALERTAS GLOBALES
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
            localStorage.setItem('tema_usuario_' + config.idUsuario, nuevoTema);
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
    // 3. FUNCIONES PRINCIPALES DE INTERFAZ (UI)
    // =======================================================

    function aplicarFiltroUniversal(idCargo = null, botonSeleccionado = null, reiniciarPaginacion = true) {
        if (reiniciarPaginacion) limiteActual = ITEMS_POR_CARGA;
        
        if (idCargo !== null) {
            cargoActivoUniv = idCargo;
            document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
            if (botonSeleccionado) botonSeleccionado.classList.add('activo');
        }

        const textoBusqueda = inputBuscadorUniv ? inputBuscadorUniv.value.toLowerCase().trim() : '';
        let coincidentes = 0;

        document.querySelectorAll('.item-filtrable').forEach(item => {
            const coincideCargo  = (cargoActivoUniv === 'todos') || (item.getAttribute('data-cargo') == cargoActivoUniv);
            const nombre = (item.querySelector('.nombre-empleado')?.innerText || '').toLowerCase();
            const cargo  = (item.querySelector('.cargo-empleado')?.innerText  || '').toLowerCase();
            const coincideTexto  = nombre.includes(textoBusqueda) || cargo.includes(textoBusqueda);
            
            if (coincideCargo && coincideTexto) {
                item.classList.remove('oculto-por-filtro');
                coincidentes++;
                
                if (coincidentes > limiteActual) {
                    item.classList.add('oculto-por-paginacion');
                    item.classList.remove('animacion-aparecer');
                } else {
                    item.classList.remove('oculto-por-paginacion');
                    item.classList.add('animacion-aparecer');
                }
            } else {
                item.classList.add('oculto-por-filtro');
                item.classList.remove('oculto-por-paginacion', 'animacion-aparecer');
            }
        });

        const btnVerMas = document.getElementById('contenedor-ver-mas-personal');
        if (btnVerMas) btnVerMas.style.display = coincidentes > limiteActual ? 'block' : 'none';
    }

    function abrirModalHorario(btn) {
        document.getElementById('modal_h_id_personal').value = btn.dataset.id;
        document.getElementById('modal_h_nombre').textContent = "Horario: " + btn.dataset.nombre.split(' ')[0];
        document.getElementById('modal_h_entrada').value = btn.dataset.entrada;
        document.getElementById('modal_h_salida').value  = btn.dataset.salida;
        document.getElementById('modalOverlay').classList.add('activo');
        document.getElementById('modalHorario').classList.add('activo');
    }

    function abrirModalEditar(btn) {
        const esElMismo = (btn.dataset.idusuario == config.idUsuario);

        document.getElementById('modal_e_id_usuario').value  = btn.dataset.idusuario;
        document.getElementById('modal_e_id_personal').value = btn.dataset.idpersonal;
        document.getElementById('modal_e_nombres').value     = btn.dataset.nombres;
        document.getElementById('modal_e_apellidos').value   = btn.dataset.apellidos;
        document.getElementById('modal_e_cedula').value      = btn.dataset.cedula;
        document.getElementById('modal_e_telefono').value    = btn.dataset.telefono;
        document.getElementById('modal_e_usuario').value     = btn.dataset.usuario;
        document.getElementById('modal_e_cargo').value       = btn.dataset.cargo;
        document.getElementById('modal_e_estado').value      = btn.dataset.estado;
        document.getElementById('modal_e_rol').value         = btn.dataset.rol;

        document.getElementById('modal_e_estado_hidden').value = btn.dataset.estado;
        document.getElementById('modal_e_rol_hidden').value    = btn.dataset.rol;

        document.getElementById('modal_e_nombre').textContent = "Editar: " + btn.dataset.nombres.split(' ')[0];
        document.getElementById('modalEditar').setAttribute('data-nombre-eliminar', btn.dataset.nombres + ' ' + btn.dataset.apellidos);
        
        document.getElementById('modal_e_foto').value = '';
        document.getElementById('texto-archivo-editar').textContent = 'Seleccionar nueva imagen...';

        limpiarValidaciones();

        const selectEstado = document.getElementById('modal_e_estado');
        const selectRol    = document.getElementById('modal_e_rol');
        const avisoEstado  = document.getElementById('aviso-estado-bloqueado');
        const avisoRol     = document.getElementById('aviso-rol-bloqueado');
        const btnEliminar  = document.getElementById('btn-eliminar-empleado');

        if (esElMismo) {
            selectEstado.disabled = true;
            selectEstado.style.opacity = '0.5';
            selectEstado.style.cursor  = 'not-allowed';
            selectRol.disabled = true;
            selectRol.style.opacity = '0.5';
            selectRol.style.cursor  = 'not-allowed';
            avisoEstado.style.display = 'inline';
            avisoRol.style.display    = 'inline';
            btnEliminar.style.display = 'none';
        } else {
            selectEstado.disabled = false;
            selectEstado.style.opacity = '';
            selectEstado.style.cursor  = '';
            selectRol.disabled = false;
            selectRol.style.opacity = '';
            selectRol.style.cursor  = '';
            avisoEstado.style.display = 'none';
            avisoRol.style.display    = 'none';
            btnEliminar.style.display = '';
        }

        document.getElementById('modalOverlay').classList.add('activo');
        document.getElementById('modalEditar').classList.add('activo');
    }

    function cerrarModales() {
        const modalActivo = document.querySelector('.modal-contenido.activo');
        const modalOverlay = document.getElementById('modalOverlay');
        const modalHorario = document.getElementById('modalHorario');
        const modalEditar = document.getElementById('modalEditar');
        
        if (modalActivo) modalActivo.classList.add('cerrando');
        if (modalOverlay) modalOverlay.classList.add('cerrando');
        
        setTimeout(function() {
            if (modalOverlay) modalOverlay.classList.remove('activo', 'cerrando');
            if (modalHorario) modalHorario.classList.remove('activo', 'cerrando');
            if (modalEditar) modalEditar.classList.remove('activo', 'cerrando');
        }, 220);
    }

    /**
     * Elimina el horario especial del usuario vaciando los inputs
     */
    function eliminarHorarioEspecial() {
        const nombre = document.getElementById('modal_h_nombre').textContent.replace('Horario: ', '');
        
        Swal.fire({
            title: `¿Quitar horario especial?`,
            html: `<b>${nombre}</b> volverá a regirse por el <b>horario general</b> de la institución.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, quitar horario',
            cancelButtonText: 'Cancelar',
            ...parametrosTema()
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('modal_h_entrada').value = '';
                document.getElementById('modal_h_salida').value = '';
                
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); },
                    ...parametrosTema()
                });
                
                document.querySelector('#modalHorario form').submit();
            }
        });
    }

    // =======================================================
    // 4. LÓGICA DE SEGURIDAD: ELIMINACIÓN DE USUARIO
    // =======================================================
    function confirmarEliminacion() {
        const idUsr  = document.getElementById('modal_e_id_usuario').value;
        const nombre = document.getElementById('modalEditar').getAttribute('data-nombre-eliminar');
        const idRolHidden = document.getElementById('modal_e_rol_hidden').value; 
        
        const modalOverlay = document.getElementById('modalOverlay');
        const modalEditar = document.getElementById('modalEditar');
        
        cerrarModales(); 
        
        let textoMensaje = '';
        let placeholderInput = '';
        
        if (idRolHidden == '1') {
            textoMensaje = `Estás a punto de eliminar a otro <b>Administrador</b>. Esta acción es irreversible.<br><br>Ingresa <b>la contraseña de la cuenta de ${nombre.split(' ')[0]}</b> para autorizar el borrado:`;
            placeholderInput = `Contraseña de ${nombre.split(' ')[0]}`;
        } else {
            textoMensaje = `Esta acción es irreversible y borrará todo su historial.<br><br>Ingresa <b>tu contraseña de administrador</b> para confirmar:`;
            placeholderInput = `Tu contraseña de acceso`;
        }

        Swal.fire({
            title: `¿Eliminar a ${nombre}?`,
            html: `
                <p style="margin-bottom: 15px; font-size: 0.95rem; line-height: 1.5;">${textoMensaje}</p>
                <div style="position: relative; width: 100%; max-width: 320px; margin: 0 auto;">
                    <input 
                        type="password" 
                        id="swal-input-password" 
                        class="swal2-input" 
                        placeholder="${placeholderInput}"
                        autocomplete="new-password"
                        readonly 
                        onfocus="this.removeAttribute('readonly');"
                        style="margin: 0; width: 100%; padding-right: 45px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;"
                    >
                    <span onclick="alternarClaveSwal(this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; display: flex;">
                        <svg class="icono-ver" viewBox="0 0 512 512" style="width: 24px; height: 24px;"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                        <svg class="icono-ocultar oculto" viewBox="0 0 512 512" style="width: 24px; height: 24px;"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                    </span>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, Eliminar Permanentemente',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true, 
            preConfirm: () => {
                const password = document.getElementById('swal-input-password').value;
                if (!password) {
                    Swal.showValidationMessage('Debes ingresar la contraseña para continuar');
                    return false;
                }

                const formData = new FormData();
                formData.append('id', idUsr);
                formData.append('password', password);

                return fetch('../controladores/ControladorEliminarPersonal.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error de servidor.');
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        Swal.showValidationMessage(data.msg);
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage('Fallo de red o comunicación con el servidor.');
                });
            },
            didOpen: () => {
                const input = document.getElementById('swal-input-password');
                if (input) setTimeout(() => input.focus(), 100);
            },
            allowOutsideClick: () => !Swal.isLoading(),
            ...parametrosTema()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    title: 'Eliminado',
                    text: result.value.msg,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    ...parametrosTema()
                }).then(() => {
                    location.reload(); 
                });
            } else if (result.isDismissed) {
                modalOverlay.classList.remove('cerrando');
                modalEditar.classList.remove('cerrando');
                modalOverlay.classList.add('activo');
                modalEditar.classList.add('activo');
            }
        });
    }

    window.alternarClaveSwal = function(spanElement) {
        const input = document.getElementById('swal-input-password');
        const iconVer = spanElement.querySelector('.icono-ver');
        const iconOcultar = spanElement.querySelector('.icono-ocultar');

        if (input.type === 'password') {
            input.type = 'text';
            iconVer.classList.add('oculto');
            iconOcultar.classList.remove('oculto');
        } else {
            input.type = 'password';
            iconVer.classList.remove('oculto');
            iconOcultar.classList.add('oculto');
        }
    };

    // =======================================================
    // 5. VALIDACIONES DE FORMULARIO (EDICIÓN Y HORARIOS)
    // =======================================================

    function setError(inputId, errorId, mostrar) {
        const campo  = document.getElementById(inputId);
        const mensaje = document.getElementById(errorId);
        if (mostrar) {
            campo.classList.add('input-error');
            campo.classList.remove('input-ok');
            mensaje.classList.add('visible');
        } else {
            campo.classList.remove('input-error');
            campo.classList.add('input-ok');
            mensaje.classList.remove('visible');
        }
        return !mostrar; 
    }

    function limpiarValidaciones() {
        ['modal_e_nombres','modal_e_apellidos','modal_e_cedula','modal_e_telefono','modal_e_usuario','modal_e_foto']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.classList.remove('input-error','input-ok'); }
            });
        document.querySelectorAll('.mensaje-error-campo').forEach(el => el.classList.remove('visible'));
    }

    const REGEX = {
        nombres:   /^[\u00C0-\u024Fa-zA-Z\s\-'\.]+$/, 
        cedula:    /^\d{6,12}$/,
        telefono:  /^[\d\s\-\+\(\)]{7,15}$/,
        usuario:   /^[a-zA-Z0-9_\.]{4,30}$/
    };

    function validarCampo(inputId, errorId, regex, valorMinLen = 1) {
        const valor = document.getElementById(inputId).value.trim();
        const invalido = valor.length < valorMinLen || (regex && !regex.test(valor));
        return setError(inputId, errorId, invalido);
    }

    function validarFoto(input) {
        const nombre = input.files[0] ? input.files[0].name : 'Seleccionar nueva imagen...';
        document.getElementById('texto-archivo-editar').textContent = nombre;

        if (!input.files[0]) return true;

        const tiposPermitidos = ['image/jpeg','image/png','image/webp','image/gif'];
        const tamanoMax = 2 * 1024 * 1024;
        const archivo = input.files[0];
        const invalido = !tiposPermitidos.includes(archivo.type) || archivo.size > tamanoMax;
        return setError('modal_e_foto', 'err-foto', invalido);
    }

    const formHorario = document.querySelector('#modalHorario form');

    if (formHorario) {
        formHorario.addEventListener('submit', function (e) {
            const inputEntrada = document.getElementById('modal_h_entrada');
            const inputSalida  = document.getElementById('modal_h_salida');

            if (inputEntrada && inputSalida && inputEntrada.value && inputSalida.value) {
                
                function horaAMinutos(hora) {
                    const [h, m] = hora.split(':').map(Number);
                    return (h * 60) + m;
                }

                const minEntrada = horaAMinutos(inputEntrada.value);
                const minSalida  = horaAMinutos(inputSalida.value);
                const duracion   = minSalida - minEntrada;

                if (minSalida <= minEntrada) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Horario inválido',
                        text: 'La hora de salida debe ser posterior a la hora de entrada.',
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        ...parametrosTema()
                    });
                    return;
                }

                if (duracion < 60) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Jornada muy corta',
                        text: 'El horario especial debe ser de al menos 1 hora (60 minutos).',
                        icon: 'warning',
                        confirmButtonColor: '#f59e0b',
                        ...parametrosTema()
                    });
                    return;
                }

                if (duracion > 480) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Jornada excesiva',
                        html: `El horario especial no puede superar las <strong>8 horas</strong> máximas permitidas.<br>Duración actual: <strong>${duracion} min</strong>.`,
                        icon: 'warning',
                        confirmButtonColor: '#f59e0b',
                        ...parametrosTema()
                    });
                    return;
                }
            }
        });
    }

    // =======================================================
    // 6. EVENT LISTENERS Y TRIGGERS
    // =======================================================
    
    if (inputBuscadorUniv) {
        inputBuscadorUniv.addEventListener('input', () => aplicarFiltroUniversal(null, null, true));
    }
    
    const btnCargarMas = document.getElementById('btn-ver-mas-personal');
    if (btnCargarMas) {
        btnCargarMas.addEventListener('click', () => {
            limiteActual += ITEMS_POR_CARGA;
            aplicarFiltroUniversal(cargoActivoUniv, document.querySelector('.btn-filtro.activo'), false);
        });
    }

    const modalOverlayEl = document.getElementById('modalOverlay');
    if (modalOverlayEl) {
        modalOverlayEl.addEventListener('click', (e) => { 
            if (e.target === modalOverlayEl) cerrarModales(); 
        });
    }

    const elNombres = document.getElementById('modal_e_nombres');
    const elApellidos = document.getElementById('modal_e_apellidos');
    const elCedula = document.getElementById('modal_e_cedula');
    const elTelefono = document.getElementById('modal_e_telefono');
    const elUsuario = document.getElementById('modal_e_usuario');
    const formEditar = document.getElementById('form-editar-personal');

    if (elNombres) elNombres.addEventListener('blur', () => validarCampo('modal_e_nombres', 'err-nombres', REGEX.nombres, 2));
    if (elApellidos) elApellidos.addEventListener('blur', () => validarCampo('modal_e_apellidos', 'err-apellidos', REGEX.nombres, 2));
    if (elCedula) elCedula.addEventListener('blur', () => validarCampo('modal_e_cedula', 'err-cedula', REGEX.cedula, 6));
    if (elTelefono) elTelefono.addEventListener('blur', () => validarCampo('modal_e_telefono', 'err-telefono', REGEX.telefono, 7));
    if (elUsuario) elUsuario.addEventListener('blur', () => validarCampo('modal_e_usuario', 'err-usuario', REGEX.usuario, 4));

    ['modal_e_nombres','modal_e_apellidos','modal_e_cedula','modal_e_telefono','modal_e_usuario'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    this.classList.remove('input-error');
                    const errId = 'err-' + id.replace('modal_e_','');
                    const errEl = document.getElementById(errId);
                    if (errEl) errEl.classList.remove('visible');
                }
            });
        }
    });

    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            const checks = [
                validarCampo('modal_e_nombres',   'err-nombres',   REGEX.nombres,   2),
                validarCampo('modal_e_apellidos', 'err-apellidos', REGEX.nombres,   2),
                validarCampo('modal_e_cedula',    'err-cedula',    REGEX.cedula,    6),
                validarCampo('modal_e_telefono',  'err-telefono',  REGEX.telefono,  7),
                validarCampo('modal_e_usuario',   'err-usuario',   REGEX.usuario,   4),
            ];

            const inputFoto = document.getElementById('modal_e_foto');
            if (inputFoto && inputFoto.files[0]) {
                checks.push(validarFoto(inputFoto));
            }

            const formularioValido = checks.every(Boolean);

            if (!formularioValido) {
                e.preventDefault();
                const btnGuardar = document.getElementById('btn-guardar-empleado');
                btnGuardar.classList.add('sacudir');
                btnGuardar.addEventListener('animationend', () => btnGuardar.classList.remove('sacudir'), { once: true });
                const modalEditar = document.getElementById('modalEditar');
                const primerError = modalEditar.querySelector('.input-error');
                if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    // =======================================================
    // 7. EXPORTACIÓN AL SCOPE GLOBAL (WINDOW)
    // =======================================================
    window.aplicarFiltroUniversal  = aplicarFiltroUniversal;
    window.abrirModalHorario       = abrirModalHorario;
    window.abrirModalEditar        = abrirModalEditar;
    window.cerrarModales           = cerrarModales;
    window.validarFoto             = validarFoto;
    window.confirmarEliminacion    = confirmarEliminacion;
    window.eliminarHorarioEspecial = eliminarHorarioEspecial; 

    // Ejecución Inicial
    aplicarFiltroUniversal(null, null, true);

    // =======================================================
    // 8. INICIALIZACIÓN DE LA GUÍA DINÁMICA (PERSONAL)
    // =======================================================
    let diccionarioPersonal = [];

    if (document.querySelector('.campo-busqueda-elegante')) {
        diccionarioPersonal.push({ 
            selector: '.campo-busqueda-elegante', 
            titulo: 'Buscador de Personal', 
            texto: 'Encuentra rápidamente a cualquier empleado escribiendo su nombre, apellido o cargo sin recargar la página.' 
        });
    }

    if (document.querySelectorAll('.btn-filtro').length > 0) {
        diccionarioPersonal.push({ 
            selector: '.btn-filtro', 
            titulo: 'Filtros Rápidos', 
            texto: 'Haz clic en estas etiquetas para aislar la vista y mostrar únicamente a los empleados que pertenezcan a ese departamento.' 
        });
    }

    if (document.querySelectorAll('.tarjeta-perfil').length > 0) {
        diccionarioPersonal.push({ 
            selector: '.tarjeta-perfil', 
            titulo: 'Tarjeta de Empleado', 
            texto: 'Muestra los datos básicos, foto y una etiqueta que indica si el usuario está activo o suspendido en el sistema.' 
        });
    }

    if (document.querySelectorAll('.btn-editar-horario').length > 0) {
        diccionarioPersonal.push({ 
            selector: '.btn-editar-horario', 
            titulo: 'Horario Personalizado', 
            texto: 'Abre un panel para asignarle a este empleado horas de entrada y salida exclusivas, las cuales ignorarán el horario general de la institución.' 
        });
    }

    if (document.querySelectorAll('.btn-editar-usuario').length > 0) {
        diccionarioPersonal.push({ 
            selector: '.btn-editar-usuario', 
            titulo: 'Editar y Gestionar', 
            texto: 'Permite modificar los datos personales, cambiar su cargo o rol, subir una nueva foto, suspender su acceso o eliminar su cuenta permanentemente.' 
        });
    }

    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppPersonal = new window.GuiaDinamica(diccionarioPersonal);
    }
});