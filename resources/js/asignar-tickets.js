var table;
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
        order: [[0, "asc"]],
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
            const drive = $(this).data("drive");

            const datosSLA = {
                estado: $(this).data("estado"),
                fechaLimite: $(this).data("fecha-limite"),
                tiempoRespuesta: $(this).data("tiempo-respuesta"),
            };

            window.verDetalle(asunto, descripcion, tipo, drive, datosSLA);
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

    //------------------AUTO REFRESCO-----------------
    const selectorTabla = "#tablaAsignarTickets";
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
            wrapper.classList.remove("bg-green-50","border-green-100","text-green-600",
            );
            wrapper.classList.add("bg-red-50","border-red-200","text-red-600","animate-pulse",
            );
            return;
        }
        wrapper.classList.remove("bg-red-50","border-red-200","text-red-600","animate-pulse",
        );
        wrapper.classList.add("bg-green-50","border-green-100","text-green-600",
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
window.verDetalle = function (asunto, descripcion, tipoNombre, drive, datosSLA = {}) {
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

    iniciarContadorSLA(datosSLA);

    if (modal && titulo && desc && tipo) {
        titulo.textContent = asunto;
        desc.textContent = descripcion;
        tipo.textContent = tipoNombre;
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
};

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
        nombre.textContent = name;
        correo.textContent = email;
        departamento.textContent = unidad;
        puesto.textContent = cargo;
        contacto.textContent = telefono;

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


