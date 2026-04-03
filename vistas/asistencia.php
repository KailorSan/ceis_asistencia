<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];
$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

// Obtenemos los datos completos del usuario logueado para su tarjeta
$stmt_mi_id = $conexion->prepare("SELECT p.id_personal, p.foto_perfil, p.nombres, p.apellidos, c.nombre_cargo 
                                  FROM personal p 
                                  INNER JOIN cargos c ON p.id_cargo = c.id_cargo 
                                  WHERE p.id_usuario = ?");
$stmt_mi_id->execute([$id_usuario]);
$mis_datos = $stmt_mi_id->fetch(PDO::FETCH_ASSOC);
$mi_id_personal = $mis_datos ? $mis_datos['id_personal'] : 0;

$es_admin = ($id_rol == 1 || $id_rol == 2);

$lista_personal = [];
$cargos_activos_en_grid = []; // NUEVO: Array para guardar qué cargos realmente existen abajo

if ($es_admin) {
    try {
        // Añadimos p.id_cargo a la consulta para poder filtrarlo inteligentemente
        $sql = "SELECT p.id_personal, p.cedula, p.nombres, p.apellidos, p.foto_perfil, p.id_cargo, c.nombre_cargo 
                FROM personal p
                INNER JOIN cargos c ON p.id_cargo = c.id_cargo
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.estado = 'Activo' AND p.id_personal != ?
                ORDER BY p.nombres ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$mi_id_personal]);
        $lista_personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Extraemos solo los IDs de los cargos de las personas que SÍ aparecen en la grilla inferior
        $cargos_activos_en_grid = array_unique(array_column($lista_personal, 'id_cargo'));

        // Consultamos la tabla maestra de cargos
        $stmt_cargos = $conexion->query("SELECT id_cargo, nombre_cargo FROM cargos ORDER BY id_cargo ASC");
        $cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {}
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia - CEIS Julian Yánez</title>
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            document.documentElement.setAttribute('data-theme', localStorage.getItem('tema_usuario_' + idUsr) || 'light');
        })();
    </script>
