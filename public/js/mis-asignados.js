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

    table = tableElement.DataTable({
        stateSave: true,
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
        order: [[0, "desc"]],
        dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
    });

    $("#inputBusqueda")
        .off("keyup")
        .on("keyup", function () {
            table.search(this.value).draw(false);
        });

    $(document).on("click", ".btn-ver-detalle", function () {
        const asunto = $(this).data("asunto");
        const descripcion = $(this).data("descripcion");
        const tipo = $(this).data("tipo");
        verDetalle(asunto, descripcion, tipo);
    });

    $(document).on("click", ".btn-ver-usuario", function () {
        const nombre = $(this).data("nombre");
        const email = $(this).data("email");
        const unidad = $(this).data("unidad");
        const cargo = $(this).data("cargo");
        const telefono = $(this).data("telefono");
        verUsuario(nombre, email, unidad, cargo, telefono);
    });
};

/****************** FILTROS ******************/
window.filtrarEstado = function (estado, btn) {
    //--actualizar estilos de botones
    $(".filtro-btn")
        .removeClass("bg-secondary text-white shadow-md")
        .addClass("bg-slate-100 text-slate-500");
    $(btn)
        .removeClass("bg-slate-100 text-slate-500")
        .addClass("bg-secondary text-white shadow-md");

    let valorBusqueda = "";
    if (estado !== "todos") {
        const estados = String(estado)
            .split(",")
            .map((e) => e.trim());

        valorBusqueda = `(${estados.join("|")})`;
    }
    //---filtros de estado
    table.column(2).search(valorBusqueda, true, false, true).draw();
};

/**
 * Gestión de Modal de detalles
 */
window.verDetalle = function (asunto, descripcion, tipoNombre) {
    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    if (modal && titulo && desc && tipo) {
        titulo.innerText = asunto;
        desc.innerText = descripcion;
        tipo.innerText = tipoNombre;
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

//------------------AUTO REFRESCO-----------------
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector("#tablaMisAsignados")) {
        window.inicializarTablaTickets("#tablaMisAsignados");
    }
});

//-------funcion dry---------------
function procesarAccionTicket(btn, config) {
    const form = btn.closest("form");
    const url = form.action;

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
            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "X-Requested-With": "XMLHttpRequest",
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(new FormData(form)),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        autoRefrescoUniversal();
                        Swal.fire({
                            title: config.tituloExito,
                            text: data.message,
                            icon: "success",
                            iconColor: "#84cc16",
                            timer: 3000,
                            showConfirmButton: false,
                            customClass: { popup: "rounded-3xl" },
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    Swal.fire("Error", "No se pudo procesar la solicitud", "error");
                });
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
        tituloExito: "¡Ticket Resuelto!"
    });
};

window.confirmarEquivocado = function (btn) {
    procesarAccionTicket(btn, {
        tituloConfirmacion: "¿Marcar como Equivocado?",
        textoBoton: "Sí, marcar",
        tituloExito: "¡Ticket Marcado como Equivocado!"
    });
};

window.confirmarNoCorresponde = function (btn) {
    procesarAccionTicket(btn, {
        tituloConfirmacion: "¿Marcar No Corresponde?",
        textoBoton: "Sí, marcar",
        tituloExito: "¡Ticket Marcado como No Corresponde!"
    });
};