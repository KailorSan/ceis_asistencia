document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.AsistenciaConfig;

    const configHoraEntrada = config.horaEntradaSys;
    const configTolerancia = config.toleranciaSys;

    const ITEMS_POR_CARGA = 8;
    let limitePaginacionActual = ITEMS_POR_CARGA; 
    let cargoActivoGlobal = 'todos'; 

    let arrayArchivosRango = [];

    let idPersonalActual = config.miIdPersonal;
    let nombrePersonalActual = "";
    let esModoAdmin = false;
    let mesActual = new Date().getMonth() + 1;
    let anioActual = new Date().getFullYear();
    let fechaIngresoActual = ""; 

    // =======================================================
    // 2. DECLARACIÓN DE FUNCIONES (HOISTING SEGURO)
    // =======================================================

    function actualizarRelojSistema() {
        const elHora = document.getElementById('reloj-hora');
        if (!elHora) return;

        const ahora = new Date();
        let horas = ahora.getHours();
        const minutos = ahora.getMinutes().toString().padStart(2, '0');
        const ampm = horas >= 12 ? 'PM' : 'AM';

        let saludo = 'Buenas noches';
        if (horas >= 5 && horas < 12) saludo = 'Buenos días';
        else if (horas >= 12 && horas < 18) saludo = 'Buenas tardes';

        horas = horas % 12 || 12; 

        elHora.innerHTML = `${horas.toString().padStart(2, '0')}:${minutos}<span>${ampm}</span>`;
        
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        document.getElementById('reloj-fecha').textContent = `${dias[ahora.getDay()]}, ${ahora.getDate()} de ${meses[ahora.getMonth()]} de ${ahora.getFullYear()}`;
        
        document.getElementById('reloj-saludo').textContent = `${saludo}, ${config.nombreDirector}.`;

        const horaEntradaParts = configHoraEntrada.split(':');
        let fechaEntrada = new Date();
        fechaEntrada.setHours(parseInt(horaEntradaParts[0]), parseInt(horaEntradaParts[1]), 0, 0);
        
        let fechaTolerancia = new Date(fechaEntrada.getTime() + (configTolerancia * 60000));
        
        const estadoTurno = document.getElementById('texto-estatus-turno');
        const tarjetaTurno = document.getElementById('tarjeta-estado-turno');
        const iconoTurno = document.getElementById('icono-estado-turno');
        
        if (estadoTurno && tarjetaTurno && iconoTurno) {
            if (ahora < fechaEntrada) {
                estadoTurno.innerHTML = `<span style="color: #3b82f6;">Aún no inicia</span>`;
                tarjetaTurno.style.borderLeftColor = '#3b82f6';
                iconoTurno.style.color = '#3b82f6';
            } else if (ahora >= fechaEntrada && ahora <= fechaTolerancia) {
                let diffMins = Math.floor((fechaTolerancia - ahora) / 60000);
                estadoTurno.innerHTML = `<span style="color: #10b981;">Quedan ${diffMins} min</span>`;
                tarjetaTurno.style.borderLeftColor = '#10b981';
                iconoTurno.style.color = '#10b981';
            } else {
                estadoTurno.innerHTML = `<span style="color: #ef4444;">Turno cerrado</span>`;
                tarjetaTurno.style.borderLeftColor = '#ef4444';
                iconoTurno.style.color = '#ef4444';
            }
        }
    }

    function aplicarFiltroUniversal(idCargo = null, botonSeleccionado = null, reiniciarPaginacion = true) {
        if (reiniciarPaginacion) limitePaginacionActual = ITEMS_POR_CARGA;
        
        if (idCargo !== null) {
            cargoActivoGlobal = idCargo;
            document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
            if(botonSeleccionado) botonSeleccionado.classList.add('activo');
        }
        
        const inputVal = document.getElementById('buscador-universal');
        const textoBusqueda = inputVal ? inputVal.value.toLowerCase().trim() : '';
        let coincidentes = 0;
        
        document.querySelectorAll('.item-filtrable').forEach(item => {
            const coincideCargo = (cargoActivoGlobal === 'todos') || (item.getAttribute('data-cargo') == cargoActivoGlobal);
            const elNombre = item.querySelector('.nombre-empleado');
            const elCargo = item.querySelector('.cargo-empleado');
            
            const nombreStr = elNombre ? elNombre.innerText.toLowerCase() : '';
            const cargoStr = elCargo ? elCargo.innerText.toLowerCase() : '';
            const coincideTexto = nombreStr.includes(textoBusqueda) || cargoStr.includes(textoBusqueda);
            
            if (coincideCargo && coincideTexto) {
                item.classList.remove('oculto-por-filtro');
                coincidentes++;
                
                if (coincidentes > limitePaginacionActual) {
                    item.classList.add('oculto-por-paginacion');
                    item.classList.remove('animacion-aparecer');
                } else {
                    if (item.classList.contains('oculto-por-paginacion') || reiniciarPaginacion) {
                        item.classList.remove('oculto-por-paginacion', 'animacion-aparecer');
                        void item.offsetWidth; 
                        item.classList.add('animacion-aparecer');
                    }
                }
            } else {
                item.classList.add('oculto-por-filtro');
                item.classList.remove('oculto-por-paginacion', 'animacion-aparecer');
            }
        });

        const contenedorVerMas = document.getElementById('contenedor-ver-mas');
        if (contenedorVerMas) {
            contenedorVerMas.style.display = (coincidentes > limitePaginacionActual) ? 'block' : 'none';
        }
    }

    function renderizarPreviewArchivosRango() {
        const listaPreview = document.getElementById('lista_preview_archivos_rango');
        if(!listaPreview) return;
        listaPreview.innerHTML = '';
        
        arrayArchivosRango.forEach((archivo, index) => {
            const div = document.createElement('div');
            div.className = 'item-archivo-preview';
            
            let iconHtml = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width: 28px; height: 28px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>`;
            
            if (archivo.type && archivo.type.startsWith('image/')) {
                const objUrl = URL.createObjectURL(archivo);
                iconHtml = `<img src="${objUrl}" class="img-preview-mini" onload="URL.revokeObjectURL(this.src)">`;
            }

            div.innerHTML = `
                ${iconHtml}
                <span class="nombre-archivo" title="${archivo.name}">${archivo.name}</span>
                <button type="button" class="btn-eliminar-preview" onclick="eliminarArchivoRango(${index})" title="Quitar archivo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            `;
            listaPreview.appendChild(div);
        });
    }

    function eliminarArchivoRango(index) {
        arrayArchivosRango.splice(index, 1);
        renderizarPreviewArchivosRango();
    }

    function ejecutarModalRango() {
        if (!fechaIngresoActual) {
            const contenedor = document.getElementById('contenedor-calendario-modal');
            if (contenedor) fechaIngresoActual = contenedor.getAttribute('data-fecha-ingreso') || "";
        }

        if (fechaIngresoActual) {
            const hoy = new Date().toISOString().slice(0,10);
            if (fechaIngresoActual > hoy) {
                Swal.fire('Aviso', 'La fecha de ingreso del empleado es futura. No se puede procesar justificación.', 'warning');
                return;
            }
        }

        cerrarModal(); 
        
        setTimeout(() => {
            const inputId = document.getElementById('rango_id_personal');
            const inputNombre = document.getElementById('rango_nombre_personal');
            if (inputId && inputNombre) {
                inputId.value = idPersonalActual;
                inputNombre.value = nombrePersonalActual;
            }
            document.getElementById('modalRango').classList.add('activo');
            document.getElementById('contenidoModalRango').classList.add('activo');
        }, 150);
    }

    function cerrarModalRango() {
        const modal = document.getElementById('modalRango');
        const contenido = document.getElementById('contenidoModalRango');
        const form = document.getElementById('formJustificacionRango');
        
        if (modal) modal.classList.remove('activo');
        if (contenido) contenido.classList.remove('activo');
        if (form) form.reset();
        
        document.querySelectorAll('.input-modal-rango').forEach(el => el.classList.remove('input-error', 'sacudir'));
        document.querySelectorAll('.error-inline').forEach(el => el.style.display = 'none');
        
        arrayArchivosRango = [];
        renderizarPreviewArchivosRango();
    }

    function procesarEnvioJustificacionRango(e) {
        e.preventDefault();
        
        const inpInicio = document.getElementById('rango_fecha_inicio');
        const inpFin = document.getElementById('rango_fecha_fin');
        const inpMotivo = document.getElementById('rango_motivo');
        
        const errInicio = document.getElementById('err_rango_inicio');
        const errFin = document.getElementById('err_rango_fin');
        const errMotivo = document.getElementById('err_rango_motivo');

        [inpInicio, inpFin, inpMotivo].forEach(el => el.classList.remove('input-error', 'sacudir'));
        [errInicio, errFin, errMotivo].forEach(el => el.style.display = 'none');

        let hasError = false;

        if (!inpInicio.value) {
            inpInicio.classList.add('input-error', 'sacudir');
            errInicio.style.display = 'block';
            hasError = true;
        }
        
        if (!inpFin.value) {
            inpFin.classList.add('input-error', 'sacudir');
            errFin.innerText = 'Seleccione una fecha final.';
            errFin.style.display = 'block';
            hasError = true;
        } else if (inpInicio.value && inpFin.value < inpInicio.value) {
            inpFin.classList.add('input-error', 'sacudir');
            errFin.innerText = 'La fecha fin no puede ser anterior al inicio.';
            errFin.style.display = 'block';
            hasError = true;
        }

        if (!inpMotivo.value || inpMotivo.value.trim().length < 10) {
            inpMotivo.classList.add('input-error', 'sacudir');
            errMotivo.style.display = 'block';
            hasError = true;
        }

        if (hasError) return;

        const formData = new FormData();
        formData.append('id_personal', document.getElementById('rango_id_personal').value);
        formData.append('fecha_inicio', inpInicio.value);
        formData.append('fecha_fin', inpFin.value);
        formData.append('motivo', inpMotivo.value);
        
        arrayArchivosRango.forEach((archivo) => {
            formData.append('evidencias[]', archivo);
        });

        Swal.fire({
            title: 'Procesando...',
            text: 'Validando archivos y registrando en el sistema...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); },
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
        });

        fetch('../controladores/ControladorJustificacionRango.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Completado!',
                    text: data.msg,
                    icon: 'success',
                    confirmButtonColor: '#10b981',
                    background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                    color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                }).then(() => {
                    cerrarModalRango();
                    location.reload(); 
                });
            } else {
                Swal.fire('Error de Validación', data.msg, 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error de Conexión', 'Hubo un problema al procesar la solicitud.', 'error');
        });
    }

    function abrirCalendario(idPersonal, nombre, modoEdicion) {
        idPersonalActual = idPersonal;
        nombrePersonalActual = nombre;
        esModoAdmin = modoEdicion;
        mesActual = new Date().getMonth() + 1;
        anioActual = new Date().getFullYear();
        
        const titulo = document.getElementById('titulo_modal_calendario');
        if (titulo) titulo.innerText = 'Asistencia: ' + nombre;
        
        const btnFab = document.getElementById('btnFlotanteRangoModal');
        if(btnFab) btnFab.style.display = modoEdicion ? 'flex' : 'none';

        document.getElementById('modalOverlay').classList.add('activo');
        document.getElementById('modalCalendario').classList.add('activo');
        
        cargarCalendarioHtml('contenedor-calendario-modal');
    }

    function cerrarModal() {
        const overlay = document.getElementById('modalOverlay');
        const modal = document.getElementById('modalCalendario');
        if(overlay) overlay.classList.remove('activo');
        if(modal) modal.classList.remove('activo');
    }

    function cargarCalendarioHtml(idContenedor) {
        const contenedor = document.getElementById(idContenedor);
        if (!contenedor) return;
        
        contenedor.innerHTML = '<div style="text-align:center; padding: 40px;"><svg class="animacion-vibrar" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--primary-color)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p>Cargando fechas...</p></div>';

        fetch(`../controladores/ControladorCalendario.php?id=${idPersonalActual}&mes=${mesActual}&anio=${anioActual}&admin=${esModoAdmin}&contenedor=${idContenedor}`)
            .then(response => response.text())
            .then(html => {
                contenedor.innerHTML = html;
                const contPrincipal = document.getElementById(idContenedor);
                if (contPrincipal) {
                    fechaIngresoActual = contPrincipal.getAttribute('data-fecha-ingreso') || "";
                }
            })
            .catch(() => {
                contenedor.innerHTML = '<p style="color:red; text-align:center;">Error al cargar el calendario.</p>';
            });
    }

    function cambiarMes(direccion, idContenedor) {
        mesActual += direccion;
        if (mesActual > 12) { mesActual = 1; anioActual++; }
        if (mesActual < 1) { mesActual = 12; anioActual--; }
        cargarCalendarioHtml(idContenedor);
    }
    
    function parseTime12to24(time12) {
        if (!time12 || time12 === '--:--' || time12 === 'Sin marcar') return '';
        const partes = time12.split(' ');
        if (partes.length < 2) return '';
        
        let time = partes[0];
        let modifier = partes[1];
        let timeSplit = time.split(':');
        
        let hours = timeSplit[0];
        let minutes = timeSplit[1];
        
        if (hours === '12') hours = '00';
        if (modifier.toUpperCase() === 'PM') hours = parseInt(hours, 10) + 12;
        
        return `${hours.toString().padStart(2, '0')}:${minutes}`;
    }

    function editarDia(fechaBD, fechaVisual, estadoActual, motivo, archivo, horaEntrada, horaSalida) {
        if (!esModoAdmin) return;
        
        if (fechaIngresoActual && fechaBD < fechaIngresoActual) {
            Swal.fire('Operación no permitida', 'No se puede modificar una fecha anterior a la fecha de ingreso del empleado.', 'error');
            return;
        }

        const hoy = new Date().toISOString().slice(0,10);
        const esFuturo = fechaBD > hoy;

        let estadoPrimario = estadoActual;
        let estadoSecundario = '';

        if (estadoActual && (estadoActual.includes('Feriado') || estadoActual.includes('Día Libre'))) {
            estadoPrimario = 'Feriado';
        } else if (estadoActual && estadoActual.includes(' y ')) {
            const partes = estadoActual.split(' y ');
            estadoPrimario = partes[0].trim();
            estadoSecundario = partes[1].trim();
        }

        let opcionesEstado = esFuturo ? `
            <option value="Justificado" selected>Justificado (Gris)</option>
            <option value="Feriado" ${estadoPrimario === 'Feriado' ? 'selected' : ''}>Feriado (Morado)</option>
            <option value="Eliminar">Eliminar Registro (Deshacer Justificación)</option>
        ` : `
            <option value="Puntual" ${estadoPrimario === 'Puntual' ? 'selected' : ''}>Puntual (Verde)</option>
            <option value="Retraso" ${estadoPrimario === 'Retraso' ? 'selected' : ''}>Retraso (Naranja)</option>
            <option value="Salida Irregular" ${estadoPrimario === 'Salida Irregular' ? 'selected' : ''}>Salida Irregular (Rojo Oscuro)</option>
            <option value="Justificado" ${estadoPrimario === 'Justificado' ? 'selected' : ''}>Justificado (Gris)</option>
            <option value="Falta" ${estadoPrimario.includes('Falta') ? 'selected' : ''}>Falta (Rojo)</option>
            <option value="Feriado" ${estadoPrimario === 'Feriado' ? 'selected' : ''}>Feriado (Morado)</option>
        `;

        let enlaceEvidencia = '';
        if (archivo && archivo !== 'undefined' && archivo !== 'null') {
            enlaceEvidencia = `
                <div style="margin-block-end: 15px; text-align: start; background: var(--bg-light); padding: 10px; border-radius: 8px;">
                    <span style="font-size:0.85rem; color:var(--text-color); display:block; margin-block-end:5px;">Evidencia adjunta:</span>
                    <a href="../recursos/evidencias/${archivo}" target="_blank" style="color: var(--primary-color); font-weight: bold; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        Ver Documento / Imagen
                    </a>
                </div>
            `;
        }

        let bloqueHoras = '';
        if (!esFuturo) {
            const valEntrada = parseTime12to24(horaEntrada);
            const valSalida = parseTime12to24(horaSalida);
            
            bloqueHoras = `
                <div style="display: flex; gap: 15px; background: var(--bg-light); padding: 12px 15px; border-radius: 8px; margin-block-end: 2px; border: 1px solid rgba(0,0,0,0.05);">
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-color); text-transform: uppercase;">Hora Entrada:</label>
                        <input type="time" id="swal-hora-entrada" value="${valEntrada}" style="inline-size: 100%; padding: 8px; border-radius: 8px; border: 2px solid #ccc; font-family: 'Montserrat', sans-serif; outline: none; margin-block-start: 4px; background: var(--navbar-bg); color: var(--text-color); transition: all 0.3s ease;">
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 0.75rem; font-weight: 800; color: var(--text-color); text-transform: uppercase;">Hora Salida:</label>
                        <input type="time" id="swal-hora-salida" value="${valSalida}" style="inline-size: 100%; padding: 8px; border-radius: 8px; border: 2px solid #ccc; font-family: 'Montserrat', sans-serif; outline: none; margin-block-start: 4px; background: var(--navbar-bg); color: var(--text-color); transition: all 0.3s ease;">
                    </div>
                </div>
                <div id="swal-error-tiempo" style="color: #ef4444; font-size: 0.8rem; font-weight: 600; display: none; margin-block-end: 15px; text-align: start;"></div>
            `;
        }

        Swal.fire({
            title: esFuturo ? 'Gestionar Justificación Futura' : 'Modificar Asistencia',
            html: `
                <p style="margin-block-end:10px; font-weight:bold; color:var(--text-color); font-size:1.1rem;">Fecha: <span style="color: var(--primary-color);">${fechaVisual}</span></p>
                
                ${bloqueHoras}
                
                <div style="text-align: start; margin-block-end: 5px; margin-block-start: ${esFuturo ? '0' : '15px'};">
                    <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color);">Estado Principal:</label>
                </div>
                <select id="swal-estado" style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:5px; border:2px solid #ccc; outline:none; font-family:'Montserrat'; background: var(--bg-light); color: var(--text-color);">
                    ${opcionesEstado}
                </select>

                <div style="text-align: end; margin-block-end: 15px; display: ${esFuturo ? 'none' : 'block'};">
                    <button type="button" id="btn-add-secundaria" style="background: none; border: none; color: var(--primary-color); font-weight: bold; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: transform 0.2s;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Añadir Incidencia Secundaria
                    </button>
                </div>

                <div id="caja-secundaria" style="display: ${estadoSecundario && !esFuturo ? 'block' : 'none'}; background: var(--bg-light); padding: 10px; border-radius: 8px; margin-block-end: 15px; border: 1px solid var(--primary-color);">
                    <div style="text-align: start; margin-block-end: 5px;">
                        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color);">Segunda Incidencia:</label>
                    </div>
                    <select id="swal-estado-secundario" style="inline-size:100%; padding:10px; border-radius:8px; border:2px solid #ccc; outline:none; font-family:'Montserrat'; background: var(--navbar-bg); color: var(--text-color);">
                        <option value="">Ninguna</option>
                        <option value="Salida Temprana" ${estadoSecundario === 'Salida Temprana' ? 'selected' : ''}>Salida Temprana</option>
                        <option value="Salida Irregular" ${estadoSecundario === 'Salida Irregular' ? 'selected' : ''}>Salida Irregular</option>
                    </select>
                </div>

                <textarea id="swal-motivo" placeholder="${esFuturo ? 'Motivo de la justificación (Mín. 10 caracteres)...' : 'Escriba un motivo o nota (Mín. 10 caracteres)...'}" style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:2px; border:2px solid #ccc; min-block-size:80px; font-family:'Montserrat'; outline:none; background: var(--bg-light); color: var(--text-color); transition: all 0.3s ease;">${motivo}</textarea>
                
                <div id="swal-error-motivo" style="color: #ef4444; font-size: 0.8rem; font-weight: 600; display: none; margin-block-end: 15px; text-align: start;">El motivo debe tener al menos 10 caracteres.</div>

                ${enlaceEvidencia}
                
                <div style="text-align: start; margin-block-start: 10px; overflow: hidden; display: ${esFuturo ? 'none' : 'block'};">
                    <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-color); display: block; margin-block-end: 8px;">${archivo ? 'Reemplazar evidencia (Opcional):' : 'Subir evidencia (Opcional):'}</label>
                    <div style="position: relative; display: block; inline-size: 100%;">
                        <input type="file" id="swal-archivo" accept=".pdf, .jpg, .jpeg, .png" style="position: absolute; inset-inline-start: -9999px;">
                        <label for="swal-archivo" style="display: flex; align-items: center; gap: 10px; padding: 12px 15px; background-color: var(--bg-light); border: 2px dashed var(--primary-color); border-radius: 10px; color: var(--text-color); font-weight: 600; cursor: pointer; transition: all 0.3s ease; inline-size: 100%; box-sizing: border-box;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="inline-size: 24px; block-size: 24px; color: var(--primary-color); flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span id="texto-swal-archivo" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display:block; inline-size:100%; text-align:start;">Seleccionar archivo...</span>
                        </label>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Cambios',
            confirmButtonColor: '#406ff3',
            cancelButtonText: 'Cancelar',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333',
            
            didOpen: () => {
                const selectPrincipal = document.getElementById('swal-estado');
                const btnSecundaria = document.getElementById('btn-add-secundaria');
                const cajaSecundaria = document.getElementById('caja-secundaria');
                const selectSecundario = document.getElementById('swal-estado-secundario');
                
                const textareaMotivo = document.getElementById('swal-motivo');
                const errorMotivo = document.getElementById('swal-error-motivo');

                const inputEntrada = document.getElementById('swal-hora-entrada');
                const inputSalida = document.getElementById('swal-hora-salida');
                const errorTiempo = document.getElementById('swal-error-tiempo');

                function validarHorasProactivas() {
                    if (!inputEntrada || !inputSalida) return true;
                    
                    const vEntrada = inputEntrada.value;
                    const vSalida = inputSalida.value;

                    inputSalida.style.borderColor = '#ccc';
                    inputSalida.style.backgroundColor = 'var(--navbar-bg)';
                    errorTiempo.style.display = 'none';

                    if (vEntrada && vSalida) {
                        const [hE, mE] = vEntrada.split(':').map(Number);
                        const [hS, mS] = vSalida.split(':').map(Number);
                        
                        const minEntrada = (hE * 60) + mE;
                        const minSalida = (hS * 60) + mS;

                        if (minSalida < minEntrada) {
                            inputSalida.style.borderColor = '#ef4444';
                            inputSalida.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
                            errorTiempo.innerText = 'La hora de salida no puede ser anterior a la entrada.';
                            errorTiempo.style.display = 'block';
                            return false;
                        } 
                        else if ((minSalida - minEntrada) > 480) {
                            inputSalida.style.borderColor = '#ef4444';
                            inputSalida.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
                            errorTiempo.innerText = 'La jornada no puede exceder el límite de 8 horas.';
                            errorTiempo.style.display = 'block';
                            return false;
                        }
                    }
                    return true;
                }

                if (inputEntrada) inputEntrada.addEventListener('input', validarHorasProactivas);
                if (inputSalida) inputSalida.addEventListener('input', validarHorasProactivas);

                textareaMotivo.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (val.length > 0 && val.length < 10) {
                        this.style.borderColor = '#ef4444';
                        this.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
                        errorMotivo.style.display = 'block';
                    } else {
                        this.style.borderColor = '#ccc';
                        this.style.backgroundColor = 'var(--bg-light)';
                        errorMotivo.style.display = 'none';
                    }
                });

                if (!esFuturo) {
                    document.getElementById('swal-archivo').addEventListener('change', function(e) {
                        const name = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                        document.getElementById('texto-swal-archivo').textContent = name;
                    });
                }

                function evaluarBloqueosUI() {
                    const estado = selectPrincipal.value;
                    
                    if (estado === 'Eliminar') {
                        textareaMotivo.style.display = 'none';
                        errorMotivo.style.display = 'none';
                        btnSecundaria.style.display = 'none';
                        cajaSecundaria.style.display = 'none';
                        selectSecundario.value = '';
                    } else {
                        textareaMotivo.style.display = 'block';
                        if(textareaMotivo.value.trim().length > 0 && textareaMotivo.value.trim().length < 10) {
                            errorMotivo.style.display = 'block';
                        }
                        
                        if (esFuturo) {
                            btnSecundaria.style.display = 'none';
                        } else {
                            if (estado.includes('Falta') || estado === 'Salida Irregular' || estado === 'Feriado') {
                                btnSecundaria.style.display = 'none';
                                cajaSecundaria.style.display = 'none';
                                selectSecundario.value = '';
                            } else {
                                btnSecundaria.style.display = 'inline-flex';
                            }
                        }
                    }
                }

                selectPrincipal.addEventListener('change', evaluarBloqueosUI);
                evaluarBloqueosUI(); 

                if(btnSecundaria) {
                    btnSecundaria.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (cajaSecundaria.style.display === 'none') {
                            cajaSecundaria.style.display = 'block';
                            selectSecundario.value = 'Salida Temprana'; 
                            btnSecundaria.style.color = '#ef4444';
                            btnSecundaria.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> Quitar Incidencia Secundaria';
                        } else {
                            cajaSecundaria.style.display = 'none';
                            selectSecundario.value = '';
                            btnSecundaria.style.color = 'var(--primary-color)';
                            btnSecundaria.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> Añadir Incidencia Secundaria';
                        }
                    });
                }
            },
            preConfirm: () => {
                const estadoSeleccionado = document.getElementById('swal-estado').value;
                const estadoSecundarioSeleccionado = (estadoSeleccionado === 'Feriado' || estadoSeleccionado === 'Eliminar') ? '' : document.getElementById('swal-estado-secundario').value;
                const motivoVal = document.getElementById('swal-motivo').value.trim();

                if (estadoSeleccionado !== 'Eliminar' && motivoVal.length > 0 && motivoVal.length < 10) {
                    Swal.showValidationMessage('El motivo debe tener al menos 10 caracteres.');
                    return false;
                }

                let nuevaEntrada = '';
                let nuevaSalida = '';
                if (!esFuturo) {
                    const inputE = document.getElementById('swal-hora-entrada');
                    const inputS = document.getElementById('swal-hora-salida');
                    nuevaEntrada = inputE.value;
                    nuevaSalida = inputS.value;

                    if (nuevaEntrada && nuevaSalida) {
                        const [hE, mE] = nuevaEntrada.split(':').map(Number);
                        const [hS, mS] = nuevaSalida.split(':').map(Number);
                        const minE = (hE * 60) + mE;
                        const minS = (hS * 60) + mS;

                        if (minS < minE) {
                            Swal.showValidationMessage('La hora de salida no puede ser anterior a la entrada.');
                            return false;
                        }
                        if ((minS - minE) > 480) {
                            Swal.showValidationMessage('La jornada no puede exceder las 8 horas.');
                            return false;
                        }
                    }
                }

                let archivoVal = null;
                if (!esFuturo && document.getElementById('swal-archivo')) {
                    archivoVal = document.getElementById('swal-archivo').files[0];
                }

                return {
                    fecha: fechaBD,
                    estado: estadoSeleccionado,
                    estado_secundario: estadoSecundarioSeleccionado,
                    motivo: motivoVal,
                    archivo: archivoVal,
                    hora_entrada: nuevaEntrada,
                    hora_salida: nuevaSalida
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id_personal', idPersonalActual); 
                formData.append('fecha', result.value.fecha);
                formData.append('estado', result.value.estado);
                formData.append('estado_secundario', result.value.estado_secundario);
                formData.append('motivo', result.value.motivo);
                
                if (result.value.hora_entrada !== undefined) {
                    formData.append('hora_entrada', result.value.hora_entrada);
                    formData.append('hora_salida', result.value.hora_salida);
                }
                
                if (result.value.archivo) {
                    formData.append('archivo', result.value.archivo);
                }

                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); },
                    background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                    color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                });

                fetch('../controladores/ControladorModificarAsistencia.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Actualizado!',
                            text: data.msg,
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                        }).then(() => {
                            cargarCalendarioHtml('contenedor-calendario-modal');
                        });
                    } else {
                        Swal.fire('Error', data.msg, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error de Conexión', 'Hubo un problema al contactar al servidor.', 'error');
                });
            }
        });
    }

    function inicializarSwipeCalendario() {
        let touchstartX = 0;
        let touchendX = 0;

        function handleGesture(contenedorId) {
            const threshold = 50; 
            
            const btnPrev = document.querySelector(`#${contenedorId} .controles-calendario button[onclick*="-1"]`);
            const btnNext = document.querySelector(`#${contenedorId} .controles-calendario button[onclick*="1"]`);

            if (touchendX < touchstartX - threshold && btnNext) {
                cambiarMes(1, contenedorId);
            }
            if (touchendX > touchstartX + threshold && btnPrev) {
                cambiarMes(-1, contenedorId);
            }
        }

        function bindSwipe(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('touchstart', e => {
                    touchstartX = e.changedTouches[0].screenX;
                }, {passive: true});

                el.addEventListener('touchend', e => {
                    touchendX = e.changedTouches[0].screenX;
                    handleGesture(id);
                }, {passive: true});
            }
        }

        bindSwipe('contenedor-calendario-modal');
        bindSwipe('contenedor-calendario-inline');
    }

    function inicializarTooltipCalendario() {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip-asistencia';
        document.body.appendChild(tooltip);

        document.addEventListener('mouseover', function(e) {
            const celda = e.target.closest('.dia-celda');
            
            if (!celda || celda.classList.contains('vacio')) return;

            const estado = celda.getAttribute('data-estado');
            if (!estado) return;

            const entrada = celda.getAttribute('data-entrada');
            const salida = celda.getAttribute('data-salida');

            const textoEntrada = (entrada && entrada !== 'null' && entrada !== '') ? entrada : '--:--';
            const textoSalida = (salida && salida !== 'null' && salida !== '') ? salida : '--:--';

            tooltip.innerHTML = `
                <div class="tooltip-header">${estado}</div>
                <div class="tooltip-body">
                    <p><strong>Entrada:</strong> <span>${textoEntrada}</span></p>
                    <p><strong>Salida:</strong> <span>${textoSalida}</span></p>
                </div>
            `;

            tooltip.classList.add('visible');

            const rect = celda.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px'; 
        });

        document.addEventListener('mouseout', function(e) {
            if (e.target.closest('.dia-celda')) {
                tooltip.classList.remove('visible');
            }
        });
        
        document.addEventListener('scroll', function() {
            tooltip.classList.remove('visible');
        }, true);
    }

    function configurarListenersUI() {
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', (e) => {
                e.preventDefault();
                const html = document.documentElement;
                const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_' + config.idUsuario, nuevoTema);
            });
        }

        const inputBuscador = document.getElementById('buscador-universal');
        if (inputBuscador) {
            inputBuscador.addEventListener('input', () => aplicarFiltroUniversal(null, null, true));
        }

        const btnVerMas = document.getElementById('btn-ver-mas');
        if (btnVerMas) {
            btnVerMas.addEventListener('click', () => {
                limitePaginacionActual += ITEMS_POR_CARGA;
                aplicarFiltroUniversal(cargoActivoGlobal, document.querySelector('.btn-filtro.activo'), false); 
            });
        }

        const inputEvidenciasRango = document.getElementById('rango_evidencias');
        if (inputEvidenciasRango) {
            inputEvidenciasRango.addEventListener('change', (e) => {
                const nuevosArchivos = Array.from(e.target.files);
                arrayArchivosRango = arrayArchivosRango.concat(nuevosArchivos);
                renderizarPreviewArchivosRango();
                inputEvidenciasRango.value = ''; 
            });
        }

        const formRango = document.getElementById('formJustificacionRango');
        if (formRango) {
            formRango.addEventListener('submit', procesarEnvioJustificacionRango);
        }
    }

    // =======================================================
    // 3. EXPORTACIÓN AL SCOPE GLOBAL (PARA BOTONES HTML)
    // =======================================================
    window.aplicarFiltroUniversal = aplicarFiltroUniversal;
    window.eliminarArchivoRango   = eliminarArchivoRango;
    window.ejecutarModalRango     = ejecutarModalRango;
    window.cerrarModalRango       = cerrarModalRango;
    window.abrirCalendario        = abrirCalendario;
    window.cerrarModal            = cerrarModal;
    window.cambiarMes             = cambiarMes;
    window.editarDia              = editarDia;

    // =======================================================
    // 4. INICIALIZACIÓN
    // =======================================================
    if (document.getElementById('reloj-hora')) {
        setInterval(actualizarRelojSistema, 1000);
        actualizarRelojSistema();
    }

    aplicarFiltroUniversal(null, null, true);
    
    if (!config.esAdmin) {
        cargarCalendarioHtml('contenedor-calendario-inline');
    }

    configurarListenersUI();
    inicializarTooltipCalendario();
    inicializarSwipeCalendario();

    // =======================================================
    // 5. INICIALIZACIÓN DE LA GUÍA DINÁMICA (ASISTENCIA)
    // =======================================================
    // =======================================================
    // 5. INICIALIZACIÓN DE LA GUÍA DINÁMICA (ASISTENCIA)
    // =======================================================
    let diccionarioAsistencia = [];

    if (config.esAdmin) {
        // --- GUÍA PARA DIRECTIVOS Y ADMINS ---
        
        if (document.querySelector('.reloj-widget')) {
            diccionarioAsistencia.push({ 
                selector: '.reloj-widget', 
                titulo: 'Reloj de Sistema', 
                texto: 'Sincronizado con el servidor, determina los minutos restantes del periodo de tolerancia para el registro del personal.' 
            });
        }
        
        // CORRECCIÓN: Resaltamos las tarjetas individualmente en lugar de su contenedor cuadrado
        if (document.querySelectorAll('.stat-tarjeta').length > 0) {
            diccionarioAsistencia.push({ 
                selector: '.stat-tarjeta', 
                titulo: 'Monitoreo en Tiempo Real', 
                texto: 'Te muestra alertas críticas: cuántos empleados faltan por llegar y cuántas justificaciones están esperando tu revisión.' 
            });
        }

        // CORRECCIÓN: Resaltamos el input redondeado en lugar de la caja que lo envuelve
        if (document.querySelector('.campo-busqueda-elegante')) {
            diccionarioAsistencia.push({ 
                selector: '.campo-busqueda-elegante', 
                titulo: 'Buscador Inteligente', 
                texto: 'Encuentra empleados rápidamente escribiendo su nombre, apellido o cargo sin tener que recargar la página.' 
            });
        }

        // CORRECCIÓN: Resaltamos cada "píldora" de filtro de forma individual
        if (document.querySelectorAll('.btn-filtro').length > 0) {
            diccionarioAsistencia.push({ 
                selector: '.btn-filtro', 
                titulo: 'Filtros por Cargo', 
                texto: 'Aísla la vista para revisar solo a los obreros, docentes o administrativos con un solo clic.' 
            });
        }

        if (document.querySelector('.perfil-widget-min .btn-editar-horario')) {
            diccionarioAsistencia.push({ 
                selector: '.perfil-widget-min .btn-editar-horario', 
                titulo: 'Mi Calendario Personal', 
                texto: 'Abre tu propio calendario para consultar tus entradas y salidas. Como administrador, no puedes alterar tu propio récord de asistencia.' 
            });
        }

        if (document.querySelector('.grid-perfiles .btn-editar-horario')) {
            diccionarioAsistencia.push({ 
                selector: '.grid-perfiles .btn-editar-horario', 
                titulo: 'Gestionar Récord del Personal', 
                texto: 'Abre el calendario interactivo de este empleado para modificar sus marcajes, aprobar justificaciones o añadir permisos por rango de días.' 
            });
        }

    } else {
        // --- GUÍA PARA USUARIOS COMUNES (PERSONAL) ---
        
        // CORRECCIÓN: En lugar de resaltar todo el contenedor, iluminamos las celdas de los días
        if (document.querySelectorAll('.dia-celda').length > 0) {
            diccionarioAsistencia.push({ 
                selector: '.dia-celda', 
                titulo: 'Mi Récord Diario', 
                texto: 'Si haces clic (o tocas) sobre cualquier día laborable, podrás ver a qué hora exacta registraste tu entrada y salida.' 
            });
        }
            
        if (document.querySelector('.controles-calendario')) {
            diccionarioAsistencia.push({ 
                selector: '.controles-calendario', 
                titulo: 'Navegación de Meses', 
                texto: 'Usa estas flechas para retroceder y revisar tu historial de puntualidad de meses anteriores.' 
            });
        }
    }

    // Arrancamos el motor de la guía si el JS global está cargado
    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppAsistencia = new window.GuiaDinamica(diccionarioAsistencia);
    }
});