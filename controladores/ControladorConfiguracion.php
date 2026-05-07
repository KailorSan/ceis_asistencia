<?php
/**
 * ============================================================
 * CONTROLADOR: Configuración de Asistencia
 * ARCHIVO: ControladorConfiguracion.php
 * PROYECTO: Sistema de Asistencia - CEIS Julian Yánez
 * ------------------------------------------------------------
 * Descripción:
 *   Procesa todas las operaciones relacionadas con la
 *   configuración de asistencia. Maneja cuatro acciones
 *   distintas recibidas mediante POST:
 *
 *   1. guardar_config     → Actualiza la configuración global
 *                           del sistema (tabla: configuracion).
 *                           Opcionalmente marca una plantilla
 *                           como "es_activa".
 *
 *   2. nueva_plantilla    → Inserta una nueva configuración
 *                           preestablecida (tabla:
 *                           configuraciones_preestablecidas).
 *                           Responde en JSON (para fetch).
 *
 *   3. eliminar_plantilla → Elimina una plantilla por su ID.
 *                           Responde en JSON (para fetch).
 *
 *   4. renombrar_plantilla → Actualiza el nombre de una
 *                            plantilla existente.
 *                            Responde en JSON (para fetch).
 *
 * Seguridad:
 *   - Verificación de sesión activa en todas las acciones.
 *   - Solo roles 1 (Administrador) y 2 (Coordinador) permitidos.
 *   - Sentencias preparadas (PDO) en todas las consultas SQL.
 *   - Validación de reglas de negocio en capa servidor.
 *   - Datos saneados antes de cualquier operación en BD.
 *
 * Reglas de negocio validadas (capa servidor):
 *   - Salida posterior a la entrada.
 *   - Jornada mínima: 1 hora (3600 segundos).
 *   - Jornada máxima: 8 horas (28800 segundos).
 *   - Tolerancia < duración total de la jornada.
 *   - Máximo 4 plantillas preestablecidas en BD.
 *
 * Patrón:
 *   - Formulario principal usa redirect (PRG pattern).
 *   - Acciones de fetch (nueva/eliminar/renombrar) devuelven JSON.
 *
 * Dependencias:
 *   - ../configuracion/conexion.php  (instancia PDO $conexion)
 *   - ControladorBitacora.php        (registro de auditoría)
 *
 * Última modificación: 2025
 * ============================================================
 */

session_start();
require_once '../configuracion/conexion.php';
require_once 'ControladorBitacora.php';

// ============================================================
// HELPER: Responder en JSON y terminar ejecución
// Usado por las acciones llamadas desde fetch() en el frontend.
//
// @param {bool}   $exito   - true si la operación fue exitosa
// @param {string} $mensaje - Texto descriptivo del resultado
// @param {array}  $datos   - Datos adicionales opcionales
// ============================================================
function responderJSON(bool $exito, string $mensaje = '', array $datos = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['exito' => $exito, 'mensaje' => $mensaje], $datos));
    exit;
}

// ============================================================
// HELPER: Validar reglas de negocio del horario
// Reutilizado por guardar_config y nueva_plantilla.
//
// @param string $horaEntrada  - Formato "HH:MM:SS"
// @param string $horaSalida   - Formato "HH:MM:SS"
// @param int    $tolerancia   - Minutos de gracia
// @return string|null  - Mensaje de error, o null si es válido
// ============================================================
function validarHorario(string $horaEntrada, string $horaSalida, int $tolerancia): ?string {
    $tsEntrada  = strtotime($horaEntrada);
    $tsSalida   = strtotime($horaSalida);
    $diferencia = $tsSalida - $tsEntrada;
    $durMinutos = $diferencia / 60;

    // Regla 1: Salida posterior a la entrada
    if ($tsSalida <= $tsEntrada) {
        return 'La hora de salida debe ser posterior a la hora de entrada.';
    }

    // Regla 2: Jornada mínima de 1 hora (3600 segundos)
    if ($diferencia < 3600) {
        return 'La jornada laboral debe ser de al menos 1 hora (60 minutos).';
    }

    // Regla 3: Jornada máxima de 8 horas (28800 segundos)
    if ($diferencia > 28800) {
        return 'La jornada no puede superar las 8 horas (480 minutos).';
    }

    // Regla 4: Tolerancia menor a la duración total de la jornada
    if ($tolerancia >= $durMinutos) {
        return "Los minutos de tolerancia ({$tolerancia} min) no pueden igualar o superar la duración de la jornada (" . (int)$durMinutos . " min).";
    }

    return null; // Todo válido
}


// ============================================================
// CONTROL DE ACCESO Y SESIÓN
// Verificación estricta antes de procesar cualquier acción.
// ============================================================
if (!isset($_SESSION['logueado']) || ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2)) {
    // Las acciones fetch esperan JSON; el formulario espera redirect
    $accion = $_POST['accion'] ?? '';
    if (in_array($accion, ['nueva_plantilla', 'eliminar_plantilla', 'renombrar_plantilla'])) {
        responderJSON(false, 'Acceso no autorizado.');
    }
    header("Location: ../vistas/principal.php");
    exit;
}

