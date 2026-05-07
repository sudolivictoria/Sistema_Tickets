function autoRefrescoCliente() {
    //----no refrescar si el modal detalle esta abierto
    const modal = document.getElementById("modalTicket");
    if (modal && !modal.classList.contains("hidden")) return;

    fetch(window.location.href)
        .then((response) => response.text())
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            //---actualizar la tabla
            const nuevaTabla = doc.getElementById("tablaBody");
            const tablaActual = document.getElementById("tablaBody");
            if (nuevaTabla && tablaActual) {
                tablaActual.innerHTML = nuevaTabla.innerHTML;
            }
            actualizarContadores(doc);
            console.log("Dashboard de usuario actualizado");
        })
        .catch((err) => console.warn("Error refrescando dashboard:", err));
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


//----refresca cada 10 segundos
if (document.getElementById("tablaBody")) {
    setInterval(autoRefrescoCliente, 10000);
}
