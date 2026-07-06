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
            const asunto = $(this).data("asunto");
            const descripcion = $(this).data("descripcion");
            const tipo = $(this).data("tipo");
            const fecha = $(this).data("fecha");
            const drive = $(this).data("drive");
            verDetalle(asunto, descripcion, tipo, fecha, drive);
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
    if (!textoBuscar && !fechaInicio && !fechaFin && estado === "todos" && categoria === "todos"
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

    window.location.href = `/admin/reportes/exportar?${params.toString()}`;
};

//--------ver detalle del ticket historial--------//
window.verDetalle = function (asunto, descripcion, tipoNombre, fechaApertura, drive) {
    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");

    if (drive && drive.trim() !== "" && drive !== "null") {
        linkAnchor.href = drive;
        wrapper.classList.remove("hidden");
    } else {
        linkAnchor.href = "#";
        wrapper.classList.add("hidden");
    }

    if (modal && titulo && desc && tipo) {
        titulo.innerText = asunto;
        desc.innerText = descripcion;
        tipo.innerText = tipoNombre;
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
};

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
