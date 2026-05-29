// =============================================================
// AUTO-REFRESCO UNIVERSAL POR EVENTOS DEL SERVIDOR (SSE)
// =============================================================

const AutoRefrescoSSE = (() => {
    let evtSource = null;
    let isRefreshing = false;

    //--bloque si el usuario tiene alguna acción en curso (modal abierto, buscador activo, etc)
    window.hayAccionEnCurso = function () {
        const modalAbierto = document.querySelector(
            ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden), #modalAgregar:not(.hidden), #modalEditar:not(.hidden), .swal2-container:not(.hidden)",
        );

        //--buscador de DataTables o buscador general activo
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
    };

    //---función para actualizar texto o HTML de un elemento por ID
    window.actualizarElemento = function (id, valor, esHTML = false) {
        const el = document.getElementById(id);
        if (!el || valor === undefined || valor === null) return;
        if (esHTML) el.innerHTML = valor;
        else el.textContent = String(valor);
    };

    //---función para procesar la tabla recibida del servidor
    window.procesarTabla = function (htmlNuevo) {
        if (!htmlNuevo || isRefreshing) return;
        isRefreshing = true;

        const tablaElement = document.querySelector(
            '.dataTable, table[id*="tabla"], table[id*="Table"]',
        );
        if (!tablaElement) {
            isRefreshing = false;
            return;
        }
        //---extraer solo el tbody del HTML nuevo para evitar conflictos con DataTables
        const tablaId = tablaElement.id;
        const tablaBody = tablaElement.querySelector("tbody");
        if (!tablaBody) {
            isRefreshing = false;
            return;
        }
        //---si no hay DataTables, simplemente reemplazar el tbody y salir
        if (
            !window.$ ||
            !$.fn.DataTable ||
            !$.fn.DataTable.isDataTable(tablaElement)
        ) {
            tablaBody.innerHTML = htmlNuevo;
            isRefreshing = false;
            return;
        }
        //---si hay DataTables, destruir instancia, reemplazar tbody y re-inicializar
        const $tabla = $(tablaElement);
        let paginaActual = 0;
        let buscadorTermino = "";
        //---intentar preservar estado de paginación y búsqueda antes de destruir
        try {
            const dtInstancia = $tabla.DataTable();
            paginaActual = dtInstancia.page();
            buscadorTermino = dtInstancia.search();
        } catch (_) {}
        //---destruir instancia previa para evitar conflictos
        try {
            $tabla.DataTable().destroy();
        } catch (_) {}
        //---reemplazar solo el tbody con el nuevo HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(
            "<table>" + htmlNuevo + "</table>",
            "text/html",
        );
        //----extraer solo el tbody del nuevo HTML para evitar conflictos con DataTables
        const tbodyEl = doc.querySelector("tbody");
        tablaBody.innerHTML = tbodyEl ? tbodyEl.innerHTML : "";
        //---re-inicializar DataTables con la nueva tabla
        if (
            tablaId === "userTable" &&
            typeof window.inicializarUserTable === "function"
        ) {
            window.inicializarUserTable(); //---función específica para tabla de usuarios (si existe)
        } else if (typeof window.inicializarTablaTickets === "function") {
            window.inicializarTablaTickets("#" + tablaId); //---función genérica para tablas de tickets (si existe)
        } else {
            $tabla.DataTable({
                destroy: true,
                responsive: true,
                autoWidth: false,
                pageLength: 5,
                dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
                language: {
                    processing: "Procesando...",
                    zeroRecords: `
                    <div class="flex flex-col items-center justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No se encontraron resultados</p>
                    </div>`,
                    emptyTable: `
                    <div class="flex flex-col items-center justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">folder_off</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No hay datos disponibles</p>
                    </div>`,
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

        try {
            //--restaurar estado de búsqueda y paginación después de re-inicializar
            const dtNuevo = $tabla.DataTable();
            if (buscadorTermino) {
                dtNuevo.search(buscadorTermino).draw(false);
            }
            //--ajustar página actual solo si sigue siendo válida después de la actualización
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
    };
    //--función para aplicar estilos personalizados a los controles de paginación de DataTables
    function aplicarEstilosPaginacion() {
        const wrappers = document.querySelectorAll(".dataTables_wrapper");
        //--estilos para select de cantidad de registros
        wrappers.forEach((wrap) => {
            const lengthSelect = wrap.querySelector(
                ".dataTables_length select",
            );
            //---aplicar clases de Tailwind al select de cantidad de registros
            if (lengthSelect) {
                lengthSelect.className =
                    "mx-2 px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl focus:outline-none";
            }
            //---aplicar clases de Tailwind a los botones de paginación
            const paginateContainer = wrap.querySelector(
                ".dataTables_paginate",
            );
            //---reemplazar clases de paginación por estilos personalizados
            if (paginateContainer) {
                paginateContainer.className =
                    "dataTables_paginate flex items-center gap-1.5 mt-4 justify-end text-slate-600 text-xs font-medium";

                const links = paginateContainer.querySelectorAll(
                    "a, .paginate_button",
                );
                links.forEach((btn) => {
                    btn.removeAttribute("style");
                    btn.className =
                        "px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer border select-none no-underline inline-block ";

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
                    } else if (btn.classList.contains("disabled")) {
                        btn.classList.add(
                            "bg-slate-50",
                            "text-slate-300",
                            "border-slate-100",
                            "pointer-events-none",
                        );
                    } else {
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

    //--función para inicializar tabla de tickets (si existe)
    function iniciar() {
        detener();
        const tablaElement = document.querySelector(
            '.dataTable, table[id*="tabla"], table[id*="Table"]',
        );
        //--si no se encuentra tabla, no iniciar SSE para evitar conexiones innecesarias
        if (!tablaElement) return;
        const tablaBody = tablaElement.querySelector("tbody");
        if (!tablaBody) return;
        //---determinar tipo de tabla para enviar al servidor como parámetro (dashboard, asignar, etc)
        const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";
        //--determinar estado actual del filtro para enviar al servidor como parámetro (abiertos, proceso, resueltos, etc)
        let filtroEstado = "todos";
        const botonActivo = document.querySelector(
            '.filtro-btn.bg-secondary, .filtro-btn.active, [id="filtrosEstado"] .bg-secondary',
        );
        //--si se encuentra un botón activo, usar su estado; si no, usar el filtroSseActual como respaldo (que se actualiza al hacer clic en cualquier botón de filtro)
        if (botonActivo) {
            filtroEstado = botonActivo.getAttribute("data-estado") || "todos";
        } else {
            filtroEstado = window.filtroSseActual || "todos";
        }
        //---iniciar conexión SSE con parámetros de tipo de tabla y estado de filtro
        evtSource = new EventSource(
            `/api/tickets-stream?tipo=${encodeURIComponent(tipoTabla)}&estado=${encodeURIComponent(filtroEstado)}`,
        );
        //---evento para procesar datos recibidos del servidor
        evtSource.onmessage = function (event) {
            if (hayAccionEnCurso()) return;
            try {
                const data = JSON.parse(event.data);
                if (data.error) {
                    if (data.error === "No autenticado")
                        window.location.href = "/login";
                    return;
                }
                //---si el servidor envía un nuevo HTML para la tabla, procesarlo
                procesarTabla(data.html);
                //---actualizar otros elementos del dashboard si se incluyen en la respuesta
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

        //---evento para manejar errores de conexión y reconectar automáticamente
        evtSource.onerror = function () {
            if (evtSource && evtSource.readyState === EventSource.CLOSED) {
                console.warn("[SSE] Canal cerrado definitivamente.");
                detener();
            } else {
                console.debug("[SSE] Reconectando...");
            }
        };
    }
    //--función para detener la conexión SSEy limpiar recursos
    function detener() {
        if (evtSource) {
            evtSource.close();
            evtSource = null;
        }
    }
    //--función para forzar un refresco manual desde el cliente (por ejemplo, al cambiar de filtro)
    return {
        iniciar,
        detener,
        forzarRefresco: () => iniciar(),
        aplicarEstilosPaginacion,
    };
})();

// =============================================================
// INTERCEPCIÓN Y DISPARADORES SEGUROS DE JQUERY / DOM
// =============================================================

//---inicializar DataTables y configurar eventos después de cargar el DOM
document.addEventListener("DOMContentLoaded", function () {
    if (window.$ && $.fn.DataTable) {
        $(document).on("draw.dt", function () {
            AutoRefrescoSSE.aplicarEstilosPaginacion();
        });
    }
    AutoRefrescoSSE.iniciar(); //---iniciar SSE al cargar la página
});

//---detener SSE al salir de la página para evitar conexiones persistentes innecesarias
window.addEventListener("beforeunload", () => {
    AutoRefrescoSSE.detener();
});

//---listener global para actualizar la variable de filtroSseActual al hacer clic en cualquier botón de filtro, incluso si el botón no tiene el atributo data-estado (como en algunos casos personalizados)
window.cambiarFiltroSistema = function (estadoObjetivo, elementoBoton) {
    if (!elementoBoton) return;

    //--si el estado objetivo es el mismo que el filtro actual, no hacer nada para evitar refrescos innecesarios
    if (window.filtroSseActual === estadoObjetivo) {
        return;
    }
    window.filtroSseActual = estadoObjetivo;
    //---resetear estilos de todos los botones de filtro en el mismo contenedor (ya sea #filtrosEstado o cualquier contenedor padre común)
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

    AutoRefrescoSSE.forzarRefresco();
};

//--variable global para mantener el estado del filtro seleccionado y enviarlo al servidor en cada conexión SSE, incluso si el usuario navega a otras páginas sin botones de filtro (como detalles de ticket) o si el botón de filtro no tiene el atributo data-estado
window.filtroSseActual = "todos";

//----cambiarFiltroSistema es el único responsable de disparar el refresco---
document.addEventListener("click", function (event) {
    const boton = event.target.closest(
        '.filtro-btn, [onclick*="filtrarEstado"]',
    );
    if (boton) {
        window.filtroSseActual = boton.getAttribute("data-estado") || "todos";
    }
});
