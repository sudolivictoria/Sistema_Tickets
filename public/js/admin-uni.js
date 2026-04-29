//----desplegable de canales directos
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggle-canales");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            const list = document.getElementById("canales-list");
            const icon = this.querySelector(".material-symbols-outlined");

            if (list.style.display === "none" || list.style.display === "") {
                list.style.display = "block";
                icon.textContent = "expand_less";
            } else {
                list.style.display = "none";
                icon.textContent = "expand_more";
            }
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
        btn.classList.remove("bg-slate-100", "text-slate-500");
        btn.classList.add("bg-secondary", "text-white", "shadow-md");

        //--ejecutar filtrado
        ejecutarFiltros(estado);
    };

    function ejecutarFiltros(estadoSeleccionado) {
        const estadoFiltro = estadoSeleccionado.trim().toLowerCase();

        document.querySelectorAll(".ticket-fila").forEach((fila) => {
            const estadoId = fila.dataset.estadoId?.trim().toLowerCase() ?? "";

            //---toggle de visibilidad
            const ocultar =
                estadoFiltro !== "todos" && estadoId !== estadoFiltro;
            fila.classList.toggle("hidden", ocultar);
        });
    }
});
