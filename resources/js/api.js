// ===================================================
// AUTO-REFRESCO UNIVERSAL POR WEBSOCKETS (REVERB)
// ===================================================

window.AutoRefresco = (() => {
    let isRefreshing = false;

    //------SEGURO
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

    //---PROCESAMIENTO Y REFRESCO
    function procesarTabla(htmlNuevo) {
        if (htmlNuevo === undefined || htmlNuevo === null || isRefreshing)
            return;

        isRefreshing = true;

        try {
            const tablaElement = document.querySelector(
                '.dataTable, table[id*="tabla"], table[id*="Table"]',
            );
            if (!tablaElement) return;

            const tablaId = tablaElement.id;
            const tablaBody = tablaElement.querySelector("tbody");
            if (!tablaBody) return;

            if (tablaId !== "tablaHistorial") {
                if (
                    !window.$ ||
                    !$.fn.DataTable ||
                    !$.fn.DataTable.isDataTable(tablaElement)
                ) {
                    tablaBody.innerHTML = htmlNuevo;
                    return;
                }

                const $tabla = $(tablaElement);
                let paginaActual = 0;
                let buscadorTermino = "";

                try {
                    const dtInstancia = $tabla.DataTable();
                    paginaActual = dtInstancia.page();
                    buscadorTermino = dtInstancia.search();
                } catch (_) {}

                try {
                    $tabla.DataTable().destroy();
                } catch (_) {}

                const parser = new DOMParser();
                const doc = parser.parseFromString(
                    "<table>" + htmlNuevo + "</table>",
                    "text/html",
                );
                const tbodyEl = doc.querySelector("tbody");
                tablaBody.innerHTML = tbodyEl ? tbodyEl.innerHTML : "";

                if (
                    tablaId === "userTable" &&
                    typeof window.inicializarUserTable === "function"
                ) {
                    window.inicializarUserTable();
                } else if (
                    typeof window.inicializarTablaTickets === "function"
                ) {
                    window.inicializarTablaTickets("#" + tablaId);
                }

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
                    console.error("[Reverb] Error al restaurar DataTables:", err);
                }
            }
        } finally {
            isRefreshing = false;
        }
    }

    //---ESTILOS DE PAGINACIÓN
    function aplicarEstilosPaginacion() {
        const wrappers = document.querySelectorAll(".dataTables_wrapper");
        wrappers.forEach((wrap) => {
            if (wrap.id === "tablaHistorial_wrapper") return;

            const lengthSelect = wrap.querySelector(".dataTables_length select");
            if (lengthSelect) {
                lengthSelect.className =
                    "mx-2 px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl focus:outline-none";
            }

            const paginateContainer = wrap.querySelector(".dataTables_paginate");
            if (paginateContainer) {
                paginateContainer.className =
                    "dataTables_paginate flex items-center gap-1.5 mt-4 justify-center md:justify-end w-full text-slate-600 text-xs font-medium ml-auto";

                const links = paginateContainer.querySelectorAll("a, .paginate_button");
                wrapperLinks(links);
            }
        });
    }

    //--ESTILOS DE PAGINACIÓN: CLASES DINÁMICAS PARA ESTADOS
    function wrapperLinks(links) {
        links.forEach((btn) => {
            btn.removeAttribute("style");
            btn.className =
                "px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border select-none no-underline inline-block ";

            if (btn.classList.contains("current") || btn.classList.contains("active")) {
                btn.classList.add("bg-secondary", "text-white", "border-secondary", "shadow-sm");
            } else if (btn.classList.contains("disabled")) {
                btn.classList.add("bg-slate-50", "text-slate-300", "border-slate-100", "pointer-events-none");
            } else {
                btn.classList.add("bg-slate-100", "text-slate-600", "border-slate-200", "hover:bg-slate-200", "hover:text-slate-900");
            }
        });
    }

    //------SSE: INICIAR, DETENER Y FORZAR REFRESCO
    function iniciar() {
        detener();
        const tablaElement = document.querySelector(
            '.dataTable, table[id*="tabla"], table[id*="Table"]',
        );
        if (!tablaElement) return;

        const tablaBody = tablaElement.querySelector("tbody");
        if (!tablaBody) return;

        let tipoTablaRaw = tablaElement.id === "tablaHistorial" ? "historial" : tablaBody.getAttribute("data-tipo") || "dashboard";
        const tipoTabla = tipoTablaRaw.toLowerCase().replace('-', '_');

        let filtroEstado = "todos";
        const botonActivo = document.querySelector(
            '.filtro-btn.bg-secondary, .filtro-btn.active, [id="filtrosEstado"] .bg-secondary',
        );
        if (botonActivo) {
            filtroEstado = botonActivo.getAttribute("data-estado") || "todos";
        } else {
            filtroEstado = window.filtroSseActual || "todos";
        }

        if (
            filtroEstado.includes(",") &&
            tipoTabla !== "asignados" &&
            tipoTabla !== "dashboard" &&
            tipoTabla !== "mis_tickets" 
        ) {
            filtroEstado = "cerrado";
        }

        if (window.Echo) {
            window.Echo.channel("tickets-publicos").listen(
                ".TicketActualizado",
                (e) => {
                    if (hayAccionEnCurso()) return;

                    const url = `/api/refresh?tipo=${encodeURIComponent(tipoTabla)}&estado=${encodeURIComponent(filtroEstado)}`;

                    fetch(url)
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.error) {
                                if (data.error === "No autenticado")
                                    window.location.href = "/login";
                                return;
                            }
                            procesarTabla(data.html);

                            if (data.contadores) {
                                actualizarElemento("contador-abiertos", data.contadores.abiertos);
                                actualizarElemento("contador-proceso", data.contadores.proceso);
                                actualizarElemento("contador-resueltos", data.contadores.resueltos);
                            }
                            if (data.contadorAsignados !== undefined)
                                actualizarElemento("contador-asignados", data.contadorAsignados);
                            
                            if (data.grafico)
                                actualizarElemento("barras-rendimiento", data.grafico, true);
                                
                            if (data.cargaTrabajo !== undefined)
                                actualizarElemento("metric-carga-trabajo", data.cargaTrabajo);
                            if (data.resueltos24h !== undefined)
                                actualizarElemento("metric-resueltos-24h", data.resueltos24h);
                            if (data.tasaCierre !== undefined)
                                actualizarElemento("metric-tasa-cierre", data.tasaCierre + "%");
                        })
                        .catch((err) => console.error("[Reverb] Error al hacer fetch:", err));
                },
            );
        } else {
            console.warn("[Reverb] window.Echo no está definido.");
        }
    }

    //------SSE: DETENER
    function detener() {
        if (window.Echo) {
            window.Echo.leaveChannel("tickets-publicos");
        }
    }

    return {
        iniciar,
        detener,
        forzarRefresco: () => {
            iniciar();

            const tablaElement = document.querySelector(
                '.dataTable, table[id*="tabla"], table[id*="Table"]',
            );
            if (!tablaElement) return;
            const tablaBody = tablaElement.querySelector("tbody");
            if (!tablaBody) return;

            let tipoTablaRaw = tablaElement.id === "tablaHistorial" ? "historial" : tablaBody.getAttribute("data-tipo") || "dashboard";
            const tipoTabla = tipoTablaRaw.toLowerCase().replace('-', '_');

            let filtroEstado = window.filtroSseActual || "todos";
            if (
                filtroEstado.includes(",") &&
                tipoTabla !== "asignados" &&
                tipoTabla !== "dashboard" &&
                tipoTabla !== "mis_tickets"
            ) {
                filtroEstado = "cerrado";
            }

            const url = `/api/refresh?tipo=${encodeURIComponent(tipoTabla)}&estado=${encodeURIComponent(filtroEstado)}`;
            fetch(url)
                .then((res) => res.json())
                .then((data) => {
                    if (!data.error) {
                        procesarTabla(data.html);
                    }
                })
                .catch((err) => console.error(err));
        },
        aplicarEstilosPaginacion,
    };
})();

