var table;

//-------------------------------------------------------------------------------------------------------
window.inicializarTablaTickets = function (
    selectorId,
    columnaOrden = 0,
    sentido = "desc",
) {
    const tableElement = $(selectorId);
    if (!tableElement.length) return;
    //---destruir instancia previa si existe para evitar conflictos
    if ($.fn.DataTable.isDataTable(selectorId)) {
        $(selectorId).DataTable().destroy();
    }
    //--configuración de idioma y opciones de DataTables
    $.fn.dataTable.ext.pager.numbers_length = 1;
    table = tableElement.DataTable({
        stateSave: false,
        language: {
            processing: "Procesando...",
            lengthMenu: "Mostrar _MENU_ registros",
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
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                next: '<span class="material-symbols-outlined text-[20px] leading-none">chevron_right</span>',
                previous:
                    '<span class="material-symbols-outlined text-[20px] leading-none">chevron_left</span>',
            },
        },
        responsive: false,
        autoWidth: false,
        pageLength: 5,
        order: [[columnaOrden, sentido]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
    });
    //---buscador
    $("#inputBusqueda")
        .off("keyup")
        .on("keyup", function () {
            table.search(this.value).draw(false);
        });
    //---ajuste tamaño de tabla
    const $wrapper = $(tableElement).closest(".dataTables_wrapper");
    $wrapper.addClass("relative w-full");
    //--evita duplicar wrappers si la función se vuelve a invocar
    if (!$(tableElement).parent().hasClass("overflow-x-auto")) {
        $(tableElement)
            .addClass("w-full")
            .wrap('<div class="w-full overflow-x-auto min-h-[400px]"></div>');
    }
};

// =====================================================================
//                       DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    //--------------------perfil ver detalle-------------------------
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const $btn = $(this);

            //-----ignora fecha de apertura
            window.verDetalle({
                idTicket: $btn.data("id"),
                asunto: $btn.data("asunto"),
                descripcion: $btn.data("descripcion"),
                tipoNombre: $btn.data("tipo"),
                drive: $btn.data("drive"),
                estadoNombre: $btn.data("estado"),
                datosSLA: {
                    estadoNombre: $btn.data("estado"),
                    fechaLimite: $btn.data("fecha-limite"),
                    tiempoRespuesta: $btn.data("tiempo-respuesta"),
                },
            });
        });

    //---------------------perfil usuario--------------------------
    $(document)
        .off("click", ".btn-ver-usuario")
        .on("click", ".btn-ver-usuario", function () {
            const $btn = $(this);
            const nombre = $btn.data("nombre");
            const email = $btn.data("email");
            const unidad = $btn.data("unidad");
            const cargo = $btn.data("cargo");
            const telefono = $btn.data("telefono");

            window.verUsuario(nombre, email, unidad, cargo, telefono);
        });

    //------------------AUTO REFRESCO-----------------
    const selectorTabla = "#tablaAsignarTickets";
    if ($(selectorTabla).length) {
        window.inicializarTablaTickets(selectorTabla);
    }
});

//-----------------ASIGNAR TÉCNICO VÍA AJAX----------------->
$(document)
    .off("change", ".select-tecnico-ajax")
    .on("change", ".select-tecnico-ajax", function () {
        const $select = $(this);
        const ticketId = $select.data("id");
        const tecnicoId = $select.val();
        const url = $select.data("url") || `/admin/tickets/${ticketId}/tecnico`;
        $select.prop("disabled", true).addClass("opacity-50");
        const $fila = $select.closest("tr");
        $.ajax({
            url: url,
            method: "PATCH",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: { tecnico_id: tecnicoId },
        })
            .done(function (response) {
                if (typeof window.mostrarFlashMessages === "function") {
                    window.mostrarFlashMessages({ success: response.message });
                }
                if (typeof table !== "undefined" && table) {
                    table.row($fila).remove().draw(false);
                } else {
                    $fila.fadeOut(300, function () {
                        $(this).remove();
                    });
                }
            })
            .fail(function (xhr) {
                const errorMsg =
                    xhr.responseJSON?.message ||
                    "Ocurrió un error al intentar asignar el técnico.";
                window.mostrarFlashMessages({ error: errorMsg });
            })
            .always(function () {
                $select.prop("disabled", false).removeClass("opacity-50");
            });
    });

//------------------ACTUALIZAR PRIORIDAD VÍA AJAX----------------->
$(document)
    .off("change", ".select-prioridad-ajax")
    .on("change", ".select-prioridad-ajax", function () {
        const $select = $(this);
        const ticketId = $select.data("id");
        const prioridadId = $select.val();
        const url =
            $select.data("url") || `/admin/tickets/${ticketId}/prioridad`;
        $select.prop("disabled", true).addClass("opacity-50");
        const $td = $select.closest("td");
        const textoSeleccionado = $select.find("option:selected").text().trim();
        $.ajax({
            url: url,
            method: "PATCH",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: { prioridad_id: prioridadId },
        })
            .done(function (response) {
                if (typeof window.mostrarFlashMessages === "function") {
                    window.mostrarFlashMessages({ success: response.message });
                }
                $td.attr("data-search", textoSeleccionado);
                if (typeof table !== "undefined" && table) {
                    table.cell($td).invalidate().draw(false);
                }
            })
            .fail(function (xhr) {
                const errorMsg =
                    xhr.responseJSON?.message ||
                    "Ocurrió un error al actualizar la prioridad.";
                window.mostrarFlashMessages({ error: errorMsg });
            })
            .always(function () {
                $select.prop("disabled", false).removeClass("opacity-50");
            });
    });
