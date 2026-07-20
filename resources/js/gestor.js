//----variable global para almacenar la instancia de la tabla
var table;
window.currentStreamFilter = "todos";

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
        searching: true,
        info: false,
        responsive: true,
        autoWidth: false,
        dom: "rt",
        order: [[0, "desc"]],
        stateSave: false,
        language: {
            zeroRecords: `
                <div class="flex flex-col items-center justify-center h-[300px] bg-slate-50/40 rounded-2xl border-2 border-dashed border-slate-100 my-2 mx-2">
                    <span class="material-symbols-outlined text-slate-300 text-4xl mb-2 select-none">folder_off</span>
                    <h5 class="text-xs font-black uppercase text-slate-400 tracking-widest">Bandeja Vacía</h5>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">No existen tickets disponibles bajo este estado.</p>
                </div>
            `,
            emptyTable: `
                <div class="flex flex-col items-center justify-center h-[300px] bg-slate-50/40 rounded-2xl border-2 border-dashed border-slate-100 my-2 mx-2">
                    <span class="material-symbols-outlined text-slate-300 text-4xl mb-2 select-none">folder_off</span>
                    <h5 class="text-xs font-black uppercase text-slate-400 tracking-widest">Bandeja Vacía</h5>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">No existen tickets disponibles bajo este estado.</p>
                </div>
            `,
        },
    });
};

//----desplegable de canales directos
document.addEventListener("DOMContentLoaded", function () {
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
    // Inicializa la tabla apuntando al ID del Gestor
    window.inicializarTablaTickets("#tablaGestor");

    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const idTicket = $(this).data("id");
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");
            const fecha = $(this).data("fecha");
            const drive = $(this).data("drive");

            const datosSLA = {
                estado: $(this).data("estado"),
                fechaLimite: $(this).data("fecha-limite"),
                tiempoRespuesta: $(this).data("tiempo-respuesta"),
            };

            window.verDetalle(idTicket, asunto, descripcion, tipo, fecha, drive, datosSLA);
        });

    $(document)
        .off("click", ".btn-ver-usuario")
        .on("click", ".btn-ver-usuario", function () {
            const nombre = $(this).data("nombre");
            const email = $(this).data("email");
            const unidad = $(this).data("unidad");
            const cargo = $(this).data("cargo");
            const telefono = $(this).data("telefono");

            window.verUsuario(nombre, email, unidad, cargo, telefono);
        });
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

//------------------------TIMER SLA-------------------------------------
let timerSLA = null;
function iniciarContadorSLA(datosSLA) {
    if (timerSLA) clearInterval(timerSLA);

    const wrapper = document.getElementById("wrapperCountdown");
    const display = document.getElementById("modalCountdown");
    if (!wrapper || !display) return;

    const { estado, fechaLimite, tiempoRespuesta, tiempo_respuesta } = datosSLA;
    const segundosTranscurridos = tiempoRespuesta || tiempo_respuesta;

    const estadosCerrados = ["resuelto", "equivocado", "no corresponde"];
    if (estadosCerrados.includes(estado) || segundosTranscurridos) {
        wrapper.classList.remove("hidden");
        wrapper.className =
            "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-full text-blue-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300";

        let segundosFinales = segundosTranscurridos;
        if (
            !segundosFinales &&
            datosSLA.fechaCierre &&
            datosSLA.fechaCreacion
        ) {
            const creacion = new Date(datosSLA.fechaCreacion).getTime();
            const cierre = new Date(datosSLA.fechaCierre).getTime();
            segundosFinales = Math.max(
                0,
                Math.floor((cierre - creacion) / 1000),
            );
        }
        if (!segundosFinales) {
            display.textContent = "Finalizado";
            return;
        }

        const dias = Math.floor(segundosFinales / (3600 * 24));
        const horas = Math.floor((segundosFinales % (3600 * 24)) / 3600);
        const minutos = Math.floor((segundosFinales % 3600) / 60);
        const segundos = segundosFinales % 60;

        let tiempoTexto = "";
        if (dias > 0) {
            tiempoTexto = `${dias} d ${horas}h ${minutos} m`;
        } else if (horas > 0) {
            tiempoTexto = `${horas} h ${minutos} m`;
        } else if (minutos > 0) {
            tiempoTexto = `${minutos} m ${segundos} s`;
        } else {
            tiempoTexto = `${segundos} s`;
        }
        display.textContent = `Respuesta: ${tiempoTexto}`;
        return;
    }
    if (!fechaLimite) {
        wrapper.classList.add("hidden");
        return;
    }

    wrapper.classList.remove("hidden");
    const limite = new Date(fechaLimite).getTime();
    const tick = () => {
        const restante = limite - new Date().getTime();

        if (restante <= 0) {
            clearInterval(timerSLA);
            display.textContent = "Vencido";
            wrapper.classList.remove(
                "bg-green-50",
                "border-green-100",
                "text-green-600",
            );
            wrapper.classList.add(
                "bg-red-50",
                "border-red-200",
                "text-red-600",
                "animate-pulse",
            );
            return;
        }
        wrapper.classList.remove(
            "bg-red-50",
            "border-red-200",
            "text-red-600",
            "animate-pulse",
        );
        wrapper.classList.add(
            "bg-green-50",
            "border-green-100",
            "text-green-600",
        );

        const dias = Math.floor(restante / (1000 * 60 * 60 * 24));
        const horas = Math.floor(
            (restante % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
        );
        const minutos = Math.floor((restante % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((restante % (1000 * 60)) / 1000);
        const pad = (num) => String(num).padStart(2, "0");

        if (dias > 0) {
            display.textContent = `${dias}d ${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        } else {
            display.textContent = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        }
    };

    tick();
    timerSLA = setInterval(tick, 1000);
}

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

window.verDetalle = function (
    idTicket,
    asunto,
    descripcion,
    tipoNombre,
    fechaApertura,
    drive,
    datosSLA = {}
) {
    ticketIdActual = idTicket;

    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    const fecha = document.getElementById("modalFechaApertura");

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

    iniciarContadorSLA(datosSLA);

    if (modal && titulo && desc && tipo) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        titulo.textContent = asunto;
        desc.textContent = descripcion;
        tipo.textContent = tipoNombre;
        if (fecha) {
            fecha.textContent = fechaApertura;
        }
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

//---cerrar modal
window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};

window.verUsuario = function (name, email, unidad, cargo, telefono) {
    const modal = document.getElementById("modalUsuario");
    const nombre = document.getElementById("userNombre");
    const correo = document.getElementById("userEmail");
    const departamento = document.getElementById("userUnidad");
    const puesto = document.getElementById("userCargo");
    const contacto = document.getElementById("userTelefono");
    const elLinkCorreo = document.getElementById("linkCorreo");

    if (nombre && correo && departamento && puesto && contacto && modal) {
        nombre.textContent = name;
        correo.textContent = email;
        departamento.textContent = unidad;
        puesto.textContent = cargo;
        contacto.textContent = telefono;

        //-----------------GMAIL--------------
        if (email && email !== "---") {
            elLinkCorreo.href = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=Consulta sobre su Ticket&body=Hola ${name},`;
            elLinkCorreo.classList.remove("opacity-50", "pointer-events-none");
        } else {
            elLinkCorreo.href = "javascript:void(0)";
            elLinkCorreo.classList.add("opacity-50", "pointer-events-none");
        }
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
};

window.clearModalUsuario = window.cerrarModalUsuario = function () {
    const modal = document.getElementById("modalUsuario");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};