window.AutoRefrescoSSE = window.AutoRefresco;

//------INICIALIZACIÓN AUTOMÁTICA AL CARGAR LA PÁGINA
document.addEventListener("DOMContentLoaded", function () {
    if (window.$ && $.fn.DataTable) {
        $(document).on("draw.dt", function (e, settings) {
            if (settings && settings.nTable && settings.nTable.id === "tablaHistorial")
                return;
            window.AutoRefresco.aplicarEstilosPaginacion();
        });
    }
    window.AutoRefresco.iniciar();
});

window.addEventListener("beforeunload", () => {
    window.AutoRefresco.detener();
});

window.cambiarFiltroSistema = function (estadoObjetivo, elementoBoton) {
    if (!elementoBoton) return;

    const contenedorFiltros = elementoBoton.closest("#filtrosEstado, .flex");
    if (contenedorFiltros) {
        contenedorFiltros.querySelectorAll(".filtro-btn").forEach((btn) => {
            btn.classList.remove("bg-secondary", "active", "text-white", "shadow-md");
            btn.classList.add("bg-slate-100", "text-slate-500");
        });
    }
    elementoBoton.classList.remove("bg-slate-100", "text-slate-500");
    elementoBoton.classList.add("bg-secondary", "active", "text-white", "shadow-md");

    window.filtroSseActual = estadoObjetivo;
    window.AutoRefresco.forzarRefresco();
};

window.filtroSseActual = "todos";