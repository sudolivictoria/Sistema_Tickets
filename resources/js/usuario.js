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
        stateSave: false,
        order: [[0, "desc"]],
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

//----desplegable de canales directos--------------------------------->
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
});

// =====================================================================
//                         DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    window.inicializarTablaTickets("#tablaTicketsUsuario");

    //------------------TICKET------------------------------------------>
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const $btn = $(this);

            window.verDetalle({
                idTicket: $btn.data("id"),
                asunto: $btn.data("asunto"),
                descripcion: $btn.data("descripcion"),
                tipoNombre: $btn.data("tipo"),
                drive: $btn.data("drive"),
                estadoNombre: $btn.data("estado")
            });
        });
});