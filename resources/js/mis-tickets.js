var table;

/**
 * Inicializa DataTables
 * @param {string} selectorId
 */
window.inicializarTablaTickets = function (selectorId) {
    const tableElement = $(selectorId);
    if (!tableElement.length) return;

    if ($.fn.DataTable.isDataTable(selectorId)) {
        $(selectorId).DataTable().destroy();
    }

    $.fn.dataTable.ext.pager.numbers_length = 1;
    table = tableElement.DataTable({
        stateSave: false,
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: `
                     <div class="flex flex-col items-center h-[300px] justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">folder_off</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No hay datos disponibles</p>
                    </div>`,
            emptyTable: `
                    <div class="flex flex-col items-center h-[300px] justify-center py-10">
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
        order: [[0, "desc"]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 w-full"<"hidden md:block text-sm text-slate-500 w-full md:w-1/2 text-left"i><"w-full md:w-1/2 flex justify-center md:justify-end"p>>',
    });

    //---buscador
    $("#inputBusqueda")
        .off("keyup")
        .on("keyup", function () {
            table.search(this.value).draw(false);
        });
};

// =====================================================================
//                         DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");

            window.verDetalle(asunto, descripcion, tipo);
        });

    //------------------AUTO REFRESCO-----------------
    const selectorTabla = "#tablaMisTickets";
    if ($(selectorTabla).length) {
        window.inicializarTablaTickets(selectorTabla);
    }
});

/****************** FILTROS ******************/
window.filtrarEstado = function (estado, btn) {
    //--actualizar estilos de botones
    $(".filtro-btn")
        .removeClass("bg-secondary text-white shadow-md")
        .addClass("bg-slate-100 text-slate-500");
    $(btn)
        .removeClass("bg-slate-100 text-slate-500")
        .addClass("bg-secondary text-white shadow-md");

    setTimeout(() => {
        let valorBusqueda = "";
        if (estado !== "todos") {
            const estados = String(estado)
                .split(",")
                .map((e) => e.trim());

            valorBusqueda = `(${estados.join("|")})`;
        }

        //---filtros de estado
        table.column(2).search(valorBusqueda, true, false, true).draw();
    }, 10);
};

String.prototype.stripHtml = function () {
    return this.replace(/<[^>]*>/g, "");
};

/**
 * Gestión de Modal de detalles
 */
window.verDetalle = function (asunto, descripcion, tipoSolicitud) {
    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");

    if (modal && titulo && desc && tipo) {
        titulo.innerText = asunto;
        desc.innerText = descripcion;
        tipo.innerText = tipoSolicitud;
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
