document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.ReportesConfig;
    const periodosActivos = config.periodosActivos;
    const esDirectivo = config.esDirectivo;
    
    const selectAnio = document.getElementById('anio_global');
    const selectMes = document.getElementById('mes_global');
    const nombresMeses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    
    const html = document.documentElement;
    let chartInstancia = null;
    
    const ITEMS_POR_CARGA = 8;
    let limiteActual = ITEMS_POR_CARGA;
    let cargoActivo = 'todos'; 
    const inputBuscador = document.getElementById('buscador-empleados');

    // =======================================================
    // 2. DECLARACIÓN DE FUNCIONES
    // =======================================================

    /** Actualiza el selector de meses según el año elegido.**/
     
    function actualizarMesesDisponibles() {
        if (!selectAnio || !selectMes) return;
        const anio = selectAnio.value;
        const mesesDelAnio = periodosActivos[anio] || [];
        selectMes.innerHTML = '<option value="todos" style="font-weight:bold; color:var(--primary-color);">Todo el Año</option>';
        
        if (mesesDelAnio.length > 0) {
            mesesDelAnio.sort((a,b) => a - b);
            mesesDelAnio.forEach(mesNum => {
                const opt = document.createElement('option');
                opt.value = mesNum; opt.textContent = nombresMeses[mesNum];
                selectMes.appendChild(opt);
            });
            selectMes.value = mesesDelAnio[mesesDelAnio.length - 1]; // Seleccionar el más reciente
        } else {
            selectMes.innerHTML += '<option value="" disabled>Sin datos</option>';
        }
    }

    /** Aplica filtros de búsqueda y cargos a las tarjetas de personal.**/
     
    function aplicarFiltrosCombinados(idCargo = null, botonSeleccionado = null, reiniciarPaginacion = true) {
        if (!esDirectivo) return;

        if (reiniciarPaginacion) {
            limiteActual = ITEMS_POR_CARGA;
        }

        if (idCargo !== null) {
            cargoActivo = idCargo;
            document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
            if(botonSeleccionado) botonSeleccionado.classList.add('activo');
        }
        
        const textoBusqueda = inputBuscador ? inputBuscador.value.toLowerCase().trim() : '';
        let coincidentes = 0;

        document.querySelectorAll('.item-filtrable').forEach(tarjeta => {
            const coincideCargo = cargoActivo === 'todos' || tarjeta.getAttribute('data-cargo') == cargoActivo;
            const nombre = tarjeta.querySelector('.nombre-empleado').innerText.toLowerCase();
            const cargo = tarjeta.querySelector('.cargo-empleado').innerText.toLowerCase();
            const coincideTexto = nombre.includes(textoBusqueda) || cargo.includes(textoBusqueda);
            
            if (coincideCargo && coincideTexto) {
                tarjeta.classList.remove('oculto-por-filtro');
                coincidentes++;
                
                if (coincidentes > limiteActual) {
                    tarjeta.classList.add('oculto-por-paginacion');
                    tarjeta.classList.remove('animacion-aparecer');
                } else {
                    if (tarjeta.classList.contains('oculto-por-paginacion')) {
                        tarjeta.classList.remove('oculto-por-paginacion');
                        void tarjeta.offsetWidth;
                        tarjeta.classList.add('animacion-aparecer');
                    } else if (reiniciarPaginacion) {
                        tarjeta.classList.remove('animacion-aparecer');
                        void tarjeta.offsetWidth; 
                        tarjeta.classList.add('animacion-aparecer');
                    }
                }
            } else {
                tarjeta.classList.add('oculto-por-filtro');
                tarjeta.classList.remove('oculto-por-paginacion');
                tarjeta.classList.remove('animacion-aparecer');
            }
        });

        const contenedorVerMas = document.getElementById('contenedor-ver-mas-reportes');
        if (contenedorVerMas) {
            contenedorVerMas.style.display = (coincidentes > limiteActual) ? 'block' : 'none';
        }
    }

    /** Abre el modal de resumen, hace fetch de los datos y renderiza el gráfico.**/
    
    function abrirResumen(idPersonal, nombre, cargo) {
        document.getElementById('modalOverlayResumen').classList.add('activo');
        document.getElementById('modalContenidoResumen').classList.add('activo'); 
        document.getElementById('resumen-nombre').innerText = nombre;
        document.getElementById('resumen-cargo').innerText = cargo;
        
        const mes = selectMes.value;
        const anio = selectAnio.value;
        const nombreMes = selectMes.options[selectMes.selectedIndex].text;
        
        document.getElementById('resumen-periodo').innerText = `Período: ${nombreMes} ${anio}`;
        
        // Reset de visibilidad inicial
        document.getElementById('datos-resumen').style.display = 'none';
        document.getElementById('contenedor-grafico-resumen').style.display = 'none';
        document.getElementById('cargando-resumen').style.display = 'block';
        document.getElementById('btn-info-feriados').style.display = 'none';
        document.getElementById('tarjeta-info-feriados').classList.remove('activa');

        // Resetear o buscar el mensaje vacío
        let msgVacio = document.getElementById('mensaje-vacio-resumen');
        if (msgVacio) msgVacio.style.display = 'none';

        fetch(`../controladores/ControladorResumenMensual.php?id=${idPersonal}&mes=${mes}&anio=${anio}`)
            .then(async response => {
                const texto = await response.text(); 
                try { return JSON.parse(texto); } 
                catch (err) { throw new Error("Error del servidor."); }
            })
            .then(data => {
                if(data.error) throw new Error(data.error);
                
                const totalRegistros = data.puntual + data.retraso + data.salida_temprana + 
                                       data.salida_irregular + data.falta + data.justificado;

                const contenedorCargando = document.getElementById('cargando-resumen');
                const contenedorDatos = document.getElementById('datos-resumen');
                const contenedorGrafico = document.getElementById('contenedor-grafico-resumen');

                let msgVacio = document.getElementById('mensaje-vacio-resumen');
                if (!msgVacio) {
                    msgVacio = document.createElement('div');
                    msgVacio.id = 'mensaje-vacio-resumen';
                    msgVacio.className = 'mensaje-fin-semana'; 
                    msgVacio.style.margin = '20px 0';
                    contenedorDatos.parentNode.insertBefore(msgVacio, contenedorGrafico);
                }

                contenedorCargando.style.display = 'none';

                // 3. Renderizado Condicional: Si no hay registros en absoluto
                if (totalRegistros === 0) {
                    // Ocultar gráficas y mostrar mensaje elegante
                    contenedorDatos.style.display = 'none';
                    contenedorGrafico.style.display = 'none';
                    msgVacio.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 10px auto; color: #94a3b8; display: block;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Este usuario no tiene registros en este mes.
                    `;
                    msgVacio.style.display = 'block';
                    document.getElementById('btn-info-feriados').style.display = 'none';
                } else {
                    // Si hay datos, ocultar mensaje y pintar gráficas
                    msgVacio.style.display = 'none';
                    contenedorDatos.style.display = 'grid';
                    contenedorGrafico.style.display = 'flex';

                    // Llenar tarjetas de resumen numéricas
                    document.getElementById('num-puntual').innerText = data.puntual;
                    document.getElementById('num-retraso').innerText = data.retraso;
                    document.getElementById('num-salida-temp').innerText = data.salida_temprana;
                    document.getElementById('num-salida-irreg').innerText = data.salida_irregular;
                    document.getElementById('num-falta').innerText = data.falta;
                    document.getElementById('num-justificado').innerText = data.justificado;
                    
                    // Manejo del contador de días feriados omitidos
                    if (data.feriados_omitidos && data.feriados_omitidos > 0) {
                        document.getElementById('cantidad-feriados-omitidos').innerText = data.feriados_omitidos;
                        document.getElementById('btn-info-feriados').style.display = 'block';
                    }
                    
                    // Renderizar Chart.js
                    if(chartInstancia) { chartInstancia.destroy(); } 
                    const ctx = document.getElementById('miGraficoDona').getContext('2d');
                    chartInstancia = new Chart(ctx, {
                        type: 'doughnut', 
                        data: {
                            labels: ['Puntuales', 'Retrasos', 'Salidas Tempranas', 'Salidas Irregulares', 'Faltas', 'Justificadas'],
                            datasets: [{
                                data: [data.puntual, data.retraso, data.salida_temprana, data.salida_irregular, data.falta, data.justificado],
                                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#991b1b', '#ef4444', '#64748b'], hoverOffset: 4
                            }]
                        }, 
                        options: { responsive: true, plugins: { legend: { display: false } } }
                    });
                }
            })
            .catch(error => { 
                document.getElementById('cargando-resumen').innerHTML = `<p style="color:#ef4444; font-weight:bold;">${error.message}</p>`; 
            });
    }

    function cerrarResumen() {
        document.getElementById('modalOverlayResumen').classList.remove('activo');
        document.getElementById('modalContenidoResumen').classList.remove('activo');
    }

    function toggleTarjetaFeriados(e) {
        if (e) e.stopPropagation();
        const tarjeta = document.getElementById('tarjeta-info-feriados');
        if(tarjeta) tarjeta.classList.toggle('activa');
    }

    function descargarPDF(idPersonal) {
        const mes = selectMes.value;
        const anio = selectAnio.value;
        if(!mes || mes === "") { 
            Swal.fire({
                title: 'Atención', 
                text: 'Seleccione un periodo válido.', 
                icon: 'warning',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            }); 
            return; 
        }
        document.getElementById('pdf_id_personal').value = idPersonal;
        document.getElementById('pdf_mes').value = mes;
        document.getElementById('pdf_anio').value = anio;
        document.getElementById('pdf_id_cargo').value = cargoActivo; 
        
        document.getElementById('formGenerarPDF').submit();
    }

    // =======================================================
    // 3. EVENT LISTENERS
    // =======================================================

    // Inicializar select de meses
    if(Object.keys(periodosActivos).length > 0) {
        selectAnio.addEventListener('change', actualizarMesesDisponibles);
        actualizarMesesDisponibles();
    }

    // Cambiar tema
    const btnCambiarTema = document.getElementById('btnCambiarTema');
    if(btnCambiarTema) {
        btnCambiarTema.addEventListener('click', function(e) {
            e.preventDefault();
            const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', nuevoTema);
            localStorage.setItem('tema_usuario_' + config.idUsuario, nuevoTema);
        });
    }

    if (esDirectivo) {
        if (inputBuscador) {
            inputBuscador.addEventListener('input', () => aplicarFiltrosCombinados(null, null, true));
        }

        const btnVerMas = document.getElementById('btn-ver-mas-reportes');
        if (btnVerMas) {
            btnVerMas.addEventListener('click', () => {
                limiteActual += ITEMS_POR_CARGA;
                aplicarFiltrosCombinados(cargoActivo, document.querySelector('.btn-filtro.activo'), false); 
            });
        }

        aplicarFiltrosCombinados(null, null, true);
    }

    document.addEventListener('click', function(e) {
        const btnFeriado = document.getElementById('btn-info-feriados');
        const tarjeta = document.getElementById('tarjeta-info-feriados');
        
        if (btnFeriado && tarjeta && tarjeta.classList.contains('activa')) {
            if (!btnFeriado.contains(e.target)) {
                tarjeta.classList.remove('activa');
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarResumen();
    });

    // =======================================================
    // 4. EXPORTACIÓN AL SCOPE GLOBAL
    // =======================================================
    window.aplicarFiltrosCombinados = aplicarFiltrosCombinados;
    window.abrirResumen = abrirResumen;
    window.cerrarResumen = cerrarResumen;
    window.toggleTarjetaFeriados = toggleTarjetaFeriados;
    window.descargarPDF = descargarPDF;
});