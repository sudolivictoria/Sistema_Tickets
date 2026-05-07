let table;

/**
 * Inicializa DataTables
 * @param {string} selectorId
 */
function inicializarTablaTickets(selectorId) {
    const tableElement = $(selectorId);
    if (!tableElement.length) return; //--verificación de existencia del elemento

    table = tableElement.DataTable({
        scrollX: true,       
        scrollCollapse: true,
        language: {
            processing: "Procesando...",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: `
                <div class="flex flex-col items-center justify-center py-10">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
                    <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No se encontraron resultados</p>
                </div>`,
            emptyTable: `
                <div class="flex flex-col items-center justify-center py-10">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">folder_off</span>
                    <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No hay datos disponibles</p>
                </div>`,
            info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
            infoFiltered: "(filtrado de un total de _MAX_ registros)",
            infoEmpty: "Mostrando 0 registros",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                next: '<span class="material-symbols-outlined text-[20px] leading-none">chevron_right</span>',
                previous:
                    '<span class="material-symbols-outlined text-[20px] leading-none">chevron_left</span>',
            },
        },
        responsive: true,
        autoWidth: false,
        pageLength: 5,
        order: [[5, "desc"]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
    });

    //---buscador
    $("#inputBusqueda").on("keyup", function () {
        table.search(this.value).draw();
    });
}

/**
 * Filtro por estado
 */
window.filtrarEstado = function (estado, btn) {
    //--actualizar estilos de botones
    $(".filtro-btn")
        .removeClass("bg-primary text-white shadow-md")
        .addClass("bg-slate-100 text-slate-500");
    $(btn)
        .removeClass("bg-slate-100 text-slate-500")
        .addClass("bg-primary text-white shadow-md");

    //---filtros de estado
    const valorBusqueda = estado === "todos" ? "" : `^${estado}$`;
    table.column(2).search(valorBusqueda, true, false, true).draw();
};

/**
 * Gestión de Modal de detalles
 */
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

mis - tickes.js;
