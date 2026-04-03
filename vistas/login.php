<?php
session_start();
// --- NUEVO: Si ya está logueado, mandarlo al principal ---
if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    header("Location: principal.php");
    exit;
}
require_once '../configuracion/conexion.php';

try {
    $sql_check = "SELECT COUNT(*) FROM usuarios WHERE id_rol = 1";
    $stmt_check = $conexion->query($sql_check);
    $existe_director = ($stmt_check->fetchColumn() > 0);

    if (!$existe_director) {
        $sql_cargos = "SELECT id_cargo, nombre_cargo FROM cargos WHERE nombre_cargo = 'Directora'";
        $mensaje_alerta = "¡Atención! Serás registrado como el PRIMER USUARIO (Director/a).";
    } else {
        $sql_cargos = "SELECT id_cargo, nombre_cargo FROM cargos WHERE nombre_cargo NOT IN ('Directora', 'Subdirectora')";
        $mensaje_alerta = "";
    }

    $stmt_cargos = $conexion->prepare($sql_cargos);
    $stmt_cargos->execute();
    $lista_cargos = $stmt_cargos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lista_cargos = [];
}

$preguntas_seguridad = [
    1 => "¿Nombre de tu primera mascota?",
    2 => "¿Ciudad de nacimiento de tu padre?",
    3 => "¿Mejor amigo de la infancia?",
    4 => "¿Plato de comida favorito?",
    5 => "¿Marca de tu primer vehículo?",
    6 => "¿Nombre de tu escuela primaria?",
    7 => "¿Personaje histórico favorito?",
    8 => "¿Apellido de soltera de tu madre?"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso CEIS Julian Yánez</title>
    <link rel="stylesheet" href="../recursos/css/login.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="contenedor-principal" id="contenedorPrincipal">

        <div class="contenedor-formulario contenedor-registro">
            <form action="../controladores/ControladorRegistro.php" method="POST" id="formularioRegistro" autocomplete="off" enctype="multipart/form-data">
                <h1>Crear Cuenta</h1>

                <div class="paso-registro activo" id="paso1">
                    <span>Paso 1: Datos Personales</span>

                    <div class="contenedor-foto-perfil">
                        <label for="registro_foto" class="label-foto">
                            <img src="../recursos/img/perfiles/default.png" id="vista_previa_foto" alt="Foto de perfil">
                            <div class="capa-subir">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                        </label>
                        <input type="file" name="foto_perfil" id="registro_foto" accept="image/jpeg, image/png" style="display: none;">
                    </div>

                    <input type="text" name="cedula" id="registro_cedula" placeholder="Cédula"
                        maxlength="8"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="nope"/>

                    <div style="display:flex; gap:5px; width:100%">
                        <input type="text" name="nombres" id="registro_nombres" placeholder="Nombres" autocomplete="off" />
                        <input type="text" name="apellidos" id="registro_apellidos" placeholder="Apellidos" autocomplete="off" />
                    </div>
                    
                    <input type="text" name="telefono" id="registro_telefono" placeholder="Teléfono" maxlength="11"
                     oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="off"/>

                    <?php if (!empty($mensaje_alerta)): ?>
                        <div style="background-color: #e3f2fd; color: #0d47a1; padding: 5px; font-size: 11px; margin-bottom: 5px; border-radius: 4px;">
                            <?php echo $mensaje_alerta; ?>
                        </div>
                    <?php endif; ?>

                    <select name="id_cargo" id="registro_cargo">
                        <option value="" disabled selected>Seleccione Cargo</option>
                        <?php foreach ($lista_cargos as $cargo): ?>
                            <option value="<?php echo $cargo['id_cargo']; ?>"><?php echo $cargo['nombre_cargo']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="botones-navegacion">
                        <span class="btn-cambio-movil" onclick="irALoginMovil()">Ya tengo cuenta</span>
                        <button type="button" class="btn-siguiente" onclick="cambiarPaso(1)">Siguiente</button>
                    </div>
                </div>

                <div class="paso-registro" id="paso2">
                    <span>Paso 2: Datos de Usuario</span>
                    <input type="text" name="nuevo_usuario" id="registro_usuario" placeholder="Usuario deseado" autocomplete="off" />
                    
                    <div class="contenedor-clave">
                        <input type="password" name="nueva_password" id="registro_clave1" placeholder="Contraseña (Mín. 6 carácteres)" autocomplete="new-password" />
                        <span class="icono-alternar" onclick="alternarVisibilidad('registro_clave1', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar" viewBox="0 0 512 512" style="display:none;"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                        </span>
                    </div>

                    <div class="contenedor-clave">
                        <input type="password" id="registro_clave2" placeholder="Confirmar Contraseña" autocomplete="new-password"/>
                        <span class="icono-alternar" onclick="alternarVisibilidad('registro_clave2', this)">
                           <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                           <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar" viewBox="0 0 512 512" style="display:none;"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                        </span>
                    </div>

                    <div class="botones-navegacion">
                        <button type="button" class="btn-atras" onclick="cambiarPaso(-1)">Atrás</button>
                        <button type="button" class="btn-siguiente" onclick="cambiarPaso(1)">Siguiente</button>
                    </div>
                </div>

                <div class="paso-registro campos-compactos" id="paso3">
                    <span>Paso 3: Seguridad</span>
                    <div style="width:100%; text-align:left; font-size:10px; color:#666;">Seleccione 3 preguntas DISTINTAS:</div>

                    <select name="pregunta_1" id="registro_pregunta_1">
                        <option value="" disabled selected>Pregunta 1</option>
                        <?php foreach ($preguntas_seguridad as $k => $v): echo "<option value='$k'>$v</option>"; endforeach; ?>
                    </select>
                    <input type="text" name="respuesta_1" id="registro_respuesta_1" placeholder="Respuesta 1" autocomplete="off" />

                    <select name="pregunta_2" id="registro_pregunta_2">
                        <option value="" disabled selected>Pregunta 2</option>
                        <?php foreach ($preguntas_seguridad as $k => $v): echo "<option value='$k'>$v</option>"; endforeach; ?>
                    </select>
                    <input type="text" name="respuesta_2" id="registro_respuesta_2" placeholder="Respuesta 2" autocomplete="off"/>

                    <select name="pregunta_3" id="registro_pregunta_3">
                        <option value="" disabled selected>Pregunta 3</option>
                        <?php foreach ($preguntas_seguridad as $k => $v): echo "<option value='$k'>$v</option>"; endforeach; ?>
                    </select>
                    <input type="text" name="respuesta_3" id="registro_respuesta_3" placeholder="Respuesta 3" autocomplete="off" />

                    <div class="botones-navegacion">
                        <button type="button" class="btn-atras" onclick="cambiarPaso(-1)">Atrás</button>
                        <button type="submit">Finalizar</button>
                    </div>
                </div>

                <div class="indicadores-pasos">
                    <span class="punto activo"></span>
                    <span class="punto"></span>
                    <span class="punto"></span>
                </div>
            </form>
        </div>

        <div class="contenedor-formulario contenedor-ingreso">
            <form action="../controladores/ControladorAcceso.php" method="POST" id="formularioLogin" autocomplete="off">
                
            <img src="../recursos/img/logo_ceis.jpg" alt="Escudo CEIS Julián Yánez" class="logo-login-flotante">
            <h1>CEIS Julian Yánez</h1>
                <span>Sistema de Asistencia</span>

                <input type="text" name="nombre_usuario" id="login_usuario" placeholder="Usuario" autocomplete="off" />
                
                <div class="contenedor-clave">
                    <input type="password" name="password" id="login_clave" placeholder="Contraseña" autocomplete="new-password"/>
                    <span class="icono-alternar" onclick="alternarVisibilidad('login_clave', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ver" viewBox="0 0 512 512"><path d="M255.66 112c-77.94 0-157.89 45.11-220.83 135.33a16 16 0 00-.27 17.77C82.92 340.8 161.8 400 255.66 400c92.84 0 173.34-59.38 221.79-135.25a16.14 16.14 0 000-17.47C428.89 172.28 347.8 112 255.66 112z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/><circle cx="256" cy="256" r="80" fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icono-svg icono-ocultar" viewBox="0 0 512 512" style="display:none;"><path d="M432 448a15.92 15.92 0 01-11.31-4.69l-352-352a16 16 0 0122.62-22.62l352 352A16 16 0 01432 448zM255.66 384c-41.49 0-81.5-12.28-118.92-36.5-34.07-22-64.74-53.51-88.7-91v-.08c19.94-28.57 41.78-52.73 65.24-72.21a2 2 0 00.14-2.94L93.5 161.38a2 2 0 00-2.71-.12c-24.92 21-48.05 46.76-69.08 76.92a31.92 31.92 0 00-.64 35.54c26.41 41.33 60.4 76.14 98.28 100.65C162 402 207.9 416 255.66 416a239.13 239.13 0 0075.8-12.58 2 2 0 00.77-3.31l-21.58-21.58a4 4 0 00-3.83-1 204.8 204.8 0 01-51.16 6.47zM490.84 238.6c-26.46-40.92-60.79-75.68-99.27-100.53C349 110.55 302 96 255.66 96a227.34 227.34 0 00-74.89 12.83 2 2 0 00-.75 3.31l21.55 21.55a4 4 0 003.88 1 192.82 192.82 0 0150.21-6.69c40.69 0 80.58 12.43 118.55 37 34.71 22.4 65.74 53.88 89.76 91a.13.13 0 010 .16 310.72 310.72 0 01-64.12 72.73 2 2 0 00-.15 2.95l19.9 19.89a2 2 0 002.7.13 343.49 343.49 0 0068.64-78.48 32.2 32.2 0 00-.1-34.78z"/><path d="M256 160a95.88 95.88 0 00-21.37 2.4 2 2 0 00-1 3.38l112.59 112.56a2 2 0 003.38-1A96 96 0 00256 160zM165.78 233.66a2 2 0 00-3.38 1 96 96 0 00115 115 2 2 0 001-3.38z"/></svg>
                    </span>
                </div>

                <a href="recuperar_contraseña.php">¿Olvidaste tu contraseña?</a>
                <button type="submit">Ingresar</button>
                <span class="btn-cambio-movil" onclick="irARegistroMovil()">Crear cuenta nueva</span>
            </form>
        </div>

        <div class="contenedor-capa">
            <div class="capa-deslizante">
                <div class="panel-superior panel-izquierdo">
                    <h1>¡Crea tu cuenta para ser parte del Equipo!</h1>
                    <p>Si ya tienes cuenta, inicia sesión aquí.</p>
                    <button class="boton-transparente" id="botonIrLogin">Ingresar</button>
                </div>
                <div class="panel-superior panel-derecho">
                    <h1>¡Bienvenido a Asistencia Julian Yánez!</h1>
                    <p>¿No tienes cuenta aún? Regístrate aquí.</p>
                    <button class="boton-transparente" id="botonIrRegistro">Registrarse</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // === ELEMENTOS DEL DOM ===
        const btnIrRegistro = document.getElementById('botonIrRegistro');
        const btnIrLogin = document.getElementById('botonIrLogin');
        const contenedorPrincipal = document.getElementById('contenedorPrincipal');
        const formularioRegistro = document.getElementById('formularioRegistro');
        const formularioLogin = document.getElementById('formularioLogin');
        const pasos = document.getElementsByClassName("paso-registro");
        const puntos = document.getElementsByClassName("punto");
        
        // Elementos de Preguntas (Nombres descriptivos)
        const selectPregunta1 = document.getElementById('registro_pregunta_1');
        const selectPregunta2 = document.getElementById('registro_pregunta_2');
        const selectPregunta3 = document.getElementById('registro_pregunta_3');
        const grupoSelects = [selectPregunta1, selectPregunta2, selectPregunta3];

        let pasoActual = 0;

        // === NUEVO: PREVISUALIZAR FOTO DE PERFIL ===
        document.getElementById('registro_foto').addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('vista_previa_foto').src = e.target.result;
            }
            if(e.target.files[0]) {
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // === NAVEGACIÓN ENTRE PANELES ===
        btnIrRegistro.addEventListener('click', () => { contenedorPrincipal.classList.add("panel-derecho-activo"); });
        btnIrLogin.addEventListener('click', () => { contenedorPrincipal.classList.remove("panel-derecho-activo"); });
        
        function irARegistroMovil() { contenedorPrincipal.classList.add("modo-movil-registro"); }
        function irALoginMovil() { contenedorPrincipal.classList.remove("modo-movil-registro"); }

        // === LÓGICA DE BLOQUEO DE PREGUNTAS ===
        function actualizarSelects() {
            const valoresSeleccionados = grupoSelects.map(s => s.value).filter(v => v !== "");
            grupoSelects.forEach(selectActual => {
                Array.from(selectActual.options).forEach(opcion => {
                    if (opcion.value === "") return;
                    if (valoresSeleccionados.includes(opcion.value) && selectActual.value !== opcion.value) {
                        opcion.disabled = true;
                    } else {
                        opcion.disabled = false;
                    }
                });
            });
        }

        grupoSelects.forEach(select => { select.addEventListener('change', actualizarSelects); });

        // === FUNCIONES DE ERROR VISUAL ===
        function mostrarError(input) {
            input.classList.add('campo-error', 'animacion-vibrar');
            setTimeout(() => { input.classList.remove('animacion-vibrar'); }, 500);
        }
        function limpiarError(input) { input.classList.remove('campo-error'); }

        // === CONTROL DE PASOS REGISTRO ===
        function cambiarPaso(n) {
            if (n === 1 && !validarPasoActual()) return false;
            
            pasos[pasoActual].classList.remove("activo");
            puntos[pasoActual].classList.remove("activo");
            pasoActual = pasoActual + n;
            pasos[pasoActual].classList.add("activo");
            puntos[pasoActual].classList.add("activo");
        }

        function validarPasoActual() {
            let esValido = true;

            // PASO 1: DATOS PERSONALES
            if (pasoActual === 0) {
                const cedula = document.getElementById("registro_cedula");
                const nombres = document.getElementById("registro_nombres");
                const apellidos = document.getElementById("registro_apellidos");
                const cargo = document.getElementById("registro_cargo");
                const telefono = document.getElementById("registro_telefono");

                [cedula, nombres, apellidos, cargo, telefono].forEach(limpiarError);

                if (cedula.value.trim() === "") { mostrarError(cedula); esValido = false; }
                else if (cedula.value.length < 6) { mostrarError(cedula); esValido = false; }

                if (nombres.value.trim() === "") { mostrarError(nombres); esValido = false; }
                if (apellidos.value.trim() === "") { mostrarError(apellidos); esValido = false; }
                if (cargo.value === "") { mostrarError(cargo); esValido = false; }

                if (telefono.value.trim() === "") { 
                    mostrarError(telefono); esValido = false; 
                } else if (telefono.value.length < 11) { 
                    mostrarError(telefono); esValido = false; 
                }
            }

            // PASO 2: USUARIO Y CLAVE
            if (pasoActual === 1) {
                const usuario = document.getElementById("registro_usuario");
                const clave1 = document.getElementById("registro_clave1");
                const clave2 = document.getElementById("registro_clave2");

                [usuario, clave1, clave2].forEach(limpiarError);

                if (usuario.value.trim() === "") { mostrarError(usuario); esValido = false; }
                if (clave1.value.trim() === "") { mostrarError(clave1); esValido = false; }
                if (clave2.value.trim() === "") { mostrarError(clave2); esValido = false; }

                if (!esValido) return false;

                if (clave1.value.length < 6) {
                    Swal.fire({ title: 'Contraseña Corta', text: 'Mínimo 6 caracteres.', icon: 'warning', confirmButtonColor: '#cc0000', heightAuto: false });
                    mostrarError(clave1);
                    return false;
                }

                if (clave1.value !== clave2.value) {
                    Swal.fire({ title: 'Error', text: 'Las contraseñas no coinciden.', icon: 'warning', confirmButtonColor: '#cc0000', heightAuto: false });
                    mostrarError(clave2);
                    return false;
                }
            }
            return esValido;
        }

        // === VALIDACIÓN FINAL AL ENVIAR REGISTRO ===
        formularioRegistro.addEventListener('submit', function(e) {
            e.preventDefault(); 
            let esValido = true;
            
            const resp1 = document.getElementById('registro_respuesta_1');
            const resp2 = document.getElementById('registro_respuesta_2');
            const resp3 = document.getElementById('registro_respuesta_3');
            
            const campos = [selectPregunta1, selectPregunta2, selectPregunta3, resp1, resp2, resp3];
            
            campos.forEach(limpiarError);

            campos.forEach(input => {
                if (input.value.trim() === "") {
                    mostrarError(input);
                    esValido = false;
                }
            });

            if (!esValido) return false;

            const v1 = selectPregunta1.value;
            const v2 = selectPregunta2.value;
            const v3 = selectPregunta3.value;

            if (v1 === v2 || v1 === v3 || v2 === v3) {
                 Swal.fire({
                    title: 'Preguntas Repetidas',
                    text: 'Error inesperado: Has seleccionado preguntas idénticas.',
                    icon: 'error', confirmButtonColor: '#003366', heightAuto: false 
                });
                return false;
            }

            this.submit();
        });

        // === VALIDACIÓN FORMULARIO LOGIN ===
        formularioLogin.addEventListener('submit', function(e) {
            let usuario = document.getElementById('login_usuario');
            let clave = document.getElementById('login_clave');
            let esValido = true;

            [usuario, clave].forEach(limpiarError);

            if (usuario.value.trim() === '') { mostrarError(usuario); esValido = false; }
            if (clave.value.trim() === '') { mostrarError(clave); esValido = false; }

            if (!esValido) e.preventDefault();
        });

        // === FUNCIÓN MOSTRAR/OCULTAR CONTRASEÑA ===
        function alternarVisibilidad(idInput, contenedorIcono) {
            const input = document.getElementById(idInput);
            const iconoVer = contenedorIcono.querySelector('.icono-ver');
            const iconoOcultar = contenedorIcono.querySelector('.icono-ocultar');

            if (input.type === "password") {
                input.type = "text";
                iconoVer.style.display = 'none';
                iconoOcultar.style.display = 'block'; 
            } else {
                input.type = "password";
                iconoVer.style.display = 'block';
                iconoOcultar.style.display = 'none';
            }
        }
    </script>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>

    <?php if (isset($_SESSION['error_login'])): ?>
        <script>
            Swal.fire({
                title: '¡Error!', text: '<?php echo $_SESSION['error_login']; ?>',
                icon: 'error', confirmButtonText: 'Reintentar', confirmButtonColor: '#003366', heightAuto: false 
            });
        </script>
    <?php unset($_SESSION['error_login']); endif; ?>

    <?php if (isset($_SESSION['registro_exito'])): ?>
        <script>
            Swal.fire({
                title: '¡Registro Exitoso!', html: 'Cuenta creada correctamente.<br>Inicia sesión.',
                icon: 'success', confirmButtonText: 'Aceptar', confirmButtonColor: '#003366', heightAuto: false 
            });
        </script>
    <?php unset($_SESSION['registro_exito']); endif; ?>

</body>
</html>