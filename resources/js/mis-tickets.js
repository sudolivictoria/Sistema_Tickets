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

    //---ajuste tamaño de tabla
    const $wrapper = $(tableElement).closest(".dataTables_wrapper");
    $wrapper.addClass("relative w-full");
    $(tableElement)
        .addClass("w-full")
        .wrap('<div class="w-full overflow-x-auto min-h-[400px]"></div>');
};

// =====================================================================
//                         DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const idTicket = $(this).data("id");
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");
            const drive = $(this).data("drive");

            window.verDetalle(idTicket, asunto, descripcion, tipo, drive);
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

    //---estado actual para mantener el filtro activo al refrescar
    window.filtroSseActual = estado;

    //----Reverb del filtro a la tabla
    if (typeof window.AutoRefresco !== "undefined") {
        window.AutoRefresco.forzarRefresco();
    }
};

/**
 * Gestión de Modal de detalles
 */
let ticketIdActual = null;
window.cargarComentariosDelTicket = function (idTicket, state = null) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");
    const $formularioComentario = $("#form-comentario-modal");
    const modal = document.getElementById("modalTicket");

    if (!idTicket) return;

    if (!document.getElementById("preloaderGlobalModal") && modal) {
        const preloaderHTML = `
            <div id="preloaderGlobalModal" class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center z-[100] transition-all duration-300 rounded-3xl">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-secondary rounded-full animate-spin mb-3"></div>
                <p class="text-slate-500 font-semibold text-xs tracking-wide uppercase">Cargando información del ticket...</p>
            </div>`;

        const contenedorInterno = modal.querySelector(".bg-white") || modal;
        if (contenedorInterno) {
            contenedorInterno.style.position = "relative";
            $(contenedorInterno).prepend(preloaderHTML);
        }
    } else {
        $("#preloaderGlobalModal").removeClass("hidden");
    }

    const stateNum = Number(state);
    if (stateNum === 3 || stateNum === 4 || stateNum === 5) {
        $formularioComentario.hide();
    } else {
        $formularioComentario.show();
    }

    $.get(`/tickets/${idTicket}/comentarios`, function (comentarios) {
        $lista.empty();

        if (!comentarios || comentarios.length === 0) {
            $seccionHistorico.hide();
            $("#preloaderGlobalModal").addClass("hidden");
            return;
        }
        $seccionHistorico.show();

        comentarios.forEach((com) => {
            const bg = com.es_privado
                ? "bg-green-50 border-green-100"
                : "bg-white border-slate-100";
            const tag = com.es_privado
                ? '<span class="text-green-700 font-bold">[Interno]</span> '
                : "";
            $lista.append(`
                <div class="p-2 rounded-xl border ${bg}">
                    <div class="flex justify-between font-bold text-green-950 mb-0.5">
                        <span>${tag}${com.user.name}</span>
                        <span class="text-[10px] text-slate-400 font-normal">${com.tiempo_legible}</span>
                    </div>
                    <p class="text-slate-600 font-medium">${com.contenido}</p>
                </div>
            `);
        });
        $lista.scrollTop($lista[0].scrollHeight);
        $("#preloaderGlobalModal").addClass("hidden");
    }).fail(function () {
        $("#preloaderGlobalModal").addClass("hidden");
    });
};

window.verDetalle = function (idTicket, asunto, descripcion, tipoSolicitud, drive) {
    ticketIdActual = idTicket;

    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");

    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");
    const imgPreview = document.getElementById("modalEvidenciaImg");

    if (drive && drive.trim() !== "" && drive !== "null") {
        const urlImagen = `/storage/${drive}`;
        if (imgPreview) {
            imgPreview.src = urlImagen;
        }
        if (linkAnchor) {
            linkAnchor.href = urlImagen;
        }
        wrapper.classList.remove("hidden");
    } else {
        if (imgPreview) {
            imgPreview.src = "";
        }
        if (linkAnchor) {
            linkAnchor.href = "#";
        }
        wrapper.classList.add("hidden");
    }

    if (modal && titulo && desc && tipo) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        titulo.textContent = asunto;
        desc.textContent = descripcion;
        tipo.textContent = tipoSolicitud;
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    $("#contenido-comentario").val("");
    if ($("#es_privado").length) $("#es_privado").prop("checked", false);
    $("#modalListaComentarios").html(
        '<p class="text-center text-slate-400 py-2">Cargando comentarios...</p>',
    );
    window.cargarComentariosDelTicket(ticketIdActual);
};

$(document).on("submit", "#form-comentario-modal", function (e) {
    e.preventDefault();
    if (!ticketIdActual) return;

    const contenido = $("#contenido-comentario").val().trim();
    if (contenido === "") return;

    const esPrivado = $("#es_privado").is(":checked") ? 1 : 0;
    const $btnSubmit = $(this).find('button[type="submit"]');
    const textoOriginal = $btnSubmit.html();

    $btnSubmit.prop("disabled", true).addClass("opacity-75 cursor-not-allowed");
    $btnSubmit.html('<span class="inline-block animate-spin mr-2">⏳</span> Guardando comentario...');

    $.ajax({
        url: `/tickets/${ticketIdActual}/comentarios`,
        method: "POST",
        data: {
            _token: $('input[name="_token"]').val(),
            contenido: contenido,
            es_privado: esPrivado,
        },
        success: function (response) {
            $btnSubmit.prop("disabled", false).removeClass("opacity-75 cursor-not-allowed").html(textoOriginal);

            if (response.success) {
                $("#contenido-comentario").val("");
                if ($("#es_privado").length) $("#es_privado").prop("checked", false);
                window.cargarComentariosDelTicket(ticketIdActual);
            }
        },
        error: function (err) {
            $btnSubmit.prop("disabled", false).removeClass("opacity-75 cursor-not-allowed").html(textoOriginal);
            console.error("Error al guardar comentario:", err);
        },
    });
});

window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "";
    }
};
