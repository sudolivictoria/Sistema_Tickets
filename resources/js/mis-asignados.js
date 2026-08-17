//------variable global------->
var table;

//------------INICIALIZACION---------------->
window.inicializarTablaTickets = function (
    selectorId,
    columnaOrden = 0,
    sentido = "desc"
) {
    const tableElement = $(selectorId);
    if (!tableElement.length) return;

    if ($.fn.DataTable.isDataTable(selectorId)) {
        $(selectorId).DataTable().destroy();
    }
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
                previous: '<span class="material-symbols-outlined text-[20px] leading-none">chevron_left</span>',
            },
        },
        responsive: false,
        autoWidth: false,
        pageLength: 5,
        order: [[columnaOrden, sentido]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
    });
    //--buscador------------->
    $("#inputBusqueda")
        .off("keyup")
        .on("keyup", function () {
            table.search(this.value).draw(false);
        });
    //---ajuste tamaño de tabla (Evita el re-wrapping infinito)----------->
    if (!tableElement.parent().hasClass("overflow-x-auto")) {
        const $wrapper = tableElement.closest(".dataTables_wrapper");
        $wrapper.addClass("relative w-full");
        tableElement
            .addClass("w-full")
            .wrap('<div class="w-full overflow-x-auto min-h-[400px]"></div>');
    }
};

// =====================================================================
//                         DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const $btn = $(this);
            
            window.verDetalle({
                idTicket: $btn.data("id"),
                asunto: $btn.data("asunto"),
                descripcion: $btn.data("descripcion"),
                tipoNombre: $btn.data("tipo"),
                fechaApertura: $btn.data("fecha"), //---------Incluye fecha
                drive: $btn.data("drive"),
                estadoNombre: $btn.data("estado"),
                datosSLA: {
                    estadoNombre: $btn.data("estado"),
                    fechaLimite: $btn.data("fecha-limite"),
                    tiempoRespuesta: $btn.data("tiempo-respuesta"),
                }
            });
        });

    //-----------------PERFIL DE USUARIO------------>
    $(document)
        .off("click", ".btn-ver-usuario")
        .on("click", ".btn-ver-usuario", function () {
            const $btn = $(this);
            window.verUsuario(
                $btn.data("nombre"),
                $btn.data("email"),
                $btn.data("unidad"),
                $btn.data("cargo"),
                $btn.data("telefono")
            );
        });

    const selectorTabla = "#tablaMisAsignados";
    if ($(selectorTabla).length) {
        window.inicializarTablaTickets(selectorTabla);
    }
});

//------------------ACTUALIZAR PRIORIDAD VÍA AJAX---------------->
$(document)
    .off("change", ".select-prioridad-ajax")
    .on("change", ".select-prioridad-ajax", function () {
        const $select = $(this);
        const url = $select.data("url");
        const ticketId = $select.data("id");
        const prioridadId = $select.val();
        const $td = $select.closest("td");
        const textoSeleccionado = $select.find("option:selected").text().trim();
        $select.prop("disabled", true).addClass("opacity-50");

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
                if (response.fecha_vencimiento_sla) {
                    //---.data(), no .attr(): ticket-core.js lee este valor vía $btn.data("fecha-limite"),
                    //---que jQuery cachea en su primer acceso e ignora cambios al atributo del DOM después
                    $(`.btn-ver-detalle[data-id="${ticketId}"]`).data(
                        "fecha-limite",
                        response.fecha_vencimiento_sla
                    );
                }
                if (typeof table !== "undefined" && table) {
                    table.cell($td).invalidate().draw(false);
                }
            })
            .fail(function (xhr) {
                const errorMsg =
                    xhr.responseJSON?.message ||
                    "Ocurrió un error al actualizar la prioridad.";
                if (typeof window.mostrarFlashMessages === "function") {
                    window.mostrarFlashMessages({ error: errorMsg });
                }
            })
            .always(function () {
                $select.prop("disabled", false).removeClass("opacity-50");
            });
    });

