function autoRefrescoUniversal() {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;

    //----evitar el refresco automatico si el usuario esta interactutuando
    const modalAbierto = document.querySelector(
        ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden)",
    );
    const buscador = document.querySelector('input[type="search"]');
    const buscadorEnFoco = buscador === document.activeElement;

    if (modalAbierto || buscadorEnFoco) return;

    const tipoTabla = tablaBody.getAttribute("data-tipo") || "dashboard";

    //----api para obtener el html
    fetch(`/api/refresh-table?tipo=${tipoTabla}`)
        .then((response) => response.json())
        .then((data) => {
            const tablaElement = tablaBody.closest("table");

            if ($.fn.DataTable.isDataTable(tablaElement)) {
                //---si son datatables
                const dt = $(tablaElement).DataTable();
                const nuevasFilas = $(data.html).filter("tr");

                dt.clear();
                nuevasFilas.each(function () {
                    dt.row.add(this);
                });
                dt.draw(false);
            } else {
                //---tabla normal sin datatables
                tablaBody.innerHTML = data.html;
            }

            //--actualizar contadores si existen en la respuesta
            if (data.contadores) {
                const ids = ["cont-abiertos", "cont-proceso", "cont-resueltos"];
                ids.forEach((id) => {
                    const el = document.getElementById(id);
                    if (el)
                        el.innerText = data.contadores[id.replace("cont-", "")];
                });
            }

            //---actualizar contador de tickets asignados
            if (data.contadorAsignados !== undefined) {
                const elContador =
                    document.getElementById("contador-asignados");
                if (elContador) {
                    elContador.innerText = data.contadorAsignados;
                }
            }

            //---actualizar grafico si viene en la respuesta
            if (data.grafico) {
                const contenedorGrafico =
                    document.getElementById("barras-rendimiento");
                if (contenedorGrafico) {
                    contenedorGrafico.innerHTML = data.grafico;
                }
            }

            //---re-ejecutar el filtro activo para mantener la tabla filtrada
            const botonActivo = document.querySelector(
                ".filtro-btn.bg-secondary",
            );

            if (botonActivo && typeof ejecutarFiltros === "function") {
                const onclickAttr = botonActivo.getAttribute("onclick");
                if (onclickAttr) {
                    const estadoId =
                        onclickAttr.match(/'([^']+)'/)?.[1] || "todos";

                    //---ejecuta los filtros
                    ejecutarFiltros(estadoId);
                }
            }
        })
        .catch((err) => console.error("Error en refresco:", err));
}

//---ejecutar cada 30 segundos
if (document.getElementById("tablaBody")) {
    setInterval(autoRefrescoUniversal, 30000);
}
