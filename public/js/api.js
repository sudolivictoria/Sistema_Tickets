// =======================================================
// REFRESCO PARA DASHBOARD, MIS TICKETS Y MIS ASIGNADOS
// =======================================================
function refrescoTablasEstandar() {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;

    //---evitar refresco si hay modals abiertos
    const modalAbierto = document.querySelector(
        ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden)",
    );

    //---capturar buscador
    const buscador =
        document.getElementById("inputBusqueda") ||
        document.querySelector('input[type="search"]');
    const buscadorEnFoco = buscador === document.activeElement;

    if (modalAbierto || buscadorEnFoco) return;

    const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";
    const tablaElement = tablaBody.closest("table");

    let textoAntes =
        tablaElement && $.fn.DataTable.isDataTable(tablaElement)
            ? $(tablaElement).DataTable().search()
            : "";

    //----respaldar el boton activo
    const botonActivo = document.querySelector(
        ".filtro-btn.bg-primary, .filtro-btn.bg-secondary, .filtro-btn.active",
    );

    fetch(`/api/refresh-table?tipo=${tipoTabla}`)
        .then((res) => res.json())
        .then((data) => {
            if (tablaElement && $.fn.DataTable.isDataTable(tablaElement)) {
                const id = "#" + tablaElement.id;

                $(id).DataTable().destroy();
                tablaBody.innerHTML = data.html;

                if (typeof window.inicializarTablaTickets === "function") {
                    window.inicializarTablaTickets(id);
                }

                //---mantener la pagina en el caso que refresca
                if (textoAntes) {
                    if (document.getElementById("inputBusqueda")) {
                        document.getElementById("inputBusqueda").value =
                            textoAntes;
                    }
                    $(id).DataTable().search(textoAntes).draw(false);
                } else {
                    $(id).DataTable().draw(false);
                }
            } else {
                tablaBody.innerHTML = data.html;
            }

            //--sincronizar contadores
            if (data.contadores) {
                if (document.getElementById("contador-abiertos"))
                    document.getElementById("contador-abiertos").textContent =
                        data.contadores.abiertos;
                if (document.getElementById("contador-proceso"))
                    document.getElementById("contador-proceso").textContent =
                        data.contadores.proceso;
                if (document.getElementById("contador-resueltos"))
                    document.getElementById("contador-resueltos").textContent =
                        data.contadores.resueltos;
            }
            //--sincronizar mis asignados
            if (
                data.contadorAsignados !== undefined &&
                document.getElementById("contador-asignados")
            ) {
                document.getElementById("contador-asignados").textContent =
                    data.contadorAsignados;
            }

            //---sincronizar el grafico
            if (data.grafico) {
                const contenedorGrafico =
                    document.getElementById("barras-rendimiento");
                if (contenedorGrafico) {
                    contenedorGrafico.innerHTML = data.grafico;
                }
            }

            //---reaplicar estilos
            if (botonActivo) {
                const onclickAttr = botonActivo.getAttribute("onclick");
                if (onclickAttr) {
                    const match = onclickAttr.match(/'([^']+)'/);
                    const estadoId = match ? match[1] : "todos";

                    if (typeof window.ejecutarFiltros === "function") {
                        window.ejecutarFiltros(estadoId);
                    } else if (typeof window.filtrarEstado === "function") {
                        window.filtrarEstado(estadoId, botonActivo);
                    }
                }
            }
        })
        .catch((err) => console.error("Error en refresco estándar:", err));
}

// ========================================================
// REFRESCO EXCLUSIVO PARA LA PÁGINA DE ASIGNAR TICKETS
// ========================================================
function refrescoAsignar() {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;

    const modalAbierto = document.querySelector(
        ".modal:not(.hidden), #modalUsuario:not(.hidden)",
    );
    const buscador = document.querySelector('input[type="search"]');
    if (modalAbierto || (buscador && buscador === document.activeElement))
        return;

    const tablaElement = tablaBody.closest("table");
    let textoAntes =
        tablaElement && $.fn.DataTable.isDataTable(tablaElement)
            ? $(tablaElement).DataTable().search()
            : "";

    fetch(`/api/refresh-table?tipo=asignar`)
        .then((res) => res.json())
        .then((data) => {
            if (tablaElement && $.fn.DataTable.isDataTable(tablaElement)) {
                const id = "#" + tablaElement.id;

                $(id).DataTable().destroy();
                tablaBody.innerHTML = data.html;

                if (typeof window.inicializarTablaTickets === "function") {
                    window.inicializarTablaTickets(id);
                }
                if (textoAntes) {
                    $(id).DataTable().search(textoAntes).draw(false);
                } else {
                    $(id).DataTable().draw(false);
                }
            } else {
                tablaBody.innerHTML = data.html;
            }
        })
        .catch((err) => console.error("Error en refresco Asignar:", err));
}

// =========================
// ORQUESTADOR CENTRAL
// =========================
function enrutadorRefresco() {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;

    const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";

    if (tipoTabla === "historial") {
        if (typeof window.refrescoHistorial === "function") {
            window.refrescoHistorial();
        }
    } else if (tipoTabla === "asignar") {
        refrescoAsignar();
    } else {
        refrescoTablasEstandar();
    }
}
function iniciarAutoRefresco() {
    if (document.getElementById("tablaBody")) {
        enrutadorRefresco();
        setTimeout(iniciarAutoRefresco, 30000);
    }
}
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("tablaBody")) {
        setTimeout(iniciarAutoRefresco, 30000);
    }
});
window.autoRefrescoUniversal = function () {
    if (typeof enrutadorRefresco === "function") {
        enrutadorRefresco();
    }
};
