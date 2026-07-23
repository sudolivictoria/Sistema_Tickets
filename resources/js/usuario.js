//----variable global para almacenar la instancia de la tabla
var table;
let ticketIdActual = null;
let ticketEstadoActual = null;

//---echo
let canalEchoActual = null;

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

//--------------------------LARAVEL ECHO REVERB------------------------
window.escucharComentariosWebSocket = function (idTicket) {
    if (typeof Echo === "undefined") {
        console.warn("[Reverb] Echo no está inicializado globalmente.");
        return;
    }
    window.desconectarComentariosWebSocket();
    canalEchoActual = idTicket;
    //---conexion dinamica del ticket
    Echo.channel(`ticket.${idTicket}`)
        .listen('.comentario.creado', (e) => { //--punto inicial
            if (e && e.comentario) {
                const esPrivado = e.comentario.es_privado == 1 || e.comentario.es_privado === true || e.comentario.es_privado === "true";
                if (esPrivado) return;
                window.agregarComentarioAlModal(e.comentario);
            }
        });
};
window.desconectarComentariosWebSocket = function () {
    if (typeof Echo !== "undefined" && canalEchoActual) {
        Echo.leaveChannel(`ticket.${canalEchoActual}`);
        canalEchoActual = null;
    }
};

// =====================================================================
//                         DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    window.inicializarTablaTickets("#tablaTicketsUsuario");

    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const idTicket = $(this).data("id");
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");
            const state = $(this).data("estado");
            const drive = $(this).data("drive");
            const estadoNombre = $(this).data("estado"); 

            window.verDetalle(idTicket, asunto, descripcion, tipo, state, drive, estadoNombre);
        });
});

//=====================================================================
//                     FUNCIONES MODALES
//=====================================================================
window.cargarComentariosDelTicket = function (idTicket, estadoNombre) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");
    const $formularioComentario = $("#form-comentario-modal");

    if (!idTicket) return;

    const estadosCerradosTextos = ["resuelto", "equivocado", "no corresponde", "cerrado"];
    const estadoStr = String(estadoNombre || "").toLowerCase().trim();
    const esCerradoPorTexto = estadosCerradosTextos.includes(estadoStr);

    if (esCerradoPorTexto) {
        $formularioComentario.hide();
    } else {
        $formularioComentario.show();
    }
    $.get(`/tickets/${idTicket}/comentarios`)
        .done(function (comentarios) {
            $lista.empty();

            const comentariosVisibles = (comentarios || []).filter(
                (com) => !com.es_privado && com.es_privado != 1
            );

            if (comentariosVisibles.length === 0) {
                $seccionHistorico.hide();
                $("#preloaderGlobalModal").addClass("hidden");
                return;
            }

            $seccionHistorico.show();

            const fragment = document.createDocumentFragment();

            comentariosVisibles.forEach((com) => {
                const item = document.createElement("div");
                item.className = "p-2 rounded-xl border bg-white border-slate-200";

                if (com.id) item.setAttribute("data-comentario-id", com.id);

                item.innerHTML = `
                    <div class="flex justify-between font-bold text-green-950 mb-0.5">
                        <span>${com.user ? com.user.name : "Usuario"}</span>
                        <span class="text-[10px] text-slate-400 font-normal">${com.tiempo_legible || ""}</span>
                    </div>
                    <p class="text-slate-600 font-medium">${com.contenido}</p>
                `;
                fragment.appendChild(item);
            });

            $lista[0].appendChild(fragment);
            $lista.scrollTop($lista[0].scrollHeight);
        })
        .fail(function (err) {
            console.error("Error al obtener comentarios:", err);
        })
        .always(function () {
            $("#preloaderGlobalModal").addClass("hidden");
        });
};

//---------------------------AGREGAR COMENTARIOS DINAMICAMENTE---------------------------
window.agregarComentarioAlModal = function (comentario) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");

    if (!$lista.length) return;
    //--prevenir notas privadas
    const esPrivado = comentario.es_privado == 1 || comentario.es_privado === true || comentario.es_privado === "true";
    if (esPrivado) return;
    //---prevenir duplicados
    if (comentario.id && $lista.find(`[data-comentario-id="${comentario.id}"]`).length > 0) {
        return;
    }
    $seccionHistorico.show();

    const dataAttr = comentario.id ? `data-comentario-id="${comentario.id}"` : "";

    const elComentario = `
       <div ${dataAttr} class="p-2 rounded-xl border bg-white border-slate-200 transition-all duration-300">
            <div class="flex justify-between font-bold text-green-950 mb-0.5">
                <span>${comentario.user ? comentario.user.name : "Usuario"}</span>
                <span class="text-[10px] text-slate-400 font-normal">${comentario.tiempo_legible || "Ahora mismo"}</span>
            </div>
            <p class="text-slate-600 font-medium">${comentario.contenido}</p>
        </div>
    `;

    $lista.append(elComentario);
    $lista.scrollTop($lista[0].scrollHeight);
};

