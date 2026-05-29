// =============================================================
// AUTO-REFRESCO EXCLUSIVO PARA TARJETAS DE RECURSOS (SSE)
// =============================================================
const AutoRefrescoSSE = (() => {
    let evtSource = null;
    let isRefreshing = false;

    //--bloquea la inyección si el usuario está interactuando (viendo un PDF/Video o buscando)
    window.hayAccionEnCurso = function () {
        const modalAbierto = document.querySelector(
            "#modalVisor:not(.hidden), .swal2-container:not(.hidden)"
        );

        const buscadorGeneral = document.getElementById("inputBusqueda");
        const buscadorActivo = buscadorGeneral && buscadorGeneral === document.activeElement;

        return !!(modalAbierto || buscadorActivo);
    };

    //-----procesa e inyecta nuevas tarjetas
    window.procesarTarjetasRecursos = function (htmlNuevo) {
        if (!htmlNuevo || isRefreshing) return;
        isRefreshing = true;

        //---contenedor principal cards
        const contenedorRecursos = document.querySelector(".grid, #contenedor-recursos");
        if (!contenedorRecursos) {
            isRefreshing = false;
            return;
        }

        //---nuevas tarjetas
        contenedorRecursos.innerHTML = htmlNuevo;

        //---filtro categoria
        if (typeof window.filtrar === "function") {
            window.filtrar(window.categoriaActivaActual || "all");
        }

        isRefreshing = false;
    };

   
    function iniciar() {
        detener();
        const contenedorRecursos = document.querySelector(".grid, #contenedor-recursos");
        if (!contenedorRecursos) return;
        const categoriaFiltro = window.categoriaActivaActual || "all";

        //---------conexion
        evtSource = new EventSource(
            `/api/recursos-stream?categoria=${encodeURIComponent(categoriaFiltro)}`
        );

        //----actualizacion servidor
        evtSource.onmessage = function (event) {
            if (hayAccionEnCurso()) return;
            
            try {
                const data = JSON.parse(event.data);
                
                if (data.error) {
                    if (data.error === "No autenticado") window.location.href = "/login";
                    return;
                }

                if (data.html) {
                    procesarTarjetasRecursos(data.html);
                }
            } catch (err) {
                console.error("[SSE Recursos] JSON Parse Error:", err);
            }
        };

        evtSource.onerror = function () {
            if (evtSource && evtSource.readyState === EventSource.CLOSED) {
                console.warn("[SSE Recursos] Canal cerrado definitivamente.");
                detener();
            } else {
                console.debug("[SSE Recursos] Intentando reconectar...");
            }
        };
    }

    function detener() {
        if (evtSource) {
            evtSource.close();
            evtSource = null;
        }
    }

    return {
        iniciar,
        detener,
        forzarRefresco: () => iniciar()
    };
})();

// =============================================================
// INTERCEPCIÓN DEL DOM Y CONTROL DE CICLO DE VÍA
// =============================================================

document.addEventListener("DOMContentLoaded", function () {
    //---cambio categoria
    document.addEventListener("click", function (event) {
        const botonFiltro = event.target.closest(".filter-btn");
        if (botonFiltro) {
            const catId = botonFiltro.getAttribute("data-id") || "all";
            
            //si  hace clic en el mismo botón que ya está activo, no reconectamos de forma innecesaria
            if (window.categoriaActivaActual === catId) return;

            //variable global para que recursos.js y este script estén sincronizados
            window.categoriaActivaActual = catId;
            
            //forzamos el refresco del canal para avisar al servidor de la nueva categoría
            AutoRefrescoSSE.forzarRefresco();
        }
    });

    //arrancar el SSE automáticamente al cargar la página
    AutoRefrescoSSE.iniciar();
});

window.addEventListener("beforeunload", () => {
    AutoRefrescoSSE.detener();
});