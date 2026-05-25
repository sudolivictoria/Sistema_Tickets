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

    //----api para obtener el html
    fetch(`/api/refresh-table?tipo=${tipoTabla}`)
        .then((response) => response.json())
        .then((data) => {
            const tablaElement = tablaBody.closest("table");

            if ($.fn.DataTable.isDataTable(tablaElement)) {
                //=== SOLUCIÓN PROFESIONAL PARA DATATABLES ===
                const selectorId = "#" + tablaElement.id;
                $(selectorId).DataTable().destroy();
                tablaBody.innerHTML = data.html;
                if (typeof window.inicializarTablaTickets === "function") {
                    window.inicializarTablaTickets(selectorId);
                }
            } else {
                tablaBody.innerHTML = data.html;
            }

            //--actualizar contadores si existen en los tickets asignados
            if (data.contadorAsignados !== undefined) {
                const elContador = document.getElementById("contador-asignados");
                if (elContador) {
                    elContador.innerText = data.contadorAsignados;
                }
            }

            //---actualizar grafico si viene en la respuesta
            if (data.grafico) {
                const contenedorGrafico = document.getElementById("barras-rendimiento");
                if (contenedorGrafico) {
                    contenedorGrafico.innerHTML = data.grafico;
                }
            }

            //---re-ejecutar el filtro activo para mantener la tabla filtrada en vistas estáticas
            const botonActivo = document.querySelector(".filtro-btn.bg-secondary");

            if (botonActivo && typeof ejecutarFiltros === "function") {
                const onclickAttr = botonActivo.getAttribute("onclick");
                if (onclickAttr) {
                    const estadoId = onclickAttr.match(/'([^']+)'/)?.[1] || "todos";
                    //---ejecuta los filtros visuales de CSS
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