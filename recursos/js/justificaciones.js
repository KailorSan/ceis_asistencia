document.addEventListener('DOMContentLoaded', () => {
    // =======================================================
    // 1. CONFIGURACIÓN Y VARIABLES DE ESTADO
    // =======================================================
    const config = window.JustificacionesConfig;
    const html = document.documentElement;

    // =======================================================
    // 2. TEMAS Y ALERTAS (SWEETALERT2)
    // =======================================================
    
    // Función helper para aplicar el color dinámicamente a las alertas
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

    // Comprobar si hay una alerta en sesión enviada desde PHP
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
    // 3. DECLARACIÓN DE FUNCIONES (HOISTING SEGURO)
    // =======================================================

    function procesar(id, accion, tipo, estado_base) {
        let textoAlerta = '';
        
        // Configurar el texto informativo según la acción y el tipo
        if (accion === 'aprobar') {
            if (tipo === 'Llegada Tardía') {
                textoAlerta = "La incidencia se registrará oficialmente como 'Retraso'.";
            } else if (tipo === 'Salida Temprana') {
                let nuevo_estado = (estado_base === 'Retraso' || estado_base === 'Puntual') ? (estado_base + ' y Salida Temprana') : 'Salida Temprana';
                textoAlerta = "El registro se actualizará a '" + nuevo_estado + "'.";
            } else {
                textoAlerta = "La inasistencia se convertirá en 'Justificado'.";
            }
        } else {
            if (tipo === 'Llegada Tardía' || tipo === 'Inasistencia') {
                textoAlerta = "La incidencia se marcará definitivamente como 'Falta'.";
            } else if (tipo === 'Salida Temprana') {
                let nuevo_estado = (estado_base === 'Retraso' || estado_base === 'Puntual') ? (estado_base + ' y Salida Irregular') : 'Salida Irregular';
                textoAlerta = "Se denegará la salida y se marcará como '" + nuevo_estado + "'.";
            } else {
                textoAlerta = "La incidencia se marcará como Falta.";
            }
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: textoAlerta,
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: accion === 'aprobar' ? '#10b981' : '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, ' + accion,
            cancelButtonText: 'Cancelar',
            ...parametrosTema()
        }).then((result) => {
            if (result.isConfirmed) {
                
                // Si la acción es rechazar, pedir un motivo
                if (accion === 'rechazar') {
                    Swal.fire({
                        title: 'Motivo del Rechazo',
                        text: 'Por favor, indica a continuación por qué se rechaza esta justificación:',
                        input: 'textarea',
                        inputPlaceholder: 'Escribe el motivo aquí...',
                        inputAttributes: {
                            maxlength: '250',
                            'aria-label': 'Motivo del rechazo'
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Rechazar y Enviar Observación',
                        cancelButtonText: 'Cancelar',
                        ...parametrosTema(),
                        inputValidator: (value) => {
                            if (!value) {
                                return '¡Necesitas escribir un motivo para poder rechazarlo!';
                            }
                        }
                    }).then((motivoResult) => {
                        if (motivoResult.isConfirmed) {
                            const formData = new FormData();
                            formData.append('id',             id);
                            formData.append('accion',         accion);
                            formData.append('motivo_rechazo', motivoResult.value);

                            fetch('../controladores/ControladorProcesarJustificacion.php', {
                                method: 'POST',
                                body: formData
                            }).then(() => {
                                window.location.href = '../vistas/justificaciones.php';
                            }).catch(() => {
                                Swal.fire('Error', 'No se pudo conectar con el servidor. Intenta de nuevo.', 'error');
                            });
                        }
                    });
                } else {
                    // Si es aprobar, redirigir directamente al procesador GET
                    window.location.href = '../controladores/ControladorProcesarJustificacion.php?id=' + id + '&accion=' + accion;
                }
            }
        });
    }

    // =======================================================
    // 4. EXPORTACIÓN AL SCOPE GLOBAL
    // =======================================================
    window.procesar = procesar;

    // =======================================================
    // 5. INICIALIZACIÓN DE LA GUÍA DINÁMICA (JUSTIFICACIONES)
    // =======================================================
    let diccionarioJustificaciones = [];

    // 1. Aviso de justificaciones propias (si existe)
    if (document.querySelector('.aviso-propias')) {
        diccionarioJustificaciones.push({
            selector: '.aviso-propias',
            titulo: 'Política de Transparencia',
            texto: 'El sistema detectó que tienes una justificación propia pendiente. Por seguridad, no puedes auto-aprobarte; otro directivo debe procesarla.'
        });
    }

    // 2. Tarjeta general de la solicitud
    if (document.querySelectorAll('.tarjeta-justificacion').length > 0) {
        diccionarioJustificaciones.push({
            selector: '.tarjeta-justificacion',
            titulo: 'Solicitud de Justificación',
            texto: 'Muestra los datos del empleado, la fecha de la incidencia y su argumento ("Llegada Tardía", "Inasistencia" o "Salida Temprana").'
        });
    }

    // 3. Botón de evidencia adjunta
    if (document.querySelectorAll('.btn-evidencia').length > 0) {
        diccionarioJustificaciones.push({
            selector: '.btn-evidencia',
            titulo: 'Evidencia Adjunta',
            texto: 'Abre el documento PDF o la imagen (ej: récipe médico o constancia) que el empleado subió como prueba.'
        });
    }

    // 4. Botón Aprobar
    if (document.querySelectorAll('.btn-aprobar').length > 0) {
        diccionarioJustificaciones.push({
            selector: '.btn-aprobar',
            titulo: 'Aprobar Solicitud',
            texto: 'Autoriza la incidencia. El récord del empleado cambiará a "Justificado" o "Salida Temprana" y la falta desaparecerá.'
        });
    }

    // 5. Botón Rechazar
    if (document.querySelectorAll('.btn-rechazar').length > 0) {
        diccionarioJustificaciones.push({
            selector: '.btn-rechazar',
            titulo: 'Rechazar Solicitud',
            texto: 'Deniega el permiso. El sistema te pedirá escribir un motivo, el cual será enviado directamente a la campana de notificaciones del empleado.'
        });
    }

    // Instanciar el motor de la guía
    if (typeof window.GuiaDinamica !== 'undefined') {
        const guiaAppJustificaciones = new window.GuiaDinamica(diccionarioJustificaciones);
    }
    
});