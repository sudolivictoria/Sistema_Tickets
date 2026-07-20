//------variable global-------
var table;
let timerSLA = null;
let ticketIdActual = null;
let ticketEstadoActual = null;

window.inicializarTablaTickets = function (
    selectorId,
    columnaOrden = 0,
    sentido = "desc",
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

    //--buscador
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
            const fecha = $(this).data("fecha");
             const drive = $btn.data("drive");
            const estadoNombre = $btn.data("estado"); 
            const estadoSLA = $btn.data("state");    

            const datosSLA = {
                estadoNombre: estadoNombre,
                estadoSLA: estadoSLA,
                fechaLimite: $btn.data("fecha-limite"),         
                tiempoRespuesta: $btn.data("tiempo-respuesta"), 
            };

            window.verDetalle(idTicket, asunto, descripcion, tipo, fecha, drive, estadoNombre, estadoSLA, datosSLA);
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
    const selectorTabla = "#tablaMisAsignados";
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
            display.textContent = `${dias}d ${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        } else {
            display.textContent = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
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
                    ? "bg-green-50 border-green-100"
                    : "bg-white border-slate-100";
                const tag = com.es_privado
                    ? '<span class="text-green-700 font-bold">[Interno]</span> '
                    : "";

                const item = document.createElement("div");
                item.className = `p-2 rounded-xl border ${bg}`;
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

window.verDetalle = function (idTicket, asunto, descripcion, tipoNombre, fechaApertura, drive, estadoNombre, estadoSLA, datosSLA = {}) {
    ticketIdActual = idTicket;
    ticketEstadoActual = estadoNombre;

    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    const fecha = document.getElementById("modalFechaApertura");
    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");

    //************PRELOADER GLOBAL*****************/
    if (modal && !document.getElementById("preloaderGlobalModal")) {
        const preloaderHTML = `
            <div id="preloaderGlobalModal" class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center z-[100] transition-all duration-300 rounded-3xl">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-primary rounded-full animate-spin mb-3"></div>
                <p class="text-slate-500 font-semibold text-xs tracking-wide uppercase">Cargando información del ticket...</p>
            </div>`;
        const contenedorInterno = modal.querySelector(".bg-white") || modal;
        contenedorInterno.style.position = "relative";
        $(contenedorInterno).prepend(preloaderHTML);
    } else {
        $("#preloaderGlobalModal").removeClass("hidden");
    }
    //***************************************************/

    if (modal && titulo && desc && tipo && fecha) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        titulo.textContent = asunto || "";
        desc.textContent = descripcion || "";
        tipo.textContent = tipoNombre || "";
        fecha.textContent = fechaApertura || "";
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    //-----------IMAGEN DE EVIDENCIA-------
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
    
    iniciarContadorSLA(datosSLA);
};

//--------------NUEVO COMENTARIO---------------------
$(document).off("submit", "#form-comentario-modal").on("submit", "#form-comentario-modal", function (e) {
    e.preventDefault();
    if (!ticketIdActual) return;

    const $inputContenido = $("#contenido-comentario");
    const contenido = $inputContenido.val().trim();
    if (contenido === "") return;

    const esPrivado = $("#es_privado").is(":checked") ? 1 : 0;
    const $btnSubmit = $(this).find('button[type="submit"]');
    const textoOriginal = $btnSubmit.html();

    $btnSubmit.prop("disabled", true).addClass("opacity-75 cursor-not-allowed");

    const loaderText = esPrivado === 1 ? "Guardando comentario..." : "Enviando comentario...";
    $btnSubmit.html(`<span class="inline-block animate-spin mr-2">⏳</span> ${loaderText}`);

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
            if (response.success) {
                $inputContenido.val("");
                if ($("#es_privado").length) $("#es_privado").prop("checked", false);
                window.cargarComentariosDelTicket(ticketIdActual, ticketEstadoActual);
            }
        })
        .fail(function (err) {
            console.error("Error al guardar comentario:", err);
            window.cargarComentariosDelTicket(ticketIdActual, ticketEstadoActual);
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
    //----------------envio de correos directo----------------
    const elLinkCorreo = document.getElementById("linkCorreo");
    if (nombre && correo && departamento && puesto && contacto && modal) {
        nombre.innerText = name;
        correo.innerText = email;
        departamento.innerText = unidad;
        puesto.innerText = cargo;
        contacto.innerText = telefono;
        //-----------------GMAIL--------------
        if (email && email !== "---") {
            //----abre gmail directamente para su redaccion
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

//------------------CERRAR MODAL USUARIO-----------------
window.cerrarModalUsuario = function () {
    const modal = document.getElementById("modalUsuario");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};

//-------función con Textarea Opcional para Comentario de Cierre---------------
function procesarAccionTicket(btn, config) {
    const form = btn.closest("form");
    const idTicket = ticketIdActual || $(btn).data("id") || $(form).data("id");

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
            confirmButton: "px-6 py-2.5 rounded-xl font-semibold text-sm shadow-md transition-transform active:scale-95",
            cancelButton: "px-6 py-2.5 rounded-xl font-semibold text-sm shadow-sm transition-transform active:scale-95",
        },
        buttonsStyling: true,
        preConfirm: (comentarioTexto) => {
            const textoLimpio = comentarioTexto ? comentarioTexto.trim() : "";

            if (textoLimpio !== "" && idTicket) {
                const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();

                return $.ajax({
                    url: `/tickets/${idTicket}/comentarios`,
                    method: "POST",
                    data: {
                        _token: csrfToken,
                        contenido: textoLimpio,
                        es_privado: 0 
                    }
                }).catch(error => {
                    Swal.showValidationMessage(`Error al guardar el comentario: ${error.statusText}`);
                });
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

// =====================================================================
// BOTONES HTML (RESOLVER, MARCAR COMO EQUÍVOCADO, MARCAR COMO NO CORRESPONDE)
// =====================================================================

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
