document.addEventListener("DOMContentLoaded", () => {
    // 1. Cargamos el objeto de configuración enviado desde PHP
    const config = window.PrincipalConfig;
    
    // 2. Transición y Animaciones GSAP
    const cortina = document.getElementById("cortina-transicion");
    if (typeof gsap !== 'undefined' && sessionStorage.getItem('mostrarAnimacionEntrada') === 'true') {
        sessionStorage.removeItem('mostrarAnimacionEntrada');
        
        const tl = gsap.timeline();
        tl.to(cortina, { 
            opacity: 0, 
            duration: 0.7, 
            ease: "power2.inOut",
            onComplete: () => { cortina.style.display = "none"; }
        })
        .from(".contenido h1, .contenido p, .panel-asistencia, .grid-tarjetas, .grid-graficos, .banner-pausa", { 
            y: 30, 
            opacity: 0, 
            duration: 0.6, 
            stagger: 0.1, 
            ease: "back.out(1.2)" 
        }, "-=0.4");
    } else {
        if (cortina) {
            cortina.style.display = "none";
        }
    }

    // 3. Sistema de Alertas Global (SweetAlert2)
    const ToastSwal = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    function mostrarToast(tipo, titulo, mensaje) {
        ToastSwal.fire({
            icon: tipo,
            title: titulo,
            html: mensaje
        });
    }

    // Verificamos si PHP inyectó alguna alerta para mostrar
    if (config.alerta && config.alerta.mostrar) {
        mostrarToast(config.alerta.tipo, config.alerta.titulo, config.alerta.mensaje);
    }

    // 4. Lógica del Formulario de Justificaciones
    const inputMotivo = document.getElementById('modal_j_motivo');
    const errorMotivo = document.getElementById('error-motivo');
    const formJ       = document.getElementById('formJustificacion');

    if(inputMotivo) {
        inputMotivo.addEventListener('input', function() {
            if (this.value.trim().length > 0 && this.value.trim().length < 15) {
                this.classList.add('input-error');
                errorMotivo.style.display = 'block';
            } else {
                this.classList.remove('input-error');
                errorMotivo.style.display = 'none';
            }
        });
    }

    if(formJ) {
        formJ.addEventListener('submit', function(e) {
            let errores = [];
            const valFecha = document.getElementById('modal_j_fecha').value;
            const valTipo  = document.getElementById('modal_j_tipo').value;
            
            inputMotivo.classList.remove('input-error');
            errorMotivo.style.display = 'none';

            if (!valFecha) errores.push("• Debes seleccionar la fecha de la incidencia.");
            if (!valTipo)  errores.push("• Debes seleccionar un tipo de incidencia.");
            if (inputMotivo.value.trim().length < 15) {
                inputMotivo.classList.add('input-error');
                errorMotivo.style.display = 'block';
                errores.push("• El motivo debe tener al menos 15 caracteres.");
            }

            if (errores.length > 0) {
                e.preventDefault(); 
                mostrarToast('error', 'Datos Incompletos', errores.join('<br>'));
            }
        });
    }

    // Cambiar texto de input de archivo
    const archivoInput = document.getElementById('modal_j_archivo');
    if (archivoInput) {
        archivoInput.addEventListener('change', function(e) {
            var nombreArchivo = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
            document.getElementById('texto-archivo').textContent = nombreArchivo;
        });
    }

    // 5. Tema Claro/Oscuro y Actualización de Gráficos (Chart.js)
    const btnCambiarTema = document.getElementById('btnCambiarTema');
    const html = document.documentElement;

    if (btnCambiarTema) {
        btnCambiarTema.addEventListener('click', function(e) {
            e.preventDefault();
            
            this.classList.add('girando'); 
            
            const temaActual = html.getAttribute('data-theme');
            const nuevoTema  = temaActual === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', nuevoTema);
            
            // Usamos el idUsuario que viene desde el puente
            localStorage.setItem('tema_usuario_' + config.idUsuario, nuevoTema);

            const nuevoColorTexto = nuevoTema === 'dark' ? '#cbd5e1' : '#64748b';
            const nuevoColorGrid  = nuevoTema === 'dark' ? '#334155' : '#e2e8f0';

            // Repinta todas las instancias de gráficos
            if (typeof Chart !== 'undefined') {
                for (let id in Chart.instances) {
                    let chart = Chart.instances[id];
                    
                    if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                        chart.options.plugins.legend.labels.color = nuevoColorTexto;
                    }
                    if (chart.options.scales) {
                        if (chart.options.scales.x) chart.options.scales.x.ticks.color = nuevoColorTexto;
                        if (chart.options.scales.y) {
                            chart.options.scales.y.ticks.color = nuevoColorTexto;
                            if(chart.options.scales.y.grid) chart.options.scales.y.grid.color = nuevoColorGrid;
                        }
                        if (chart.options.scales.r) { 
                            if(chart.options.scales.r.grid) chart.options.scales.r.grid.color = nuevoColorGrid;
                        }
                    }
                    chart.update();
                }
            }

            setTimeout(() => { this.classList.remove('girando'); }, 500);
        });
    }

    // 6. Configuración y Renderizado de Gráficos (Chart.js)
    try {
        if (!config.esPausado && document.getElementById('grafico1')) {
            const rolUser     = config.idRol;
            const colorTexto  = html.getAttribute('data-theme') === 'dark' ? '#cbd5e1' : '#64748b';
            const colorGrid   = html.getAttribute('data-theme') === 'dark' ? '#334155' : '#e2e8f0';
            
            const t1 = config.tarjetas.t1;
            const t2 = config.tarjetas.t2;
            const t3 = config.tarjetas.t3;

            const retrasosHoy          = config.graficos.retrasosHoy;
            const puntualesHoy         = config.graficos.puntualesHoy;
            const justPendientes       = config.graficos.justPendientes;
            const faltasInjustificadas = config.graficos.faltasInjustificadas;

            let d_labels_1 = [], d_data_1 = [], d_colors_1 = [];
            let d_labels_2 = [], d_data_2 = [], d_colors_2 = [];
            let d_labels_3 = [], d_data_3 = [], d_colors_3 = [];

            if (rolUser == 1 || rolUser == 2) {
                document.getElementById('tituloGrafico1').innerText = "Asistencia General Hoy";
                document.getElementById('tituloGrafico2').innerText = "Bandeja de Justificaciones";
                document.getElementById('tituloGrafico3').innerText = "Calidad de Llegada Hoy";

                d_labels_1 = ['Presentes', 'Ausentes/Faltas'];
                d_data_1   = [t2, t3];
                d_colors_1 = ['#3b82f6', '#ef4444'];

                d_labels_2 = ['Pendientes (Revisar)', 'Aprobadas'];
                d_data_2   = [justPendientes, t2]; 
                d_colors_2 = ['#f59e0b', '#10b981'];

                d_labels_3 = ['Llegaron Puntuales', 'Llegaron Tarde'];
                d_data_3   = [puntualesHoy, retrasosHoy];
                d_colors_3 = ['#10b981', '#f59e0b'];
            } else {
                document.getElementById('tituloGrafico1').innerText = "Mis Llegadas (Mes)";
                document.getElementById('tituloGrafico2').innerText = "Balance del Mes";
                document.getElementById('tituloGrafico3').innerText = "Mis Inasistencias (Mes)";

                const puntuales   = (t1 - t3) > 0 ? (t1 - t3) : 0;
                const totalFaltas = t2 + faltasInjustificadas;

                d_labels_1 = ['Llegadas Puntuales', 'Retrasos'];
                d_data_1   = [puntuales, t3];
                d_colors_1 = ['#10b981', '#f59e0b'];

                d_labels_2 = ['Días Asistidos', 'Total Faltas'];
                d_data_2   = [t1, totalFaltas];
                d_colors_2 = ['#3b82f6', '#ef4444'];

                d_labels_3 = ['Faltas Justificadas', 'Faltas Sin Justificar'];
                d_data_3   = [t2, faltasInjustificadas];
                d_colors_3 = ['#8b5cf6', '#ef4444'];
            }

            const opcionesComunes = {
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: colorTexto, font: { size: 11, family: "'Montserrat', sans-serif" } } } }
            };

            const hayDatos = d_data_1.reduce((a, b) => a + b, 0) > 0 || d_data_2.reduce((a, b) => a + b, 0) > 0 || d_data_3.reduce((a, b) => a + b, 0) > 0;

            if (hayDatos) {
                new Chart(document.getElementById('grafico1').getContext('2d'), {
                    type: 'doughnut',
                    data: { labels: d_labels_1, datasets: [{ data: d_data_1, backgroundColor: d_colors_1, borderWidth: 0, hoverOffset: 4 }] },
                    options: { ...opcionesComunes, cutout: '70%' }
                });

                new Chart(document.getElementById('grafico2').getContext('2d'), {
                    type: 'bar',
                    data: { labels: d_labels_2, datasets: [{ label: 'Cantidad', data: d_data_2, backgroundColor: d_colors_2, borderRadius: 6 }] },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { beginAtZero: true, grid: { color: colorGrid }, ticks: { color: colorTexto, stepSize: 1 } },
                            x: { grid: { display: false }, ticks: { color: colorTexto } }
                        }
                    }
                });

                new Chart(document.getElementById('grafico3').getContext('2d'), {
                    type: 'polarArea',
                    data: { labels: d_labels_3, datasets: [{ data: d_data_3, backgroundColor: d_colors_3, borderWidth: 0 }] },
                    options: {
                        ...opcionesComunes,
                        scales: { r: { ticks: { display: false }, grid: { color: colorGrid } } }
                    }
                });
            } else {
                document.querySelector('.grid-graficos').innerHTML = '<div style="width: 100%; text-align: center; padding: 40px; color: var(--text-color); font-weight: bold;">Aún no hay suficientes datos registrados para generar las gráficas.</div>';
            }
        }
    } catch(e) {
        console.error(e);
    }

    // 7. Cronjob Virtual Silencioso
    fetch('../controladores/ControladorCronjob.php')
        .then(response => response.json())
        .then(data => {
            if (data.procesado && (data.salidas_cerradas > 0 || data.faltas_creadas > 0)) {
                console.log(`[Cronjob] Mantenimiento automático realizado: ${data.salidas_cerradas} salidas cerradas, ${data.faltas_creadas} faltas creadas.`);
            }
        })
        .catch(error => console.error('[Cronjob] Error en mantenimiento:', error));
});

