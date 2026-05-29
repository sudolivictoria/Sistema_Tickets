/**
 * Gestión de Manuales y Recursos
 */
//---filtro por categoría
window.filtrar = function (catId, event) {
    //---filtrar
    const tarjetas = document.querySelectorAll(".manual-card");

    tarjetas.forEach((tarjeta) => {
        const tarjetaCat = tarjeta.getAttribute("data-categoria");

        if (catId === "all" || tarjetaCat == catId) {
            tarjeta.style.display = "block";
            tarjeta.classList.add("animate-fade-in");
        } else {
            tarjeta.style.display = "none";
        }
    });
    //---estilos de botones
    const botones = document.querySelectorAll(".filter-btn");
    botones.forEach((btn) => {
        btn.classList.remove(
            "bg-[#04003B]",
            "text-white",
            "border-[#04003B]",
            "hover:text-white",
        );
        btn.classList.add(
            "bg-white",
            "text-slate-600",
            "border-slate-200",
            "hover:text-[#04003B]",
        );
    });
    //---destacar botón activo
    if (event) {
        const botonActivo = event.currentTarget;
        botonActivo.classList.remove(
            "bg-white",
            "text-slate-600",
            "border-slate-200",
            "hover:text-[#04003B]",
        );
        botonActivo.classList.add(
            "bg-[#04003B]",
            "text-white",
            "border-[#04003B]",
            "hover:text-white",
        );
    }
};

//--Visor de manuales
window.abrirVisor = function (url, titulo = "Recurso") {
    const ext = url.split(".").pop().toLowerCase();
    const visor = document.getElementById("contenedor-visor");
    const tituloVisor = document.getElementById("visor-titulo");
    const iconoVisor = document.getElementById("visor-icono");

    tituloVisor.innerText = titulo;
    visor.innerHTML = `<div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#04003B]"></div>`;
    //--cargar contenido
    if (ext === "pdf") {
        iconoVisor.innerText = "picture_as_pdf";
        setTimeout(() => {
            visor.innerHTML = `
              <iframe src="${url}#view=FitH&toolbar=1"
                class="w-full h-full border-none opacity-0 transition-opacity duration-500"
                onload="this.classList.remove('opacity-0')">
            </iframe>`;
        }, 400);
    } else {
        iconoVisor.innerText = "movie";
        visor.innerHTML = `
            <video controls autoplay class="max-w-full max-h-full shadow-2xl">
                <source src="${url}" type="video/mp4">
                Tu navegador no soporta videos.
            </video>`;
    }
    //---mostrar modal
    const modal = document.getElementById("modalVisor");
    modal.classList.remove("hidden");
    modal.classList.add("flex"); //--centrar el visor
    document.body.style.overflow = "hidden"; //---bloquear el scroll de fondo
};

//--Editar manual
window.editarManual = function (id, titulo, categoriaId) {
    const form = document.getElementById("formEditar");
    form.action = `/admin/manuales/${id}`;
    //---llenar campos del formulario
    document.getElementById("edit_titulo").value = titulo;
    document.getElementById("edit_categoria_id").value = categoriaId;
    document.getElementById("modalEditar").classList.remove("hidden");
};

//--Eliminar manual
window.eliminarManual = function (id) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "El archivo se eliminará permanentemente del servidor.",
        icon: "warning",
        iconColor: "#04003B",
        showCancelButton: true,
        confirmButtonColor: "#84cc16",
        cancelButtonColor: "#ef4444",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        customClass: { popup: "rounded-3xl" },
    }).then((result) => {
        if (result.isConfirmed) {
            //---delete manual via form submission
            const form = document.createElement("form");
            form.method = "POST";
            form.action = `/admin/manuales/${id}`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
};

//---helpers para abrir y cerrar modales
window.abrirModal = function (id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    } else {
        console.error("No se encontró el modal con ID: " + id);
    }
};

window.cerrarModal = function (id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};

window.cerrarVisor = function () {
    const modal = document.getElementById("modalVisor");
    const contenedor = document.getElementById("contenedor-visor");

    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
    if (contenedor) {
        contenedor.innerHTML = "";
    }
};
