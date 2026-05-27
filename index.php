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
           PANTALLA DE TRANSICIÓN
        ══════════════════════════════════════ */
        #pantalla-transicion {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: #4a0e1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: translateY(100%);
            pointer-events: none;
            overflow: hidden;
        }
        #pantalla-transicion::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(139,28,49,0.6) 0%, transparent 50%),
                radial-gradient(circle at 80% 30%, rgba(212,175,55,0.12) 0%, transparent 45%);
            pointer-events: none;
        }
        .transicion-logo {
            width: 100px; height: auto;
            margin-bottom: 24px;
            opacity: 0;
            /* multiply elimina el fondo blanco del JPG sobre fondos oscuros */
            mix-blend-mode: luminosity;
            filter: drop-shadow(0 0 20px rgba(212,175,55,0.5)) brightness(1.3);
        }
        .transicion-spinner {
            width: 48px; height: 48px;
            border: 3px solid rgba(212,175,55,0.2);
            border-top-color: #d4af37;
            border-radius: 50%;
            animation: girar 0.8s linear infinite;
            margin-bottom: 20px;
            opacity: 0;
        }
        @keyframes girar { to { transform: rotate(360deg); } }
        .transicion-linea {
            width: 0; height: 2px;
            background: linear-gradient(90deg, transparent, #d4af37, transparent);
            margin: 0 auto 18px;
            opacity: 0;
        }
        .transicion-texto {
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.1em;
            opacity: 0;
            margin: 0 0 6px;
            font-family: Georgia, serif;
        }
        .transicion-subtexto {
            font-size: 0.82rem;
            font-weight: 600;
            color: #d4af37;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0;
            margin: 0;
        }

        /* ══════════════════════════════════════
           HERO
        ══════════════════════════════════════ */
        /* Partículas decorativas */
        .hero-particle { position: absolute; border-radius: 50%; pointer-events: none; z-index: 1; }
        .hp1 { width: 350px; height: 350px; top: -100px; right: -100px; background: radial-gradient(circle, rgba(139,28,49,0.55) 0%, transparent 70%); }
        .hp2 { width: 220px; height: 220px; bottom: 80px; left: -70px; background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%); }
        .hp3 { width: 90px; height: 90px; top: 35%; left: 12%; background: transparent; border: 1px solid rgba(212,175,55,0.12); }
        .hp4 { width: 55px; height: 55px; top: 55%; right: 18%; background: transparent; border: 1px solid rgba(255,255,255,0.07); }

        /* Badge institucional */
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 18px;
            border-radius: 100px;
            border: 1px solid rgba(212,175,55,0.35);
            background: rgba(212,175,55,0.08);
            backdrop-filter: blur(8px);
            color: #d4af37;
            font-size: 0.78rem; font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #d4af37;
            animation: pulso 2s ease-in-out infinite;
        }
        @keyframes pulso { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.6)} }

        /* Título */
        .hero-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(2.2rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.12;
            color: #fff;
            margin: 0 0 16px;
        }
        .hero-title .gold { color: #d4af37; }

        /* Divisor decorativo */
        .hero-divider {
            display: flex; align-items: center; gap: 14px;
            justify-content: center;
            max-width: 340px; margin: 24px auto;
        }
        .hdl { flex: 1; height: 1px; background: linear-gradient(90deg, transparent, rgba(212,175,55,0.4)); }
        .hdr { flex: 1; height: 1px; background: linear-gradient(90deg, rgba(212,175,55,0.4), transparent); }
        .hdi { color: #d4af37; font-size: 1rem; opacity: 0.7; }

        /* Botón CTA — SIN ::before para evitar el problema de texto invisible */
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
            font-family: 'Montserrat', sans-serif;
        }
        .btn-cta:hover {
            background: #f0ca5a;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 14px 40px rgba(212,175,55,0.45);
        }
        .btn-cta svg { flex-shrink: 0; }

        /* Indicador scroll */
        .scroll-hint {
            position: absolute; bottom: 32px; left: 50%;
            transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            opacity: 0.35; pointer-events: none; z-index: 10;
        }
        .scroll-hint span {
            color: #fff; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase;
        }
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
            content: '';
            position: absolute; inset: 0;
            border: 2px solid rgba(139,28,49,0.18);
            border-radius: 20px;
            pointer-events: none;
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
           SECCIÓN CARACTERÍSTICAS
        ══════════════════════════════════════ */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }
        .feature-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #8b1c31, #d4af37);
            opacity: 0; transition: opacity 0.3s;
        }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 18px 40px rgba(139,28,49,0.12); }
        .feature-card:hover::before { opacity: 1; }
        .feature-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
            background: linear-gradient(135deg, rgba(139,28,49,0.08), rgba(139,28,49,0.14));
        }
        .feature-icon svg { color: #8b1c31; }
        .feature-title { font-size: 1rem; font-weight: 700; color: #1f2937; margin: 0 0 8px; }
        .feature-desc { font-size: 0.85rem; color: #6b7280; line-height: 1.6; margin: 0; }

        /* ══════════════════════════════════════
           SECCIÓN PILARES — FONDO OSCURO
           ⚠ Todo en CSS puro, sin depender de Tailwind
        ══════════════════════════════════════ */
        .pilares-section {
            background: #1a0609;
            position: relative;
            overflow: hidden;
            padding: 100px 16px;
        }
        .pilares-section::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 10% 50%, rgba(212,175,55,0.05) 0%, transparent 55%),
                radial-gradient(ellipse at 90% 30%, rgba(139,28,49,0.45) 0%, transparent 55%);
            pointer-events: none;
        }
        .pilares-inner { max-width: 1152px; margin: 0 auto; position: relative; z-index: 1; }
        .pilares-header { text-align: center; margin-bottom: 60px; }
        .pilares-label {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em;
            text-transform: uppercase; color: rgba(212,175,55,0.65); margin-bottom: 10px;
        }
        .pilares-label::before {
            content: ''; display: block;
            width: 22px; height: 2px; background: rgba(212,175,55,0.5); border-radius: 2px;
        }
        .pilares-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            font-weight: 700; color: #fff; margin: 0 0 12px;
        }
        .pilares-sub { color: #9ca3af; font-size: 1rem; max-width: 460px; margin: 0 auto; }

        .pilares-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
            align-items: start;
        }
        @media (max-width: 768px) { .pilares-grid { grid-template-columns: 1fr; } }

        .pilar-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 36px 28px;
            transition: all 0.35s ease;
            position: relative;
            overflow: hidden;
        }
        .pilar-card-mid { transform: translateY(-16px); }
        .pilar-card::after {
            content: '';
            position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #8b1c31, #d4af37);
            opacity: 0; transition: opacity 0.3s;
        }
        .pilar-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(212,175,55,0.2);
            transform: translateY(-4px);
        }
        .pilar-card-mid:hover { transform: translateY(-20px); }
        .pilar-card:hover::after { opacity: 1; }

        .pilar-num {
            font-family: Georgia, serif;
            font-size: 5rem; font-weight: 800;
            color: rgba(212,175,55,0.06);
            position: absolute; top: -10px; right: 16px;
            line-height: 1; user-select: none;
            transition: color 0.3s;
        }
        .pilar-card:hover .pilar-num { color: rgba(212,175,55,0.13); }
        .pilar-icon {
            width: 56px; height: 56px; border-radius: 16px;
            border: 1px solid rgba(212,175,55,0.22);
            background: rgba(212,175,55,0.07);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            color: #d4af37;
        }
        .pilar-card h3 { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0 0 12px; }
        .pilar-card p  { color: #9ca3af; font-size: 0.88rem; line-height: 1.7; margin: 0; }

        /* ══════════════════════════════════════
           FOOTER
        ══════════════════════════════════════ */
        .footer-section {
            background: #0f0306;
            position: relative;
            overflow: hidden;
            padding: 48px 16px;
        }
        .footer-section::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 1px;
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
            mix-blend-mode: multiply;
            filter: brightness(1.8) contrast(0.9);
        }
        .footer-brand-name { color: #fff; font-weight: 700; font-size: 0.9rem; }
        .footer-brand-city { color: #6b7280; font-size: 0.75rem; margin-top: 2px; }
        .footer-sep { flex: 1; height: 1px; background: #1f1014; min-width: 40px; }
        .footer-credits { text-align: right; }
        .footer-credits p { color: #6b7280; font-size: 0.82rem; margin: 0 0 2px; }
        .footer-credits .dev-name { color: #d4af37; }
        .footer-bottom {
            margin-top: 28px; padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.04);
            text-align: center;
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
         HERO
    ══════════════════════════════════════ -->
    <header class="relative flex items-center justify-center overflow-hidden clip-path-slant bg-gray-900"
            style="min-height: 92vh; padding: 80px 16px;">

        <!-- Imagen de fondo -->
        <div class="absolute inset-0" style="z-index:0;">
            <img src="recursos/img/fondoindex.jpeg" alt="Fachada CEIS"
                 class="w-full h-full object-cover" style="opacity:0.38;">
            <div class="absolute inset-0"
                 style="background: linear-gradient(to bottom, rgba(0,0,0,0.45) 0%, transparent 40%, rgba(74,14,26,0.88) 100%);"></div>
            <div class="absolute inset-0"
                 style="background: linear-gradient(to right, rgba(74,14,26,0.65) 0%, transparent 55%);"></div>
        </div>

        <!-- Partículas -->
        <div class="hp1 hero-particle"></div>
        <div class="hp2 hero-particle"></div>
        <div class="hp3 hero-particle"></div>
        <div class="hp4 hero-particle"></div>

        <!-- Contenido -->
        <div class="relative text-center px-4 w-full" style="z-index:10; max-width: 900px; margin: 40px auto 0;">

            <div class="gs-hero-item" style="margin-bottom:24px;">
                <div style="display:inline-flex; align-items:center; gap:8px;
                            padding:6px 18px; border-radius:100px;
                            border:1px solid rgba(212,175,55,0.35);
                            background:rgba(212,175,55,0.08);
                            color:#d4af37; font-size:0.78rem; font-weight:600;
                            letter-spacing:0.04em; text-transform:uppercase;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#d4af37;
                                 animation:pulso 2s ease-in-out infinite; flex-shrink:0;"></span>
                    Ministerio del Poder Popular para la Educación
                </div>
            </div>

            <div class="gs-hero-item" style="margin-bottom:20px;">
                <h1 class="hero-title">
                    Bienvenido a<br>
                    <span class="gold">CEIS Julián Yánez</span>
                </h1>
            </div>

            <div class="gs-hero-item" style="display:flex;align-items:center;gap:14px;
                         justify-content:center;max-width:340px;margin:0 auto 24px;">
                <div class="hdl"></div>
                <span style="color:#d4af37;opacity:.7;font-size:1rem;">✦</span>
                <div class="hdr"></div>
            </div>

            <div class="gs-hero-item" style="margin-bottom:36px;">
                <p style="color:#d1d5db; font-size:clamp(1rem,2vw,1.2rem);
                           max-width:580px; margin:0 auto; line-height:1.7; font-weight:400;">
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

        <!-- Indicador scroll -->
        <div class="scroll-hint">
            <span>Explorar</span>
            <div class="scroll-hint-bar"></div>
        </div>
    </header>

    <!-- ══════════════════════════════════════
         SECCIÓN: ACERCA
    ══════════════════════════════════════ -->
    <section style="padding:96px 16px; background:#fff;" id="acerca">
        <div style="max-width:1100px; margin:0 auto; display:flex;
                    flex-wrap:wrap; align-items:center; gap:64px;">

            <!-- Imagen -->
            <div class="gs-about-img" style="flex:1; min-width:280px;">
                <div class="about-frame">
                    <img src="recursos/img/simoncito.jpg" alt="Niños en el CEIS"
                         style="width:100%; height:auto; display:block; object-fit:cover;">
                </div>
            </div>

            <!-- Texto -->
            <div class="gs-about-text" style="flex:1; min-width:280px;">
                <div class="section-label">Sobre el sistema</div>
                <h2 class="section-title">
                    Innovación en la <span class="accent">Gestión de Personal</span>
                </h2>
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
                <!-- Métricas -->
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
    <section style="padding:80px 16px; background:#f9fafb;" id="caracteristicas">
        <div style="max-width:1100px; margin:0 auto;">
            <div class="gs-feat-title" style="text-align:center; margin-bottom:52px;">
                <div class="section-label" style="justify-content:center;">Funcionalidades</div>
                <h2 class="section-title">Lo que ofrece el sistema</h2>
            </div>
            <div class="features-grid">

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="feature-title">Control de Asistencia</p>
                    <p class="feature-desc">Registro de entradas, salidas, retrasos e inasistencias en tiempo real para todo el personal.</p>
                </div>

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="feature-title">Gestión de Justificaciones</p>
                    <p class="feature-desc">Flujo completo de solicitud y aprobación de justificaciones con soporte de documentos adjuntos.</p>
                </div>

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="feature-title">Reportes y Estadísticas</p>
                    <p class="feature-desc">Generación de reportes en PDF y visualizaciones gráficas para la toma de decisiones directivas.</p>
                </div>

                <div class="feature-card gs-feat-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
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
         ⚠ CSS puro — sin clases Tailwind de color
    ══════════════════════════════════════ -->
    <section class="pilares-section" id="valores">
        <div class="pilares-inner">

            <div class="pilares-header gs-pilares-title">
                <div class="pilares-label">Nuestra filosofía</div>
                <h2 class="pilares-title">Nuestros Pilares</h2>
                <p class="pilares-sub">El motor que impulsa nuestra labor diaria en la institución.</p>
            </div>

            <div class="pilares-grid">

                <div class="pilar-card gs-pilar">
                    <span class="pilar-num">01</span>
                    <div class="pilar-icon">
                        <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3>Compromiso con los Niños</h3>
                    <p>Nuestro mayor compromiso es el bienestar integral de los niños y niñas. Todo esfuerzo administrativo se traduce en una mejor atención y cuidado para nuestros estudiantes de Vista Hermosa.</p>
                </div>

                <div class="pilar-card pilar-card-mid gs-pilar">
                    <span class="pilar-num">02</span>
                    <div class="pilar-icon">
                        <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3>El Valor de la Puntualidad</h3>
                    <p>La puntualidad es el primer ejemplo de responsabilidad que transmitimos a los niños. Un control eficiente nos ayuda a mantener este estándar de excelencia institucional.</p>
                </div>

                <div class="pilar-card gs-pilar">
                    <span class="pilar-num">03</span>
                    <div class="pilar-icon">
                        <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3>Innovación y Eficiencia</h3>
                    <p>Al digitalizar y automatizar los procesos de asistencia garantizamos la seguridad de los datos y reducimos drásticamente la carga de trabajo manual.</p>
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
                    <p>© 2026 — Desarrollo Socio Tecnológico</p>
                    <p>UPT Bolívar · Desarrollado por <span class="dev-name">Anthony Maita</span></p>
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
    let intentosCarga = 0;

    // ── FALLBACK DE SEGURIDAD ──────────────────────────────────────
    // Si GSAP no carga o algo falla, estos elementos NUNCA quedan invisibles
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

    // Si en 3 segundos GSAP no inició, mostramos todo
    var fallbackTimer = setTimeout(asegurarVisibilidad, 3000);

    function iniciarAnimaciones() {
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            clearTimeout(fallbackTimer); // GSAP cargó, cancelamos el fallback
            gsap.registerPlugin(ScrollTrigger);

            // ── HERO ──────────────────────────────────────────────
            gsap.from(".gs-hero-item", {
                y: 45, opacity: 0, duration: 1,
                stagger: 0.15, ease: "power3.out", delay: 0.3
            });

            // ── ACERCA ────────────────────────────────────────────
            gsap.from(".gs-about-img", {
                scrollTrigger: {
                    trigger: "#acerca",
                    start: "top 82%",
                    once: true  // ← dispara UNA vez, no queda bloqueado
                },
                x: -60, opacity: 0, duration: 1, ease: "power2.out"
            });
            gsap.from(".gs-about-text > *", {
                scrollTrigger: {
                    trigger: "#acerca",
                    start: "top 82%",
                    once: true
                },
                x: 40, opacity: 0, duration: 0.8, stagger: 0.1, ease: "power2.out", delay: 0.15
            });

            // ── CARACTERÍSTICAS ───────────────────────────────────
            gsap.from(".gs-feat-title", {
                scrollTrigger: {
                    trigger: "#caracteristicas",
                    start: "top 88%",
                    once: true
                },
                y: 30, opacity: 0, duration: 0.7
            });
            gsap.from(".gs-feat-card", {
                scrollTrigger: {
                    trigger: "#caracteristicas",
                    start: "top 85%",
                    once: true
                },
                y: 55, opacity: 0, duration: 0.75,
                stagger: 0.13, ease: "back.out(1.4)"
            });

            // ── PILARES ───────────────────────────────────────────
            gsap.from(".gs-pilares-title", {
                scrollTrigger: {
                    trigger: ".pilares-section",
                    start: "top 88%",
                    once: true
                },
                y: 30, opacity: 0, duration: 0.8
            });
            gsap.from(".gs-pilar", {
                scrollTrigger: {
                    trigger: ".pilares-section",
                    start: "top 82%",
                    once: true
                },
                y: 70, opacity: 0, duration: 0.9,
                stagger: 0.2, ease: "back.out(1.5)"
            });

            // ── TRANSICIÓN AL LOGIN ───────────────────────────────
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
                asegurarVisibilidad(); // GSAP nunca cargó → mostrar todo
                console.error("ERROR: No se encontró GSAP en 'recursos/librerias/'.");
            }
        }
    }

    iniciarAnimaciones();
    </script>
</body>
</html>