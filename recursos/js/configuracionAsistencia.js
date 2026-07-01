document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.ConfiguracionConfig;
    const html = document.documentElement;
    const claveTema = 'tema_usuario_' + config.idUsuario;
    
    let totalPlantillas = config.totalPlantillas;
    const MAXIMO_PLANTILLAS = config.maximoPlantillas;

    let mesCal = new Date().getMonth();
    let anioCal = new Date().getFullYear();
    let feriadosActuales = [];

    // =======================================================
    // 2. ALERTAS Y TEMAS
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
        btnCambiarTema.addEventListener('click', function (e) {
            e.preventDefault();
            const temaActual = html.getAttribute('data-theme');
            const nuevoTema  = temaActual === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', nuevoTema);
            localStorage.setItem(claveTema, nuevoTema);
        });
    }

    if (config.alertas && config.alertas.length > 0) {
        config.alertas.forEach(alerta => {
            Swal.fire({
                title: alerta.titulo,
                text: alerta.mensaje,
                icon: alerta.tipo,
                confirmButtonColor: alerta.tipo === 'success' ? '#10b981' : '#ef4444',
                ...parametrosTema()
            });
        });
    }

    // =======================================================
    // 3. DECLARACIÓN DE FUNCIONES (HOISTING SEGURO)
    // =======================================================

    function actualizarBotonAgregar() {
        const btn      = document.getElementById('btnAgregarNueva');
        const aviso    = document.getElementById('avisosLimite');
        const contador = document.getElementById('contadorPlantillas');
        const lleno    = totalPlantillas >= MAXIMO_PLANTILLAS;

        if(btn) btn.classList.toggle('oculto', lleno);
        if(aviso) aviso.classList.toggle('visible', lleno);
        if (contador) contador.textContent = totalPlantillas + ' / ' + MAXIMO_PLANTILLAS;
    }

    function confirmarAplicarPlantilla(idPlantilla, nombrePlantilla, entrada, salida, tolerancia) {
        const t = parametrosTema();
        const colorSubtexto = t.color === '#fff' ? '#94a3b8' : '#6b7280';

        Swal.fire({
            title:             `Aplicar "${nombrePlantilla}"`,
            html:              `¿Quieres cargar esta configuración en el formulario?<br><br>
                                <small style="color:${colorSubtexto}">
                                    Entrada: <strong>${entrada}</strong> &nbsp;·&nbsp;
                                    Salida: <strong>${salida}</strong> &nbsp;·&nbsp;
                                    Tolerancia: <strong>${tolerancia} min</strong>
                                </small>`,
            icon:              'question',
            showCancelButton:   true,
            confirmButtonText:  'Sí, aplicar',
            cancelButtonText:   'Cancelar',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor:  '#6b7280',
            ...t,
        }).then(resultado => {
            if (!resultado.isConfirmed) return;

            document.getElementById('hora_entrada').value       = entrada;
            document.getElementById('hora_salida').value        = salida;
            document.getElementById('minutos_tolerancia').value = tolerancia;
            document.getElementById('campoIdActivaOculto').value = idPlantilla;

            ['hora_entrada', 'hora_salida', 'minutos_tolerancia'].forEach(id => {
                const campo = document.getElementById(id);
                if(campo) {
                    campo.style.borderColor = '#3b82f6';
                    campo.style.boxShadow   = '0 0 0 3px rgba(59,130,246,0.18)';
                    setTimeout(() => {
                        campo.style.borderColor = '';
                        campo.style.boxShadow   = '';
                    }, 2400);
                }
            });

            const form = document.getElementById('formConfiguracion');
            if(form) form.scrollIntoView({ behavior: 'smooth', block: 'center' });

            Swal.fire({
                title:             '¡Listo!',
                text:              'Revisa los valores y haz clic en "Guardar Cambios".',
                icon:              'info',
                timer:             2400,
                showConfirmButton:  false,
                ...parametrosTema(),
            });

            cerrarModalPlantillas();
        });
    }

    function guardarComoNueva() {
        if (totalPlantillas >= MAXIMO_PLANTILLAS) {
            Swal.fire({
                title:              'Límite alcanzado',
                text:               'Solo se permiten 4 plantillas. Elimina una antes de agregar otra.',
                icon:               'warning',
                confirmButtonColor: '#f59e0b',
                ...parametrosTema(),
            });
            return;
        }

        const entrada    = document.getElementById('hora_entrada').value;
        const salida     = document.getElementById('hora_salida').value;
        const tolerancia = document.getElementById('minutos_tolerancia').value;

        if (!entrada || !salida) {
            Swal.fire({
                title:              'Formulario incompleto',
                text:               'Completa los campos de hora antes de guardar como plantilla.',
                icon:               'warning',
                confirmButtonColor: '#f59e0b',
                ...parametrosTema(),
            });
            return;
        }

        Swal.fire({
            title:            'Nombre de la plantilla',
            input:            'text',
            inputPlaceholder: 'Ej: Jornada corta, Llegada tarde...',
            showCancelButton:   true,
            confirmButtonText:  'Guardar',
            cancelButtonText:   'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor:  '#6b7280',
            inputAttributes: { maxlength: '60' },
            inputValidator: valor => {
                if (!valor || !valor.trim()) return 'El nombre no puede estar vacío.';
                if (valor.trim().length > 60) return 'Máximo 60 caracteres.';
            },
            ...parametrosTema(),
        }).then(resultado => {
            if (!resultado.isConfirmed) return;

            const nombre = resultado.value.trim();
            const datos  = new FormData();
            datos.append('accion',            'nueva_plantilla');
            datos.append('nombre',             nombre);
            datos.append('hora_entrada',       entrada);
            datos.append('hora_salida',        salida);
            datos.append('minutos_tolerancia', tolerancia);

            fetch('../controladores/ControladorConfiguracion.php', {
                method: 'POST',
                body:   datos,
            })
            .then(res => res.json())
            .then(json => {
                if (json.exito) {
                    Swal.fire({
                        title:             '¡Plantilla guardada!',
                        text:              `"${nombre}" está disponible en las plantillas.`,
                        icon:              'success',
                        confirmButtonColor: '#10b981',
                        ...parametrosTema(),
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        title:             'Error',
                        text:              json.mensaje || 'No se pudo guardar la plantilla.',
                        icon:              'error',
                        confirmButtonColor: '#ef4444',
                        ...parametrosTema(),
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title:             'Error de red',
                    text:              'No se pudo comunicar con el servidor.',
                    icon:              'error',
                    confirmButtonColor: '#ef4444',
                    ...parametrosTema(),
                });
            });
        });
    }

    function confirmarEliminarTarjeta(idPlantilla, nombrePlantilla) {
        Swal.fire({
            title:              `¿Eliminar "${nombrePlantilla}"?`,
            text:               'Esta plantilla no se podrá recuperar.',
            icon:               'warning',
            showCancelButton:   true,
            confirmButtonText:  'Sí, eliminar',
            cancelButtonText:   'Cancelar',
            confirmButtonColor: '#ef4444',
            cancelButtonColor:  '#6b7280',
            ...parametrosTema(),
        }).then(resultado => {
            if (!resultado.isConfirmed) return;

            const datos = new FormData();
            datos.append('accion',             'eliminar_plantilla');
            datos.append('id_preestablecida',   idPlantilla);

            fetch('../controladores/ControladorConfiguracion.php', {
                method: 'POST',
                body:   datos,
            })
            .then(res => res.json())
            .then(json => {
                if (json.exito) {
                    const tarjeta = document.getElementById('tarjeta-' + idPlantilla);
                    if (tarjeta) {
                        tarjeta.style.opacity   = '0';
                        tarjeta.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            tarjeta.remove();
                            totalPlantillas--;
                            actualizarBotonAgregar();
                        }, 300);
                    }
                    Swal.fire({
                        title:             'Eliminada',
                        text:              'La plantilla fue eliminada correctamente.',
                        icon:              'success',
                        timer:             1800,
                        showConfirmButton:  false,
                        ...parametrosTema(),
                    });
                } else {
                    Swal.fire({
                        title:             'Error',
                        text:              json.mensaje || 'No se pudo eliminar la plantilla.',
                        icon:              'error',
                        confirmButtonColor: '#ef4444',
                        ...parametrosTema(),
                    });
                }
            });
        });
    }

    function activarEdicionNombre(span, id) {
        const nombreOriginal = span.textContent.trim();

        const inputEditable     = document.createElement('input');
        inputEditable.type      = 'text';
        inputEditable.value     = nombreOriginal;
        inputEditable.maxLength = 60;
        inputEditable.className = 'input-nombre-editable';

        span.parentNode.replaceChild(inputEditable, span);
        inputEditable.focus();
        inputEditable.select();

        let guardando = false; 

        function guardarNombre() {
            if (guardando) return;
            guardando = true;

            const nuevoNombre = inputEditable.value.trim();

            if (!nuevoNombre || nuevoNombre === nombreOriginal) {
                inputEditable.parentNode.replaceChild(span, inputEditable);
                return;
            }

            const datos = new FormData();
            datos.append('accion',             'renombrar_plantilla');
            datos.append('id_preestablecida',   id);
            datos.append('nombre',              nuevoNombre);

            fetch('../controladores/ControladorConfiguracion.php', {
                method: 'POST',
                body:   datos,
            })
            .then(res => res.json())
            .then(json => {
                if (json.exito) {
                    span.textContent = nuevoNombre;
                }
                inputEditable.parentNode.replaceChild(span, inputEditable);
            })
            .catch(() => {
                inputEditable.parentNode.replaceChild(span, inputEditable);
            });
        }

        inputEditable.addEventListener('blur', guardarNombre);

        inputEditable.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                inputEditable.blur(); 
            }
            if (e.key === 'Escape') {
                guardando = true; 
                inputEditable.removeEventListener('blur', guardarNombre);
                inputEditable.parentNode.replaceChild(span, inputEditable);
            }
        });
    }

    // Modal Plantillas (Móvil)
    function abrirModalPlantillas() {
        const overlay = document.getElementById('modalPlantillasOverlay');
        if (!overlay) return;
        overlay.classList.add('abierto');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalPlantillas() {
        const overlay = document.getElementById('modalPlantillasOverlay');
        if (!overlay) return;
        overlay.classList.remove('abierto');
        document.body.style.overflow = '';
    }

    function cerrarModalPlantillasSiOverlay(evento) {
        if (evento.target === document.getElementById('modalPlantillasOverlay')) {
            cerrarModalPlantillas();
        }
    }

    // Modal Días Libres (Feriados)
    function abrirModalFeriados() {
        const overlay = document.getElementById('modalFeriadosOverlay');
        if (overlay) {
            overlay.classList.add('abierto');
            document.body.style.overflow = 'hidden';
        }
        cargarFeriados();
    }

    function cerrarModalFeriados() {
        const overlay = document.getElementById('modalFeriadosOverlay');
        if (overlay) {
            overlay.classList.remove('abierto');
            document.body.style.overflow = '';
        }
    }

    function cerrarModalFeriadosSiOverlay(e) {
        if (e.target.id === 'modalFeriadosOverlay') cerrarModalFeriados();
    }

    function cambiarMesFeriado(dir) {
        mesCal += dir;
        if (mesCal < 0) { mesCal = 11; anioCal--; }
        else if (mesCal > 11) { mesCal = 0; anioCal++; }
        renderizarCalendarioFeriados();
    }

    function cargarFeriados() {
        fetch('../controladores/ControladorFeriados.php?accion=listar')
            .then(res => res.json())
            .then(data => {
                if (data.success) feriadosActuales = data.data; 
                renderizarCalendarioFeriados();
            }).catch(err => console.error('Error cargando feriados:', err));
    }

    function renderizarCalendarioFeriados() {
        const grid = document.getElementById('gridCalendarioFeriados');
        const labelMes = document.getElementById('mesAnioFeriado');
        const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        if(!grid) return;
        labelMes.textContent = `${nombresMeses[mesCal]} ${anioCal}`;
        grid.innerHTML = '';

        diasSemana.forEach(d => {
            const div = document.createElement('div');
            div.style.fontWeight = 'bold'; div.style.padding = '10px 0'; div.style.color = 'var(--text-color)'; div.textContent = d;
            grid.appendChild(div);
        });

        const primerDia = new Date(anioCal, mesCal, 1).getDay();
        const diasEnMes = new Date(anioCal, mesCal + 1, 0).getDate();

        for (let i = 0; i < primerDia; i++) grid.appendChild(document.createElement('div'));

        for (let i = 1; i <= diasEnMes; i++) {
            const div = document.createElement('div');
            const fechaFormat = `${anioCal}-${String(mesCal + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            const feriadoEncontrado = feriadosActuales.find(f => f.fecha === fechaFormat);
            
            div.textContent = i; div.style.padding = '15px 5px'; div.style.borderRadius = '8px';
            div.style.border = '1px solid var(--text-color)';
            div.style.transition = 'all 0.2s';
            
            const diaSem = new Date(anioCal, mesCal, i).getDay();
            const esFinde = (diaSem === 0 || diaSem === 6); 
            
            if (feriadoEncontrado) {
                div.style.backgroundColor = '#e9d5ff'; 
                div.style.color = '#6b21a8';
                div.style.fontWeight = 'bold';
                div.style.borderColor = '#c084fc';
                div.title = feriadoEncontrado.descripcion;
                div.style.cursor = 'pointer';
                div.onclick = () => gestionarFeriado(fechaFormat, feriadoEncontrado);
            } else if (esFinde) {
                div.style.backgroundColor = 'var(--bg-light)';
                div.style.color = 'var(--text-color)';
                div.style.opacity = '0.6';
                div.style.cursor = 'default';
                div.title = 'Día no laborable, no se puede modificar';
                div.onclick = null;
            } else {
                div.style.backgroundColor = 'var(--bg-card)';
                div.style.color = 'var(--text-color)';
                div.style.cursor = 'pointer';
                div.onclick = () => gestionarFeriado(fechaFormat, null);
            }

            div.onmouseover = () => {
                if (!esFinde) div.style.transform = 'scale(1.05)';
            };
            div.onmouseout = () => {
                if (!esFinde) div.style.transform = 'scale(1)';
            };
            
            grid.appendChild(div);
        }
    }

    function gestionarFeriado(fechaStr, datosFeriado) {
        const t = parametrosTema();
        const fechaVisual = fechaStr.split('-').reverse().join('/');

        if (datosFeriado) {
            Swal.fire({
                title: '¿Restaurar Día?',
                html: `Día libre:<br><b style="color:var(--primary-color);">${datosFeriado.descripcion}</b><br><br>¿Volver a marcar como laborable?`,
                icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, restaurar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10b981', cancelButtonColor: '#6b7280', ...t
            }).then((res) => { if (res.isConfirmed) enviarFeriadoBD(fechaStr, null, null); });
        } else {
            Swal.fire({
                title: 'Inhabilitar Día',
                html: `
                    <div style="text-align: left; font-size: 0.95rem;">
                        <p style="margin-bottom: 10px; color: var(--primary-color); font-weight: bold;">Fecha: ${fechaVisual}</p>
                        <label style="font-weight: bold; margin-bottom: 5px; display: block; color: var(--text-color);">Clasificación:</label>
                        <select id="swal-feriado-tipo" style="width: 100%; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ccc; outline: none; font-family: inherit;">
                            <option value="Feriado">Feriado Nacional / Regional</option>
                            <option value="Día Festivo">Día Festivo del Plantel</option>
                        </select>
                        <label style="font-weight: bold; margin-bottom: 5px; display: block; color: var(--text-color);">Justificación (Mín. 10 caracteres):</label>
                        <input type="text" id="swal-feriado-motivo" placeholder="Ej: Aniversario del Plantel..." autocomplete="new-password" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; outline: none; box-sizing: border-box; font-family: inherit;">
                    </div>
                `,
                showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280', ...t,
                preConfirm: () => {
                    const tipo = document.getElementById('swal-feriado-tipo').value;
                    const motivo = document.getElementById('swal-feriado-motivo').value.trim();
                    if (motivo.length < 10) { Swal.showValidationMessage('La justificación debe tener al menos 10 caracteres.'); return false; }
                    return { tipo, motivo };
                }
            }).then((res) => {
                if (res.isConfirmed) { enviarFeriadoBD(fechaStr, res.value.tipo, res.value.motivo); }
            });
        }
    }

    function enviarFeriadoBD(fecha, tipo, motivo) {
        const payload = { fecha: fecha };
        if (tipo && motivo) { payload.tipo = tipo; payload.motivo = motivo; }

        fetch('../controladores/ControladorFeriados.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
        }).then(res => res.json()).then(data => {
            if (data.success) {
                Swal.fire({ title: '¡Éxito!', text: data.message || 'Actualizado.', icon: 'success', timer: 2000, showConfirmButton: false, ...parametrosTema() });
                cargarFeriados(); 
            } else {
                Swal.fire('Error', data.message || 'Error de validación', 'error');
            }
        }).catch(() => Swal.fire('Error', 'Fallo de comunicación', 'error'));
    }

    // =======================================================
    // 4. EVENT LISTENERS Y VALIDACIÓN DE FORMULARIO
    // =======================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarModalPlantillas();
            cerrarModalFeriados();
        }
    });

    const formConfiguracion = document.getElementById('formConfiguracion');
    if (formConfiguracion) {
        formConfiguracion.addEventListener('submit', function (e) {
            const inputEntrada    = document.getElementById('hora_entrada');
            const inputSalida     = document.getElementById('hora_salida');
            const inputTolerancia = document.getElementById('minutos_tolerancia');
            const tolerancia      = parseInt(inputTolerancia.value, 10);
            const t               = parametrosTema();

            function horaAMinutos(hora) {
                const [h, m] = hora.split(':').map(Number);
                return h * 60 + m;
            }

            function marcarError(campo) {
                campo.style.borderColor = '#ef4444';
                campo.style.boxShadow   = '0 0 0 3px rgba(239,68,68,0.2)';
                setTimeout(() => {
                    campo.style.borderColor = '';
                    campo.style.boxShadow   = '';
                }, 3000);
            }

            function limpiarErrores() {
                [inputEntrada, inputSalida, inputTolerancia].forEach(c => {
                    c.style.borderColor = '';
                    c.style.boxShadow   = '';
                });
            }

            limpiarErrores();

            const minEntrada = horaAMinutos(inputEntrada.value);
            const minSalida  = horaAMinutos(inputSalida.value);
            const duracion   = minSalida - minEntrada;

            if (minSalida <= minEntrada) {
                e.preventDefault();
                marcarError(inputEntrada);
                marcarError(inputSalida);
                Swal.fire({
                    title: 'Horario inválido',
                    text:  'La hora de salida debe ser posterior a la hora de entrada.',
                    icon:  'error', confirmButtonColor: '#ef4444', ...t,
                });
                return;
            }

            if (duracion < 60) {
                e.preventDefault();
                marcarError(inputEntrada);
                marcarError(inputSalida);
                Swal.fire({
                    title: 'Jornada muy corta',
                    text:  'La jornada debe ser de al menos 1 hora (60 minutos).',
                    icon:  'warning', confirmButtonColor: '#f59e0b', ...t,
                });
                return;
            }

            if (duracion > 480) {
                e.preventDefault();
                marcarError(inputEntrada);
                marcarError(inputSalida);
                Swal.fire({
                    title: 'Jornada excesiva',
                    html:  `La jornada no puede superar las <strong>8 horas</strong>.<br>Jornada actual: <strong>${duracion} min</strong>.`,
                    icon:  'warning', confirmButtonColor: '#f59e0b', ...t,
                });
                return;
            }

            if (!isNaN(tolerancia) && tolerancia >= duracion) {
                e.preventDefault();
                marcarError(inputTolerancia);
                Swal.fire({
                    title: 'Tolerancia inválida',
                    html:  `Los minutos de tolerancia (<strong>${tolerancia} min</strong>) no pueden igualar o superar la duración de la jornada (<strong>${duracion} min</strong>).`,
                    icon:  'warning', confirmButtonColor: '#f59e0b', ...t,
                });
                return;
            }
        });
    }

    // =======================================================
    // 5. EXPORTACIÓN AL SCOPE GLOBAL
    // =======================================================
    window.confirmarAplicarPlantilla    = confirmarAplicarPlantilla;
    window.guardarComoNueva             = guardarComoNueva;
    window.confirmarEliminarTarjeta     = confirmarEliminarTarjeta;
    window.activarEdicionNombre         = activarEdicionNombre;
    window.abrirModalPlantillas         = abrirModalPlantillas;
    window.cerrarModalPlantillas        = cerrarModalPlantillas;
    window.cerrarModalPlantillasSiOverlay = cerrarModalPlantillasSiOverlay;
    window.abrirModalFeriados           = abrirModalFeriados;
    window.cerrarModalFeriados          = cerrarModalFeriados;
    window.cerrarModalFeriadosSiOverlay = cerrarModalFeriadosSiOverlay;
    window.cambiarMesFeriado            = cambiarMesFeriado;

    // =======================================================
    // INICIALIZACIÓN DE LA GUÍA DINÁMICA (CONFIGURACIÓN)
    // =======================================================
    let diccionarioConfiguracion = [];

    // 1. Los inputs del formulario global
    if (document.querySelector('.grid-formulario')) {
        diccionarioConfiguracion.push({ 
            selector: '.grid-formulario', 
            titulo: 'Parámetros Globales', 
            texto: 'Establece la hora oficial de entrada, salida y los minutos de tolerancia. Esto aplicará por defecto a todo el personal.' 
        });
    }

    // 2. Botón flotante de Días Libres/Feriados
    const btnFeriados = document.querySelector('.btn-flotante-rango-modal') || document.querySelector('.btn-flotante-feriados-movil');
    if (btnFeriados) {
        diccionarioConfiguracion.push({ 
            selector: '.btn-flotante-rango-modal, .btn-flotante-feriados-movil', 
            titulo: 'Días Libres y Feriados', 
            texto: 'Haz clic aquí para abrir el calendario anual y marcar días festivos. El sistema no exigirá asistencia ni marcará faltas en estas fechas.' 
        });
    }

    // 3. Tarjetas de Plantillas Preestablecidas
    if (document.querySelectorAll('.tarjeta-preestablecida').length > 0) {
        diccionarioConfiguracion.push({ 
            selector: '.tarjeta-preestablecida', 
            titulo: 'Plantillas Rápidas', 
            texto: '¿Tienes un horario de contingencia o navideño? Haz clic en "Aplicar" para cargar esa configuración al formulario al instante. Haz doble clic en el nombre para editarlo.' 
        });
    }

    // 4. Botón de guardar como nueva plantilla
    if (document.querySelector('.tarjeta-agregar-nueva')) {
        diccionarioConfiguracion.push({ 
            selector: '.tarjeta-agregar-nueva', 
            titulo: 'Guardar como Plantilla', 
            texto: 'Toma la configuración que tienes actualmente escrita en el formulario y la guarda como una nueva plantilla reutilizable (Límite: 4).' 
        });
    }

    // 5. Botón principal de guardar
    if (document.querySelector('.btn-guardar')) {
        diccionarioConfiguracion.push({ 
            selector: '.btn-guardar', 
            titulo: 'Aplicar Configuración', 
            texto: 'Guarda los cambios y altera el reloj del sistema. Nota: Los empleados que tienen un horario personalizado configurado en su perfil no se verán afectados.' 
        });
    }

    // Instanciar el motor de la guía
    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppConfig = new window.GuiaDinamica(diccionarioConfiguracion);
    }
});