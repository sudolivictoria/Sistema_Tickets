//----desplegable de canales directos
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggle-canales");
    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            const list = document.getElementById("canales-list");
            const icon = this.querySelector(".material-symbols-outlined");

            if (list.style.display === "none" || list.style.display === "") {
                list.style.display = "block";
                icon.textContent = "expand_less";
            } else {
                list.style.display = "none";
                icon.textContent = "expand_more";
            }
        });
    }

    $(document).on("click", ".btn-ver-detalle", function () {
        const asunto = $(this).data("asunto");
        const descripcion = $(this).data("descripcion");
        const tipo = $(this).data("tipo");
        const fecha = $(this).data("fecha");
        verDetalle(asunto, descripcion, tipo, fecha);
    });

    // 3. Clics seguros para ver usuario (Delegación de eventos)
    $(document).on("click", ".btn-ver-usuario", function () {
        const nombre = $(this).data("nombre");
        const email = $(this).data("email");
        const unidad = $(this).data("unidad");
        const cargo = $(this).data("cargo");
        const telefono = $(this).data("telefono");
        verUsuario(nombre, email, unidad, cargo, telefono);
    });
});

//----ejecutar filtros
window.ejecutarFiltros = function (estadoSeleccionado) {
    const estadoFiltro = String(estadoSeleccionado).trim().toLowerCase();
    const listaEstados = estadoFiltro.split(",");

    document.querySelectorAll(".ticket-fila").forEach((fila) => {
        const estadoIdFila = String(fila.dataset.estadoId || "")
            .trim()
            .toLowerCase();
        const coincide =
            estadoFiltro === "todos" || listaEstados.includes(estadoIdFila);
        fila.classList.toggle("hidden", !coincide);
    });
};

//--filtros por estado
window.filtrarEstado = function (estado, btn) {
    //--resetear estilos
    document.querySelectorAll(".filtro-btn").forEach((b) => {
        b.classList.remove("bg-secondary", "text-white", "shadow-md");
        b.classList.add("bg-slate-100", "text-slate-500");
    });

    //--destacar botón seleccionado
    if (btn) {
        btn.classList.remove("bg-slate-100", "text-slate-500");
        btn.classList.add("bg-secondary", "text-white", "shadow-md");
    }

    //--ejecutar filtrado
    ejecutarFiltros(estado);
};

/**
 * Gestión de Modal de detalles
 */
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

//---cerrar modal
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