// ============================================================
// VERIFICACIÓN DE MÉTODO HTTP
// Solo se acepta POST.
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../vistas/configuracionAsistencia.php");
    exit;
}

// ============================================================
// ENRUTAMIENTO DE ACCIONES
// El campo 'accion' determina qué operación ejecutar.
// ============================================================
$accion = trim($_POST['accion'] ?? '');

switch ($accion) {


    // ==========================================================
    // ACCIÓN 1: guardar_config
    // Actualiza la configuración global del sistema.
    // Opcionalmente marca una plantilla como activa si el
    // usuario aplicó una antes de guardar.
    // Responde con redirect (patrón PRG).
    // ==========================================================
    case 'guardar_config':

        // ── Sanitización de datos recibidos ──────────────────
        $hora_entrada = trim($_POST['hora_entrada'] ?? '') . ':00';
        $hora_salida  = trim($_POST['hora_salida']  ?? '') . ':00';
        $tolerancia   = max(0, (int)($_POST['minutos_tolerancia'] ?? 0));

        // ID de la plantilla que el usuario cargó en el formulario
        // (0 o vacío si no cargó ninguna)
        $id_activa = (int)($_POST['id_preestablecida_activa'] ?? 0);

        // ── Validación de reglas de negocio ──────────────────
        $errorHorario = validarHorario($hora_entrada, $hora_salida, $tolerancia);
        if ($errorHorario !== null) {
            $_SESSION['config_error'] = $errorHorario;
            header("Location: ../vistas/configuracionAsistencia.php");
            exit;
        }

        // ── Actualización en BD ───────────────────────────────
        try {
            // Actualizar configuración global del sistema
            $stmt = $conexion->prepare(
                "UPDATE configuracion
                    SET hora_entrada_general = ?,
                        hora_salida_general  = ?,
                        minutos_tolerancia   = ?
                  WHERE id_config = 1"
            );
            $stmt->execute([$hora_entrada, $hora_salida, $tolerancia]);

            // Si el usuario aplicó una plantilla antes de guardar,
            // marcarla como activa y desmarcar las demás
            if ($id_activa > 0) {
                // Desmarcar todas las plantillas
                $stmtDesmarcar = $conexion->prepare(
                    "UPDATE configuraciones_preestablecidas SET es_activa = 0"
                );
                $stmtDesmarcar->execute();

                // Marcar solo la plantilla seleccionada
                $stmtMarcar = $conexion->prepare(
                    "UPDATE configuraciones_preestablecidas SET es_activa = 1 WHERE id_preestablecida = ?"
                );
                $stmtMarcar->execute([$id_activa]);
            }

            // ── Registro en bitácora de auditoría ────────────
            ControladorBitacora::registrar(
                $conexion,
                $_SESSION['id_usuario'],
                'Configuracion',
                'Modificación de Horarios del Sistema',
                "Nueva entrada: {$hora_entrada}, salida: {$hora_salida}, tolerancia: {$tolerancia} min." .
                ($id_activa > 0 ? " Plantilla ID {$id_activa} marcada como activa." : '')
            );

            $_SESSION['config_exito'] = true;

        } catch (PDOException $e) {
            $_SESSION['config_error'] = 'Error al actualizar la base de datos. Intente nuevamente.';
        }

        // Patrón PRG: siempre redirigir tras el POST
        header("Location: ../vistas/configuracionAsistencia.php");
        exit;


    // ==========================================================
    // ACCIÓN 2: nueva_plantilla
    // Inserta una nueva configuración preestablecida en BD.
    // Verifica el límite de 4 plantillas antes de insertar.
    // Responde en JSON (llamada desde fetch).
    // ==========================================================
    case 'nueva_plantilla':

        // ── Sanitización ─────────────────────────────────────
        $nombre      = trim($_POST['nombre']      ?? '');
        $hora_entrada = trim($_POST['hora_entrada'] ?? '') . ':00';
        $hora_salida  = trim($_POST['hora_salida']  ?? '') . ':00';
        $tolerancia  = max(0, (int)($_POST['minutos_tolerancia'] ?? 0));

        if (empty($nombre)) {
            responderJSON(false, 'El nombre de la plantilla es obligatorio.');
        }

        if (strlen($nombre) > 60) {
            responderJSON(false, 'El nombre no puede superar los 60 caracteres.');
        }

        // ── Validación del horario ────────────────────────────
        $errorHorario = validarHorario($hora_entrada, $hora_salida, $tolerancia);
        if ($errorHorario !== null) {
            responderJSON(false, $errorHorario);
        }

        try {
            // ── Verificar límite de 4 plantillas ─────────────
            $stmtConteo = $conexion->prepare(
                "SELECT COUNT(*) FROM configuraciones_preestablecidas"
            );
            $stmtConteo->execute();
            $totalActual = (int)$stmtConteo->fetchColumn();

            if ($totalActual >= 4) {
                responderJSON(false, 'Se ha alcanzado el límite de 4 plantillas. Elimina una antes de crear otra.');
            }

            // ── Inserción en BD ───────────────────────────────
            $stmtInsertar = $conexion->prepare(
                "INSERT INTO configuraciones_preestablecidas
                    (nombre, hora_entrada, hora_salida, minutos_tolerancia, id_usuario_creador)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmtInsertar->execute([
                $nombre,
                $hora_entrada,
                $hora_salida,
                $tolerancia,
                $_SESSION['id_usuario'],
            ]);

            $nuevoId = $conexion->lastInsertId();

            // ── Registro en bitácora ──────────────────────────
            ControladorBitacora::registrar(
                $conexion,
                $_SESSION['id_usuario'],
                'Configuracion',
                'Nueva Plantilla Preestablecida',
                "Plantilla '{$nombre}' creada (ID: {$nuevoId}). Entrada: {$hora_entrada}, salida: {$hora_salida}, tolerancia: {$tolerancia} min."
            );

            responderJSON(true, 'Plantilla guardada correctamente.', ['id' => $nuevoId]);

        } catch (PDOException $e) {
            responderJSON(false, 'Error al guardar la plantilla en la base de datos.');
        }
        break;


    // ==========================================================
    // ACCIÓN 3: eliminar_plantilla
    // Elimina una configuración preestablecida por su ID.
    // Responde en JSON (llamada desde fetch).
    // ==========================================================
    case 'eliminar_plantilla':

        $id_preestablecida = (int)($_POST['id_preestablecida'] ?? 0);

        if ($id_preestablecida <= 0) {
            responderJSON(false, 'ID de plantilla no válido.');
        }

        try {
            // Recuperar nombre antes de borrar (para la bitácora)
            $stmtNombre = $conexion->prepare(
                "SELECT nombre FROM configuraciones_preestablecidas WHERE id_preestablecida = ?"
            );
            $stmtNombre->execute([$id_preestablecida]);
            $nombrePlantilla = $stmtNombre->fetchColumn() ?: 'Desconocida';

            // ── Eliminación ───────────────────────────────────
            $stmtEliminar = $conexion->prepare(
                "DELETE FROM configuraciones_preestablecidas WHERE id_preestablecida = ?"
            );
            $stmtEliminar->execute([$id_preestablecida]);

            if ($stmtEliminar->rowCount() === 0) {
                responderJSON(false, 'No se encontró la plantilla indicada.');
            }

            // ── Registro en bitácora ──────────────────────────
            ControladorBitacora::registrar(
                $conexion,
                $_SESSION['id_usuario'],
                'Configuracion',
                'Eliminación de Plantilla Preestablecida',
                "Plantilla '{$nombrePlantilla}' (ID: {$id_preestablecida}) eliminada."
            );

            responderJSON(true, 'Plantilla eliminada correctamente.');

        } catch (PDOException $e) {
            responderJSON(false, 'Error al eliminar la plantilla de la base de datos.');
        }
        break;


    // ==========================================================
    // ACCIÓN 4: renombrar_plantilla
    // Actualiza solo el nombre de una plantilla existente.
    // Responde en JSON (llamada desde fetch).
    // ==========================================================
    case 'renombrar_plantilla':

        $id_preestablecida = (int)($_POST['id_preestablecida'] ?? 0);
        $nuevoNombre       = trim($_POST['nombre'] ?? '');

        if ($id_preestablecida <= 0) {
            responderJSON(false, 'ID de plantilla no válido.');
        }

        if (empty($nuevoNombre)) {
            responderJSON(false, 'El nombre no puede estar vacío.');
        }

        if (strlen($nuevoNombre) > 60) {
            responderJSON(false, 'El nombre no puede superar los 60 caracteres.');
        }

        try {
            $stmtRenombrar = $conexion->prepare(
                "UPDATE configuraciones_preestablecidas SET nombre = ? WHERE id_preestablecida = ?"
            );
            $stmtRenombrar->execute([$nuevoNombre, $id_preestablecida]);

            if ($stmtRenombrar->rowCount() === 0) {
                responderJSON(false, 'No se encontró la plantilla indicada.');
            }

            // ── Registro en bitácora ──────────────────────────
            ControladorBitacora::registrar(
                $conexion,
                $_SESSION['id_usuario'],
                'Configuracion',
                'Renombrado de Plantilla Preestablecida',
                "Plantilla ID {$id_preestablecida} renombrada a '{$nuevoNombre}'."
            );

            responderJSON(true, 'Plantilla renombrada correctamente.');

        } catch (PDOException $e) {
            responderJSON(false, 'Error al renombrar la plantilla en la base de datos.');
        }
        break;


    // ==========================================================
    // ACCIÓN DESCONOCIDA
    // Cualquier valor no reconocido en 'accion' redirige
    // a la vista principal como medida de seguridad.
    // ==========================================================
    default:
        header("Location: ../vistas/configuracionAsistencia.php");
        exit;
}