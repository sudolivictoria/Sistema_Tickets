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
            console.log("Dashboard de cliente actualizado");
        })
        .catch((err) => console.warn("Error refrescando dashboard:", err));
}

function actualizarContadores(nuevoDoc) {
    //----actualiza los contadores
    const contadoresNuevos = nuevoDoc.querySelectorAll(
        ".font-black.text-primary, .font-black.text-2xl",
    );
    const contadoresActuales = document.querySelectorAll(
        ".font-black.text-primary, .font-black.text-2xl",
    );

    contadoresActuales.forEach((cont, index) => {
        if (contadoresNuevos[index]) {
            cont.innerText = contadoresNuevos[index].innerText;
        }
    });
}

//----refresca cada 10 segundos
if (document.getElementById("tablaBody")) {
    setInterval(autoRefrescoCliente, 10000);
}
