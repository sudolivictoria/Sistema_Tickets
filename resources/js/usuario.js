//----variable global para almacenar la instancia de la tabla
var table;

/**
 * Inicializa DataTables de forma avanzada con estilos Tailwind
 * @param {string} selectorId
 */
window.inicializarTablaTickets = function (selectorId) {
    const tableElement = $(selectorId);
    if (!tableElement.length) return;

    if ($.fn.DataTable.isDataTable(selectorId)) {
        $(selectorId).DataTable().destroy();
    }
    table = tableElement.DataTable({
        paging: false,
        searching: false,
        info: false,
        responsive: true,
        autoWidth: false,
        dom: "rt",
        language: {
            emptyTable: `
                <div class="flex flex-col items-center justify-center bg-slate-50/40 rounded-2xl border-2 border-dashed p-5 border-slate-100 my-2 mx-2">
                    <span class="material-symbols-outlined text-slate-300 text-4xl mb-2 select-none">folder_off</span>
                    <h5 class="text-xs font-black uppercase text-slate-400 tracking-widest">Bandeja Vacía</h5>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">No tiene tickets por el momento.</p>
                </div>
            `,
        },
    });
};

//----desplegable de canales directos
document.addEventListener("DOMContentLoaded", function () {
    //--canales directos
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
});

// =====================================================================
//                         DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    window.inicializarTablaTickets("#tablaTicketsUsuario");

    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");

            window.verDetalle(asunto, descripcion, tipo);
        });
});

//---funciones para ver detalles de los tickets
window.verDetalle = function (asunto, descripcion, solicitud) {
    const modal = document.getElementById("modalTicket");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescripcion = document.getElementById("modalDescripcion");
    const modalTipoSolicitud = document.getElementById("modalTipoSolicitud");

    if (modal && modalTitulo && modalDescripcion && modalTipoSolicitud) {
        modalTitulo.innerText = asunto;
        modalDescripcion.innerText = descripcion;
        modalTipoSolicitud.innerText = solicitud;
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
