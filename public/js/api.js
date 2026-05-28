// =============================================================
// AUTO-REFRESCO UNIVERSAL POR EVENTOS DEL SERVIDOR (SSE)
// =============================================================

const AutoRefrescoSSE = (() => {
    let evtSource = null;
    let isRefreshing = false;

    // Control de interfaz: Bloquea el refresco si el usuario escribe o tiene un modal abierto
    function hayAccionEnCurso() {
        const modalAbierto = document.querySelector(
            ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden), #modalAgregar:not(.hidden), #modalEditar:not(.hidden), .swal2-container:not(.hidden)",
        );

        const buscadorDeTabla =
            document.activeElement &&
            (document.activeElement.getAttribute("type") === "search" ||
                document.activeElement.closest(".dataTables_filter"));

        const buscadorGeneral = document.getElementById("inputBusqueda");

        return !!(
            modalAbierto ||
            buscadorDeTabla ||
            (buscadorGeneral && buscadorGeneral === document.activeElement)
        );
    }

    function actualizarElemento(id, valor, esHTML = false) {
        const el = document.getElementById(id);
        if (!el || valor === undefined || valor === null) return;
        if (esHTML) el.innerHTML = valor;
        else el.textContent = String(valor);
    }

    // Procesamiento y Renderizado de Tablas
    function procesarTabla(htmlNuevo) {
        if (!htmlNuevo || isRefreshing) return;
        isRefreshing = true;

        const tablaElement = document.querySelector(
            '.dataTable, table[id*="tabla"], table[id*="Table"]',
        );
        if (!tablaElement) {
            isRefreshing = false;
            return;
        }

        const tablaId = tablaElement.id;
        const tablaBody = tablaElement.querySelector("tbody");
        if (!tablaBody) {
            isRefreshing = false;
            return;
        }

        // Si no existe DataTables o jQuery listo, renderiza el HTML crudo
        if (
            !window.$ ||
            !$.fn.DataTable ||
            !$.fn.DataTable.isDataTable(tablaElement)
        ) {
            tablaBody.innerHTML = htmlNuevo;
            isRefreshing = false;
            return;
        }

        const $tabla = $(tablaElement);
        let paginaActual = 0;
        let buscadorTermino = "";

        // Guardar el estado actual del usuario (Página y búsqueda) antes de destruir
        try {
            const dtInstancia = $tabla.DataTable();
            paginaActual = dtInstancia.page();
            buscadorTermino = dtInstancia.search();
        } catch (_) {}

        // Destrucción limpia para evitar el error de duplicidad
        try {
            $tabla.DataTable().destroy();
        } catch (_) {}

        // Inserción del HTML nuevo usando un DOMParser seguro
        const parser = new DOMParser();
        const doc = parser.parseFromString(
            "<table>" + htmlNuevo + "</table>",
            "text/html",
        );
        const tbodyEl = doc.querySelector("tbody");
        tablaBody.innerHTML = tbodyEl ? tbodyEl.innerHTML : "";

        // RE-INICIALIZACIÓN CONTROLADA SEGÚN LA TABLA
        if (
            tablaId === "userTable" &&
            typeof window.inicializarUserTable === "function"
        ) {
            window.inicializarUserTable();
        } else if (typeof window.inicializarTablaTickets === "function") {
            window.inicializarTablaTickets("#" + tablaId);
        } else {
            // Respaldo genérico si la tabla no tiene inicializador propio
            $tabla.DataTable({
                destroy: true,
                responsive: true,
                autoWidth: false,
                pageLength: 5,
                dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
                language: {
                    processing: "Procesando...",
                    zeroRecords: "No se encontraron resultados",
                    emptyTable: "No hay datos disponibles en la tabla",
                    info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
                    infoFiltered: "(filtrado de un total de _MAX_ registros)",
                    infoEmpty: "Mostrando 0 registros",
                    paginate: {
                        next: '<span class="material-symbols-outlined text-[20px] leading-none">chevron_right</span>',
                        previous:
                            '<span class="material-symbols-outlined text-[20px] leading-none">chevron_left</span>',
                    },
                },
            });
        }

        // Restaurar estado estricto y aplicar las clases estéticas de Tailwind
        try {
            const dtNuevo = $tabla.DataTable();
            if (buscadorTermino) {
                dtNuevo.search(buscadorTermino).draw(false);
            }
            const totalPaginas = dtNuevo.page.info().pages;
            if (paginaActual > 0 && paginaActual < totalPaginas) {
                dtNuevo.page(paginaActual).draw(false);
            }
            aplicarEstilosPaginacion();
        } catch (err) {
            console.error("[SSE] Error al restaurar DataTables:", err);
        } finally {
            isRefreshing = false;
        }
    }

    // Solución al problema visual: Aplica clases Tailwind a los botones de la captura
    function aplicarEstilosPaginacion() {
        const wrappers = document.querySelectorAll(".dataTables_wrapper");
        wrappers.forEach((wrap) => {
            const lengthSelect = wrap.querySelector(
                ".dataTables_length select",
            );
            if (lengthSelect) {
                lengthSelect.className =
                    "mx-2 px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl focus:outline-none";
            }

            const paginateContainer = wrap.querySelector(
                ".dataTables_paginate",
            );
            if (paginateContainer) {
                // Separar los botones con un flex ordenado
                paginateContainer.className =
                    "dataTables_paginate flex items-center gap-1.5 mt-4 justify-end text-slate-600 text-xs font-medium";

                // Buscar tanto enlaces <a> como botones nativos creados por DataTables
                const links = paginateContainer.querySelectorAll(
                    "a, .paginate_button",
                );
                links.forEach((btn) => {
                    btn.removeAttribute("style"); // Borrar estilos en línea de DataTables

                    // Diseño base de botón redondeado elegante
                    btn.className =
                        "px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border select-none no-underline inline-block ";

                    // Condición para el botón de la página actual activa
                    if (
                        btn.classList.contains("current") ||
                        btn.classList.contains("active")
                    ) {
                        btn.classList.add(
                            "bg-secondary",
                            "text-white",
                            "border-secondary",
                            "shadow-sm",
                        );
                    }
                    // Condición para botones deshabilitados (ej: Anterior/Siguiente en los extremos)
                    else if (btn.classList.contains("disabled")) {
                        btn.classList.add(
                            "bg-slate-50",
                            "text-slate-300",
                            "border-slate-100",
                            "pointer-events-none",
                        );
                    }
                    // Botones interactivos normales
                    else {
                        btn.classList.add(
                            "bg-slate-100",
                            "text-slate-600",
                            "border-slate-200",
                            "hover:bg-slate-200",
                            "hover:text-slate-900",
                        );
                    }
                });
            }
        });
    }

    function iniciar() {
        detener();
        const tablaElement = document.querySelector(
            '.dataTable, table[id*="tabla"], table[id*="Table"]',
        );
        if (!tablaElement) return;
        const tablaBody = tablaElement.querySelector("tbody");
        if (!tablaBody) return;

        const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";

        // 🎯 NUEVO MOTOR DE DETECCIÓN DE FILTROS (A prueba de balas)
        let filtroEstado = "todos";

        // Intento 1: Buscar por clases de Tailwind comunes en tu proyecto
        const botonActivo = document.querySelector(
            '.filtro-btn.bg-secondary, .filtro-btn.active, [id="filtrosEstado"] .bg-secondary',
        );

        if (botonActivo) {
            filtroEstado = botonActivo.getAttribute("data-estado") || "todos";
        } else {
            // Intento 2: Si falla el CSS, buscamos cuál es el botón que el usuario cliqueó basándonos en el almacenamiento global temporal
            filtroEstado = window.filtroSseActual || "todos";
        }

        // Conectar pasándole el parámetro real corregido
        evtSource = new EventSource(
            `/api/tickets-stream?tipo=${encodeURIComponent(tipoTabla)}&estado=${encodeURIComponent(filtroEstado)}`,
        );

        // Conectar pasándole el parámetro exacto a Laravel
        evtSource = new EventSource(
            `/api/tickets-stream?tipo=${encodeURIComponent(tipoTabla)}&estado=${encodeURIComponent(filtroEstado)}`,
        );

        evtSource.onmessage = function (event) {
            if (hayAccionEnCurso()) return;
            try {
                const data = JSON.parse(event.data);
                if (data.error) {
                    if (data.error === "No autenticado")
                        window.location.href = "/login";
                    return;
                }

                procesarTabla(data.html);

                // Actualizar contadores y métricas superiores de los Dashboards
                if (data.contadores) {
                    actualizarElemento(
                        "contador-abiertos",
                        data.contadores.abiertos,
                    );
                    actualizarElemento(
                        "contador-proceso",
                        data.contadores.proceso,
                    );
                    actualizarElemento(
                        "contador-resueltos",
                        data.contadores.resueltos,
                    );
                }
                if (data.contadorAsignados !== undefined)
                    actualizarElemento(
                        "contador-asignados",
                        data.contadorAsignados,
                    );
                if (data.grafico)
                    actualizarElemento(
                        "barras-rendimiento",
                        data.grafico,
                        true,
                    );
                if (data.cargaTrabajo !== undefined)
                    actualizarElemento(
                        "metric-carga-trabajo",
                        data.cargaTrabajo,
                    );
                if (data.resueltos24h !== undefined)
                    actualizarElemento(
                        "metric-resueltos-24h",
                        data.resueltos24h,
                    );
                if (data.tasaCierre !== undefined)
                    actualizarElemento(
                        "metric-tasa-cierre",
                        data.tasaCierre + "%",
                    );
            } catch (err) {
                console.error("[SSE] JSON Parse Error:", err);
            }
        };

        evtSource.onerror = function () {
            console.debug("[SSE] Buscando canal activo...");
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
        forzarRefresco: () => {
            iniciar();
        },
        aplicarEstilosPaginacion,
    };
})();

