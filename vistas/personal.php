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
    $sql = "SELECT p.id_personal, p.cedula, p.nombres, p.apellidos, p.telefono, p.foto_perfil, 
                   p.hora_entrada_personalizada, p.hora_salida_personalizada, p.id_cargo,
                   c.nombre_cargo, u.estado, u.id_usuario, u.id_rol, u.nombre_usuario 
            FROM personal p
            INNER JOIN cargos c ON p.id_cargo = c.id_cargo
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            ORDER BY p.nombres ASC";
    $stmt = $conexion->query($sql);
    $lista_personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt_roles = $conexion->query("SELECT id_rol, nombre_rol FROM roles");
    $lista_roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

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
                    <p>Gestiona los perfiles, accesos y datos personales de tu equipo.</p>
                </div>
            </div>

            <div class="contenedor-filtros-globales" style="justify-content: center; margin-inline: auto; max-inline-size: 500px; margin-block-end: 25px; padding: 15px;">
                <div class="contenedor-busqueda-elegante" style="margin: 0; inline-size: 100%;">
                  <input type="text" id="buscador-universal" class="campo-busqueda-elegante" placeholder="Buscar por nombre o cargo...">
                    <svg class="icono-busqueda" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <div class="botones-filtro-cargo">
                <button class="btn-filtro activo" onclick="aplicarFiltroUniversal('todos', this, true)">Todos</button>
                <?php foreach($cargos as $c): ?>
                    <button class="btn-filtro" onclick="aplicarFiltroUniversal(<?php echo $c['id_cargo']; ?>, this, true)">
                        <?php echo htmlspecialchars($c['nombre_cargo']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="grid-perfiles">
                <?php foreach ($lista_personal as $emp): ?>
                    <?php $esMiPerfil = ($emp['id_usuario'] == $_SESSION['id_usuario']); ?>
                    <div class="tarjeta-perfil item-filtrable <?php echo ($emp['estado'] == 'Inactivo') ? 'inactivo' : ''; ?>" data-cargo="<?php echo $emp['id_cargo']; ?>">
                        <div class="banner-tarjeta">
                            <span class="etiqueta-estado"><?php echo $emp['estado']; ?></span>
                            <?php if ($esMiPerfil): ?>
                                <span style="background: #000000;color:#fff;font-size:0.65rem;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:0.5px;text-transform:uppercase;">Tú</span>
                            <?php endif; ?>
                        </div>
                        <div class="contenedor-avatar">
                            <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($emp['foto_perfil']); ?>" alt="Foto" loading="lazy">
                        </div>
                        <div class="info-perfil">
                            <h3 class="nombre-empleado"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></h3>
                            <span class="cargo-empleado"><?php echo htmlspecialchars($emp['nombre_cargo']); ?></span>
                            <div class="detalles-empleado">
                                <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg> C.I: <?php echo htmlspecialchars($emp['cedula']); ?></p>
                                <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> <?php echo htmlspecialchars($emp['telefono']); ?></p>
                            </div>
                            <div class="acciones-perfil">
                                <button class="btn-editar-horario" 
                                    data-id="<?php echo $emp['id_personal']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?>"
                                    data-entrada="<?php echo $emp['hora_entrada_personalizada'] ? date('H:i', strtotime($emp['hora_entrada_personalizada'])) : ''; ?>"
                                    data-salida="<?php echo $emp['hora_salida_personalizada'] ? date('H:i', strtotime($emp['hora_salida_personalizada'])) : ''; ?>"
                                    onclick="abrirModalHorario(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Horario
                                </button>
                                <button class="btn-editar-usuario"
                                    data-idpersonal="<?php echo $emp['id_personal']; ?>"
                                    data-idusuario="<?php echo $emp['id_usuario']; ?>"
                                    data-nombres="<?php echo htmlspecialchars($emp['nombres']); ?>"
                                    data-apellidos="<?php echo htmlspecialchars($emp['apellidos']); ?>"
                                    data-cedula="<?php echo htmlspecialchars($emp['cedula']); ?>"
                                    data-telefono="<?php echo htmlspecialchars($emp['telefono']); ?>"
                                    data-usuario="<?php echo htmlspecialchars($emp['nombre_usuario']); ?>"
                                    data-cargo="<?php echo $emp['id_cargo']; ?>"
                                    data-estado="<?php echo $emp['estado']; ?>"
                                    data-rol="<?php echo $emp['id_rol']; ?>"
                                    onclick="abrirModalEditar(this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div id="contenedor-ver-mas-personal" style="text-align: center; margin-block-start: 10px; margin-block-end: 30px; display: none;">
                <button id="btn-ver-mas-personal" class="btn-guardar" style="background-color: var(--primary-color); border-radius: 25px; padding: 0.8rem 2rem; font-size: 0.95rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="inline-size: 20px; block-size: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    Cargar más personal
                </button>
            </div>
        </main>
    </div>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!--  MODALES                                                       -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <div class="modal-overlay" id="modalOverlay">
        
        <!-- Modal Horario -->
        <div class="modal-contenido" id="modalHorario">
            <div class="modal-header">
                <h2 id="modal_h_nombre">Horario</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModales()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form action="../controladores/ControladorHorario.php" method="POST">
                <input type="hidden" name="id_personal" id="modal_h_id_personal">
                <p style="font-size: 0.85rem; margin-block-end: 15px; color: var(--text-color);">*Si dejas los campos vacíos, el empleado utilizará el horario general.</p>
                <div class="grupo-input" style="margin-block-end: 15px;">
                    <label>Entrada Especial</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        <input type="time" name="hora_entrada" id="modal_h_entrada">
                    </div>
                </div>
                <div class="grupo-input" style="margin-block-end: 25px;">
                    <label>Salida Especial</label>
                    <div class="input-con-icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        <input type="time" name="hora_salida" id="modal_h_salida">
                    </div>
                </div>
                <button type="submit" class="btn-guardar" style="inline-size: 100%; justify-content: center;">Guardar Horario</button>
            </form>
        </div>

        <!-- Modal Editar Personal -->
        <div class="modal-contenido" id="modalEditar" style="max-inline-size: 1000px; inline-size: 95%;">
            <div class="modal-header">
                <h2 id="modal_e_nombre">Editar Usuario</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModales()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            
            <form id="form-editar-personal" action="../controladores/ControladorEditarPersonal.php" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate>
                <input style="display:none" type="text" name="fakeusernameremembered"/>
                <input style="display:none" type="password" name="fakepasswordremembered"/>

                <input type="hidden" name="id_usuario"  id="modal_e_id_usuario">
                <input type="hidden" name="id_personal" id="modal_e_id_personal">
                <!-- Inputs ocultos que envían estado y rol cuando los selects están disabled -->
                <input type="hidden" name="estado"  id="modal_e_estado_hidden">
                <input type="hidden" name="id_rol"   id="modal_e_rol_hidden">
                
                <div class="grid-edicion">

                    <!-- Nombres -->
                    <div class="grupo-input">
                        <label for="modal_e_nombres">Nombres</label>
                        <div class="input-con-icono">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <input type="text" name="nombres" id="modal_e_nombres" autocomplete="new-password" placeholder="Ej: María José">
                        </div>
                        <span class="mensaje-error-campo" id="err-nombres">Solo se permiten letras y espacios.</span>
                    </div>

                    <!-- Apellidos -->
                    <div class="grupo-input">
                        <label for="modal_e_apellidos">Apellidos</label>
                        <div class="input-con-icono">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <input type="text" name="apellidos" id="modal_e_apellidos" autocomplete="new-password" placeholder="Ej: González Pérez">
                        </div>
                        <span class="mensaje-error-campo" id="err-apellidos">Solo se permiten letras y espacios.</span>
                    </div>

                    <!-- Cédula -->
                    <div class="grupo-input">
                        <label for="modal_e_cedula">Cédula</label>
                        <div class="input-con-icono">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" /></svg>
                            <input type="text" name="cedula" id="modal_e_cedula" autocomplete="new-password" placeholder="Ej: 12345678" maxlength="12">
                        </div>
                        <span class="mensaje-error-campo" id="err-cedula">Solo números, entre 6 y 12 dígitos.</span>
                    </div>

                    <!-- Teléfono -->
                    <div class="grupo-input">
                        <label for="modal_e_telefono">Teléfono</label>
                        <div class="input-con-icono">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <input type="text" name="telefono" id="modal_e_telefono" autocomplete="new-password" placeholder="Ej: 0414-1234567" maxlength="15">
                        </div>
                        <span class="mensaje-error-campo" id="err-telefono">Formato inválido. Ej: 0414-1234567</span>
                    </div>

                    <!-- Usuario -->
                    <div class="grupo-input">
                        <label for="modal_e_usuario">Usuario (Sistema)</label>
                        <div class="input-con-icono">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <input type="text" name="usuario" id="modal_e_usuario" autocomplete="new-password" placeholder="Ej: maria.gonzalez" maxlength="30">
                        </div>
                        <span class="mensaje-error-campo" id="err-usuario">Solo letras, números, puntos y guiones bajos (4-30 caracteres).</span>
                    </div>

                    <!-- Cargo -->
                    <div class="grupo-input">
                        <label for="modal_e_cargo">Cargo</label>
                        <div class="input-con-icono">
                            <select name="id_cargo" id="modal_e_cargo" style="inline-size: 100%; padding: 12px 15px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat'; font-weight: 600;">
                                <?php foreach ($cargos as $c): ?>
                                    <option value="<?php echo $c['id_cargo']; ?>"><?php echo htmlspecialchars($c['nombre_cargo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="grupo-input">
                        <label for="modal_e_estado">
                            Estado
                            <span id="aviso-estado-bloqueado" style="display:none; font-size:0.7rem; color:var(--primary-color); font-weight:600; margin-inline-start:6px;">(no puedes desactivar tu propia cuenta)</span>
                        </label>
                        <div class="input-con-icono">
                            <!-- ✅ CAMBIO: name corregido de "estado_display" → "estado_display" se mantiene visual,
                                 pero ahora sincroniza el hidden "estado" al cambiar -->
                            <select name="estado_display" id="modal_e_estado"
                                onchange="document.getElementById('modal_e_estado_hidden').value = this.value;"
                                style="inline-size: 100%; padding: 12px 15px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat'; font-weight: 600;">
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Rol -->
                    <div class="grupo-input">
                        <label for="modal_e_rol">
                            Rol
                            <span id="aviso-rol-bloqueado" style="display:none; font-size:0.7rem; color:var(--primary-color); font-weight:600; margin-inline-start:6px;">(no puedes cambiar tu propio rol)</span>
                        </label>
                        <div class="input-con-icono">
                            <!-- ✅ CAMBIO: ahora sincroniza el hidden "id_rol" al cambiar el select -->
                            <select name="id_rol_display" id="modal_e_rol"
                                onchange="document.getElementById('modal_e_rol_hidden').value = this.value;"
                                style="inline-size: 100%; padding: 12px 15px; border: 2px solid var(--bg-light); border-radius: 10px; background-color: var(--bg-light); color: var(--text-color); font-family: 'Montserrat'; font-weight: 600;">
                                <?php foreach ($lista_roles as $r): ?>
                                    <option value="<?php echo $r['id_rol']; ?>"><?php echo htmlspecialchars($r['nombre_rol']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Foto -->
                    <div class="grupo-input campo-completo">
                        <label>Cambiar Foto (Opcional)</label>
                        <label class="btn-subir-archivo">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span id="texto-archivo-editar">Seleccionar nueva imagen...</span>
                            <input type="file" name="foto_perfil" id="modal_e_foto" class="input-file-oculto" accept="image/jpeg,image/png,image/webp,image/gif"
                                onchange="validarFoto(this)">
                        </label>
                        <span class="mensaje-error-campo" id="err-foto">Solo JPG, PNG, WEBP o GIF. Máximo 2 MB.</span>
                    </div>

                </div><!-- /grid-edicion -->

                <div class="botones-accion-formulario" style="align-items: center; justify-content: space-between;">
                    <button type="button" id="btn-eliminar-empleado" class="btn-eliminar-usuario" onclick="confirmarEliminacion()" style="margin: 0; inline-size: auto; border: none; background: transparent;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Eliminar Registro
                    </button>
                    <button type="submit" class="btn-guardar" id="btn-guardar-empleado">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div><!-- /modal-overlay -->

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
        const inputBuscadorUniv = document.getElementById('buscador-universal');
        let cargoActivoUniv = 'todos'; 
        const itemsPorCarga = 8;
        let limiteActual = itemsPorCarga;

        // ── Filtro y paginación ─────────────────────────────────────────
        function aplicarFiltroUniversal(idCargo = null, botonSeleccionado = null, reiniciarPaginacion = true) {
            if (reiniciarPaginacion) limiteActual = itemsPorCarga;
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

        if (inputBuscadorUniv) inputBuscadorUniv.addEventListener('input', () => aplicarFiltroUniversal(null, null, true));
        const btnCargarMas = document.getElementById('btn-ver-mas-personal');
        if (btnCargarMas) btnCargarMas.addEventListener('click', () => {
            limiteActual += itemsPorCarga;
            aplicarFiltroUniversal(cargoActivoUniv, document.querySelector('.btn-filtro.activo'), false);
        });
        document.addEventListener('DOMContentLoaded', () => aplicarFiltroUniversal(null, null, true));

        // ── Tema ─────────────────────────────────────────────────────────
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        const html = document.documentElement;
        if (btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
            });
        }

        // ── Modales ───────────────────────────────────────────────────────
        const modalOverlay = document.getElementById('modalOverlay');
        const modalHorario = document.getElementById('modalHorario');
        const modalEditar  = document.getElementById('modalEditar');

        function abrirModalHorario(btn) {
            document.getElementById('modal_h_id_personal').value = btn.dataset.id;
            document.getElementById('modal_h_nombre').textContent = "Horario: " + btn.dataset.nombre.split(' ')[0];
            document.getElementById('modal_h_entrada').value = btn.dataset.entrada;
            document.getElementById('modal_h_salida').value  = btn.dataset.salida;
            modalOverlay.classList.add('activo');
            modalHorario.classList.add('activo');
        }

        function abrirModalEditar(btn) {
            const MI_ID    = "<?php echo $_SESSION['id_usuario']; ?>";
            const esElMismo = (btn.dataset.idusuario == MI_ID);

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

            // Limpiar estado de validación al abrir
            limpiarValidaciones();

            // Bloqueos de protección propia
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

            modalOverlay.classList.add('activo');
            modalEditar.classList.add('activo');
        }

        function cerrarModales() {
            const modalActivo = document.querySelector('.modal-contenido.activo');
            if (modalActivo) modalActivo.classList.add('cerrando');
            modalOverlay.classList.add('cerrando');
            setTimeout(function() {
                modalOverlay.classList.remove('activo', 'cerrando');
                modalHorario.classList.remove('activo', 'cerrando');
                modalEditar.classList.remove('activo', 'cerrando');
            }, 220);
        }

        modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) cerrarModales(); });

        // ══════════════════════════════════════════════════════════════════
        // VALIDACIÓN JS ─ helpers
        // ══════════════════════════════════════════════════════════════════

        // Muestra o quita el estado de error en un campo y su mensaje
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
            return !mostrar; // true = válido
        }

        // Limpia todos los estados de validación del formulario
        function limpiarValidaciones() {
            ['modal_e_nombres','modal_e_apellidos','modal_e_cedula','modal_e_telefono','modal_e_usuario','modal_e_foto']
                .forEach(id => {
                    const el = document.getElementById(id);
                    if (el) { el.classList.remove('input-error','input-ok'); }
                });
            document.querySelectorAll('.mensaje-error-campo').forEach(el => el.classList.remove('visible'));
        }

        // ── Reglas de validación ──────────────────────────────────────────
        const REGEX = {
            nombres:   /^[\u00C0-\u024Fa-zA-Z\s\-'\.]+$/,   // letras, tildes, espacios, guion, punto, apóstrofe
            cedula:    /^\d{6,12}$/,
            telefono:  /^[\d\s\-\+\(\)]{7,15}$/,
            usuario:   /^[a-zA-Z0-9_\.]{4,30}$/
        };

        function validarCampo(inputId, errorId, regex, valorMinLen = 1) {
            const valor = document.getElementById(inputId).value.trim();
            const invalido = valor.length < valorMinLen || (regex && !regex.test(valor));
            return setError(inputId, errorId, invalido);
        }

        // Validación en tiempo real al salir del campo (blur)
        document.getElementById('modal_e_nombres').addEventListener('blur',    () => validarCampo('modal_e_nombres',   'err-nombres',   REGEX.nombres,   2));
        document.getElementById('modal_e_apellidos').addEventListener('blur',  () => validarCampo('modal_e_apellidos', 'err-apellidos', REGEX.nombres,   2));
        document.getElementById('modal_e_cedula').addEventListener('blur',     () => validarCampo('modal_e_cedula',    'err-cedula',    REGEX.cedula,    6));
        document.getElementById('modal_e_telefono').addEventListener('blur',   () => validarCampo('modal_e_telefono',  'err-telefono',  REGEX.telefono,  7));
        document.getElementById('modal_e_usuario').addEventListener('blur',    () => validarCampo('modal_e_usuario',   'err-usuario',   REGEX.usuario,   4));

        // Quitar error mientras el usuario corrige (input event)
        ['modal_e_nombres','modal_e_apellidos','modal_e_cedula','modal_e_telefono','modal_e_usuario'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                if (this.classList.contains('input-error')) {
                    this.classList.remove('input-error');
                    const errId = 'err-' + id.replace('modal_e_','');
                    const errEl = document.getElementById(errId);
                    if (errEl) errEl.classList.remove('visible');
                }
            });
        });

        // Validación de foto al seleccionar
        function validarFoto(input) {
            const nombre = input.files[0] ? input.files[0].name : 'Seleccionar nueva imagen...';
            document.getElementById('texto-archivo-editar').textContent = nombre;

            if (!input.files[0]) return true;

            const tiposPermitidos = ['image/jpeg','image/png','image/webp','image/gif'];
            const tamanoMax = 2 * 1024 * 1024; // 2 MB
            const archivo = input.files[0];
            const invalido = !tiposPermitidos.includes(archivo.type) || archivo.size > tamanoMax;
            return setError('modal_e_foto', 'err-foto', invalido);
        }

        // ── Validación completa al enviar ─────────────────────────────────
        document.getElementById('form-editar-personal').addEventListener('submit', function(e) {
            const checks = [
                validarCampo('modal_e_nombres',   'err-nombres',   REGEX.nombres,   2),
                validarCampo('modal_e_apellidos', 'err-apellidos', REGEX.nombres,   2),
                validarCampo('modal_e_cedula',    'err-cedula',    REGEX.cedula,    6),
                validarCampo('modal_e_telefono',  'err-telefono',  REGEX.telefono,  7),
                validarCampo('modal_e_usuario',   'err-usuario',   REGEX.usuario,   4),
            ];

            // Si hay foto seleccionada, validarla también
            const inputFoto = document.getElementById('modal_e_foto');
            if (inputFoto.files[0]) {
                checks.push(validarFoto(inputFoto));
            }

            const formularioValido = checks.every(Boolean);

            if (!formularioValido) {
                e.preventDefault();
                // Animación shake en el botón de guardar
                const btnGuardar = document.getElementById('btn-guardar-empleado');
                btnGuardar.classList.add('sacudir');
                btnGuardar.addEventListener('animationend', () => btnGuardar.classList.remove('sacudir'), { once: true });
                // Hacer scroll al primer error dentro del modal
                const primerError = modalEditar.querySelector('.input-error');
                if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        // ── Eliminación ───────────────────────────────────────────────────
        function confirmarEliminacion() {
            const idUsr  = document.getElementById('modal_e_id_usuario').value;
            const nombre = document.getElementById('modalEditar').getAttribute('data-nombre-eliminar');
            cerrarModales();
            Swal.fire({
                title: '¿Eliminar a ' + nombre + '?',
                text: "Esta acción borrará todo su historial. Es definitiva.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, Eliminar Todo',
                cancelButtonText: 'Cancelar',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color:      html.getAttribute('data-theme') === 'dark' ? '#fff'    : '#333'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../controladores/ControladorEliminarPersonal.php?id=' + idUsr;
                } else {
                    modalOverlay.classList.remove('cerrando');
                    modalEditar.classList.remove('cerrando');
                    modalOverlay.classList.add('activo');
                    modalEditar.classList.add('activo');
                }
            });
        }
    </script>

    <?php if(isset($_SESSION['alerta_personal'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_personal']['tipo'] == 'success' ? 'Éxito' : 'Error'; ?>',
                text: '<?php echo addslashes($_SESSION['alerta_personal']['mensaje']); ?>',
                icon: '<?php echo $_SESSION['alerta_personal']['tipo']; ?>',
                background: html.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: html.getAttribute('data-theme') === 'dark' ? '#fff' : '#333',
                confirmButtonColor: '<?php echo $_SESSION['alerta_personal']['tipo'] == 'success' ? '#10b981' : '#ef4444'; ?>'
            });
        </script>
        <?php unset($_SESSION['alerta_personal']); ?>
    <?php endif; ?>
</body>
</html>