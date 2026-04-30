function autoRefresco() {
    //---no refrescar si hay modal abiertos
    const modal = document.getElementById("modalTicket");
    if (modal && !modal.classList.contains("hidden")) return;

    //--refresca el cache
    fetch(window.location.href)
        .then((response) => response.text())
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const nuevaTabla = doc.getElementById("tablaBody");
            const tablaActual = document.getElementById("tablaBody");

            if (nuevaTabla && tablaActual) {
                //----actualizar contenido
                tablaActual.innerHTML = nuevaTabla.innerHTML;
            }

            actualizarContadores(doc);

            const nuevoGrafico = doc.getElementById("contenedor-grafico");
            const graficoActual = document.getElementById("contenedor-grafico");
            if (nuevoGrafico && graficoActual) {
                graficoActual.innerHTML = nuevoGrafico.innerHTML;
            }
            //---mantener filtro que el usuario tiene seleccionado
            const botonActivo = document.querySelector(
                ".filtro-btn.bg-secondary",
            );
            if (botonActivo && typeof ejecutarFiltros === "function") {
                const onclickAttr = botonActivo.getAttribute("onclick");
                if (onclickAttr) {
                    const estadoMatch = onclickAttr.match(/'([^']+)'/);
                    const estadoId = estadoMatch ? estadoMatch[1] : "todos";
                    ejecutarFiltros(estadoId);
                }
            }
            console.log("Tabla sincronizada automáticamente");
        })
        .catch((err) => console.warn("Error en auto-refresco:", err));
}

/**
 * Función para actualizar los números de las tarjetas superiores
 */
function actualizarContadores(nuevoDoc) {
    const ids = ["cont-abiertos", "cont-proceso", "cont-resueltos"];

    ids.forEach((id) => {
        const nuevoValor = nuevoDoc.getElementById(id);
        const actualValor = document.getElementById(id);

        if (nuevoValor && actualValor) {
            actualValor.innerText = nuevoValor.innerText;
        }
    });
}

//----ejecutar cada 10 s
if (document.getElementById("tablaBody")) {
    setInterval(autoRefresco, 10000);
}
