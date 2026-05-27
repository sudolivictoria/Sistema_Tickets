function ejecutarRefrescoInteligente() {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;

    //--verificar si hay una accion en curso
    const modalAbierto = document.querySelector(
        ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden), .swal2-container:not(.hidden)",
    );
    const buscador =
        document.getElementById("inputBusqueda") ||
        document.querySelector('input[type="search"]');
    const buscadorEnFoco = buscador === document.activeElement;

    if (modalAbierto || buscadorEnFoco) return; //--pausar refresco

    //--identificar tipo de tabla
    const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";
    const tablaElement = tablaBody.closest("table");

    //--hacer peticion a la api
    fetch(`/api/refresh-table?tipo=${tipoTabla}`)
        .then((res) => res.json())
        .then((data) => {
            if (tablaElement && $.fn.DataTable.isDataTable(tablaElement)) {
                const dt = $(tablaElement).DataTable();
                dt.clear();
                if (data.html && data.html.trim() !== "") {
                    dt.rows.add($(data.html).filter('tr'));
                }

                //---no redibujar toda la tabla, solo actualizar datos
                dt.draw(false);
            } else {
                tablaBody.innerHTML = data.html;
            }

            //--actualizar contadores y métricas
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

            if (data.contadorAsignados !== undefined) {
                actualizarElemento(
                    "contador-asignados",
                    data.contadorAsignados,
                );
            }

            if (data.grafico) {
                actualizarElemento("barras-rendimiento", data.grafico, true);
            }

            if (data.cargaTrabajo !== undefined)
                actualizarElemento("metric-carga-trabajo", data.cargaTrabajo);
            if (data.resueltos24h !== undefined)
                actualizarElemento("metric-resueltos-24h", data.resueltos24h);
            if (data.tasaCierre !== undefined)
                actualizarElemento("metric-tasa-cierre", data.tasaCierre);
        })
        .catch((err) => console.error("Error en auto-refresco:", err));
}

//--funcion para actualizar texto o html
function actualizarElemento(id, valor, esHTML = false) {
    const el = document.getElementById(id);
    if (el) {
        if (esHTML) el.innerHTML = valor;
        else el.textContent = valor;
    }
}

// ===================================
// INICIALIZACIÓN DE LA ACTUALIZACION
// ==================================
function iniciarAutoRefresco() {
    if (document.getElementById("tablaBody")) {
        ejecutarRefrescoInteligente();
        setTimeout(iniciarAutoRefresco, 30000); 
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("tablaBody")) {
        setTimeout(iniciarAutoRefresco, 30000);
    }
});

window.autoRefrescoUniversal = function () {
    ejecutarRefrescoInteligente();
};
