// ===================================================
// AUTO-REFRESCO UNIVERSAL POR WEBSOCKETS (REVERB)
// ===================================================

/**
 * Módulo autoejecutable (IIFE) encargado de gestionar la actualización
 * en tiempo real de las tablas y métricas del sistema HelpDesk sin romper
 * la experiencia de navegación ni la interacción del usuario.
 */
window.AutoRefresco = (() => {
    let isRefreshing = false; //-------flag de control

    /**
     * SEGURO DE INTERACCIÓN
     * Evalúa si el usuario está realizando alguna acción crítica para pausar el refresco interactivo.
     * @returns {boolean} True si hay un modal abierto o el usuario está escribiendo en un buscador.
     */
    //------SEGURO (detecta si hay un modal abierto o se esta escribiendo algo en el buscador)
    function hayAccionEnCurso() {
        const modalAbierto = document.querySelector(
            ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden), #modalAgregar:not(.hidden), #modalEditar:not(.hidden), .swal2-container:not(.hidden)",
        );
        //----validar por foco
        const tieneFoco =
            document.activeElement &&
            (document.activeElement.getAttribute("type") === "search" ||
                document.activeElement.closest(".dataTables_filter"));
        //----validar si tiene texto
        const inputsBusqueda = document.querySelectorAll(
            'input[type="search"], #inputBusqueda',
        );
        let tieneTexto = false;
        inputsBusqueda.forEach((input) => {
            if (input.value && input.value.trim() !== "") {
                tieneTexto = true;
            }
        });
        return !!(modalAbierto || tieneFoco || tieneTexto);
    }

    /**
     * UTILIDAD DE ACTUALIZACIÓN INDIVIDUAL
     * Modifica el contenido de un elemento HTML específico por su ID de forma segura.
     * @param {string} id - ID del elemento en el DOM.
     * @param {string|number} valor - Contenido a inyectar.
     * @param {boolean} esHTML - Define si se insertará como código HTML o texto plano.
     */
    function actualizarElemento(id, valor, esHTML = false) {
        const el = document.getElementById(id);
        if (!el || valor === undefined || valor === null) return;
        if (esHTML) el.innerHTML = valor;
        else el.textContent = String(valor);
    }

    /**
     * PROCESAMIENTO Y REFRESCO DE TABLAS DINÁMICAS
     * Desestructura e inyecta las nuevas filas en la tabla del sistema,
     * manteniendo el estado de paginación y filtros que tenía el usuario previamente.
     * @param {string} htmlNuevo - Cacho de código HTML (generalmente filas <tr>) recibido del servidor.
     */
    function procesarTabla(htmlNuevo) {
        //----------Bloqueo de seguridad: Evitar procesar si el contenido está vacío o ya hay un refresco en marcha
        if (htmlNuevo === undefined || htmlNuevo === null || isRefreshing)
            return;
        isRefreshing = true;
        try {
            //--------------buscar la tabla activa en la vista actual mediante selectores genericos
            const tablaElement = document.querySelector(
                '.dataTable, table[id*="tabla"], table[id*="Table"]',
            );
            if (!tablaElement) return;

            const tablaId = tablaElement.id;
            const tablaBody = tablaElement.querySelector("tbody");
            if (!tablaBody) return;
            //----------------la tablahistorial no se auto-refresca dinamicamente para no afectar auditorias
            if (tablaId !== "tablaHistorial") {
                //------si la tabla no está inicializada con el Plugin jQuery DataTables, se actualiza el HTML directamente
                if (
                    !window.$ ||
                    !$.fn.DataTable ||
                    !$.fn.DataTable.isDataTable(tablaElement)
                ) {
                    tablaBody.innerHTML = htmlNuevo;
                    return;
                }
                //-----el flujo continua si es datatable
                const $tabla = $(tablaElement);
                let paginaActual = 0;
                let buscadorTermino = "";
                //------captura y resguarda el estado actual de la tabla
                try {
                    const dtInstancia = $tabla.DataTable();
                    paginaActual = dtInstancia.page();
                    buscadorTermino = dtInstancia.search();
                } catch (_) {}
                //----destruye temporalmente la instancia de datatable
                try {
                    $tabla.DataTable().destroy();
                } catch (_) {}
                //-----------utilizar DOMParser para limpiar e interpretar el fragmento HTML recibido de forma segura
                const parser = new DOMParser();
                const doc = parser.parseFromString(
                    "<table>" + htmlNuevo + "</table>",
                    "text/html",
                );
                const tbodyEl = doc.querySelector("tbody");
                tablaBody.innerHTML = tbodyEl ? tbodyEl.innerHTML : "";
                //---------------Re-inicializar configuraciones específicas dependiendo del ID de la tabla en pantalla
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
                //-------------reinstanciar datatable y restaurar filtros, busqueda y paginaion
                try {
                    const dtNuevo = $tabla.DataTable();
                    if (buscadorTermino) {
                        dtNuevo.search(buscadorTermino).draw(false);
                    }
                    const totalPaginas = dtNuevo.page.info().pages;
                    //--------verificar que la pag donde estaba exista despues de la actualizacion
                    if (paginaActual > 0 && paginaActual < totalPaginas) {
                        dtNuevo.page(paginaActual).draw(false);
                    }
                    aplicarEstilosPaginacion(); //---------aplica de nuevo los estilos de los botones
                } catch (err) {
                    console.error(
                        "[Reverb] Error al restaurar DataTables:",
                        err,
                    );
                }
            }
        } finally {
            isRefreshing = false; //-----------liberar bandera de control
        }
    }

    /**
     * REDISEÑO ESTÉTICO DE LA PAGINACIÓN Y FILTROS DE DATATABLES
     * Inyecta clases utilitarias de Tailwind CSS a los componentes nativos generados por DataTables.
     */
    function aplicarEstilosPaginacion() {
        const wrappers = document.querySelectorAll(".dataTables_wrapper");
        wrappers.forEach((wrap) => {
            if (wrap.id === "tablaHistorial_wrapper") return;
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
                paginateContainer.className =
                    "dataTables_paginate flex items-center gap-1.5 mt-4 justify-center md:justify-end w-full text-slate-600 text-xs font-medium ml-auto";

                const links = paginateContainer.querySelectorAll(
                    "a, .paginate_button",
                );
                wrapperLinks(links);
            }
        });
    }

    /**
     * ASIGNADOR DE CLASES DINÁMICAS PARA BOTONES DE PAGINACIÓN
     * Evalúa el estado del botón (Activo, Deshabilitado, Común) y aplica clases Tailwind personalizadas.
     * @param {NodeList} links - Colección de botones/enlaces HTML.
     */
    function wrapperLinks(links) {
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

    /**
     * PERSISTENCIA LOCAL: CACHÉ DE INTERFAZ (UI)
     * Recupera instantáneamente del almacenamiento del navegador (localStorage) el último estado visualizado.
     * Esto evita el parpadeo blanco (FOUC) o contadores en cero mientras el WebSocket procesa cambios.
     */
    function restaurarElementosGlobales() {
        const cachedGrafico = localStorage.getItem("global_grafico_html");
        const contenedorGrafico = document.getElementById("barras-rendimiento");
        if (contenedorGrafico && cachedGrafico) {
            contenedorGrafico.innerHTML = cachedGrafico;
        }
        const contadores = ["abiertos", "proceso", "resueltos", "asignados"];
        contadores.forEach((key) => {
            const cachedVal = localStorage.getItem(`global_contador_${key}`);
            if (cachedVal) actualizarElemento(`contador-${key}`, cachedVal);
        });
        //------------METRICAS
        const metricas = ["carga-trabajo", "resueltos-24h", "tasa-cierre"];
        metricas.forEach((key) => {
            const cachedVal = localStorage.getItem(`global_metric_${key}`);
            if (cachedVal) actualizarElemento(`metric-${key}`, cachedVal);
        });
    }

    /**
     * CANAL EN TIEMPO REAL: SUSCRIPCIÓN ESCUCHA DE WEBSOCKETS
     * Abre e interactúa con los canales públicos de Laravel Echo (Reverb) para escuchar transmisiones (Broadcasts).
     */
    function iniciar() {
        detener(); //---limpiar y remover suscripciones internas
        restaurarElementosGlobales();
        const tablaElement = document.querySelector(
            '.dataTable, table[id*="tabla"], table[id*="Table"]',
        );
        if (!tablaElement) return;
        const tablaBody = tablaElement.querySelector("tbody");
        if (!tablaBody) return;
        //----------DETERMINAR QUE TIPO DE TABLA SE ESTA VISUALIZANDO
        let tipoTablaRaw =
            tablaElement.id === "tablaHistorial"
                ? "historial"
                : tablaBody.getAttribute("data-tipo") || "dashboard";
        const tipoTabla = tipoTablaRaw.toLowerCase().replace("-", "_");

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
            tipoTabla !== "dashboard" &&
            tipoTabla !== "mis_tickets"
        ) {
            filtroEstado = "cerrado";
        }
        //***********Validar que el cliente WebSocket de Laravel (Echo) esté instanciado globalmente**************
        if (window.Echo) {
            window.Echo.channel("tickets-publicos").listen(
                ".TicketActualizado",
                (e) => {
                    //--------Si el usuario esta operando un modal o buscando, abortar refresco
                    if (hayAccionEnCurso()) return;
                    const url = `/api/refresh?tipo=${encodeURIComponent(tipoTabla)}&estado=${encodeURIComponent(filtroEstado)}`;
                    //-----consultar los datos actualizados de forma asincrona mediante fetch
                    fetch(url)
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.error) {
                                if (data.error === "No autenticado")
                                    //-------seguro vencimiento de sesion
                                    window.location.href = "/login";
                                return;
                            }
                            //----renderizar las filas internas de la tabla
                            procesarTabla(data.html);
                            //--------contadores generales, refrescar y guardar en cache local
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

                                localStorage.setItem(
                                    "global_contador_abiertos",
                                    data.contadores.abiertos,
                                );
                                localStorage.setItem(
                                    "global_contador_proceso",
                                    data.contadores.proceso,
                                );
                                localStorage.setItem(
                                    "global_contador_resueltos",
                                    data.contadores.resueltos,
                                );
                            }
                            //---------actualiza contador de mis asignados
                            if (data.contadorAsignados !== undefined) {
                                actualizarElemento(
                                    "contador-asignados",
                                    data.contadorAsignados,
                                );
                                localStorage.setItem(
                                    "global_contador_asignados",
                                    data.contadorAsignados,
                                );
                            }
                            //--------actualiza grafico
                            if (
                                data.grafico !== undefined &&
                                data.grafico !== ""
                            ) {
                                localStorage.setItem(
                                    "global_grafico_html",
                                    data.grafico,
                                );
                                const contenedorGrafico =
                                    document.getElementById(
                                        "barras-rendimiento",
                                    );
                                if (contenedorGrafico) {
                                    contenedorGrafico.innerHTML = data.grafico;
                                }
                            }
                            //----------actualiza carga de trabajo
                            if (data.cargaTrabajo !== undefined) {
                                actualizarElemento(
                                    "metric-carga-trabajo",
                                    data.cargaTrabajo,
                                );
                                localStorage.setItem(
                                    "global_metric_carga-trabajo",
                                    data.cargaTrabajo,
                                );
                            }
                            //----------actualiza resueltos 24h
                            if (data.resueltos24h !== undefined) {
                                actualizarElemento(
                                    "metric-resueltos-24h",
                                    data.resueltos24h,
                                );
                                localStorage.setItem(
                                    "global_metric_resueltos-24h",
                                    data.resueltos24h,
                                );
                            }
                            //-------actualiza tasa cierre
                            if (data.tasaCierre !== undefined) {
                                const formatoTasa = data.tasaCierre + "%";
                                actualizarElemento(
                                    "metric-tasa-cierre",
                                    formatoTasa,
                                );
                                localStorage.setItem(
                                    "global_metric_tasa-cierre",
                                    formatoTasa,
                                );
                            }
                        })
                        .catch((err) =>
                            console.error(
                                "[Reverb] Error al hacer fetch:",
                                err,
                            ),
                        );
                },
            );
        } else {
            console.warn("[Reverb] window.Echo no está definido.");
        }
    }

    /**
     * DESCONEXIÓN DEL WEBSOCKET
     * Libera el canal escuchado de Laravel Echo para optimizar el consumo de memoria en el servidor Reverb.
     */
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

            let tipoTablaRaw =
                tablaElement.id === "tablaHistorial"
                    ? "historial"
                    : tablaBody.getAttribute("data-tipo") || "dashboard";
            const tipoTabla = tipoTablaRaw.toLowerCase().replace("-", "_");

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

                        if (data.grafico !== undefined && data.grafico !== "") {
                            localStorage.setItem(
                                "global_grafico_html",
                                data.grafico,
                            );
                        }
                    }
                })
                .catch((err) => console.error(err));
        },
        aplicarEstilosPaginacion,
        restaurarElementosGlobales,
    };
})();

