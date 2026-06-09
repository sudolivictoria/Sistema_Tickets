// ===================================================
// AUTO-REFRESCO UNIVERSAL POR WEBSOCKETS (REVERB)
// ===================================================

window.AutoRefresco = (() => {
    let isRefreshing = false;

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

    function procesarTabla(tablaId, htmlNuevo) {
        const tablaElement = document.getElementById(tablaId);
        if (!tablaElement || !htmlNuevo) return;

        const tbody = tablaElement.querySelector("tbody") || tablaElement;

        // Guardamos el scroll para evitar saltos molestos de pantalla
        const scrollX = window.scrollX;
        const scrollY = window.scrollY;

        tbody.innerHTML = htmlNuevo;

        window.scrollTo(scrollX, scrollY);
    }

    function restaurarElementosGlobales(contadores, metricas) {
        // Sincronizar contadores superiores con el HTML
        if (contadores) {
            const mappings = {
                abiertos: "contador-abiertos",
                proceso: "contador-proceso",
                resueltos: "contador-resueltos",
                asignados: "contador-asignados",
            };

            Object.keys(mappings).forEach((key) => {
                const elementId = mappings[key];
                const el = document.getElementById(elementId);
                if (el && contadores[key] !== undefined) {
                    el.textContent = String(contadores[key]);
                }
            });
        }

        // Sincronizar métricas de KPI en Historial
        if (metricas) {
            const metricMappings = {
                cargaTrabajo: "metric-carga-trabajo",
                resueltos24h: "metric-resueltos-24h",
                tasaCierre: "metric-tasa-cierre",
            };

            Object.keys(metricMappings).forEach((key) => {
                const elementId = metricMappings[key];
                const el = document.getElementById(elementId);
                if (el && metricas[key] !== undefined) {
                    el.textContent =
                        key === "tasaCierre"
                            ? `${metricas[key]}%`
                            : String(metricas[key]);
                }
            });
        }
    }

    async function ejecutarPeticionRefresco() {
        if (isRefreshing) return;

        // 1. Buscamos tu atributo original "data-tipo" en la pantalla
        const contenedorConTipo = document.querySelector("[data-tipo]");
        if (!contenedorConTipo) return;

        // Seguro para no interrumpir si el usuario está usando un modal o escribiendo
        if (hayAccionEnCurso()) {
            console.log(
                "[AutoRefresco] Sincronización pausada por actividad del usuario.",
            );
            return;
        }

        // 2. Leemos el valor de tu atributo (ej: 'dashboard', 'mis_tickets', 'mis_asignados')
        const tipoTabla = contenedorConTipo.getAttribute("data-tipo");
        const filtroEstado = window.filtroSseActual || "todos";

        isRefreshing = true;

        try {
            const token = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            // CORRECCIÓN: Usamos window.location.origin para que detecte la IP actual (172.16.116.80)
            // y le pegamos la ruta /api/table/refresh pasando el tipo en la URL
            const urlBase = window.location.origin;
            const urlDestino = `${urlBase}/api/table/refresh?tipo=${tipoTabla}`;

            const response = await fetch(urlDestino, {
                method: "POST", // Coincide exactamente con el Route::post de Laravel
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    estado: filtroEstado,
                }),
            });

            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

            const data = await response.json();

            if (data.success) {
                // Buscamos la tabla en pantalla para actualizar sus filas
                const tablaElement = document.querySelector("table");
                if (tablaElement) {
                    // Buscamos el tbody de la tabla
                    const tbody =
                        tablaElement.querySelector("tbody") || tablaElement;

                    // Mantenemos la posición del scroll para evitar saltos
                    const scrollX = window.scrollX;
                    const scrollY = window.scrollY;

                    // Inyectamos el HTML limpio que devuelve el controlador
                    tbody.innerHTML = data.html;

                    window.scrollTo(scrollX, scrollY);
                }

                // Actualizamos los contadores de las tarjetas superiores
                if (
                    data.contadores &&
                    typeof restaurarElementosGlobales === "function"
                ) {
                    restaurarElementosGlobales(data.contadores, data.metricas);
                }
            }
        } catch (error) {
            console.error("[AutoRefresco] Error al sincronizar:", error);
        } finally {
            isRefreshing = false;
        }
    }

    return {
        iniciar: () => {
            if (typeof window.Echo !== "undefined") {
                window.Echo.channel("tickets").listen(
                    "TicketActualizado",
                    () => {
                        console.log(
                            "[WebSocket] Evento de actualización detectado.",
                        );
                        ejecutarPeticionRefresco();
                    },
                );
            } else {
                console.warn("[AutoRefresco] Laravel Echo no está disponible.");
            }
        },
        forzarRefresco: ejecutarPeticionRefresco,
    };
})();

// Inicialización automática
document.addEventListener("DOMContentLoaded", () => {
    window.AutoRefresco.iniciar();
});

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

    window.filtroSseActual = estadoObjetivo;
    window.AutoRefresco.forzarRefresco();
};
