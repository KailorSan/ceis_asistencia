<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

if ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2) {
    header("Location: principal.php");
    exit;
}

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

try {
    // Incorporamos id_cargo en la consulta para que el JS sepa cómo filtrar
    $sql = "SELECT p.id_personal, p.cedula, p.nombres, p.apellidos, p.telefono, p.foto_perfil, 
                   p.hora_entrada_personalizada, p.hora_salida_personalizada, p.id_cargo,
                   c.nombre_cargo, u.estado, u.id_usuario, u.id_rol 
            FROM personal p
            INNER JOIN cargos c ON p.id_cargo = c.id_cargo
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            ORDER BY p.nombres ASC";
    $stmt = $conexion->query($sql);
    $lista_personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_roles = $conexion->query("SELECT id_rol, nombre_rol FROM roles");
    $lista_roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de cargos para los botones del filtro
    $stmt_cargos = $conexion->query("SELECT id_cargo, nombre_cargo FROM cargos ORDER BY id_cargo ASC");
    $cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $lista_personal = [];
    $lista_roles = [];
    $cargos = [];
}
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal - CEIS Julian Yánez</title>
    <!-- CSS principal con scroll y buscador -->
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    
    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            const temaGuardado = localStorage.getItem('tema_usuario_' + idUsr) || 'light';
            document.documentElement.setAttribute('data-theme', temaGuardado);
        })();
    </script>