// =============================================================
// INTERCEPCIÓN Y DISPARADORES SEGUROS DE JQUERY / DOM
// =============================================================
document.addEventListener("DOMContentLoaded", function () {
    // Escucha permanente: Si DataTables redibuja la tabla, re-aplica los estilos visuales de inmediato
    if (window.$ && $.fn.DataTable) {
        $(document).on("draw.dt", function () {
            AutoRefrescoSSE.aplicarEstilosPaginacion();
        });
    }

    // Iniciar conexión en tiempo real
    AutoRefrescoSSE.iniciar();
});

window.addEventListener("beforeunload", () => {
    AutoRefrescoSSE.detener();
});

// Interruptor Universal de Estados (Garantiza sincronía visual y corte de canal)
window.cambiarFiltroSistema = function (estadoObjetivo, elementoBoton) {
    if (!elementoBoton) return;

    const contenedorFiltros = elementoBoton.closest("#filtrosEstado, .flex");
    if (contenedorFiltros) {
        contenedorFiltros.querySelectorAll(".filtro-btn").forEach((btn) => {
            btn.classList.remove(
                "bg-secondary",
                "active",
                "text-white",
                "shadow-md",
            );
            btn.classList.add("bg-slate-100", "text-slate-500");
        });
    }
    elementoBoton.classList.remove("bg-slate-100", "text-slate-500");
    elementoBoton.classList.add(
        "bg-secondary",
        "active",
        "text-white",
        "shadow-md",
    );

    // Forzar reconexión del SSE mandándole el nuevo estado capturado
    AutoRefrescoSSE.forzarRefresco();
};

// Variable global de respaldo para que el cliente mantenga el filtro en memoria
window.filtroSseActual = 'todos';

// Escuchar los clics del usuario en CUALQUIER botón de filtro de la aplicación
document.addEventListener('click', function (event) {
    const boton = event.target.closest('.filtro-btn, [onclick*="filtrarEstado"]');
    if (boton) {
        // Capturar el estado directamente desde el atributo HTML
        const estado = boton.getAttribute('data-estado') || 'todos';
        window.filtroSseActual = estado;
        
        // Darle un pequeño respiro al navegador para que actualice las clases visuales y refrescar el SSE
        setTimeout(() => {
            if (typeof AutoRefrescoSSE !== 'undefined') {
                AutoRefrescoSSE.forzarRefresco();
            }
        }, 50);
    }
});