//---funciones para ver detalles de los tickets
window.verDetalle = function (idTicket, asunto, descripcion, solicitud, state, drive, estadoNombre) {
    ticketIdActual = idTicket;
    ticketEstadoActual = state;
    
    const modal = document.getElementById("modalTicket");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescripcion = document.getElementById("modalDescripcion");
    const modalTipoSolicitud = document.getElementById("modalTipoSolicitud");
    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");

     //**********PRELOADER GLOBAL*******************/
    if (!document.getElementById("preloaderGlobalModal") && modal) {
        const preloaderHTML = `
            <div id="preloaderGlobalModal" class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center z-[100] transition-all duration-300 rounded-3xl">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-primary rounded-full animate-spin mb-3"></div>
                <p class="text-slate-500 font-semibold text-xs tracking-wide uppercase">Cargando información del ticket...</p>
            </div>`;
        const contenedorInterno = modal.querySelector(".bg-white") || modal;
        if (contenedorInterno) {
            contenedorInterno.style.position = "relative"; //----POSICIONAMIENTO
            $(contenedorInterno).prepend(preloaderHTML);
        }
    } else {
        $("#preloaderGlobalModal").removeClass("hidden");
    }
    //************************************************/
  
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
     //----IMAGEN DE EVIDENCIA------
    if (drive && drive.trim() !== "" && drive !== "null") {
        const pathLimpio = drive.startsWith("/") ? drive.substring(1) : drive;
        const urlImagen = `${window.location.origin}/storage/${pathLimpio}`;

        if (linkAnchor) linkAnchor.href = urlImagen;
        if (wrapper) wrapper.classList.remove("hidden");
    } else {
        if (linkAnchor) linkAnchor.href = "#";
        if (wrapper) wrapper.classList.add("hidden");
    }

    $("#contenido-comentario").val("");
    if ($("#es_privado").length) $("#es_privado").prop("checked", false);
    $("#modalListaComentarios").html(
        '<p class="text-center text-slate-400 py-2">Cargando comentarios...</p>',
    );
    window.cargarComentariosDelTicket(ticketIdActual, ticketEstadoActual);

     //----conectar al canal en tiempo real
    if (typeof window.escucharComentariosWebSocket === "function") {
        window.escucharComentariosWebSocket(ticketIdActual);
    }
};

//--------------NUEVO COMENTARIO---------------------
$(document).on("submit", "#form-comentario-modal", function (e) {
    e.preventDefault();
    if (!ticketIdActual) return;

    const $inputContenido = $("#contenido-comentario");
    const contenido = $inputContenido.val().trim();
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
            _token: $('input[name="_token"]').val() || $('meta[name="csrf-token"]').attr('content'),
            contenido: contenido,
            es_privado: esPrivado,
        },
    })
        .done(function (response) {
            if (response.success || response.comentario) {
                $inputContenido.val("");
                if ($("#es_privado").length) $("#es_privado").prop("checked", false);
                const comentarioData = response.comentario || response;
                //---nota privada evitar que salgan para el usuario
                const esPrivado = comentarioData.es_privado == 1 || comentarioData.es_privado === true || comentarioData.es_privado === "true";
                if (esPrivado) return;
                window.agregarComentarioAlModal(comentarioData);
            }
        })
        .fail(function (err) {
            console.error("Error al guardar comentario:", err);
            alert("Ocurrió un error al intentar publicar el comentario.");
        })
        .always(function () {
            $btnSubmit.prop("disabled", false).removeClass("opacity-75 cursor-not-allowed").html(textoOriginal);
        });
});

window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "";
        ticketIdActual = null;

        //---desconectar el canal echo al cerrar
        if (typeof window.desconectarComentariosWebSocket === "function") {
            window.desconectarComentariosWebSocket();
        }
    }
};