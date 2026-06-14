document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.SeguridadConfig;
    const html = document.documentElement;
    const CSRF_TOKEN = config.csrfToken;
    const FECHAS_VALIDAS = new Set(config.fechasValidas);

    // =======================================================
    // 2. TEMAS Y ALERTAS (SWEETALERT2)
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
            this.classList.add('girando');
            const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', nuevoTema);
            localStorage.setItem('tema_usuario_' + config.idUsuario, nuevoTema);
            setTimeout(() => { this.classList.remove('girando'); }, 500);
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

    // ------ SEGURIDAD Y RESPALDOS ------

    function generarRespaldo(tipo, restantes) {
        if (restantes <= 0) {
            Swal.fire({ title: 'Límite alcanzado', text: 'Ya has generado el máximo de respaldos permitidos por hoy (4).', icon: 'warning', ...parametrosTema() });
            return;
        }
        let textoMensaje = tipo === 'local' 
            ? 'El respaldo se guardará en el servidor y aparecerá en tu historial.'
            : 'El respaldo se generará y se descargará automáticamente a tu equipo.';
        
        Swal.fire({
            title: '¿Generar copia de seguridad?', 
            text: textoMensaje, 
            icon: 'info',
            showCancelButton: true, 
            confirmButtonColor: '#406ff3', 
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, generar', 
            cancelButtonText: 'Cancelar',
            ...parametrosTema()
        }).then((result) => {
            if (result.isConfirmed) {
                if (tipo === 'local') {
                    Swal.fire({
                        title: 'Procesando...', 
                        text: 'Generando y guardando archivo SQL.',
                        allowOutsideClick: false, 
                        didOpen: () => { Swal.showLoading(); },
                        ...parametrosTema()
                    });
                    window.location.href = '../controladores/ControladorSeguridad.php?accion=generar&tipo=local';
                } else {
                    Swal.fire({
                        title: '¡Preparando Descarga!', 
                        text: 'El archivo SQL se descargará en breve.',
                        icon: 'success', 
                        timer: 3000, 
                        showConfirmButton: false,
                        ...parametrosTema()
                    });
                    setTimeout(() => { window.location.href = '../controladores/ControladorSeguridad.php?accion=generar&tipo=descargar'; }, 800);
                }
            }
        });
    }

    function pedirPasswordRestaurar(nombreArchivo, restantes) {
        if (restantes <= 0) {
            Swal.fire({ title: 'Límite alcanzado', text: 'Ya has utilizado el máximo de 2 restauraciones permitidas por hoy.', icon: 'warning', ...parametrosTema() });
            return;
        }
        Swal.fire({
            title: '⚠️ ADVERTENCIA CRÍTICA',
            html: `<p style="margin:0 0 10px;">Esta operación <strong>reemplazará toda la base de datos</strong> con el respaldo seleccionado.</p>
                   <p style="margin:0; color:#ef4444; font-size:0.9rem;">Esta acción <u>no se puede deshacer</u>. Asegúrate de que el archivo es correcto.</p>`,
            icon: 'warning', 
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Entiendo, continuar', 
            cancelButtonText: 'Cancelar',
            ...parametrosTema()
        }).then((paso1) => {
            if (!paso1.isConfirmed) return;
            Swal.fire({
                title: 'Confirma tu identidad',
                html: `<p style="margin:0 0 15px; font-size:0.9rem;">Ingresa tu contraseña de administrador para autorizar la restauración del archivo:<br>
                       <code style="font-size:0.8rem; background:rgba(0,0,0,0.1); padding:3px 8px; border-radius:5px;">${nombreArchivo}</code></p>`,
                icon: 'warning', 
                input: 'password',
                inputAttributes: { autocomplete: 'off', minlength: '6', placeholder: 'Introduce tu contraseña' },
                didOpen: () => {
                    const inp = Swal.getInput();
                    if (inp) {
                        inp.setAttribute('name', 'pwd_' + Math.random().toString(36).slice(2));
                        inp.setAttribute('readonly', 'true');
                        setTimeout(() => inp.removeAttribute('readonly'), 100);
                    }
                },
                showCancelButton: true, 
                confirmButtonColor: '#ef4444', 
                cancelButtonColor: '#64748b',
                confirmButtonText: 'CONFIRMAR RESTAURACIÓN', 
                cancelButtonText: 'Cancelar',
                ...parametrosTema(),
                preConfirm: (password) => {
                    if (!password) { Swal.showValidationMessage('⛔ La contraseña es obligatoria.'); return false; }
                    return password;
                }
            }).then((paso2) => {
                if (!paso2.isConfirmed) return;
                const form = document.createElement('form');
                form.method = 'POST'; form.action = '../controladores/ControladorSeguridad.php';
                const campos = { accion: 'restaurar', archivo: nombreArchivo, password_admin: paso2.value, csrf_token: CSRF_TOKEN };
                Object.entries(campos).forEach(([name, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = name; input.value = value;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                Swal.fire({ 
                    title: 'Restaurando...', 
                    text: 'Verificando credenciales e importando base de datos.', 
                    allowOutsideClick: false, 
                    didOpen: () => { Swal.showLoading(); },
                    ...parametrosTema()
                });
                form.submit();
            });
        });
    }

    function eliminarRespaldo(nombreArchivo) {
        Swal.fire({
            title: '¿Eliminar respaldo?', 
            text: "Ya no podrás utilizar este archivo para restaurar el sistema.", 
            icon: 'warning',
            showCancelButton: true, 
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar', 
            cancelButtonText: 'Cancelar',
            ...parametrosTema()
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = '../controladores/ControladorSeguridad.php?accion=eliminar&archivo=' + encodeURIComponent(nombreArchivo); }
        });
    }

    // ------ BITÁCORA Y FILTROS ------

    function abrirModalBitacora() {
        const modal = document.getElementById('modalBitacora');
        if (!modal) return;
        const contenedor = modal.querySelector('.modal-bitacora-contenedor');
        modal.style.display = 'flex';
        contenedor.style.animation = 'none';
        contenedor.offsetHeight; 
        contenedor.style.animation = 'zoomIn 0.3s ease-out';
        document.body.style.overflow = 'hidden';
        
        const btnActivo = document.querySelector('.btn-tab-bitacora.activo');
        const moduloActivo = btnActivo ? (btnActivo.getAttribute('data-filtro') || 'Todos') : 'Todos';
        actualizarDropdownsPorModulo(moduloActivo);
        filtrarBitacora();
    }

    function cerrarModalBitacora() {
        const modal = document.getElementById('modalBitacora');
        if (!modal) return;
        const contenedor = modal.querySelector('.modal-bitacora-contenedor');
        contenedor.style.animation = 'zoomOut 0.22s ease-in forwards';
        setTimeout(() => {
            modal.style.display = 'none';
            contenedor.style.animation = '';
            document.body.style.overflow = 'auto';
        }, 210);
    }
    
    function cambiarTabBitacora(btn) {
        document.querySelectorAll('.btn-tab-bitacora').forEach(b => b.classList.remove('activo'));
        btn.classList.add('activo');
        const moduloActivo = btn.getAttribute('data-filtro') || 'Todos';
        actualizarDropdownsPorModulo(moduloActivo);
        filtrarBitacora();
    }

    function actualizarDropdownsPorModulo(moduloActivo) {
        let diasDisponibles = new Set();
        let mesesDisponibles = new Set();
        let aniosDisponibles = new Set();

        document.querySelectorAll('.fila-bitacora').forEach(fila => {
            const moduloFila = fila.getAttribute('data-modulo') || '';
            if (moduloActivo === 'Todos' || moduloFila === moduloActivo) {
                const celdaFecha = fila.querySelector('td:first-child');
                if (celdaFecha) {
                    const textoCelda = celdaFecha.textContent.trim();
                    const matchFecha = textoCelda.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                    if (matchFecha) {
                        diasDisponibles.add(matchFecha[1]); 
                        mesesDisponibles.add(matchFecha[2]); 
                        aniosDisponibles.add(matchFecha[3]); 
                    }
                }
            }
        });

        const itemsDia = document.querySelectorAll('#cdd-dia .cdd-panel .cdd-item:not(.cdd-placeholder)');
        itemsDia.forEach(item => {
            const val = item.getAttribute('data-val');
            if (diasDisponibles.has(val)) {
                item.className = 'cdd-item cdd-available';
                item.setAttribute('onclick', `seleccionarCdd('cdd-dia','${val}','${val}')`);
                item.title = '';
            } else {
                item.className = 'cdd-item cdd-disabled';
                item.removeAttribute('onclick');
                item.title = 'Sin registros de ' + moduloActivo + ' este día';
            }
        });

        const itemsMes = document.querySelectorAll('#cdd-mes .cdd-panel .cdd-item:not(.cdd-placeholder)');
        itemsMes.forEach(item => {
            const val = item.getAttribute('data-val');
            const nombre = item.getAttribute('data-nombre');
            if (mesesDisponibles.has(val)) {
                item.className = 'cdd-item cdd-available';
                item.setAttribute('onclick', `seleccionarCdd('cdd-mes','${val}','${nombre}')`);
                item.title = '';
            } else {
                item.className = 'cdd-item cdd-disabled';
                item.removeAttribute('onclick');
                item.title = 'Sin registros de ' + moduloActivo + ' este mes';
            }
        });

        const itemsAnio = document.querySelectorAll('#cdd-anio .cdd-panel .cdd-item:not(.cdd-placeholder)');
        itemsAnio.forEach(item => {
            const val = item.getAttribute('data-val');
            if (aniosDisponibles.has(val)) {
                item.className = 'cdd-item cdd-available';
                item.setAttribute('onclick', `seleccionarCdd('cdd-anio','${val}','${val}')`);
                item.title = '';
            } else {
                item.className = 'cdd-item cdd-disabled';
                item.removeAttribute('onclick');
                item.title = 'Sin registros de ' + moduloActivo + ' en ' + val;
            }
        });

        const diaSeleccionado = document.getElementById('filtro-dia')?.value;
        if (diaSeleccionado && !diasDisponibles.has(diaSeleccionado)) seleccionarCdd('cdd-dia', '', 'Día');
        
        const mesSeleccionado = document.getElementById('filtro-mes')?.value;
        if (mesSeleccionado && !mesesDisponibles.has(mesSeleccionado)) seleccionarCdd('cdd-mes', '', 'Mes');
        
        const anioSeleccionado = document.getElementById('filtro-anio')?.value;
        if (anioSeleccionado && !aniosDisponibles.has(anioSeleccionado)) seleccionarCdd('cdd-anio', '', 'Año');
    }

    function filtrarBitacora() {
        const textoBusqueda = (document.getElementById('busqueda-bitacora')?.value || '').toLowerCase().trim();
        const dia  = document.getElementById('filtro-dia')?.value  || '';
        const mes  = document.getElementById('filtro-mes')?.value  || '';
        const anio = document.getElementById('filtro-anio')?.value || '';

        const mensajeVacio = document.getElementById('mensaje-sin-registros');
        let fechaEsInvalida = false;
        
        if (dia !== '' && mes !== '' && anio !== '') {
            const fechaISO = `${anio}-${mes}-${dia}`;
            const objFecha = new Date(fechaISO + 'T00:00:00');
            const dow = objFecha.getDay(); 
            if (dow === 0 || dow === 6) {
                fechaEsInvalida = 'fin_de_semana';
            } else if (!FECHAS_VALIDAS.has(fechaISO)) {
                fechaEsInvalida = 'sin_registros';
            }
        }

        const btnActivo = document.querySelector('.btn-tab-bitacora.activo');
        const moduloSeleccionado = btnActivo ? btnActivo.getAttribute('data-filtro') : 'Todos';

        const filas = document.querySelectorAll('.fila-bitacora');
        let visibles = 0;
        const total = filas.length;

        filas.forEach(fila => {
            const moduloFila = fila.getAttribute('data-modulo') || '';
            const pasaModulo = (moduloSeleccionado === 'Todos') || (moduloFila === moduloSeleccionado);

            const textoFila = fila.textContent.toLowerCase();
            const pasaTexto = textoBusqueda === '' || textoFila.includes(textoBusqueda);

            const celdaFecha = fila.querySelector('td:first-child');
            const textoCelda = celdaFecha ? celdaFecha.textContent.trim() : '';
            const matchFecha = textoCelda.match(/(\d{2})\/(\d{2})\/(\d{4})/);
            let pasaDia = true, pasaMes = true, pasaAnio = true;

            if (matchFecha) {
                const [, diaFila, mesFila, anioFila] = matchFecha;
                if (dia  !== '') pasaDia  = diaFila  === dia;
                if (mes  !== '') pasaMes  = mesFila  === mes;
                if (anio !== '') pasaAnio = anioFila === anio;
            } else if (dia !== '' || mes !== '' || anio !== '') {
                pasaDia = pasaMes = pasaAnio = false;
            }

            const mostrar = pasaModulo && pasaTexto && pasaDia && pasaMes && pasaAnio;
            fila.style.display = mostrar ? 'table-row' : 'none';
            if (mostrar) visibles++;
        });

        if (mensajeVacio) {
            if (fechaEsInvalida === 'fin_de_semana') {
                mensajeVacio.style.display = 'flex';
                mensajeVacio.querySelector('.msg-texto').textContent = 'Los fines de semana no tienen registros de actividad.';
                mensajeVacio.querySelector('.msg-icono').textContent = '📅';
            } else if (fechaEsInvalida === 'sin_registros') {
                mensajeVacio.style.display = 'flex';
                mensajeVacio.querySelector('.msg-texto').textContent = 'No hubo actividad registrada en esta fecha.';
                mensajeVacio.querySelector('.msg-icono').textContent = '🗓️';
            } else if (visibles === 0 && (textoBusqueda !== '' || dia !== '' || mes !== '' || anio !== '')) {
                mensajeVacio.style.display = 'flex';
                mensajeVacio.querySelector('.msg-texto').textContent = 'No se encontraron registros con los filtros aplicados.';
                mensajeVacio.querySelector('.msg-icono').textContent = '🔍';
            } else {
                mensajeVacio.style.display = 'none';
            }
        }

        const contador = document.getElementById('contador-resultados');
        if (contador) {
            if (total === 0 || fechaEsInvalida) {
                contador.textContent = '';
            } else if (visibles === total) {
                contador.textContent = `Mostrando ${total} registro${total !== 1 ? 's' : ''}`;
            } else {
                contador.textContent = `Mostrando ${visibles} de ${total} registro${total !== 1 ? 's' : ''}`;
            }
        }
    }

    function toggleCdd(id) {
        const wrap = document.getElementById(id);
        if (!wrap) return;
        const isOpen = wrap.classList.contains('cdd-open');
        document.querySelectorAll('.cdd-wrap.cdd-open').forEach(w => w.classList.remove('cdd-open'));
        if (!isOpen) wrap.classList.add('cdd-open');
    }

    function seleccionarCdd(wrapId, value, label) {
        const wrap = document.getElementById(wrapId);
        if (!wrap) return;
        document.getElementById(
            wrapId === 'cdd-dia'  ? 'filtro-dia'  :
            wrapId === 'cdd-mes'  ? 'filtro-mes'  : 'filtro-anio'
        ).value = value;
        
        wrap.querySelector('.cdd-label').textContent = label || (
            wrapId === 'cdd-dia' ? 'Día' : wrapId === 'cdd-mes' ? 'Mes' : 'Año'
        );
        
        wrap.querySelectorAll('.cdd-item').forEach(i => i.classList.remove('cdd-active'));
        if (value !== '') {
            const itemActivo = wrap.querySelector(`.cdd-available[data-val="${value}"]`);
            if(itemActivo) itemActivo.classList.add('cdd-active');
        }
        
        const trigger = wrap.querySelector('.cdd-trigger');
        if (value === '') {
            trigger.classList.remove('cdd-selected');
        } else {
            trigger.classList.add('cdd-selected');
        }
        wrap.classList.remove('cdd-open');
        filtrarBitacora();
    }

    function limpiarFiltrosBitacora() {
        const input = document.getElementById('busqueda-bitacora');
        if (input) input.value = '';
        seleccionarCdd('cdd-dia',  '', 'Día');
        seleccionarCdd('cdd-mes',  '', 'Mes');
        seleccionarCdd('cdd-anio', '', 'Año');
        filtrarBitacora();
    }

    // =======================================================
    // 4. EVENT LISTENERS Y EVENTOS DEL DOM
    // =======================================================
    
    // Cerrar dropdowns de la bitácora al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.cdd-wrap')) {
            document.querySelectorAll('.cdd-wrap.cdd-open').forEach(w => w.classList.remove('cdd-open'));
        }
    });

    const inputBuscadorBitacora = document.getElementById('busqueda-bitacora');
    if (inputBuscadorBitacora) {
        inputBuscadorBitacora.addEventListener('input', filtrarBitacora);
    }

    // =======================================================
    // 5. EXPORTACIÓN AL SCOPE GLOBAL
    // =======================================================
    window.generarRespaldo = generarRespaldo;
    window.pedirPasswordRestaurar = pedirPasswordRestaurar;
    window.eliminarRespaldo = eliminarRespaldo;
    window.abrirModalBitacora = abrirModalBitacora;
    window.cerrarModalBitacora = cerrarModalBitacora;
    window.cambiarTabBitacora = cambiarTabBitacora;
    window.toggleCdd = toggleCdd;
    window.seleccionarCdd = seleccionarCdd;
    window.filtrarBitacora = filtrarBitacora;
    window.limpiarFiltrosBitacora = limpiarFiltrosBitacora;
});