<header class="barra-superior">
    <h3><?php echo isset($titulo_pagina) ? $titulo_pagina : 'CEIS Julian Yánez'; ?></h3>
    
    <div class="acciones-superior">
        <button class="btn-tema-top" id="btnCambiarTema" aria-label="Cambiar Tema">
            <svg class="icono-sol" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="icono-luna" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
        <div class="usuario-info">
            <h4><?php echo htmlspecialchars($nombre); ?></h4>
            <span><?php echo $rol; ?></span>
        </div>
    </div>
</header>