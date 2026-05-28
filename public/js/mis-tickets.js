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

    table = tableElement.DataTable({
        scrollX: true,
        scrollCollapse: true,
        language: {
            processing: "Procesando...",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles",
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
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
    });

    //---buscador
    $("#inputBusqueda")
        .off("keyup")
        .on("keyup", function () {
            table.search(this.value).draw(false);
        });

    $(document).on("click", ".btn-ver-detalle", function () {
        const asunto = $(this).data("asunto");
        const descripcion = $(this).data("descripcion");
        const tipo = $(this).data("tipo");
        verDetalle(asunto, descripcion, tipo);
    });
    
};

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

    let valorBusqueda = "";
    if (estado !== "todos") {
        const estados = String(estado)
            .split(",")
            .map((e) => e.trim());

        valorBusqueda = `(${estados.join("|")})`;
    }
    //---filtros de estado
    table.column(2).search(valorBusqueda, true, false, true).draw();
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

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector("#tablaMisTickets")) {
        window.inicializarTablaTickets("#tablaMisTickets");
    }
});