</head>
<body>

   <?php $pagina_activa = 'asistencia'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Control de Asistencia'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            
            <?php if ($es_admin): ?>
                <div class="cabecera-personal" style="margin-block-end: 15px;">
                    <div>
                        <h1>Mi Asistencia</h1>
                        <p>Revisa tu historial de entradas y salidas.</p>
                    </div>
                </div>

                <!-- CORRECCIÓN DE ESPACIOS: Bajamos el margin-block-end a 1rem para acercarlo al separador -->
                <div class="grid-perfiles" style="margin-block-end: 1rem;">
                    <div class="tarjeta-perfil" style="max-inline-size: 320px;"> 
                        <div class="banner-tarjeta" style="block-size: 70px;"></div>
                        <div class="contenedor-avatar" style="margin-block-start: -40px; inline-size: 80px; block-size: 80px;">
                            <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($mis_datos['foto_perfil']); ?>" alt="Mi Foto">
                        </div>
                        <div class="info-perfil" style="padding-block-start: 10px;">
                            <h3 class="nombre-empleado"><?php echo htmlspecialchars($mis_datos['nombres'] . ' ' . $mis_datos['apellidos']); ?></h3>
                            <span class="cargo-empleado"><?php echo htmlspecialchars($mis_datos['nombre_cargo']); ?></span>
                            
                            <div class="acciones-perfil">
                                <button class="btn-editar-horario" onclick="abrirCalendario(<?php echo $mi_id_personal; ?>, 'Mi Asistencia', false)" style="inline-size: 100%; background-color: var(--primary-color); color: white;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: white; inline-size: 18px; block-size: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Ver mi calendario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Añadimos un pequeño margen abajo al separador -->
                <div class="separador-personal" style="margin-block-end: 20px;">Asistencia del Personal</div>

                <!-- CAJA GLOBAL ESTANDARIZADA -->
                <div class="contenedor-filtros-globales" style="justify-content: center; margin-bottom: 25px; padding: 15px;">
                    <div class="contenedor-busqueda-elegante" style="margin: 0; width: 100%; max-width: 450px;">
                      <input type="text" id="buscador-universal" class="campo-busqueda-elegante" placeholder="Buscar por nombre o cargo...">
                        <svg class="icono-busqueda" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <!-- FILTROS INTELIGENTES -->
                <div class="botones-filtro-cargo" style="margin-block-end: 30px;">
                    <button class="btn-filtro activo" onclick="aplicarFiltroUniversal('todos', this)">Todos</button>
                    <?php foreach($cargos as $c): ?>
                        <!-- La Magia: Solo dibuja el botón si hay personal con este cargo en la grilla -->
                        <?php if(in_array($c['id_cargo'], $cargos_activos_en_grid)): ?>
                            <button class="btn-filtro" onclick="aplicarFiltroUniversal(<?php echo $c['id_cargo']; ?>, this)">
                                <?php echo htmlspecialchars($c['nombre_cargo']); ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="grid-perfiles">
                    <?php foreach ($lista_personal as $emp): ?>
                        <div class="tarjeta-perfil item-filtrable" data-cargo="<?php echo $emp['id_cargo']; ?>">
                            <div class="banner-tarjeta" style="block-size: 70px;"></div>
                            <div class="contenedor-avatar" style="margin-block-start: -40px; inline-size: 80px; block-size: 80px;">
                                <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($emp['foto_perfil']); ?>" alt="Foto">
                            </div>
                            <div class="info-perfil" style="padding-block-start: 10px;">
                                <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                                <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                                
                                <div class="acciones-perfil">
                                    <button class="btn-editar-horario" onclick="abrirCalendario(<?php echo $emp['id_personal']; ?>, '<?php echo addslashes($emp['nombres']); ?>', true)" style="inline-size: 100%;">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="inline-size: 18px; block-size: 18px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                        Revisar Asistencia
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="cabecera-personal">
                    <div>
                        <h1>Mi Historial de Asistencia</h1>
                        <p>Calendario de tus entradas, retrasos y justificaciones.</p>
                    </div>
                </div>

                <div class="tarjeta-formulario-config" style="max-inline-size: 100%;">
                    <div id="contenedor-calendario-inline"></div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <?php if ($es_admin): ?>
    <style>
        #modalCalendario { max-inline-size: 450px; padding: 1.5rem; }
        #modalCalendario .dia-celda { min-block-size: 50px; padding: 4px; }
        #modalCalendario .numero-dia { font-size: 0.95rem; }
        #modalCalendario .icono-estado { inline-size: 16px; block-size: 16px; }
    </style>
    
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-contenido" id="modalCalendario">
            <div class="modal-header">
                <h2 id="titulo_modal_calendario" style="font-size: 1.2rem;">Asistencia</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <div id="contenedor-calendario-modal"></div>
        </div>
    </div>
    <?php endif; ?>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
        // --- MOTOR DE FILTRADO UNIVERSAL ---
        const inputBuscadorUniv = document.getElementById('buscador-universal');
        let cargoActivoUniv = 'todos'; 
        
        function aplicarFiltroUniversal(idCargo = null, botonSeleccionado = null) {
            if (idCargo !== null) {
                cargoActivoUniv = idCargo;
                document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
                if(botonSeleccionado) botonSeleccionado.classList.add('activo');
            }
            
            const textoBusqueda = inputBuscadorUniv ? inputBuscadorUniv.value.toLowerCase().trim() : '';
            
            document.querySelectorAll('.item-filtrable').forEach(item => {
                const coincideCargo = (cargoActivoUniv === 'todos') || (item.getAttribute('data-cargo') == cargoActivoUniv);
                const elNombre = item.querySelector('.nombre-empleado');
                const elCargo = item.querySelector('.cargo-empleado');
                
                const nombre = elNombre ? elNombre.innerText.toLowerCase() : '';
                const cargo = elCargo ? elCargo.innerText.toLowerCase() : '';
                
                const coincideTexto = nombre.includes(textoBusqueda) || cargo.includes(textoBusqueda);
                
                if (coincideCargo && coincideTexto) {
                    item.classList.remove('oculto-por-filtro');
                } else {
                    item.classList.add('oculto-por-filtro');
                }
            });
        }

        if (inputBuscadorUniv) {
            inputBuscadorUniv.addEventListener('input', () => aplicarFiltroUniversal());
        }
        // ------------------------------------

        const btnCambiarTema = document.getElementById('btnCambiarTema');
        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                const html = document.documentElement;
                const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
            });
        }

        let idPersonalActual = <?php echo $mi_id_personal; ?>;
        let esModoAdmin = false;
        let mesActual = new Date().getMonth() + 1;
        let anioActual = new Date().getFullYear();

        function abrirCalendario(idPersonal, nombre, modoEdicion) {
            idPersonalActual = idPersonal;
            esModoAdmin = modoEdicion;
            mesActual = new Date().getMonth() + 1;
            anioActual = new Date().getFullYear();
            
            const titulo = document.getElementById('titulo_modal_calendario');
            if (titulo) titulo.innerText = 'Asistencia: ' + nombre;
            
            document.getElementById('modalOverlay').classList.add('activo');
            document.getElementById('modalCalendario').classList.add('activo');
            
            cargarCalendario('contenedor-calendario-modal');
        }

        function cerrarModal() {
            document.getElementById('modalOverlay').classList.remove('activo');
            document.getElementById('modalCalendario').classList.remove('activo');
        }

        <?php if (!$es_admin): ?>
            document.addEventListener('DOMContentLoaded', function() {
                cargarCalendario('contenedor-calendario-inline');
            });
        <?php endif; ?>

        function cargarCalendario(idContenedor) {
            const contenedor = document.getElementById(idContenedor);
            contenedor.innerHTML = '<div style="text-align:center; padding: 40px;"><svg class="animacion-vibrar" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--primary-color)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p>Cargando fechas...</p></div>';

            fetch(`../controladores/ControladorCalendario.php?id=${idPersonalActual}&mes=${mesActual}&anio=${anioActual}&admin=${esModoAdmin}&contenedor=${idContenedor}`)
                .then(response => response.text())
                .then(html => { contenedor.innerHTML = html; })
                .catch(error => { contenedor.innerHTML = '<p style="color:red; text-align:center;">Error al cargar el calendario.</p>'; });
        }

        function cambiarMes(direccion, idContenedor) {
            mesActual += direccion;
            if (mesActual > 12) { mesActual = 1; anioActual++; }
            if (mesActual < 1) { mesActual = 12; anioActual--; }
            cargarCalendario(idContenedor);
        }

        function editarDia(fechaBD, fechaVisual, estadoActual, motivo, archivo) {
            if (!esModoAdmin) return;

            let enlaceEvidencia = '';
            if (archivo !== '') {
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

            Swal.fire({
                title: 'Modificar Asistencia',
                html: `
                    <p style="margin-block-end:15px; font-weight:bold; color:var(--primary-color); font-size:1.1rem;">Fecha: ${fechaVisual}</p>
                    <select id="swal-estado" style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:15px; border:1px solid #ccc; outline:none; font-family:'Montserrat';">
                        <option value="Puntual" ${estadoActual === 'Puntual' ? 'selected' : ''}>Puntual (Verde)</option>
                        <option value="Retraso" ${estadoActual === 'Retraso' ? 'selected' : ''}>Retraso (Naranja)</option>
                        <option value="Justificado" ${estadoActual === 'Justificado' ? 'selected' : ''}>Justificado (Gris)</option>
                        <option value="Falta" ${estadoActual.includes('Falta') ? 'selected' : ''}>Falta (Rojo)</option>
                    </select>
                    <textarea id="swal-motivo" placeholder="Escriba un motivo o nota de Dirección..." style="inline-size:100%; padding:10px; border-radius:8px; margin-block-end:15px; border:1px solid #ccc; min-block-size:80px; font-family:'Montserrat'; outline:none;">${motivo}</textarea>
                    ${enlaceEvidencia}
                    <div style="text-align: start; margin-block-start: 10px; overflow: hidden;">
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
                    document.getElementById('swal-archivo').addEventListener('change', function(e) {
                        const nombreArchivo = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
                        document.getElementById('texto-swal-archivo').textContent = nombreArchivo;
                    });
                },
                preConfirm: () => {
                    return {
                        fecha: fechaBD,
                        estado: document.getElementById('swal-estado').value,
                        motivo: document.getElementById('swal-motivo').value,
                        archivo: document.getElementById('swal-archivo').files[0]
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_personal', idPersonalActual); 
                    formData.append('fecha', result.value.fecha);
                    formData.append('estado', result.value.estado);
                    formData.append('motivo', result.value.motivo);
                    
                    if (result.value.archivo) {
                        formData.append('archivo', result.value.archivo);
                    }

                    Swal.fire({
                        title: 'Guardando cambios...',
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
                                cargarCalendario('contenedor-calendario-modal');
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.msg,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error de Conexión', 'Hubo un problema al contactar al servidor.', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>