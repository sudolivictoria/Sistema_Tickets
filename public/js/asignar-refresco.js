(function() {
    let estaRefrescando = false;

    function autoRefrescoAsignar() {
        const modal = document.getElementById("modalTicket");
        const modalAbierto = modal && !modal.classList.contains("hidden");
        const escribiendo = document.activeElement.id === "inputBusqueda";
        
        //---evitar el refresco si se esta realizando una actividad
        if (!window.tablaAsignar || modalAbierto || escribiendo || estaRefrescando) return;

        estaRefrescando = true;

        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const nuevoBody = doc.querySelector("#tablaAsignarTickets tbody");
                const actualBody = document.querySelector("#tablaAsignarTickets tbody");

                if (nuevoBody && actualBody) {
                    //----solo actualizar si se esta realizando un cambio
                    if (actualBody.innerHTML !== nuevoBody.innerHTML) {
                        actualBody.innerHTML = nuevoBody.innerHTML;
                        
                        //---sincronizar script de datatable
                        window.tablaAsignar.rows().invalidate().draw(false);
                        console.log("Tabla de asignación sincronizada");
                    }
                }
            })
            .catch(err => console.warn("Error en refresco admin:", err))
            .finally(() => {
                estaRefrescando = false;
            });
    }

    setInterval(autoRefrescoAsignar, 30000);
})();