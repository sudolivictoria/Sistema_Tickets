var table;
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

            window.verDetalle(
                asunto,
                descripcion,
                tipo,
                fecha,
                drive,
                datosSLA,
            );
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
window.verDetalle = function (
    asunto,
    descripcion,
    tipoNombre,
    fechaApertura,
    drive,
    datosSLA = {},
) {
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

    if (modal && titulo && desc && tipo && fecha) {
        titulo.textContent = asunto;
        desc.textContent = descripcion;
        tipo.textContent = tipoNombre;
        fecha.textContent = fechaApertura;
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    ticketIdActual = parseInt(asunto.match(/\d+/)) || null;

    //----Cargar comentarios anteriores
    $("#contenido-comentario").val("");
    if ($("#es_privado").length) $("#es_privado").prop("checked", false);

    const $lista = $("#modalListaComentarios");
    $lista.html(
        '<p class="text-center text-slate-400 py-2">Cargando comentarios...</p>',
    );

    if (ticketIdActual) {
        $.get(`/tickets/${ticketIdActual}/comentarios`, function (comentarios) {
            $lista.empty();
            if (comentarios.length === 0) {
                $lista.html(
                    '<p class="text-center text-slate-400 py-2">No hay comentarios todavía.</p>',
                );
                return;
            }
            comentarios.forEach((com) => {
                const bg = com.es_privado
                    ? "bg-amber-50 border-amber-100"
                    : "bg-white border-slate-100";
                const tag = com.es_privado
                    ? '<span class="text-amber-700 font-bold">[Interno]</span> '
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
    }
};

//----------funcion para COMMENTS
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
                $(".btn-ver-detalle:focus").click() ||
                    alert("Comentario guardado con éxito");
            }
        },
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

//-------funcion dry---------------
function procesarAccionTicket(btn, config) {
    const form = btn.closest("form");

    Swal.fire({
        title: config.tituloConfirmacion,
        text: "Esta acción registrará la hora de cierre del ticket.",
        icon: "question",
        iconColor: "#04003B",
        showCancelButton: true,
        confirmButtonColor: "#84cc16",
        confirmButtonText: config.textoBoton,
        cancelButtonText: "Cancelar",
        cancelButtonColor: "#ef4444",
        customClass: { popup: "rounded-3xl" },
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
