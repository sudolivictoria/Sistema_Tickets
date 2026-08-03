//------variables globales-------
var table;
window.filtroSseActual = "todos";

/**
 * Inicializa DataTables con estilos Tailwind
 */
window.inicializarTablaTickets = function (selectorId) {
    const tableElement = $(selectorId);
    if (!tableElement.length) return;

    if ($.fn.DataTable.isDataTable(selectorId)) {
        $(selectorId).DataTable().destroy();
    }
    const templateNoData = `
        <div class="flex flex-col items-center justify-center h-[300px] bg-slate-50/40 rounded-2xl border-2 border-dashed border-slate-100 my-2 mx-2">
            <span class="material-symbols-outlined text-slate-300 text-4xl mb-2 select-none">folder_off</span>
            <h5 class="text-xs font-black uppercase text-slate-400 tracking-widest">Bandeja Vacía</h5>
            <p class="text-[11px] text-slate-400 font-medium mt-1">No existen tickets disponibles bajo este estado.</p>
        </div>
    `;
    table = tableElement.DataTable({
        paging: false,
        searching: true,
        info: false,
        responsive: true,
        autoWidth: false,
        order: [[0, "desc"]],
        stateSave: false,
        dom: "rt",
        language: {
            zeroRecords: templateNoData,
            emptyTable: templateNoData,
        },
    });
};

//=====================================================================
//                 MANEJO DE EVENTOS E INICIALIZACIÓN
//=====================================================================
$(document).ready(function () {
    //----deteccion automatica de la tabla
    const selectorTabla = $("#tablaAdmin").length
        ? "#tablaAdmin"
        : "#tablaGestor";
    window.inicializarTablaTickets(selectorTabla);

    //---abrir detalle-------
    $(document)
        .off("click", ".btn-ver-detalle")
        .on("click", ".btn-ver-detalle", function () {
            const $btn = $(this);

            //-----implementación centralizada
            window.verDetalle({
                idTicket: $btn.data("id"),
                asunto: $btn.data("asunto"),
                descripcion: $btn.data("descripcion"),
                tipoNombre: $btn.data("tipo"),
                fechaApertura: $btn.data("fecha"), //---estos dashboard si incluyen fechas
                drive: $btn.data("drive"),
                estadoNombre: $btn.data("estado"),
                datosSLA: {
                    estadoNombre: $btn.data("estado"),
                    fechaLimite: $btn.data("fecha-limite"),
                    tiempoRespuesta: $btn.data("tiempo-respuesta"),
                },
            });
        });

    //----perfil de usuario--------
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

    //----desplegable canales directos---------->
    $(document).on("click", "#toggle-canales", function () {
        const list = document.getElementById("canales-list");
        const icon = this.querySelector(".material-symbols-outlined");

        if (list) {
            if (list.style.display === "none" || list.style.display === "") {
                list.style.display = "block";
                if (icon) icon.textContent = "expand_less";
            } else {
                list.style.display = "none";
                if (icon) icon.textContent = "expand_more";
            }
        }
    });
});

/****************** FILTROS ******************/
window.filtrarEstado = function (estado, btn) {
    $(".filtro-btn")
        .removeClass("bg-secondary text-white shadow-md")
        .addClass("bg-slate-100 text-slate-500");
    $(btn)
        .removeClass("bg-slate-100 text-slate-500")
        .addClass("bg-secondary text-white shadow-md");
    window.filtroSseActual = estado;
    if (typeof window.AutoRefresco !== "undefined") {
        window.AutoRefresco.forzarRefresco();
    }
};