</head>
<body>

   <?php $pagina_activa = 'personal'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
     <?php $titulo_pagina = 'Equipo de Trabajo'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            <div class="cabecera-personal">
                <div>
                    <h1>Directorio del Personal</h1>
                    <p>Gestiona los perfiles y horarios personalizados de tu equipo.</p>
                </div>
            </div>

            <!-- CAJA GLOBAL ESTANDARIZADA -->
                <div class="contenedor-filtros-globales" style="justify-content: center; margin-block-end: 25px; padding: 15px;">
                    <div class="contenedor-busqueda-elegante" style="margin: 0; inline-size: 100%; max-inline-size: 450px;">
                      <input type="text" id="buscador-universal" class="campo-busqueda-elegante" placeholder="Buscar por nombre o cargo...">
                        <svg class="icono-busqueda" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

            <div class="botones-filtro-cargo">
                <button class="btn-filtro activo" onclick="aplicarFiltroUniversal('todos', this)">Todos</button>
                <?php foreach($cargos as $c): ?>
                    <button class="btn-filtro" onclick="aplicarFiltroUniversal(<?php echo $c['id_cargo']; ?>, this)">
                        <?php echo htmlspecialchars($c['nombre_cargo']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Agregamos clase item-filtrable y data-cargo a las tarjetas -->
            <div class="grid-perfiles">
                <?php foreach ($lista_personal as $emp): ?>
                    <div class="tarjeta-perfil item-filtrable <?php echo ($emp['estado'] == 'Inactivo') ? 'inactivo' : ''; ?>" data-cargo="<?php echo $emp['id_cargo']; ?>">
                        
                        <div class="banner-tarjeta">
                            <span class="etiqueta-estado"><?php echo $emp['estado']; ?></span>
                        </div>
                        
                        <div class="contenedor-avatar">
                            <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($emp['foto_perfil']); ?>" alt="Foto de <?php echo $emp['nombres']; ?>" loading="lazy">
                        </div>
                        
                        <div class="info-perfil">
                            <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                            <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                            
                            <div class="detalles-empleado">
                                <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg> C.I: <?php echo $emp['cedula']; ?></p>
                                <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> <?php echo $emp['telefono']; ?></p>
                            </div>

                            <div class="acciones-perfil">
                                <button class="btn-editar-horario" 
                                    data-id="<?php echo $emp['id_personal']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?>"
                                    data-entrada="<?php echo $emp['hora_entrada_personalizada'] ? date('H:i', strtotime($emp['hora_entrada_personalizada'])) : ''; ?>"
                                    data-salida="<?php echo $emp['hora_salida_personalizada'] ? date('H:i', strtotime($emp['hora_salida_personalizada'])) : ''; ?>"
                                    onclick="abrirModalHorario(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Horario
                                </button>
                                
                                <button class="btn-editar-usuario"
                                    data-idpersonal="<?php echo $emp['id_personal']; ?>"
                                    data-idusuario="<?php echo $emp['id_usuario']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?>"
                                    data-estado="<?php echo $emp['estado']; ?>"
                                    data-rol="<?php echo $emp['id_rol']; ?>"
                                    onclick="abrirModalEditar(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if(empty($lista_personal)): ?>
                    <p>No hay personal registrado en el sistema.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modales (se mantienen igual que antes) -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-contenido" id="modalHorario">
            <div class="modal-header">
                <h2 id="modal_h_nombre">Horario Personalizado</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModales()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form action="../controladores/ControladorHorario.php" method="POST">
                <input type="hidden" name="id_personal" id="modal_h_id_personal">
                <p style="font-size: 0.85rem; margin-block-end: 15px; color: var(--text-color);">*Si dejas los campos vacíos, el empleado utilizará el horario general del colegio.</p>
                <div class="grupo-input" style="margin-block-end: 15px;">
                    <label>Hora de Entrada Especial</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        <input type="time" name="hora_entrada" id="modal_h_entrada">
                    </div>
                </div>
                <div class="grupo-input" style="margin-block-end: 25px;">
                    <label>Hora de Salida Especial</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <input type="time" name="hora_salida" id="modal_h_salida">
                    </div>
                </div>
                <button type="submit" class="btn-guardar" style="inline-size: 100%; justify-content: center;">Guardar Horario</button>
            </form>
        </div>

        <div class="modal-contenido" id="modalEditar">
            <div class="modal-header">
                <h2 id="modal_e_nombre">Editar Usuario</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModales()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form action="../controladores/ControladorEditarPersonal.php" method="POST">
                <input type="hidden" name="id_usuario" id="modal_e_id_usuario">
                <div class="grupo-input" style="margin-block-end: 15px;">
                    <label>Estado del Empleado</label>
                    <div class="input-con-icono">
                        <select name="estado" id="modal_e_estado" style="inline-size: 100%; padding: 12px 15px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat'; font-size: 1rem; font-weight: 600;">
                            <option value="Activo">Activo (Permitir Acceso)</option>
                            <option value="Inactivo">Inactivo (Bloquear Acceso)</option>
                        </select>
                    </div>
                </div>
                <div class="grupo-input" style="margin-block-end: 25px;">
                    <label>Nivel de Permisos (Rol)</label>
                    <div class="input-con-icono">
                        <select name="id_rol" id="modal_e_rol" style="inline-size: 100%; padding: 12px 15px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat'; font-size: 1rem; font-weight: 600;">
                            <?php foreach ($lista_roles as $r): ?>
                                <option value="<?php echo $r['id_rol']; ?>"><?php echo $r['nombre_rol']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-guardar" style="inline-size: 100%; justify-content: center;">Actualizar Datos</button>
                <button type="button" class="btn-eliminar-usuario" onclick="confirmarEliminacion()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Eliminar Trabajador Definitivamente
                </button>
            </form>
        </div>
    </div>

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

        const html = document.documentElement;
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
            });
        }

        // LÓGICA DE MODALES (Igual que antes)
        const modalOverlay = document.getElementById('modalOverlay');
        const modalHorario = document.getElementById('modalHorario');
        const modalEditar = document.getElementById('modalEditar');

        function abrirModalHorario(btn) {
            document.getElementById('modal_h_id_personal').value = btn.dataset.id;
            document.getElementById('modal_h_nombre').textContent = "Horario de " + btn.dataset.nombre.split(' ')[0];
            document.getElementById('modal_h_entrada').value = btn.dataset.entrada;
            document.getElementById('modal_h_salida').value = btn.dataset.salida;
            modalOverlay.classList.add('activo');
            modalHorario.classList.add('activo');
        }

        function abrirModalEditar(btn) {
            document.getElementById('modal_e_id_usuario').value = btn.dataset.idusuario;
            document.getElementById('modal_e_nombre').textContent = "Opciones: " + btn.dataset.nombre.split(' ')[0];
            document.getElementById('modal_e_estado').value = btn.dataset.estado;
            document.getElementById('modal_e_rol').value = btn.dataset.rol;
            document.getElementById('modalEditar').setAttribute('data-nombre-eliminar', btn.dataset.nombre);
            modalOverlay.classList.add('activo');
            modalEditar.classList.add('activo');
        }

        function cerrarModales() {
            modalOverlay.classList.remove('activo');
            modalHorario.classList.remove('activo');
            modalEditar.classList.remove('activo');
        }

        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) cerrarModales();
        });

        function confirmarEliminacion() {
            const idUsr = document.getElementById('modal_e_id_usuario').value;
            const nombreCompleto = document.getElementById('modalEditar').getAttribute('data-nombre-eliminar');
            document.getElementById('modalEditar').classList.remove('activo');

            Swal.fire({
                title: '¿Expulsar a ' + nombreCompleto + '?',
                text: "Se borrará su cuenta, su perfil y su historial. Esta acción NO se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, Eliminar Todo',
                cancelButtonText: 'Cancelar',
                allowOutsideClick: false, 
                backdrop: 'transparent', 
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../controladores/ControladorEliminarPersonal.php?id=' + idUsr;
                } else {
                    document.getElementById('modalEditar').classList.add('activo');
                }
            });
        }
    </script>
    <?php if(isset($_SESSION['alerta_personal'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_personal']['tipo'] == 'success' ? '¡Éxito!' : '¡Error!'; ?>',
                text: '<?php echo $_SESSION['alerta_personal']['mensaje']; ?>',
                icon: '<?php echo $_SESSION['alerta_personal']['tipo']; ?>',
                confirmButtonColor: '<?php echo $_SESSION['alerta_personal']['tipo'] == 'success' ? '#10b981' : '#ef4444'; ?>',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });
        </script>
        <?php unset($_SESSION['alerta_personal']); ?>
    <?php endif; ?>
</body>
</html>