//------------------REASIGNAR O DEVOLVER TÉCNICO VÍA AJAX----------------->
$(document)
    .off("change", ".select-tecnico-ajax")
    .on("change", ".select-tecnico-ajax", function () {
        const $select = $(this);
        const url = $select.data("url");
        const tecnicoId = $select.val();
        const $fila = $select.closest("tr");
        $select.prop("disabled", true).addClass("opacity-50");
        
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
                    "Ocurrió un error al reasignar el técnico.";
                if (typeof window.mostrarFlashMessages === "function") {
                    window.mostrarFlashMessages({ error: errorMsg });
                }
                $select.prop("disabled", false).removeClass("opacity-50");
            });
    });

//-------------------PROCESAMIENTO AJAX (CIERRES DE TICKET)------------------------------------>
function procesarAccionTicket(btn, config) {
    const $btn = $(btn);
    const url = $btn.data("url");
    const $fila = $btn.closest("tr");

    Swal.fire({
        title: config.tituloConfirmacion,
        text: "Puedes agregar un comentario (opcional):",
        input: "textarea",
        inputPlaceholder: "Escribe aquí la solución o motivo de cierre...",
        inputAttributes: {
            "aria-label": "Escribe tu comentario aquí",
            rows: "3",
            style: "box-sizing: border-box; margin: 0 auto; display: block;",
        },
        icon: "question",
        iconColor: "#84cc16",
        showCancelButton: true,
        confirmButtonColor: "#84cc16",
        confirmButtonText: config.textoBoton,
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#ef4444",
        showLoaderOnConfirm: true,
        customClass: {
            popup: "rounded-3xl p-6 shadow-2xl border border-gray-100",
            title: "text-xl font-bold text-[#04003B] pt-2",
            htmlContainer: "text-sm text-gray-500 my-3 font-medium",
            input: "!w-11/12 mx-auto rounded-2xl border-2 border-[#04003B] text-sm text-gray-700 p-3 bg-gray-50 focus:outline-none focus:ring-0 focus:border-[#04003B] resize-none",
            confirmButton:
                "px-6 py-2.5 rounded-xl font-semibold text-sm shadow-md transition-transform active:scale-95",
            cancelButton:
                "px-6 py-2.5 rounded-xl font-semibold text-sm shadow-sm transition-transform active:scale-95",
        },
        preConfirm: (comentarioTexto) => {
            return $.ajax({
                url: url,
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                data: {
                    comentario_cierre: comentarioTexto
                        ? comentarioTexto.trim()
                        : "",
                },
            })
                .done(function (response) {
                    return response;
                })
                .fail(function (xhr) {
                    const errorMsg =
                        xhr.responseJSON?.message ||
                        "Ocurrió un error al procesar la solicitud.";
                    Swal.showValidationMessage(errorMsg);
                });
        },
    }).then((result) => {
        if (result.isConfirmed) {
            const response = result.value;
            if (typeof window.mostrarFlashMessages === "function") {
                window.mostrarFlashMessages({
                    success: response.message || config.tituloExito,
                });
            }
            if (typeof table !== "undefined" && table) {
                table.row($fila).remove().draw(false);
            } else {
                $fila.fadeOut(300, function () {
                    $(this).remove();
                });
            }
        }
    });
}

// ============================================================================
//                                BOTONES 
// ============================================================================
window.confirmarResolver = function (btn) {
    procesarAccionTicket(btn, {
        tituloConfirmacion: "¿Marcar como Resuelto?",
        textoBoton: "Sí, resolver",
        tituloExito: "¡Ticket Resuelto!",
    });
};
window.confirmarEquivocado = function (btn) {
    procesarAccionTicket(btn, {
        tituloConfirmacion: "¿Marcar como Equivocado?",
        textoBoton: "Sí, marcar",
        tituloExito: "¡Ticket Marcado como Equivocado!",
    });
};
window.confirmarNoCorresponde = function (btn) {
    procesarAccionTicket(btn, {
        tituloConfirmacion: "¿Marcar No Corresponde?",
        textoBoton: "Sí, marcar",
        tituloExito: "¡Ticket Marcado como No Corresponde!",
    });
};