//----------------alias global para compatibilidad de codigo heredado legacy support
window.AutoRefrescoSSE = window.AutoRefresco;

/**
 * GESTORES DE EVENTOS DE PÁGINA (LIFECYCLE EVENTS)
 */
document.addEventListener("DOMContentLoaded", function () {
    //-----escuchar el evento interno de redibujado de jQuery DataTables ('draw.dt')
    //-----cada vez que se cambie de página o ordene una columna, se vuelven a inyectar estilos de Tailwind
    if (window.$ && $.fn.DataTable) {
        $(document).on("draw.dt", function (e, settings) {
            if (
                settings &&
                settings.nTable &&
                settings.nTable.id === "tablaHistorial"
            )
                return;
            window.AutoRefresco.aplicarEstilosPaginacion();
        });
    }
    //-----arrancar automáticamente el motor de escucha en tiempo real al cargar la interfaz
    window.AutoRefresco.iniciar();
});
//----------desconectar limpia y elegantemente los WebSockets si el usuario abandona o cierra la pestaña
window.addEventListener("beforeunload", () => {
    window.AutoRefresco.detener();
});

/**
 * INTERRUPTOR DE FILTROS DEL SISTEMA (FRONTEND)
 * Cambia los colores visuales de los botones de estados (Filtros Activos) y gatilla la actualización inmediata de la tabla.
 * @param {string|number} estadoObjetivo - El ID o slug del estado al cual filtrar.
 * @param {HTMLElement} elementoBoton - El elemento de botón DOM clickeado por el usuario.
 */
window.cambiarFiltroSistema = function (estadoObjetivo, elementoBoton) {
    if (!elementoBoton) return;
    //----------localizar el contenedor padre de los botones de filtro
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
    //-----guardar en memoria global el filtro seleccionado y forzar al WebSocket a traer datos alineados a este estado
    window.filtroSseActual = estadoObjetivo;
    window.AutoRefresco.forzarRefresco();
};
//*****declaración e inicialización del filtro por defecto del sistema al instanciarse el archivo
window.filtroSseActual = "todos";
