function autoRefrescoUniversal() {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;

    //----evitar el refresco automatico si el usuario esta interactuando
    const modalAbierto = document.querySelector(
        ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden)",
    );
    const buscador = document.querySelector('input[type="search"]');
    const buscadorEnFoco = buscador === document.activeElement;

    if (modalAbierto || buscadorEnFoco) return;

    const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";


    fetch(`/api/refresh-table?tipo=${tipoTabla}`)
        .then((response) => response.json())
        .then((data) => {
            const tablaElement = tablaBody.closest("table");

            //----actualizar la tabla de datos
            if (tablaElement && $.fn.DataTable.isDataTable(tablaElement)) {
                const selectorId = "#" + tablaElement.id;

                //---destruir instancia de datatable
                $(selectorId).DataTable().destroy();

                //---inyectar html
                tablaBody.innerHTML = data.html;

                //--reinicializar tabla
                if (typeof window.inicializarTablaTickets === "function") {
                    window.inicializarTablaTickets(selectorId);
                }
            } else {
                tablaBody.innerHTML = data.html;
            }

            //---contadores generales
            if (data.contadores) {
                const elAbiertos = document.getElementById("contador-abiertos");
                const elProceso = document.getElementById("contador-proceso");
                const elResueltos =
                    document.getElementById("contador-resueltos");

                if (elAbiertos)
                    elAbiertos.textContent = data.contadores.abiertos;
                if (elProceso) elProceso.textContent = data.contadores.proceso;
                if (elResueltos)
                    elResueltos.textContent = data.contadores.resueltos;
            }

            //---actualizar contadores asignados
            if (data.contadorAsignados !== undefined) {
                const elContador =
                    document.getElementById("contador-asignados");
                if (elContador) {
                    elContador.textContent = data.contadorAsignados;
                }
            }

            //---actualizar nuevas metricas
            if (data.cargaTrabajo !== undefined) {
                const elCarga = document.getElementById("metric-carga-trabajo");
                if (elCarga) elCarga.textContent = data.cargaTrabajo;
            }

            if (data.resueltos24h !== undefined) {
                const elResueltos24 = document.getElementById(
                    "metric-resueltos-24h",
                );
                if (elResueltos24)
                    elResueltos24.textContent = data.resueltos24h;
            }

            if (data.tasaCierre !== undefined) {
                const elTasa = document.getElementById("metric-tasa-cierre");
                if (elTasa) elTasa.textContent = data.tasaCierre;
            }

            //-----actualizar grafico de rendimiento
            if (data.grafico) {
                const contenedorGrafico =
                    document.getElementById("barras-rendimiento");
                if (contenedorGrafico) {
                    contenedorGrafico.innerHTML = data.grafico;
                }
            }

            //-----re-ejecutar filtros
            const botonActivo = document.querySelector(
                ".filtro-btn.bg-secondary",
            );
            if (botonActivo && typeof ejecutarFiltros === "function") {
                const onclickAttr = botonActivo.getAttribute("onclick");
                if (onclickAttr) {
                    const estadoId =
                        onclickAttr.match(/'([^']+)'/)?.[1] || "todos";
                    ejecutarFiltros(estadoId);
                }
            }
        })
        .catch((err) => console.error("Error en refresco dinámico:", err));
}

function iniciarAutoRefresco() {
    if (document.getElementById("tablaBody")) {
        //--ejecutar funcion de refresco
        autoRefrescoUniversal();

        //---progrmar ciclo para 30 se segundos
        setTimeout(iniciarAutoRefresco, 30000);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("tablaBody")) {
        //--espera para iniciar el bucle automatico
        setTimeout(iniciarAutoRefresco, 30000);
    }
});
