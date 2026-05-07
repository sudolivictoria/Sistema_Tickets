let table;

$(document).ready(function () {
    //---inicializacion datatable
    table = $("#userTable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles en la tabla",
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

    //--toggle password visibility (Usando delegación de eventos)
    $(document).on("click", ".toggle-password", function () {
        const btn = $(this);
        const input = btn.siblings("input");
        const eyeOpen = btn.find(".eye-open");
        const eyeClosed = btn.find(".eye-closed");

        const isPassword = input.attr("type") === "password";
        input.attr("type", isPassword ? "text" : "password");

        eyeOpen.toggleClass("hidden", isPassword);
        eyeClosed.toggleClass("hidden", !isPassword);
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
        .draw();
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
