var tableHistorial;
var filtrosAplicados = false;
let timerSLA = null;
let ticketIdActual = null;
let ticketEstadoActual = null;

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
            const estadoNombre = $(this).data("estado");
            const estadoSLA = $(this).data("state");
            const datosSLA = {
                estadoNombre: estadoNombre,
                estadoSLA: estadoSLA,
                fechaLimite: $(this).data("fecha-limite"),
                tiempoRespuesta: $(this).data("tiempo-respuesta"),
            };
            verDetalle(idTicket,asunto,descripcion,tipo,fecha,drive,estadoNombre,estadoSLA,datosSLA,);
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

    //-------------VALIDACIONES---------------

    //------validar si todos los parámetros están en su estado vacío por defecto
    if (
        !textoBuscar && !fechaInicio && !fechaFin && estado === "todos" && categoria === "todos") {
        Swal.fire({
            title: "¡Búsqueda muy amplia!",
            text: "Para proteger el rendimiento del sistema, debe seleccionar al menos un filtro específico.",
            icon: "warning",
            iconColor: "#84cc16",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#04003B",
            customClass: {
                popup: "rounded-3xl p-6", 
                confirmButton: "rounded-xl px-5 py-2.5 font-bold",
                cancelButton: "rounded-xl px-5 py-2.5 font-bold",
            },
        });
        return;
    }
    //------rango de fechas, complete ambos campos
    if ((fechaInicio && !fechaFin) || (!fechaInicio && fechaFin)) {
        Swal.fire({
            title: "Rango de fechas incompleto",
            text: "Por favor, especifique tanto la 'Fecha Inicio' como la 'Fecha Fin' para procesar el filtro de tiempo correctamente.",
            icon: "warning",
            iconColor: "#84cc16",
            confirmButtonText: "Completar rango",
            confirmButtonColor: "#04003B",
            customClass: {
                popup: "rounded-3xl p-6", 
                confirmButton: "rounded-xl px-5 py-2.5 font-bold",
                cancelButton: "rounded-xl px-5 py-2.5 font-bold",
            },
        });
        return;
    }
    //------rango de fechas, complete ambos campos
    if (fechaInicio > fechaFin) {
        Swal.fire({
            title: "Rango de fechas incorrecto",
            text: "La 'Fecha Inicio' no puede ser posterior a la 'Fecha Fin'. Por favor, corrija las fechas para procesar el filtro de tiempo correctamente.",
            icon: "warning",
            iconColor: "#84cc16",
            confirmButtonText: "Completar rango",
            confirmButtonColor: "#04003B",
            customClass: {
                popup: "rounded-3xl p-6", 
                confirmButton: "rounded-xl px-5 py-2.5 font-bold",
                cancelButton: "rounded-xl px-5 py-2.5 font-bold",
            },
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
            iconColor: "#84cc16",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#04003B",
            customClass: {
                popup: "rounded-3xl p-6", 
                confirmButton: "rounded-xl px-5 py-2.5 font-bold",
                cancelButton: "rounded-xl px-5 py-2.5 font-bold",
            },
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

    const urlFinal = `/admin/reportes/exportar?${params.toString()}`;

    if (formato === "pdf") {
        window.open(urlFinal, "_blank");
    } else {
        window.location.href = urlFinal;
    }
};

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

    if (modal && titulo && desc && tipo) {
        titulo.textContent = asunto;
        desc.textContent = descripcion;
        tipo.textContent = tipoNombre;
        if (fecha) {
            fecha.textContent = fechaApertura;
        }
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    //----IMAGEN DE EVIDENCIA-----
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

window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
        if (timerSLA) clearInterval(timerSLA);
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
        nombre.textContent = name || "---";
        correo.textContent = email || "---";
        departamento.textContent = unidad || "---";
        puesto.textContent = cargo || "---";
        contacto.textContent = telefono || "---";

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
