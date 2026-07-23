var table;
let timerSLA = null;
let ticketIdActual = null;
let ticketEstadoActual = null;
//---echo
let canalEchoActual = null;

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
//                       DETALLES E INICIALIZACION
// =====================================================================
$(document).ready(function () {
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const $btn = $(this);
            const idTicket = $btn.data("id");
            const asunto = $btn.data("asunto");
            const descripcion = $btn.data("descripcion");
            const tipo = $btn.data("tipo");
            const drive = $btn.data("drive");
            const estadoNombre = $btn.data("estado"); 
            const estadoSLA = $btn.data("state");    

            const datosSLA = {
                estadoNombre: estadoNombre,
                estadoSLA: estadoSLA,
                fechaLimite: $btn.data("fecha-limite"),         
                tiempoRespuesta: $btn.data("tiempo-respuesta"), 
            };

            window.verDetalle(idTicket, asunto, descripcion, tipo, drive, estadoNombre, estadoSLA, datosSLA);
        });
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

//------------------------TIMER SLA-------------------------------------
function iniciarContadorSLA(datosSLA) {
    if (timerSLA) clearInterval(timerSLA);

    const wrapper = document.getElementById("wrapperCountdown");
    const display = document.getElementById("modalCountdown");
    if (!wrapper || !display) return;

    const { estadoSLA, estadoNombre, fechaLimite, tiempoRespuesta } = datosSLA;
    const segundosTranscurridos = Number(tiempoRespuesta) || 0;
    
    const estadoCheck = String(estadoNombre || "").toLowerCase().trim();
    const estadosCerrados = ["resuelto", "equivocado", "no corresponde"];

    //-----ticket resuelto
    if (estadosCerrados.includes(estadoCheck) || segundosTranscurridos > 0) {
        wrapper.classList.remove("hidden");
        wrapper.className = "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-green-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300";

        if (segundosTranscurridos === 0) {
            display.textContent = "Finalizado";
            return;
        }
        const dias = Math.floor(segundosTranscurridos / 86400);
        const horas = Math.floor((segundosTranscurridos % 86400) / 3600);
        const minutos = Math.floor((segundosTranscurridos % 3600) / 60);
        const segundos = segundosTranscurridos % 60;

        if (dias > 0) display.textContent = `Respuesta: ${dias}d ${horas}h ${minutos}m`;
        else if (horas > 0) display.textContent = `Respuesta: ${horas}h ${minutos}m`;
        else if (minutos > 0) display.textContent = `Respuesta: ${minutos}m ${segundos}s`;
        else display.textContent = `Respuesta: ${segundos}s`;

        return;
    }
    //---sin fecha limite configurada
    if (!fechaLimite) {
        wrapper.classList.add("hidden");
        return;
    }
    //----conteo regresivo
    wrapper.classList.remove("hidden");
    const limite = new Date(fechaLimite).getTime();

    const tick = () => {
        const restante = limite - Date.now();

        //------vencido porque paso el tiempo correspondiente
        if (restante <= 0) {
            clearInterval(timerSLA);
            display.textContent = "Vencido";
            wrapper.className = "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-red-50 border border-red-200 rounded-full text-red-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300 animate-pulse";
            return;
        }
        wrapper.className = "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-green-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300";

        const dias = Math.floor(restante / (1000 * 60 * 60 * 24));
        const horas = Math.floor((restante % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutos = Math.floor((restante % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((restante % (1000 * 60)) / 1000);

        const pad = (num) => String(num).padStart(2, "0");

        if (dias > 0) {
            display.textContent = `${dias}d ${pad(horas)}h ${pad(minutos)}m`;
        } else if (horas > 0) {
            display.textContent = `${horas}h ${pad(minutos)}m ${pad(segundos)}s`;
        } else if (minutos > 0) {
            display.textContent = `${minutos}m ${pad(segundos)}s`;
        } else {
            display.textContent = `${segundos}s`;
        }
    };
    tick();
    timerSLA = setInterval(tick, 1000);
}

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

            if (!comentarios || comentarios.length === 0) {
                $seccionHistorico.hide();
                $("#preloaderGlobalModal").addClass("hidden");
                return;
            }

            $seccionHistorico.show();

            const fragment = document.createDocumentFragment();

            comentarios.forEach((com) => {
                const bg = com.es_privado
                    ? "bg-lime-50/80 border-lime-300"
                    : "bg-white border-slate-200";
                const tag = com.es_privado
                    ? '<span class="text-green-700 font-bold">[Nota Interna]</span> '
                    : "";

                const item = document.createElement("div");
                item.className = `p-2 rounded-xl border ${bg}`;

                if (com.id) item.setAttribute("data-comentario-id", com.id);

                item.innerHTML = `
                    <div class="flex justify-between font-bold text-green-950 mb-0.5">
                        <span>${tag}${com.user ? com.user.name : "Usuario"}</span>
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

    if (comentario.id && $lista.find(`[data-comentario-id="${comentario.id}"]`).length > 0) {
        return;
    }
    $seccionHistorico.show();
    const dataAttr = comentario.id ? `data-comentario-id="${comentario.id}"` : "";

    const bg = comentario.es_privado
        ? "bg-lime-50 border-lime-200"
        : "bg-white border-slate-200";
        
    const tag = comentario.es_privado
        ? '<span class="text-green-900 font-bold">[Nota Interna]</span> '
        : "";

    const elComentario = `
        <div class="p-2 rounded-xl border ${bg} transition-all duration-300">
            <div class="flex justify-between font-bold text-green-950 mb-0.5">
                <span>${tag}${comentario.user ? comentario.user.name : "Usuario"}</span>
                <span class="text-[10px] text-slate-400 font-normal">${comentario.tiempo_legible || "Ahora mismo"}</span>
            </div>
            <p class="text-slate-600 font-medium">${comentario.contenido}</p>
        </div>
    `;
    $lista.append(elComentario);
    $lista.scrollTop($lista[0].scrollHeight);
};

//-------------------TICKET------------------
window.verDetalle = function (idTicket, asunto, descripcion, tipoNombre, drive, estadoNombre, estadoSLA, datosSLA = {}) {
    ticketIdActual = idTicket;
    ticketEstadoActual = estadoNombre;

    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");

    //********** PRELOADER GLOBAL *******************/
    if (modal && !document.getElementById("preloaderGlobalModal")) {
        const preloaderHTML = `
            <div id="preloaderGlobalModal" class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center z-[100] transition-all duration-300 rounded-3xl">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-primary rounded-full animate-spin mb-3"></div>
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
    //***************************************************/
    if (modal && titulo && desc && tipo) {
        titulo.textContent = asunto || "";
        desc.textContent = descripcion || "";
        tipo.textContent = tipoNombre || "";
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
    //----------------IMAGEN DE EVIDENCIA--------------
    if (drive && String(drive).trim() !== "" && drive !== "null") {
        const pathLimpio = String(drive).startsWith("/") ? String(drive).substring(1) : drive;
        const urlImagen = `${window.location.origin}/storage/${pathLimpio}`;

        if (linkAnchor) linkAnchor.href = urlImagen;
        if (wrapper) wrapper.classList.remove("hidden");
    } else {
        if (linkAnchor) linkAnchor.href = "#";
        if (wrapper) wrapper.classList.add("hidden");
    }
    $("#contenido-comentario").val("");
    if ($("#es_privado").length) $("#es_privado").prop("checked", false);

    window.cargarComentariosDelTicket(ticketIdActual, ticketEstadoActual);
    
    //----conectar al canal en tiempo real
    if (typeof window.escucharComentariosWebSocket === "function") {
        window.escucharComentariosWebSocket(ticketIdActual);
    }
    iniciarContadorSLA(datosSLA);
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

/**
 * Cerrar modal
 */
window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
        if (timerSLA) clearInterval(timerSLA);
        ticketIdAcual = null;
        //----desconectar el canal del echo al cerrar
        if (typeof window.desconectarComentariosWebSocket === "function") {
            window.desconectarComentariosWebSocket();
        }
    }
};

//------------------DETALLES USUARIOS-----------------
window.verUsuario = function (name, email, unidad, cargo, telefono) {
    const modal = document.getElementById("modalUsuario");
    const nombre = document.getElementById("userNombre");
    const correo = document.getElementById("userEmail");
    const departamento = document.getElementById("userUnidad");
    const puesto = document.getElementById("userCargo");
    const contacto = document.getElementById("userTelefono");

    const elLinkCorreo = document.getElementById("linkCorreo");
    if (nombre && correo && departamento && puesto && contacto && modal) {
        nombre.textContent = name || "---";
        correo.textContent = email || "---";
        departamento.textContent = unidad || "---";
        puesto.textContent = cargo || "---";
        contacto.textContent = telefono || "---";

        //-----------------GMAIL--------------
        if (email && email !== "---") {
            elLinkCorreo.href = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent("Consulta sobre su Ticket")}&body=${encodeURIComponent(`Hola ${name || ""},`)}`;
            elLinkCorreo.classList.remove("opacity-50", "pointer-events-none");
        } else {
            elLinkCorreo.href = "javascript:void(0)";
            elLinkCorreo.classList.add("opacity-50", "pointer-events-none");
        }
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
};

//------------------CERRAR MODAL USUARIO-----------------
window.cerrarModalUsuario = function () {
    const modal = document.getElementById("modalUsuario");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};