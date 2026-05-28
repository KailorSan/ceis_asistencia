<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia CEIS Julián Yánez</title>

    <script>
        window.tailwind = {
            config: {
                theme: {
                    extend: {
                        colors: {
                            vinotinto: { 600: '#8b1c31', 700: '#731728', 800: '#5c1220', 900: '#4a0e1a' }
                        }
                    }
                }
            }
        }
    </script>
    <script src="recursos/js/tailwind.js"></script>

    <style>
        /* ── BASE ── */
        * { box-sizing: border-box; }
        body { overflow-x: hidden; font-family: 'Montserrat', sans-serif; margin: 0; }
        .clip-path-slant { clip-path: polygon(0 0, 100% 0, 100% 93%, 0 100%); }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a0a0e; }
        ::-webkit-scrollbar-thumb { background: #8b1c31; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #b5253f; }

        /* ══════════════════════════════════════
           PANTALLA DE TRANSICIÓN (FONDO BLANCO LIMPIO)
        ══════════════════════════════════════ */
        #pantalla-transicion {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: #ffffff; /* Fondo blanco sólido */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: translateY(100%);
            pointer-events: none;
            overflow: hidden;
        }
        .transicion-logo {
            width: 110px; height: auto;
            margin-bottom: 24px;
            opacity: 0;
            /* Eliminado el mix-blend-mode y los filtros para mantener la calidad real del JPG */
        }
        .transicion-spinner {
            width: 48px; height: 48px;
            border: 3px solid rgba(139, 28, 49, 0.15); /* Gris claro/vinotinto transparente */
            border-top-color: #8b1c31; /* Vinotinto institucional */
            border-radius: 50%;
            animation: girar 0.8s linear infinite;
            margin-bottom: 20px;
            opacity: 0;
        }
        @keyframes girar { to { transform: rotate(360deg); } }
        .transicion-linea {
            width: 0; height: 2px;
            background: linear-gradient(90deg, transparent, #8b1c31, transparent);
            margin: 0 auto 18px;
            opacity: 0;
        }
        .transicion-texto {
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 800;
            color: #4a0e1a; /* Texto oscuro para contrastar con el fondo blanco */
            letter-spacing: 0.1em;
            opacity: 0;
            margin: 0 0 6px;
            font-family: Georgia, serif;
        }
        .transicion-subtexto {
            font-size: 0.82rem;
            font-weight: 600;
            color: #6b7280; /* Gris oscuro elegante */
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0;
            margin: 0;
        }

        /* ══════════════════════════════════════
           HERO
        ══════════════════════════════════════ */
        .hero-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(2.2rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.12;
            color: #fff;
            margin: 0 0 16px;
        }
        .hero-title .gold { color: #d4af37; }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 36px;
            background: #d4af37;
            color: #4a0e1a;
            font-weight: 700;
            font-size: 1rem;
            border-radius: 100px;
            text-decoration: none;
            box-shadow: 0 8px 30px rgba(212,175,55,0.35), 0 2px 8px rgba(0,0,0,0.3);
            transition: background 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            letter-spacing: 0.02em;
        }
        .btn-cta:hover {
            background: #f0ca5a;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 14px 40px rgba(212,175,55,0.45);
        }

        .scroll-hint {
            position: absolute; bottom: 32px; left: 50%;
            transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            opacity: 0.35; pointer-events: none; z-index: 10;
        }
        .scroll-hint span { color: #fff; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; }
        .scroll-hint-bar {
            width: 1px; height: 40px;
            background: linear-gradient(to bottom, rgba(255,255,255,0.8), transparent);
            animation: scrollpulso 2s ease-in-out infinite;
        }
        @keyframes scrollpulso { 0%,100%{opacity:.35} 50%{opacity:.7} }

        /* ══════════════════════════════════════
           SECCIÓN ACERCA
        ══════════════════════════════════════ */
        .about-frame {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.14);
        }
        .about-frame::after {
            content: ''; position: absolute; inset: 0;
            border: 2px solid rgba(139,28,49,0.18);
            border-radius: 20px; pointer-events: none;
        }
        .section-label {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em;
            text-transform: uppercase; color: #8b1c31; margin-bottom: 10px;
        }
        .section-label::before {
            content: ''; display: block;
            width: 22px; height: 2px; background: #8b1c31; border-radius: 2px;
        }
        .section-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(1.7rem, 2.8vw, 2.6rem);
            font-weight: 700; color: #1f2937; line-height: 1.2; margin: 0 0 20px;
        }
        .section-title .accent { color: #8b1c31; }
        .gold-bar {
            width: 60px; height: 5px; border-radius: 4px; margin-bottom: 24px;
            background: linear-gradient(90deg, #8b1c31, #d4af37);
        }
        .stat-num { font-size: 2rem; font-weight: 800; color: #8b1c31; font-family: Georgia, serif; }
        .stat-label { font-size: 0.8rem; color: #9ca3af; font-weight: 500; margin-top: 2px; }
        .stat-divider { width: 1px; background: #e5e7eb; }

        /* ══════════════════════════════════════
           SECCIÓN CARACTERÍSTICAS (TARJETAS)
        ══════════════════════════════════════ */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 28px;
        }
        .feature-card {
            background: #fff;
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.4s ease, border-color 0.4s ease;
        }
        .feature-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #8b1c31, #d4af37);
            opacity: 0; transition: opacity 0.4s;
        }
        .feature-card:hover { 
            box-shadow: 0 20px 40px rgba(139,28,49,0.1); 
        }
        .feature-card:hover::before { opacity: 1; }
        .feature-icon {
            width: 56px; height: 56px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, rgba(139,28,49,0.05), rgba(139,28,49,0.12));
            color: #8b1c31;
        }
        .feature-title { font-size: 1.1rem; font-weight: 700; color: #1f2937; margin: 0 0 10px; }
        .feature-desc { font-size: 0.9rem; color: #6b7280; line-height: 1.6; margin: 0; }

        /* ══════════════════════════════════════
           SECCIÓN PILARES (PANELES GRANDES)
        ══════════════════════════════════════ */
        .pilares-section {
            background: #140508;
            position: relative;
            overflow: hidden;
            padding: 120px 16px;
        }
        .pilares-section::before {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 15% 50%, rgba(212,175,55,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 85% 30%, rgba(139,28,49,0.3) 0%, transparent 60%);
            pointer-events: none;
        }
        .pilares-inner { max-width: 1200px; margin: 0 auto; position: relative; z-index: 1; }
        .pilares-header { text-align: center; margin-bottom: 70px; }
        
        .pilares-label {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em;
            text-transform: uppercase; color: #d4af37; margin-bottom: 12px;
        }
        .pilares-label::before, .pilares-label::after {
            content: ''; display: block;
            width: 30px; height: 1px; background: #d4af37; opacity: 0.5;
        }
        .pilares-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(2rem, 4vw, 3.2rem);
            font-weight: 800; color: #fff; margin: 0 0 16px;
        }
        .pilares-sub { color: #a1a1aa; font-size: 1.05rem; max-width: 500px; margin: 0 auto; }

        .pilares-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            align-items: stretch;
        }
        @media (max-width: 900px) { .pilares-grid { grid-template-columns: 1fr; gap: 24px; } }

        .pilar-card {
            background: linear-gradient(160deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%);
            border: 1px solid rgba(212,175,55,0.15);
            border-radius: 24px;
            padding: 50px 40px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            transition: box-shadow 0.5s ease, border-color 0.5s ease, background 0.5s ease;
        }
        
        .pilar-card:hover {
            border-color: rgba(212,175,55,0.4);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 20px rgba(139,28,49,0.2);
            background: linear-gradient(160deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.02) 100%);
        }

        .pilar-num-bg {
            position: absolute;
            bottom: -30px;
            right: -10px;
            font-family: Georgia, serif;
            font-size: 14rem;
            font-weight: 900;
            color: rgba(255,255,255,0.02);
            line-height: 1;
            z-index: 0;
            pointer-events: none;
            transition: color 0.5s ease, transform 0.5s ease;
        }
        .pilar-card:hover .pilar-num-bg {
            color: rgba(212,175,55,0.06);
            transform: scale(1.05) translate(-10px, -10px);
        }

        .pilar-content {
            position: relative;
            z-index: 1;
        }
        .pilar-icon {
            width: 70px; height: 70px; border-radius: 20px;
            border: 1px solid rgba(212,175,55,0.3);
            background: rgba(212,175,55,0.1);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 30px;
            color: #d4af37;
            box-shadow: inset 0 0 15px rgba(212,175,55,0.1);
            transition: transform 0.4s ease;
        }
        .pilar-card:hover .pilar-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .pilar-card h3 { color: #fff; font-size: 1.4rem; font-weight: 800; margin: 0 0 16px; letter-spacing: 0.02em; }
        .pilar-card p  { color: #d1d5db; font-size: 0.95rem; line-height: 1.8; margin: 0; }

        /* ══════════════════════════════════════
           FOOTER 
        ══════════════════════════════════════ */
        .footer-section {
            background: #0a0204;
            position: relative;
            overflow: hidden;
            padding: 48px 16px;
        }
        .footer-section::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, #8b1c31, #d4af37, #8b1c31, transparent);
        }
        .footer-inner { max-width: 900px; margin: 0 auto; }
        .footer-row {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 24px;
        }
        .footer-brand { display: flex; align-items: center; gap: 14px; }
        .footer-brand-logo {
            width: 44px; height: 44px; object-fit: contain;
            mix-blend-mode: multiply; filter: brightness(1.8) contrast(0.9);
        }
        .footer-brand-name { color: #fff; font-weight: 700; font-size: 0.9rem; }
        .footer-brand-city { color: #6b7280; font-size: 0.75rem; margin-top: 2px; }
        .footer-sep { flex: 1; height: 1px; background: #1f1014; min-width: 40px; }
        .footer-credits { text-align: right; }
        .footer-credits p { color: #6b7280; font-size: 0.82rem; margin: 0; }
        .footer-credits .dev-name { color: #d4af37; font-weight: 600; }
        .footer-bottom {
            margin-top: 28px; padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.04); text-align: center;
        }
        .footer-bottom p { color: #3f1a22; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; margin: 0; }
    </style>
</head>
<body>

    <!-- ══════════════════════════════════════
         PANTALLA DE TRANSICIÓN
    ══════════════════════════════════════ -->
    <div id="pantalla-transicion">
        <img src="recursos/img/logo mejorado.jpg" alt="CEIS" class="transicion-logo" id="trans-logo">
        <div class="transicion-linea" id="trans-linea"></div>
        <div class="transicion-spinner" id="trans-spinner"></div>
        <h2 class="transicion-texto" id="trans-texto">Iniciando Sistema...</h2>
        <p class="transicion-subtexto" id="trans-sub">CEIS Julián Yánez · Ciudad Bolívar</p>
    </div>

    <!-- ══════════════════════════════════════
         HERO (FONDO LIMPIO)
    ══════════════════════════════════════ -->
    <header class="relative flex items-center justify-center overflow-hidden clip-path-slant bg-gray-900"
            style="min-height: 92vh; padding: 80px 16px;">

        <div class="absolute inset-0" style="z-index:0;">
            <img src="recursos/img/fondoindex.jpeg" alt="Fachada CEIS"
                 class="w-full h-full object-cover" style="opacity:0.38;">
            <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(0,0,0,0.45) 0%, transparent 40%, rgba(74,14,26,0.88) 100%);"></div>
            <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(74,14,26,0.65) 0%, transparent 55%);"></div>
        </div>

        <div class="relative text-center px-4 w-full" style="z-index:10; max-width: 900px; margin: 40px auto 0;">
            
            <div class="gs-hero-item" style="margin-bottom:20px;">
                <h1 class="hero-title">Bienvenido a<br><span class="gold">CEIS Julián Yánez</span></h1>
            </div>

            <div class="gs-hero-item" style="margin-bottom:36px;">
                <p style="color:#d1d5db; font-size:clamp(1rem,2vw,1.2rem); max-width:580px; margin:0 auto; line-height:1.7; font-weight:400;">
                    Sistema de gestión de asistencia centralizado para el personal docente y obrero
                    — eficiente, seguro y diseñado para la institución.
                </p>
            </div>

            <div class="gs-hero-item">
                <a href="vistas/login.php" class="btn-cta gs-hero-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                    </svg>
                    Iniciar Sesión en el Sistema
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="scroll-hint">
            <span>Explorar</span>
            <div class="scroll-hint-bar"></div>
        </div>
    </header>

    <!-- ══════════════════════════════════════
         SECCIÓN: ACERCA
    ══════════════════════════════════════ -->
    <section style="padding:96px 16px; background:#fff;" id="acerca">
        <div style="max-width:1100px; margin:0 auto; display:flex; flex-wrap:wrap; align-items:center; gap:64px;">
            <div class="gs-about-img" style="flex:1; min-width:280px;">
                <div class="about-frame">
                    <img src="recursos/img/simoncito.jpg" alt="Niños en el CEIS" style="width:100%; height:auto; display:block; object-fit:cover;">
                </div>
            </div>
            <div class="gs-about-text" style="flex:1; min-width:280px;">
                <div class="section-label">Sobre el sistema</div>
                <h2 class="section-title">Innovación en la <span class="accent">Gestión de Personal</span></h2>
                <div class="gold-bar"></div>
                <p style="color:#6b7280; font-size:1.05rem; margin:0 0 16px; line-height:1.75;">
                    Desarrollado específicamente para el <strong style="color:#374151;">CEIS Julián Yánez</strong>,
                    este sistema reemplaza los registros manuales con una plataforma digital centralizada y segura.
                </p>
                <p style="color:#6b7280; font-size:1.05rem; margin:0; line-height:1.75;">
                    El personal administrativo puede registrar, gestionar y controlar asistencias,
                    inasistencias y permisos de todo el equipo, generando estadísticas precisas
                    para la toma de decisiones.
                </p>
                <div style="display:flex; gap:32px; margin-top:32px; align-items:center;">
                    <div><div class="stat-num">100%</div><div class="stat-label">Digital</div></div>
                    <div class="stat-divider" style="height:40px;"></div>
                    <div><div class="stat-num">24/7</div><div class="stat-label">Disponible</div></div>
                    <div class="stat-divider" style="height:40px;"></div>
                    <div><div class="stat-num">0</div><div class="stat-label">Papel</div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════
         SECCIÓN: CARACTERÍSTICAS
    ══════════════════════════════════════ -->
    <section style="padding:100px 16px; background:#f9fafb;" id="caracteristicas">
        <div style="max-width:1150px; margin:0 auto;">
            <div class="gs-feat-title" style="text-align:center; margin-bottom:60px;">
                <div class="section-label" style="justify-content:center;">Funcionalidades</div>
                <h2 class="section-title">Lo que ofrece el sistema</h2>
            </div>
            
            <div class="features-grid">
                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="feature-title">Control de Asistencia</p>
                    <p class="feature-desc">Registro de entradas, salidas, retrasos e inasistencias en tiempo real para todo el personal.</p>
                </div>

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="feature-title">Gestión de Justificaciones</p>
                    <p class="feature-desc">Flujo completo de solicitud y aprobación de justificaciones con soporte de documentos adjuntos.</p>
                </div>

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="feature-title">Reportes y Estadísticas</p>
                    <p class="feature-desc">Generación de reportes en PDF y visualizaciones gráficas para la toma de decisiones directivas.</p>
                </div>

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <p class="feature-title">Seguridad y Respaldos</p>
                    <p class="feature-desc">Sistema de roles, protección CSRF y copias de seguridad automatizadas de la base de datos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════
         SECCIÓN: PILARES
    ══════════════════════════════════════ -->
    <section class="pilares-section" id="valores">
        <div class="pilares-inner">
            <div class="pilares-header gs-pilares-title">
                <div class="pilares-label">Nuestra filosofía</div>
                <h2 class="pilares-title">Nuestros Pilares</h2>
                <p class="pilares-sub">El motor que impulsa nuestra labor diaria en la institución para la formación integral de los niños.</p>
            </div>

            <div class="pilares-grid">
                <!-- Panel 1 -->
                <div class="pilar-card gs-pilar">
                    <div class="pilar-num-bg">1</div>
                    <div class="pilar-content">
                        <div class="pilar-icon">
                            <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <h3>Compromiso con los Niños</h3>
                        <p>Nuestro mayor compromiso es el bienestar integral de los niños y niñas. Todo esfuerzo administrativo se traduce en una mejor atención, cuidado y enseñanza para nuestros estudiantes de Vista Hermosa.</p>
                    </div>
                </div>

                <!-- Panel 2 -->
                <div class="pilar-card gs-pilar">
                    <div class="pilar-num-bg">2</div>
                    <div class="pilar-content">
                        <div class="pilar-icon">
                            <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3>Valor de la Puntualidad</h3>
                        <p>La puntualidad es el primer ejemplo de responsabilidad y disciplina que transmitimos a los niños. Un control eficiente nos ayuda a mantener este alto estándar de excelencia institucional.</p>
                    </div>
                </div>

                <!-- Panel 3 -->
                <div class="pilar-card gs-pilar">
                    <div class="pilar-num-bg">3</div>
                    <div class="pilar-content">
                        <div class="pilar-icon">
                            <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3>Innovación y Eficiencia</h3>
                        <p>Al digitalizar y automatizar los procesos de asistencia garantizamos la seguridad absoluta de los datos, reducimos la carga de trabajo manual y alineamos a la institución con la era digital.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════
         FOOTER
    ══════════════════════════════════════ -->
    <footer class="footer-section">
        <div class="footer-inner">
            <div class="footer-row">
                <div class="footer-brand">
                    <img src="recursos/img/logo mejorado.jpg" alt="CEIS" class="footer-brand-logo">
                    <div>
                        <div class="footer-brand-name">CEIS Julián Yánez</div>
                        <div class="footer-brand-city">Ciudad Bolívar, Venezuela</div>
                    </div>
                </div>
                <div class="footer-sep"></div>
                <div class="footer-credits">
                    <p>Desarrollado por <span class="dev-name">Anthony Maita</span></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>Sistema de Gestión de Asistencia</p>
            </div>
        </div>
    </footer>

    <script src="recursos/librerias/gsap.min.js"></script>
    <script src="recursos/librerias/ScrollTrigger.min.js"></script>
    <script>
    // Solución al problema del botón "Atrás" del navegador (Bfcache)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            var pantalla = document.getElementById('pantalla-transicion');
            if (pantalla) {
                pantalla.style.transform = 'translateY(100%)';
                pantalla.style.pointerEvents = 'none';
            }
        }
    });

    let intentosCarga = 0;

    function asegurarVisibilidad() {
        var selectores = [
            '.gs-hero-item', '.gs-about-img', '.gs-about-text > *',
            '.gs-feat-title', '.gs-feat-card',
            '.gs-pilares-title', '.gs-pilar'
        ];
        selectores.forEach(function(sel) {
            document.querySelectorAll(sel).forEach(function(el) {
                el.style.opacity   = '';
                el.style.transform = '';
                el.style.visibility = '';
            });
        });
    }

    var fallbackTimer = setTimeout(asegurarVisibilidad, 3000);

    function iniciarAnimaciones() {
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            clearTimeout(fallbackTimer); 
            gsap.registerPlugin(ScrollTrigger);

            // HERO
            gsap.from(".gs-hero-item", {
                y: 45, opacity: 0, duration: 1,
                stagger: 0.15, ease: "power3.out", delay: 0.3
            });

            // ACERCA
            gsap.from(".gs-about-img", {
                scrollTrigger: {
                    trigger: "#acerca",
                    start: "top 85%",
                    end: "bottom 15%", 
                    toggleActions: "play reverse play reverse"
                },
                x: -60, opacity: 0, duration: 0.8, ease: "power2.out"
            });
            gsap.from(".gs-about-text > *", {
                scrollTrigger: {
                    trigger: "#acerca",
                    start: "top 85%",
                    end: "bottom 15%",
                    toggleActions: "play reverse play reverse"
                },
                x: 40, opacity: 0, duration: 0.7, stagger: 0.1, ease: "power2.out"
            });

            // CARACTERÍSTICAS
            gsap.from(".gs-feat-title", {
                scrollTrigger: {
                    trigger: "#caracteristicas",
                    start: "top 90%",
                    end: "bottom 10%", 
                    toggleActions: "play reverse play reverse"
                },
                y: 30, opacity: 0, duration: 0.6
            });
            
            gsap.from(".gs-feat-card", {
                scrollTrigger: {
                    trigger: "#caracteristicas",
                    start: "top 85%",
                    end: "bottom 10%", 
                    toggleActions: "play reverse play reverse"
                },
                y: 60, scale: 0.9, opacity: 0, duration: 0.6,
                stagger: 0.15, ease: "back.out(1.2)"
            });

            // PILARES
            gsap.from(".gs-pilares-title", {
                scrollTrigger: {
                    trigger: ".pilares-section",
                    start: "top 90%",
                    end: "bottom 10%",
                    toggleActions: "play reverse play reverse"
                },
                y: 30, opacity: 0, duration: 0.8
            });
            
            gsap.from(".gs-pilar", {
                scrollTrigger: {
                    trigger: ".pilares-grid", 
                    start: "top 85%",
                    end: "bottom 20%",
                    toggleActions: "play reverse play reverse"
                },
                y: 50, opacity: 0, duration: 0.6,
                stagger: 0.2, ease: "power2.out"
            });

            // TRANSICIÓN AL LOGIN
            var btnLogin     = document.querySelector('.gs-hero-btn');
            var pantalla     = document.getElementById('pantalla-transicion');
            var transLogo    = document.getElementById('trans-logo');
            var transLinea   = document.getElementById('trans-linea');
            var transSpinner = document.getElementById('trans-spinner');
            var transTexto   = document.getElementById('trans-texto');
            var transSub     = document.getElementById('trans-sub');

            if (btnLogin) {
                btnLogin.addEventListener('click', function(e) {
                    e.preventDefault();
                    var destino = this.getAttribute('href');
                    pantalla.style.pointerEvents = 'all';

                    // Resetea cualquier opacidad previa por si el usuario vuelve atrás
                    gsap.set([transLogo, transLinea, transSpinner, transTexto, transSub], { opacity: 0 });
                    gsap.set(transLinea, { width: 0 });

                    var tl = gsap.timeline();
                    tl.to(pantalla,      { y: 0, duration: 0.55, ease: "power3.inOut" })
                      .to(transLogo,     { opacity: 1, duration: 0.4, ease: "back.out(1.4)" },  "-=0.05")
                      .to(transLinea,    { width: 160, opacity: 0.55, duration: 0.35, ease: "power2.out" }, "-=0.15")
                      .to(transSpinner,  { opacity: 1, duration: 0.3 }, "-=0.1")
                      .to([transTexto, transSub], { opacity: 1, duration: 0.3, stagger: 0.1 }, "-=0.1")
                      .to({}, { duration: 0.6 })
                      .call(function() { window.location.href = destino; });
                });
            }

        } else {
            intentosCarga++;
            if (intentosCarga < 15) {
                setTimeout(iniciarAnimaciones, 150);
            } else {
                clearTimeout(fallbackTimer);
                asegurarVisibilidad(); 
                console.error("ERROR: No se encontró GSAP en 'recursos/librerias/'.");
            }
        }
    }

    iniciarAnimaciones();
    </script>
</body>
</html>