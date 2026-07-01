class GuiaDinamica {
    constructor(diccionario = []) {
        this.diccionario = diccionario;
        this.modoAyudaActivo = false;
        this.init();
    }

    init() {
        // 1. Inyectamos el HTML de la guía si no existe
        if (!document.getElementById('overlay-ayuda-dinamica')) {
            const htmlGuia = `
                <div id="overlay-ayuda-dinamica">
                    <div class="texto-instructivo-ayuda">💡 Haz clic en los elementos resaltados para saber qué hacen</div>
                </div>
                <div id="tooltip-ayuda">
                    <h4 id="tooltip-ayuda-titulo">Título</h4>
                    <p id="tooltip-ayuda-texto">Texto descriptivo.</p>
                </div>
                <button id="btn-salir-ayuda">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cerrar Guía
                </button>
            `;
            document.body.insertAdjacentHTML('beforeend', htmlGuia);
        }

        // 2. Referencias al DOM
        this.overlay = document.getElementById('overlay-ayuda-dinamica');
        this.tooltip = document.getElementById('tooltip-ayuda');
        this.btnSalir = document.getElementById('btn-salir-ayuda');
        this.tituloTooltip = document.getElementById('tooltip-ayuda-titulo');
        this.textoTooltip = document.getElementById('tooltip-ayuda-texto');
        this.btnActivar = document.getElementById('btnAyudaDinamica'); 

        // 3. Bindings de contexto
        this.manejadorGlobalClics = this.manejadorGlobalClics.bind(this);
        this.activar = this.activar.bind(this);
        this.desactivar = this.desactivar.bind(this);

        // 4. Listeners básicos
        if (this.btnActivar) this.btnActivar.addEventListener('click', this.activar);
        if (this.btnSalir) this.btnSalir.addEventListener('click', this.desactivar);
    }

    // Permite actualizar el diccionario desde otras pantallas
    setDiccionario(nuevoDiccionario) {
        this.diccionario = nuevoDiccionario;
    }

    // ⭐ ESCUDO INTERCEPTOR TOTAL
    manejadorGlobalClics(e) {
        if (!this.modoAyudaActivo) return;
        
        // Dejamos funcionar el botón de salir de la guía
        if (e.target.closest('#btn-salir-ayuda')) return;

        // Bloqueo estricto para evitar que menús, enlaces o botones se accionen
        const tagName = e.target.tagName.toLowerCase();
        const interactivos = ['input', 'select', 'textarea', 'button', 'a', 'label'];
        
        if (interactivos.includes(tagName) || e.target.closest('.ayuda-resaltado')) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Solo procesamos la aparición del cuadro de texto en la fase de "click"
        if (e.type === 'click') {
            const elementoClickeado = e.target.closest('.ayuda-resaltado');
            if (elementoClickeado) {
                this.mostrarTooltipPara(elementoClickeado);
            } else {
                this.ocultarTooltip();
            }
        }
    }

    // ⭐ LÓGICA DE POSICIONAMIENTO DEL GLOBO DE TEXTO
    mostrarTooltipPara(targetElement) {
        let elementoConfig = null;

        // Buscamos la configuración en el diccionario actual
        this.diccionario.forEach(item => {
            if (targetElement.matches(item.selector) || targetElement.closest(item.selector)) {
                elementoConfig = item;
            }
        });

        if (elementoConfig) {
            this.tituloTooltip.textContent = elementoConfig.titulo;
            this.textoTooltip.textContent = elementoConfig.texto;
            this.tooltip.classList.add('visible');

            // Cálculos para que el cuadro nunca se salga de la pantalla
            const rect = targetElement.getBoundingClientRect();
            let topPos = rect.bottom + window.scrollY + 15;
            let leftPos = rect.left + window.scrollX + (rect.width / 2) - (this.tooltip.offsetWidth / 2);

            // ¿Choca abajo? Lo ponemos arriba
            if (topPos + this.tooltip.offsetHeight > window.innerHeight + window.scrollY) {
                topPos = rect.top + window.scrollY - this.tooltip.offsetHeight - 15;
            }
            // ¿Choca a los lados? Lo empujamos
            if (leftPos < 20) leftPos = 20;
            if (leftPos + this.tooltip.offsetWidth > window.innerWidth - 20) {
                leftPos = window.innerWidth - this.tooltip.offsetWidth - 20;
            }

            this.tooltip.style.top = topPos + 'px';
            this.tooltip.style.left = leftPos + 'px';
        }
    }

    ocultarTooltip() {
        this.tooltip.classList.remove('visible');
    }

    // ⭐ SOLUCIÓN DE STACKING CONTEXT PARA PADRES
    arreglarCapasPadres(elemento) {
        let parent = elemento.parentElement;
        let esPadreInmediato = true;
        while (parent && parent.tagName !== 'BODY') {
            const estilo = window.getComputedStyle(parent);
            // FIX: El padre inmediato se fuerza SIEMPRE, sin importar su estado
            // actual en reposo. Motivo: contenedores como ".item-respaldo" no
            // tienen transform en reposo, solo lo ganan en :hover (ej.
            // "transform: translateX(5px)"). Si solo revisamos el estado en
            // reposo (como antes), ese padre nunca recibe el fix, y al pasar
            // el mouse crea un nuevo stacking context que tapa visualmente
            // el resaltado del hijo (z-index 99999 deja de servir porque
            // queda encerrado dentro del nuevo contexto del padre).
            if (esPadreInmediato || estilo.transform !== 'none' || estilo.position === 'relative' || estilo.position === 'absolute') {
                parent.classList.add('guia-fix-stacking');
            }
            esPadreInmediato = false;
            parent = parent.parentElement;
        }
    }

    restaurarCapasPadres(elemento) {
        let parent = elemento.parentElement;
        while (parent && parent.tagName !== 'BODY') {
            parent.classList.remove('guia-fix-stacking');
            parent = parent.parentElement;
        }
    }

    // ⭐ SOLUCIÓN DE POSICIONAMIENTO RELATIVO CONDICIONAL
    prepararElementoResaltado(elemento) {
        const estilo = window.getComputedStyle(elemento);
        if (estilo.position === 'static') {
            elemento.classList.add('ayuda-necesita-relative');
        }
    }

    limpiarElementoResaltado(elemento) {
        elemento.classList.remove('ayuda-necesita-relative');
    }

    // ⭐ ENCENDIDO
    activar() {
        if(this.diccionario.length === 0) return; 

        this.modoAyudaActivo = true;
        this.overlay.classList.add('activo');
        this.btnSalir.classList.add('activo');
        this.ocultarTooltip();

        // Activamos el escudo en "mousedown" y "click" (fase true de captura)
        document.addEventListener('mousedown', this.manejadorGlobalClics, true);
        document.addEventListener('click', this.manejadorGlobalClics, true);

        // Preparamos e iluminamos cada elemento del diccionario
        this.diccionario.forEach(item => {
            const elementos = document.querySelectorAll(item.selector);
            elementos.forEach(el => {
                this.arreglarCapasPadres(el);
                this.prepararElementoResaltado(el);
                el.classList.add('ayuda-resaltado');
            });
        });
    }

    // ⭐ APAGADO (Y LIMPIEZA TOTAL)
    desactivar() {
        this.modoAyudaActivo = false;
        this.overlay.classList.remove('activo');
        this.btnSalir.classList.remove('activo');
        this.ocultarTooltip();

        // Apagamos el escudo
        document.removeEventListener('mousedown', this.manejadorGlobalClics, true);
        document.removeEventListener('click', this.manejadorGlobalClics, true);

        // Le quitamos las luces y las clases a cada elemento (dejándolos como estaban)
        this.diccionario.forEach(item => {
            const elementos = document.querySelectorAll(item.selector);
            elementos.forEach(el => {
                this.restaurarCapasPadres(el);
                this.limpiarElementoResaltado(el);
                el.classList.remove('ayuda-resaltado');
            });
        });
    }
}

window.GuiaDinamica = GuiaDinamica;