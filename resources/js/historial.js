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

    //---------------------MANEJO DE EVENTOS E INICIALIZACION------------------
    $("#tablaHistorial")
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const $btn = $(this);
            window.verDetalle({
                idTicket: $btn.data("id"),
                asunto: $btn.data("asunto"),
                descripcion: $btn.data("descripcion"),
                tipoNombre: $btn.data("tipo"),
                fechaApertura: $btn.data("fecha"), // Historial maneja fecha
                drive: $btn.data("drive"),
                estadoNombre: $btn.data("estado"),
                datosSLA: {
                    estadoNombre: $btn.data("estado"),
                    fechaLimite: $btn.data("fecha-limite"),
                    tiempoRespuesta: $btn.data("tiempo-respuesta"),
                }
            });
        });

    //----------------PERFIL USUARIO
    $("#tablaHistorial")
        .off("click", ".btn-ver-usuario")
        .on("click", ".btn-ver-usuario", function () {
            const $btn = $(this);
            window.verUsuario(
                $btn.data("nombre"),
                $btn.data("email"),
                $btn.data("unidad"),
                $btn.data("cargo"),
                $btn.data("telefono")
            );
        });

    tableHistorial.draw();
};

//----------filtros PARA HISTORIAL
window.aplicarFiltrosHistorial = function () {
    if (!tableHistorial) return;
    const textoBuscar = document.getElementById("filtroBuscar").value.trim();
    const fechaInicio = document.getElementById("filtroFechaInicio").value;
    const fechaFin = document.getElementById("filtroFechaFin").value;
    const estado = document.getElementById("filtroEstado").value;
    const categoria = document.getElementById("filtroCategoria")
        ? document.getElementById("filtroCategoria").value
        : "todos";

    //--------------------------------VALIDACIONES------------------------------
    if (!textoBuscar && !fechaInicio && !fechaFin && estado === "todos" && categoria === "todos") {
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

    if (tableHistorial && tableHistorial.rows({ filter: "applied" }).count() === 0) {
        Swal.fire({
            title: "Sin resultados",
            text: "No se encontraron registros para exportar con los filtros seleccionados.",
            icon: "info",
            iconColor: "#04003B",
            confirmButtonText: "Entendido",
            confirmButtonColor: "#04003B",
            customClass: {
                popup: "rounded-3xl p-6",
                confirmButton: "rounded-xl px-5 py-2.5 font-bold",
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

    const esGestor = window.location.pathname.startsWith('/gestor');
    const baseUrl = esGestor ? '/gestor/reportes/exportar' : '/admin/reportes/exportar';
    const urlFinal = `${baseUrl}?${params.toString()}`;

    if (formato === "pdf") {
        window.open(urlFinal, "_blank");
    } else {
        window.location.href = urlFinal;
    }
};

//--------filtros personalizados para historial--------//
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector("#tablaHistorial")) {
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== "tablaHistorial") return true;
            if (!filtrosAplicados) return false;
            
            const filaTr = settings.aoData[dataIndex].nTr;
            const fechaFilaRaw = filaTr.getAttribute("data-fecha");
            const estadoFilaId = filaTr.getAttribute("data-estado-id");
            const categoriaFilaId = filaTr.getAttribute("data-categoria-id");
            const estadoSel = document.getElementById("filtroEstado").value;
            
            if (estadoSel !== "todos" && estadoFilaId !== estadoSel) return false;
            
            const elCategoria = document.getElementById("filtroCategoria");
            if (elCategoria) {
                const categoriaSel = elCategoria.value;
                if (categoriaSel !== "todos" && categoriaFilaId !== categoriaSel) return false;
            }
            
            const fInicioRaw = document.getElementById("filtroFechaInicio").value;
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