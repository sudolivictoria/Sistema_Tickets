document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggle-canales");
    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            const list = document.getElementById("canales-list");
            const icon = this.querySelector(".material-symbols-outlined");
            const isHidden =
                list.style.display === "none" || list.style.display === "";

            list.style.display = isHidden ? "block" : "none";
            icon.textContent = isHidden ? "expand_less" : "expand_more";
        });
    }

    //--filtros por estado
    window.filtrarEstado = function (estado, btn) {
        //--resetear estilos
        document.querySelectorAll(".filtro-btn").forEach((b) => {
            b.classList.remove("bg-secondary", "text-white", "shadow-md");
            b.classList.add("bg-slate-100", "text-slate-500");
        });

        //--destacar botón seleccionado
        if (btn) {
            btn.classList.remove("bg-slate-100", "text-slate-500");
            btn.classList.add("bg-secondary", "text-white", "shadow-md");
        }

        //--ejecutar filtrado
        ejecutarFiltros(estado);
    };

    window.verDetalle = function (asunto, descripcion) {
        const modal = document.getElementById("modalTicket");
        const titulo = document.getElementById("modalTitulo");
        const desc = document.getElementById("modalDescripcion");

        if (modal && titulo && desc) {
            titulo.innerText = asunto;
            desc.innerText = descripcion;
            modal.classList.remove("hidden");
            document.body.style.overflow = "hidden";
        }
    };

    window.cerrarModal = function () {
        const modal = document.getElementById("modalTicket");
        if (modal) {
            modal.classList.add("hidden");
            document.body.style.overflow = "auto";
        }
    };
});

function ejecutarFiltros(estadoSeleccionado) {
    const estadoFiltro = String(estadoSeleccionado).trim().toLowerCase();

    document.querySelectorAll(".ticket-fila").forEach((fila) => {
        const estadoIdFila = String(fila.dataset.estadoId || "")
            .trim()
            .toLowerCase();
        const ocultar =
            estadoFiltro !== "todos" && estadoIdFila !== estadoFiltro;
        fila.classList.toggle("hidden", ocultar);
    });
}
