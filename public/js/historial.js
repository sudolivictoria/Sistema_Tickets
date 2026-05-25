var tableHistorial;

document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector("#tablaHistorial")) {
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
            pageLength: 10,
            order: [[0, "desc"]],
            dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
        });

        $("#inputBusqueda")
            .off("keyup")
            .on("keyup", function () {
                if (tableHistorial) {
                    tableHistorial.search(this.value).draw();
                }
            });
    }

    $("#tablaHistorial").on("click", ".btn-ver-detalle", function () {
        const asunto = $(this).data("asunto");
        const descripcion = $(this).data("descripcion");
        const tipo = $(this).data("tipo");
        const fecha = $(this).data("fecha");
        verDetalle(asunto, descripcion, tipo, fecha);
    });

    $("#filtroEstado").on("change", function () {
        if (!tableHistorial) return;

        const valorSelect = this.value; //"todos", "1", "2" o "3,4,5"

        if (valorSelect === "todos") {
            tableHistorial.column(3).search("").draw();
        } else {
            const estados = valorSelect.split(",").map((e) => e.trim());
            const valorBusqueda = `(${estados.join("|")})`;
            tableHistorial
                .column(3)
                .search(valorBusqueda, true, false, true)
                .draw();
        }
    });

    $("#filtroCategoria").on("change", function () {
        if (!tableHistorial) return;

        const categoriaSeleccionada = this.value;

        if (categoriaSeleccionada === "todas") {
            tableHistorial.column(6).search("").draw();
        } else {
            tableHistorial
                .column(6)
                .search("^" + categoriaSeleccionada + "$", true, false)
                .draw();
        }
    });
});

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

window.cerrarModalUsuario = function () {
    const modal = document.getElementById("modalUsuario");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};
