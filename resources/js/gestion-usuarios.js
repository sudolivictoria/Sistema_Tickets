var table;
//---------------INICIALIZACION--------------------------------
function inicializarDataTable() {
    $.fn.dataTable.ext.pager.numbers_length = 1;
    table = $("#userTable").DataTable({
        language: {
            processing: "Procesando...",
            zeroRecords: `<div class="flex flex-col items-center h-[200px] justify-center"><span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span><p class="text-slate-400 font-bold uppercase text-[10px]">No se encontraron resultados</p></div>`,
            emptyTable: `<div class="flex flex-col items-center h-[200px] justify-center"><span class="material-symbols-outlined text-4xl text-slate-300 mb-2">folder_off</span><p class="text-slate-400 font-bold uppercase text-[10px]">No hay datos disponibles</p></div>`,
            info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
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
                        ? data.includes("Activo") && !data.includes("Inactivo")
                            ? "Activo"
                            : "Inactivo"
                        : data,
            },
        ],
    });
    //----------------------BUSQUEDA------------------------
    $("#inputBusqueda")
        .off("keyup")
        .on("keyup", function () {
            table.search(this.value).draw();
        });
}
$(document).ready(function () {
    inicializarDataTable();
    //-----TOGGLE para visibilidad de contraseña
    $(document).on("click", ".toggle-password", function () {
        const input = $(this).siblings("input");
        const icon = $(this).find("span");
        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.text("visibility_off");
        } else {
            input.attr("type", "password");
            icon.text("visibility");
        }
    });
    // ---ENVIAR FORMULARIOS Y TOGGLES CON AJAX Y ESTADO DE CARGA-------------------->
    $(document).on(
        "submit",
        "#formAgregar, #formEditar, form[action*='toggle']",
        function (e) {
            e.preventDefault();
            const $form = $(this);
            const $btnSubmit = $form.find("button[type='submit']");
            //----GUARDAR CONTENIDO EN EL BOTON
            const contenidoOriginal = $btnSubmit.html();
            //---------------------ESTADO DE CARGA--------------------------------
            $btnSubmit
                .prop("disabled", true)
                .addClass("opacity-75 cursor-not-allowed");
            $btnSubmit.html(`
            <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span></span>
            </div>
        `);
            $.ajax({
                url: $form.attr("action"),
                method: $form.attr("method") || "POST",
                data: $form.serialize(),
                success: function (responseHtml) {
                    const $html = $(responseHtml);
                    //--------REEMPLAZAR SOLO LA TABLE
                    const nuevoTablaHtml = $html
                        .find("#tabla-contenedor")
                        .html();
                    if (table) table.destroy();
                    $("#tabla-contenedor").html(nuevoTablaHtml);
                    inicializarDataTable();
                    //-------CLOSE MODALS
                    window.cerrarModal("modalAgregar");
                    window.cerrarModal("modalEditar");
                    //-----------ALERTAS FLASH DE APP.JS
                    const flashDataString = $html.find("#flash-data").html();
                    if (flashDataString) {
                        eval(flashDataString);
                        window.mostrarFlashMessages();
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errores = Object.values(
                            xhr.responseJSON.errors,
                        ).flat();
                        window.mostrarFlashMessages({
                            validationTitle: "Campos Inválidos",
                            validationErrors: errores,
                        });
                    } else {
                        window.mostrarFlashMessages({
                            errorTitle: "Error del Servidor",
                            error: "No se pudo procesar la solicitud en este momento.",
                        });
                    }
                },
                complete: function () {
                    //-------------RESTAURAR BOTON-----------------------------------
                    $btnSubmit
                        .prop("disabled", false)
                        .removeClass("opacity-75 cursor-not-allowed");
                    $btnSubmit.html(contenidoOriginal);
                },
            });
        },
    );
});
//------------------FILTRO POR ESTADO--------------------------
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
//--------------------MODALES--------------------------------
window.abrirModal = function (tipo, data = null) {
    if (tipo === "agregar") {
        $("#formAgregar")[0]?.reset();
        $("#modalAgregar").removeClass("hidden");
    } else if (tipo === "editar" && data) {
        $("#modalEditar").removeClass("hidden");
        $("#formEditar").attr("action", `/admin/usuarios/${data.id}`);
        $("#edit_nombre").val(data.name);
        $("#edit_email").val(data.email);
        $("#edit_cargo").val(data.cargo);
        $("#edit_rol").val(data.rol_id);
        $("#edit_unidad").val(data.unidad_id);
        $("#edit_telefono").val(data.telefono);
        $("#edit_password").val("");
    }
    $("body").addClass("overflow-hidden");
};
//----------------CLOSE
window.cerrarModal = function (id) {
    $("#" + id).addClass("hidden");
    $("body").removeClass("overflow-hidden");
};
//--Accesibilidad para cerrar modales
$(document).on("keydown", function (e) {
    if (e.key === "Escape") {
        window.cerrarModal("modalAgregar");
        window.cerrarModal("modalEditar");
    }
});
$("#modalAgregar, #modalEditar").on("click", function (e) {
    if (e.target === this) {
        window.cerrarModal($(this).attr("id"));
    }
});
