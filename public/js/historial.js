var tableHistorial;
//----no cargar datos al inicio
var filtrosAplicados = false;

window.inicializarHistorialDataTable = function () {
    if ($.fn.DataTable.isDataTable("#tablaHistorial")) {
        $("#tablaHistorial").DataTable().destroy();
    }

    //---data table configuracion exaxta
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

    tableHistorial.draw();
};

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector("#tablaHistorial")) {
        //----filtros
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== "tablaHistorial") return true;
            //---no mostrar nada hasta ver los filtros
            if (!filtrosAplicados) {
                return false;
            }
            //---obtener el elemento de filtrado
            const filaTr = settings.aoData[dataIndex].nTr;
            const fechaFilaRaw = filaTr.getAttribute("data-fecha");
            const estadoFilaId = filaTr.getAttribute("data-estado-id");
            const categoriaFilaId = filaTr.getAttribute("data-categoria-id");
            //---filtro del estado
            const estadoSel = document.getElementById("filtroEstado").value;
            if (estadoSel !== "todos" && estadoFilaId !== estadoSel) {
                return false;
            }
            //----categoria
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
            //---fechas
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
        //--inicializacion
        window.inicializarHistorialDataTable();
    }
    //---modal detalle delegación eventos
    $("#tablaHistorial").on("click", ".btn-ver-detalle", function () {
        const asunto = $(this).data("asunto");
        const descripcion = $(this).data("descripcion");
        const tipo = $(this).data("tipo");
        const fecha = $(this).data("fecha");
        verDetalle(asunto, descripcion, tipo, fecha);
    });
    $("#tablaHistorial").on("click", ".btn-ver-usuario", function () {
        const nombre = $(this).data("nombre");
        const email = $(this).data("email");
        const unidad = $(this).data("unidad");
        const cargo = $(this).data("cargo");
        const telefono = $(this).data("telefono");
        verUsuario(nombre, email, unidad, cargo, telefono);
    });
});
//--------filtrado
window.aplicarFiltrosHistorial = function () {
    if (!tableHistorial) return;
    filtrosAplicados = true;
    //---filtrado buscador
    const textoBuscar = document.getElementById("filtroBuscar").value;
    tableHistorial.search(textoBuscar);
    //---redibujar la tabla
    tableHistorial.draw();
};
//---limpar filtros
window.limpiarFiltrosHistorial = function () {
    if (!tableHistorial) return;
    filtrosAplicados = false;
    //---resetear inputs
    document.getElementById("filtroBuscar").value = "";
    document.getElementById("filtroFechaInicio").value = "";
    document.getElementById("filtroFechaFin").value = "";
    document.getElementById("filtroEstado").value = "todos";
    document.getElementById("filtroCategoria").value = "todos";
    tableHistorial.search("").draw(); //--vista vacia
};
//----DETALLE TICKET Y USUARIO---
window.verDetalle = function (asunto, descripcion, tipoNombre, fechaApertura) {
    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    const fecha = document.getElementById("modalFechaApertura");
    if (modal && titulo && desc && tipo && fecha) {
        titulo.innerText = asunto;
        desc.innerText = descripcion;
        tipo.innerText = tipoNombre;
        fecha.innerText = fechaApertura;
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
//-------------REFRESCO HISTORIAL------------------
window.refrescoHistorial = function () {
    const tablaBody = document.getElementById("tablaBody");
    if (!tablaBody) return;
    const modalAbierto = document.querySelector(
        ".modal:not(.hidden), #modalTicket:not(.hidden), #modalUsuario:not(.hidden)",
    );
    const buscador = document.getElementById("filtroBuscar");
    if (modalAbierto || (buscador && buscador === document.activeElement))
        return;
    const tablaElement = tablaBody.closest("table");
    let textoAntes = buscador ? buscador.value : "";
    fetch(`/api/refresh-table?tipo=historial`)
        .then((res) => res.json())
        .then((data) => {
            if (tablaElement && $.fn.DataTable.isDataTable(tablaElement)) {
                $("#tablaHistorial").DataTable().destroy();
                tablaBody.innerHTML = data.html;
                if (
                    typeof window.inicializarHistorialDataTable === "function"
                ) {
                    window.inicializarHistorialDataTable();
                }
                //----si el usuario ya estaba filtrando mantner texto
                if (textoAntes && buscador) {
                    buscador.value = textoAntes;
                    if (typeof filtrosAplicados !== "undefined")
                        filtrosAplicados = true;
                    $("#tablaHistorial").DataTable().search(textoAntes).draw();
                }
            }
            //----actualizar metricas
            if (
                data.cargaTrabajo !== undefined &&
                document.getElementById("metric-carga-trabajo")
            ) {
                document.getElementById("metric-carga-trabajo").textContent =
                    data.cargaTrabajo;
            }
            if (
                data.resueltos24h !== undefined &&
                document.getElementById("metric-resueltos-24h")
            ) {
                document.getElementById("metric-resueltos-24h").textContent =
                    data.resueltos24h;
            }
            if (
                data.tasaCierre !== undefined &&
                document.getElementById("metric-tasa-cierre")
            ) {
                document.getElementById("metric-tasa-cierre").textContent =
                    data.tasaCierre;
            }
        })
        .catch((err) => console.error("Error al refrescar historial:", err));
};
