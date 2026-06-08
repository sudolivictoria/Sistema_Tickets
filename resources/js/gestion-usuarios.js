var table;

$(document).ready(function () {
    //---inicializacion datatable
    $.fn.dataTable.ext.pager.numbers_length = 1;
    table = $("#userTable").DataTable({
        language: {
            processing: "Procesando...",
            zeroRecords: `
                    <div class="flex flex-col items-center h-[300px] justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No se encontraron resultados</p>
                    </div>`,
            emptyTable: `
                    <div class="flex flex-col items-center h-[300px] justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">folder_off</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No hay datos disponibles</p>
                    </div>`,
            info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
            infoFiltered: "(filtrado de un total de _MAX_ registros)",
            infoEmpty: "Mostrando 0 registros",
            paginate: {
                next: '<span class="material-symbols-outlined text-[20px] leading-none">chevron_right</span>',
                previous:
                    '<span class="material-symbols-outlined text-[20px] leading-none">chevron_left</span>',
            },
        },
        responsive: true,
        autoWidth: false,
        pageLength: 5,
        order: [[0, "asc"]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
        columnDefs: [
            {
                targets: 6,
                render: (data, type) =>
                    type === "filter" || type === "sort"
                        ? data.includes("Activo")
                            ? "Activo"
                            : "Inactivo"
                        : data,
            },
        ],
    });

    //--buscador personalizado
    $("#inputBusqueda").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $wrapper = $("#userTable").closest(".dataTables_wrapper");
    $wrapper.addClass("relative w-full");
    $("#userTable")
        .addClass("w-full")
        .wrap('<div class="w-full overflow-x-auto min-h-[400px]"></div>');

    //--toggle password visibility (Usando delegación de eventos)
    $(document).on("click", ".toggle-password", function () {
        const btn = $(this);
        const input = btn.siblings("input");
        const eyeOpen = btn.find(".eye-open");
        const eyeClosed = btn.find(".eye-closed");

        const esPassword = input.attr("type") === "password";

        if (esPassword) {
            input.attr("type", "text");
            eyeOpen.addClass("hidden");
            eyeClosed.removeClass("hidden");
        } else {
            input.attr("type", "password");
            eyeOpen.removeClass("hidden");
            eyeClosed.addClass("hidden");
        }
    });
});

//---filtrado por estado
window.filtrarEstado = function (estado, btn) {
    $(".filtro-btn")
        .removeClass("bg-secondary text-white shadow-md")
        .addClass("bg-slate-100 text-slate-500");
    $(btn)
        .removeClass("bg-slate-100 text-slate-500")
        .addClass("bg-secondary text-white shadow-md");

    table
        .column(6)
        .search(estado === "" ? "" : `^${estado}$`, true, false)
        .draw(false);
};

//---open/close modals
window.abrirModal = function (tipo, data = null) {
    if (tipo === "agregar") {
        $("#formAgregar")[0]?.reset();
        $("#modalAgregar").removeClass("hidden");
    } else if (tipo === "editar" && data) {
        $("#modalEditar").removeClass("hidden");
        $("#formEditar").attr("action", `/admin/usuarios/${data.id}`);

        //---llenado de campos
        $("#edit_nombre").val(data.name);
        $("#edit_email").val(data.email);
        $("#edit_cargo").val(data.cargo);
        $("#edit_rol").val(data.rol_id);
        $("#edit_unidad").val(data.unidad_id);
        $("#edit_telefono").val(data.telefono);
    }
    $("body").addClass("overflow-hidden");
};

window.cerrarModal = function (id) {
    $("#" + id).addClass("hidden");
    $("body").removeClass("overflow-hidden");
};
