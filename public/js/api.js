// =============================================================
// AUTO-REFRESCO UNIVERSAL
// =============================================================

const AutoRefresco = (() => {
    const CONFIG = {
        INTERVALO_BASE_MS: 30_000,
        TIMEOUT_FETCH_MS: 10_000,
        MAX_FALLOS: 5,
        BACKOFF_MAX_MS: 300_000,
    };

    let timerId = null;
    let peticionEnCurso = false;
    let fallosConsec = 0;
    let detenido = false;

    // ──────────────────
    // Helpers
    // ──────────────────

    function calcularIntervalo() {
        if (fallosConsec === 0) return CONFIG.INTERVALO_BASE_MS;
        return Math.min(
            CONFIG.INTERVALO_BASE_MS * 2 ** fallosConsec,
            CONFIG.BACKOFF_MAX_MS,
        );
    }

    function actualizarElemento(id, valor, esHTML = false) {
        const el = document.getElementById(id);
        if (!el || valor === undefined || valor === null) return;
        if (esHTML) el.innerHTML = valor;
        else el.textContent = String(valor);
    }

    function hayAccionEnCurso() {
        const modalAbierto = document.querySelector(
            ".modal:not(.hidden), #modalTicket:not(.hidden), " +
                "#modalUsuario:not(.hidden), .swal2-container:not(.hidden)",
        );
        const buscador =
            document.getElementById("inputBusqueda") ||
            document.querySelector('input[type="search"]');
        return !!(
            modalAbierto ||
            (buscador && buscador === document.activeElement)
        );
    }

    function obtenerContextoTabla() {
        const tablaBody = document.getElementById("tablaBody");
        if (!tablaBody) return null;
        return {
            tablaBody,
            tipoTabla: tablaBody.getAttribute("data-tipo") || "dashboard",
            tablaElement: tablaBody.closest("table"),
        };
    }

    // ────────────────────
    // Actualizar tabla
    // ────────────────────
    function actualizarTabla({ tablaBody, tablaElement }, htmlNuevo) {
        if (htmlNuevo === undefined || htmlNuevo === null) return;

        if (
            !tablaElement ||
            !window.$ ||
            !$.fn.DataTable ||
            !$.fn.DataTable.isDataTable(tablaElement)
        ) {
            tablaBody.innerHTML = htmlNuevo;
            return;
        }
        const $tabla = $(tablaElement);
        const tablaId = tablaElement.id;
        if (!tablaId) {
            tablaBody.innerHTML = htmlNuevo;
            return;
        }
        //---Guardar página actual
        let paginaActual = 0;
        try {
            paginaActual = $tabla.DataTable().page();
        } catch (_) {}
        //--Destruir instancia actual
        try {
            $tabla.DataTable().destroy();
        } catch (_) {}

        // ---filas nuevas (el servidor devuelve <tbody>)
        const parser = new DOMParser();
        const doc = parser.parseFromString(
            "<table>" + htmlNuevo + "</table>",
            "text/html",
        );
        const tbodyEl = doc.querySelector("tbody");
        tablaBody.innerHTML = tbodyEl ? tbodyEl.innerHTML : "";

        //---Reinicializar DataTable con la función global del proyecto---
        if (typeof window.inicializarTablaTickets === "function") {
            window.inicializarTablaTickets("#" + tablaId);

            //---Restaurar página si sigue siendo válida---
            try {
                const dtNuevo = $tabla.DataTable();
                const totalPaginas = dtNuevo.page.info().pages;
                if (paginaActual > 0 && paginaActual < totalPaginas) {
                    dtNuevo.page(paginaActual).draw(false);
                }
            } catch (_) {}
        }
    }

    // ──────────────────────────
    // Petición principal
    // ─────────────────────────

    async function ejecutarRefresco() {
        const ctx = obtenerContextoTabla();
        if (!ctx) {
            detener();
            return;
        }
        if (peticionEnCurso || hayAccionEnCurso()) return;

        peticionEnCurso = true;
        const controller = new AbortController();
        const timeoutId = setTimeout(
            () => controller.abort(),
            CONFIG.TIMEOUT_FETCH_MS,
        );

        try {
            const res = await fetch(
                "/api/refresh-table?tipo=" + encodeURIComponent(ctx.tipoTabla),
                {
                    signal: controller.signal,
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                },
            );
            clearTimeout(timeoutId);

            if (res.status === 401) {
                console.warn(
                    "[AutoRefresco] Sesión expirada, redirigiendo al login.",
                );
                detener();
                window.location.href = "/login";
                return;
            }

            if (res.status === 403) {
                console.warn(
                    "[AutoRefresco] Acceso denegado, se detiene el ciclo.",
                );
                detener();
                return;
            }

            if (!res.ok)
                throw new Error("HTTP " + res.status + " " + res.statusText);

            const ct = res.headers.get("content-type") || "";
            if (!ct.includes("application/json"))
                throw new Error("Respuesta no es JSON: " + ct);

            const data = await res.json();
            fallosConsec = 0;

            //---tabla
            actualizarTabla(ctx, data.html);

            //---contadores
            if (data.contadores) {
                actualizarElemento(
                    "contador-abiertos",
                    data.contadores.abiertos,
                );
                actualizarElemento("contador-proceso", data.contadores.proceso);
                actualizarElemento(
                    "contador-resueltos",
                    data.contadores.resueltos,
                );
            }
            //---asignados
            if (data.contadorAsignados !== undefined) {
                actualizarElemento(
                    "contador-asignados",
                    data.contadorAsignados,
                );
            }

            //---Gráfico
            if (data.grafico)
                actualizarElemento("barras-rendimiento", data.grafico, true);

            //---Métricas
            if (data.cargaTrabajo != null)
                actualizarElemento("metric-carga-trabajo", data.cargaTrabajo);
            if (data.resueltos24h != null)
                actualizarElemento("metric-resueltos-24h", data.resueltos24h);
            if (data.tasaCierre != null)
                actualizarElemento("metric-tasa-cierre", data.tasaCierre + "%");
        } catch (err) {
            clearTimeout(timeoutId);
            if (err.name === "AbortError") {
                console.warn(
                    "[AutoRefresco] Timeout tras " +
                        CONFIG.TIMEOUT_FETCH_MS / 1000 +
                        "s",
                );
            } else {
                console.error("[AutoRefresco] Error:", err.message);
            }
            fallosConsec++;
            if (fallosConsec >= CONFIG.MAX_FALLOS) {
                console.error(
                    "[AutoRefresco] Detenido tras " +
                        CONFIG.MAX_FALLOS +
                        " fallos consecutivos.",
                );
                detener();
            }
        } finally {
            peticionEnCurso = false;
        }
    }

    // ──────────────────────
    // Control del ciclo
    // ──────────────────────

    function programarSiguiente() {
        if (detenido) return;
        timerId = setTimeout(async () => {
            await ejecutarRefresco();
            programarSiguiente();
        }, calcularIntervalo());
    }

    function iniciar() {
        if (!document.getElementById("tablaBody")) return;
        detenido = false;
        programarSiguiente();
    }

    function detener() {
        detenido = true;
        clearTimeout(timerId);
        timerId = null;
    }

    return { iniciar, detener, ejecutarAhora: ejecutarRefresco };
})();

document.addEventListener("DOMContentLoaded", () => {
    AutoRefresco.iniciar();
});

window.addEventListener("beforeunload", () => AutoRefresco.detener());

//---llamada
window.autoRefrescoUniversal = () => AutoRefresco.ejecutarAhora();
