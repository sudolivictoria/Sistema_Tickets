//----variable global para almacenar la instancia de la tabla
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

//----desplegable de canales directos
document.addEventListener("DOMContentLoaded", function () {
    //--canales directos
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

    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");
            const drive = $(this).data("drive");

            window.verDetalle(asunto, descripcion, tipo, drive);
        });
});

let ticketIdActual = null;
window.cargarComentariosDelTicket = function (idTicket) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");

    if (!idTicket) return;

    $.get(`/tickets/${idTicket}/comentarios`, function (comentarios) {
        $lista.empty();

        if (!comentarios || comentarios.length === 0) {
            $seccionHistorico.hide();
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
    });
};

//---funciones para ver detalles de los tickets
window.verDetalle = function (asunto, descripcion, solicitud, drive) {
    const modal = document.getElementById("modalTicket");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescripcion = document.getElementById("modalDescripcion");
    const modalTipoSolicitud = document.getElementById("modalTipoSolicitud");

    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");
    const imgPreview = document.getElementById("modalEvidenciaImg");

    if (drive && drive.trim() !== "" && drive !== "null") {
        const urlImagen = `/storage/${drive}`;
        if (imgPreview) { imgPreview.src = urlImagen; }
        if (linkAnchor) { linkAnchor.href = urlImagen; }
        wrapper.classList.remove("hidden");
    } else {
        if (imgPreview) { imgPreview.src = ""; }
        if (linkAnchor) { linkAnchor.href = "#"; }
        wrapper.classList.add("hidden");
    }

    if (modal && modalTitulo && modalDescripcion && modalTipoSolicitud) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modalTitulo.textContent = asunto;
        modalDescripcion.textContent = descripcion;
        modalTipoSolicitud.textContent = solicitud;
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden"; 
    }

    ticketIdActual = idTicket;

    $("#contenido-comentario").val("");
    if ($("#es_privado").length) $("#es_privado").prop("checked", false);
    $("#modalListaComentarios").html(
        '<p class="text-center text-slate-400 py-2">Cargando comentarios...</p>',
    );
    window.cargarComentariosDelTicket(ticketIdActual);
};

//--------------NUEVO COMENTARIO---------------------
$(document).on("submit", "#form-comentario-modal", function (e) {
    e.preventDefault();
    if (!ticketIdActual) return;

    const contenido = $("#contenido-comentario").val();
    const esPrivado = $("#es_privado").is(":checked");

    $.ajax({
        url: `/tickets/${ticketIdActual}/comentarios`,
        method: "POST",
        data: {
            _token: $('input[name="_token"]').val(),
            contenido: contenido,
            es_privado: esPrivado ? 1 : 0,
        },
        success: function (response) {
            if (response.success) {
                $("#contenido-comentario").val("");
                if ($("#es_privado").length) $("#es_privado").prop("checked", false);
                window.cargarComentariosDelTicket(ticketIdActual);
            }
        },
        error: function(err) {
            console.error("Error al guardar comentario:", err);
        }
    });
});

window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "";
    }
};