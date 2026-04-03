<?php
require_once '../configuracion/seguridad.php';
require_once '../configuracion/conexion.php'; 

// Solo Director (1) o Subdirector (2) pueden ver esto
if ($_SESSION['id_rol'] != 1 && $_SESSION['id_rol'] != 2) { 
    header("Location: principal.php"); 
    exit; 
}

$nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

try {
    $sql = "SELECT a.id_asistencia, a.fecha, a.estado, a.motivo_justificacion, a.archivo_evidencia,
                   p.nombres, p.apellidos, p.foto_perfil, c.nombre_cargo 
            FROM asistencias a 
            INNER JOIN personal p ON a.id_personal = p.id_personal 
            INNER JOIN cargos c ON p.id_cargo = c.id_cargo 
            WHERE a.estado_justificacion = 'Pendiente' 
            ORDER BY a.fecha DESC";
    $stmt = $conexion->query($sql);
    $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pendientes = [];
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Justificaciones</title>
    <link rel="stylesheet" href="../recursos/css/principal.css?v=<?php echo time(); ?>">
    
    <style>
        .btn-evidencia {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-block-start: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(64, 111, 243, 0.2);
        }
        .btn-evidencia:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(64, 111, 243, 0.3);
            color: white;
        }
        .btn-evidencia svg {
            inline-size: 18px;
            block-size: 18px;
        }
        .texto-sin-evidencia {
            margin-block-start: 12px;
            font-size: 0.85rem;
            color: var(--text-color);
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>

    <script>
        (function() {
            const idUsr = "<?php echo $_SESSION['id_usuario']; ?>";
            const temaGuardado = localStorage.getItem('tema_usuario_' + idUsr) || 'light';
            document.documentElement.setAttribute('data-theme', temaGuardado);
        })();
    </script>
</head>
<body>
    
   <?php $pagina_activa = 'justificaciones'; require_once 'componentes/sidebar.php'; ?>

    <div class="contenedor-principal">
        <?php $titulo_pagina = 'Bandeja de Revisión'; require_once 'componentes/topbar.php'; ?>

        <main class="contenido">
            <h1 style="margin-block-end: 25px;">Justificaciones Pendientes</h1>

            <div class="grid-justificaciones">
                <?php foreach ($pendientes as $req): ?>
                    <div class="tarjeta-justificacion">
                        <img src="../recursos/img/perfiles/<?php echo htmlspecialchars($req['foto_perfil']); ?>" alt="Avatar" class="tj-avatar">
                        <div class="tj-info">
                            <h4><?php echo htmlspecialchars($req['nombres'] . ' ' . $req['apellidos']); ?> <span>(<?php echo $req['nombre_cargo']; ?>)</span></h4>
                            <p style="margin: 5px 0; font-size: 0.85rem; color: #f59e0b; font-weight: bold;">
                                Fecha incidencia: <?php echo date('d-m-Y', strtotime($req['fecha'])); ?> | Estado original: <?php echo $req['estado']; ?>
                            </p>
                            <div class="tj-motivo">"<?php echo htmlspecialchars($req['motivo_justificacion']); ?>"</div>
                            
                            <?php if (!empty($req['archivo_evidencia'])): ?>
                                <a href="../recursos/evidencias/<?php echo htmlspecialchars($req['archivo_evidencia']); ?>" target="_blank" class="btn-evidencia">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                    Ver Evidencia Adjunta
                                </a>
                            <?php else: ?>
                                <div class="texto-sin-evidencia">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    El empleado no adjuntó ningún archivo.
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="tj-acciones">
                            <button onclick="procesar(<?php echo $req['id_asistencia']; ?>, 'aprobar')" class="btn-aprobar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Aprobar
                            </button>
                            <button onclick="procesar(<?php echo $req['id_asistencia']; ?>, 'rechazar')" class="btn-rechazar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg> Rechazar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if(empty($pendientes)): ?>
                    <div style="text-align: center; padding: 50px; background: var(--navbar-bg); border-radius: 16px; opacity: 0.7;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin-block-end: 15px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h2>¡Todo al día!</h2>
                        <p>No hay justificaciones pendientes por revisar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php if(isset($_SESSION['alerta_justificacion'])): ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['alerta_justificacion']['tipo'] == 'success' ? '¡Completado!' : '¡Error!'; ?>',
                text: '<?php echo $_SESSION['alerta_justificacion']['mensaje']; ?>',
                icon: '<?php echo $_SESSION['alerta_justificacion']['tipo']; ?>',
                confirmButtonColor: '<?php echo $_SESSION['alerta_justificacion']['tipo'] == 'success' ? '#10b981' : '#ef4444'; ?>',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            });
        </script>
        <?php unset($_SESSION['alerta_justificacion']); ?>
    <?php endif; ?>

    <script src="../recursos/js/sweetalert2.all.min.js"></script>
    <script>
        const btnCambiarTema = document.getElementById('btnCambiarTema');
        const html = document.documentElement;
        if(btnCambiarTema) {
            btnCambiarTema.addEventListener('click', function(e) {
                e.preventDefault();
                const nuevoTema = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
                html.setAttribute('data-theme', nuevoTema);
                localStorage.setItem('tema_usuario_<?php echo $_SESSION['id_usuario']; ?>', nuevoTema);
            });
        }

        function procesar(id, accion) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: accion === 'aprobar' ? "La incidencia se convertirá en 'Justificado'." : "La incidencia se mantendrá como Falta/Retraso.",
                icon: 'question', showCancelButton: true,
                confirmButtonColor: accion === 'aprobar' ? '#10b981' : '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, ' + accion,
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#fff' : '#333'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../controladores/ControladorProcesarJustificacion.php?id=' + id + '&accion=' + accion;
                }
            })
        }
    </script>

    
</body>
</html>