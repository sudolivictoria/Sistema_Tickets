var tableHistorial;
var filtrosAplicados = false;

window.inicializarHistorialDataTable = function () {
    if ($.fn.DataTable.isDataTable("#tablaHistorial")) {
        $("#tablaHistorial").DataTable().destroy();
    }
    $.fn.dataTable.ext.pager.numbers_length = 1;
    tableHistorial = $("#tablaHistorial").DataTable({
        language: {
            processing: "Procesando...",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: `
                    <div class="flex flex-col items-center justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">search_off</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No se encontraron resultados</p>
                    </div>`,
            emptyTable: `
                    <div class="flex flex-col items-center justify-center py-10">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">pageview</span>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">Por favor seleccione los filtros y presione "Filtrar" para cargar el historial</p>
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
        order: [[0, "desc"]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
    });

    $("#tablaHistorial")
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

            verDetalle(idTicket, asunto, descripcion, tipo, fecha, drive, datosSLA);
        });
    $("#tablaHistorial")
        .off("click", ".btn-ver-usuario")
        .on("click", ".btn-ver-usuario", function () {
            const nombre = $(this).data("nombre");
            const email = $(this).data("email");
            const unidad = $(this).data("unidad");
            const cargo = $(this).data("cargo");
            const telefono = $(this).data("telefono");
            verUsuario(nombre, email, unidad, cargo, telefono);
        });
    tableHistorial.draw();
};

window.aplicarFiltrosHistorial = function () {
    if (!tableHistorial) return;
    const textoBuscar = document.getElementById("filtroBuscar").value.trim();
    const fechaInicio = document.getElementById("filtroFechaInicio").value;
    const fechaFin = document.getElementById("filtroFechaFin").value;
    const estado = document.getElementById("filtroEstado").value;
    const categoria = document.getElementById("filtroCategoria")
        ? document.getElementById("filtroCategoria").value
        : "todos";

    //------validar si todos los parámetros están en su estado vacío por defecto
    if (
        !textoBuscar &&
        !fechaInicio &&
        !fechaFin &&
        estado === "todos" &&
        categoria === "todos"
    ) {
        Swal.fire({
            title: "¡Búsqueda demasiado amplia!",
            text: "Para proteger el rendimiento del sistema, debe seleccionar al menos un filtro específico (Estado, Categoría, Rango de fechas o escribir un término de búsqueda).",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#04003B",
        });
        return;
    }

    //------rango de fechas, complete ambos campos
    if ((fechaInicio && !fechaFin) || (!fechaInicio && fechaFin)) {
        Swal.fire({
            title: "Rango de fechas incompleto",
            text: "Por favor, especifique tanto la 'Fecha Inicio' como la 'Fecha Fin' para procesar el filtro de tiempo correctamente.",
            icon: "warning",
            confirmButtonText: "Completar rango",
            confirmButtonColor: "#04003B",
        });
        return;
    }

    //------rango de fechas, complete ambos campos
    if (fechaInicio > fechaFin) {
        Swal.fire({
            title: "Rango de fechas incorrecto",
            text: "La 'Fecha Inicio' no puede ser posterior a la 'Fecha Fin'. Por favor, corrija las fechas para procesar el filtro de tiempo correctamente.",
            icon: "warning",
            confirmButtonText: "Completar rango",
            confirmButtonColor: "#04003B",
        });
        return;
    }

    filtrosAplicados = true;
    tableHistorial.search(textoBuscar);
    tableHistorial.draw();
};

//--------limpiar filtros historial--------//
window.limpiarFiltrosHistorial = function () {
    if (!tableHistorial) return;
    filtrosAplicados = false;

    const elBuscar = document.getElementById("filtroBuscar");
    if (elBuscar) elBuscar.value = "";

    const elFechaInicio = document.getElementById("filtroFechaInicio");
    if (elFechaInicio) elFechaInicio.value = "";

    const elFechaFin = document.getElementById("filtroFechaFin");
    if (elFechaFin) elFechaFin.value = "";

    const elEstado = document.getElementById("filtroEstado");
    if (elEstado) elEstado.value = "todos";

    const elCategoria = document.getElementById("filtroCategoria");
    if (elCategoria) elCategoria.value = "todos";

    tableHistorial.search("").draw();
};

//--------exportar historial--------//
window.exportarHistorial = function (formato) {
    if (!filtrosAplicados) {
        Swal.fire({
            title: "¡Filtros requeridos!",
            text: "Por favor, aplique los filtros primero antes de exportar un reporte.",
            icon: "warning",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#04003B",
        });
        return;
    }

    const buscar = document.getElementById("filtroBuscar").value;
    const fechaInicio = document.getElementById("filtroFechaInicio").value;
    const fechaFin = document.getElementById("filtroFechaFin").value;
    const estado = document.getElementById("filtroEstado").value;
    const categoria = document.getElementById("filtroCategoria")
        ? document.getElementById("filtroCategoria").value
        : "todos";

    const params = new URLSearchParams({
        tipo: formato,
        buscar: buscar,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        estado: estado,
        categoria: categoria,
    });

    const urlFinal  = `/admin/reportes/exportar?${params.toString()}`;

    if (formato === 'pdf') {
        window.open(urlFinal, '_blank');
    } else {
        window.location.href = urlFinal;
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

//--------ver detalle del ticket historial--------//
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
    datosSLA = {},
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
        const pathLimpio = drive.startsWith("/") ? drive.substring(1) : drive;
        const urlImagen = `${window.location.origin}/storage/${pathLimpio}`;
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
        titulo.innerText = asunto;
        desc.innerText = descripcion;
        tipo.innerText = tipoNombre;
        if (fecha) {
            fecha.innerText = fechaApertura;
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

window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};

//--------ver detalle del usuario historial--------//
window.verUsuario = function (name, email, unidad, cargo, telefono) {
    const modal = document.getElementById("modalUsuario");
    const nombre = document.getElementById("userNombre");
    const correo = document.getElementById("userEmail");
    const departamento = document.getElementById("userUnidad");
    const puesto = document.getElementById("userCargo");
    const contacto = document.getElementById("userTelefono");
    const elLinkCorreo = document.getElementById("linkCorreo");
    if (nombre && correo && departamento && puesto && contacto && modal) {
        nombre.innerText = name;
        correo.innerText = email;
        departamento.innerText = unidad;
        puesto.innerText = cargo;
        contacto.innerText = telefono;

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

window.cerrarModalUsuario = function () {
    const modal = document.getElementById("modalUsuario");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};

//--------filtros personalizados para historial--------//
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector("#tablaHistorial")) {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== "tablaHistorial") return true;

            if (!filtrosAplicados) {
                return false;
            }

            const filaTr = settings.aoData[dataIndex].nTr;
            const fechaFilaRaw = filaTr.getAttribute("data-fecha");
            const estadoFilaId = filaTr.getAttribute("data-estado-id");
            const categoriaFilaId = filaTr.getAttribute("data-categoria-id");

            const estadoSel = document.getElementById("filtroEstado").value;
            if (estadoSel !== "todos" && estadoFilaId !== estadoSel) {
                return false;
            }
            const elCategoria = document.getElementById("filtroCategoria");
            if (elCategoria) {
                const categoriaSel = elCategoria.value;
                if (
                    categoriaSel !== "todos" &&
                    categoriaFilaId !== categoriaSel
                ) {
                    return false;
                }
            }
            const fInicioRaw =
                document.getElementById("filtroFechaInicio").value;
            const fFinRaw = document.getElementById("filtroFechaFin").value;

            if (fechaFilaRaw) {
                const fechaFila = new Date(fechaFilaRaw + "T00:00:00");

                if (fInicioRaw) {
                    const fechaInicio = new Date(fInicioRaw + "T00:00:00");
                    if (fechaFila < fechaInicio) return false;
                }
                if (fFinRaw) {
                    const fechaFin = new Date(fFinRaw + "T00:00:00");
                    if (fechaFila > fechaFin) return false;
                }
            }
            return true;
        });
        window.inicializarHistorialDataTable();
    }
});
