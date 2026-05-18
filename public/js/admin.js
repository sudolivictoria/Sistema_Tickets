document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggle-canales");
    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            const list = document.getElementById("canales-list");
            const icon = this.querySelector(".material-symbols-outlined");
            const isHidden =
                list.style.display === "none" || list.style.display === "";

            list.style.display = isHidden ? "block" : "none";
            icon.textContent = isHidden ? "expand_less" : "expand_more";
        });
    }

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
                elLinkCorreo.classList.remove(
                    "opacity-50",
                    "pointer-events-none",
                );
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
});

window.ejecutarFiltros = function (estadoSeleccionado) {
    const estadoFiltro = String(estadoSeleccionado).trim().toLowerCase();

    document.querySelectorAll(".ticket-fila").forEach((fila) => {
        const estadoIdFila = String(fila.dataset.estadoId || "")
            .trim()
            .toLowerCase();
        const ocultar =
            estadoFiltro !== "todos" && estadoIdFila !== estadoFiltro;
        fila.classList.toggle("hidden", ocultar);
    });
}