// 8. Funciones Globales para el Modal
// Al estar en un archivo externo, para que el HTML reconozca onclick="abrirModalJustificacion()"
// necesitamos engancharlas al objeto window globalmente.
window.abrirModalJustificacion = function(tipo = '') {
    const modalOverlay       = document.getElementById('modalOverlay');
    const modalJustificacion = document.getElementById('modalJustificacion');
    const fechaInput         = document.getElementById('modal_j_fecha');
    
    const hoy = new Date();
    // No permitir seleccionar fines de semana (0=Dom, 6=Sab)
    if(hoy.getDay() !== 0 && hoy.getDay() !== 6) {
        fechaInput.valueAsDate = hoy;
    } else {
        fechaInput.value = '';
    }
    
    const selectTipo = document.getElementById('modal_j_tipo');
    if(tipo) { selectTipo.value = tipo; } else { selectTipo.selectedIndex = 0; }

    const motivo   = document.getElementById('modal_j_motivo');
    const errorMot = document.getElementById('error-motivo');
    if(motivo)   { motivo.value = ''; motivo.classList.remove('input-error'); }
    if(errorMot) { errorMot.style.display = 'none'; }
    document.getElementById('texto-archivo').textContent = 'Seleccionar archivo...';
    const archivoInp = document.getElementById('modal_j_archivo');
    if(archivoInp) archivoInp.value = '';

    modalJustificacion.scrollTop = 0;
    modalJustificacion.classList.remove('cerrando');
    modalOverlay.classList.remove('cerrando');
    modalOverlay.classList.add('activo');
    modalJustificacion.classList.add('activo');
};

window.cerrarModales = function() {
    const modalOverlay       = document.getElementById('modalOverlay');
    const modalJustificacion = document.getElementById('modalJustificacion');
    
    if (modalJustificacion) modalJustificacion.classList.add('cerrando');
    if (modalOverlay) modalOverlay.classList.add('cerrando');
    
    setTimeout(function() {
        if (modalOverlay) modalOverlay.classList.remove('activo', 'cerrando');
        if (modalJustificacion) modalJustificacion.classList.remove('activo', 'cerrando');
    }, 220);
};

// Listeners de cierre de modal (click fuera y tecla ESC)
document.addEventListener('click', function(e) {
    const modalOverlay = document.getElementById('modalOverlay');
    if (e.target === modalOverlay) window.cerrarModales();
});

document.addEventListener('keydown', function(e) {
    const modalOverlay = document.getElementById('modalOverlay');
    if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('activo')) {
        window.cerrarModales();
    }
});