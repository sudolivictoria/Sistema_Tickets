/**
 * Manuales y Recursos 
 */

window.categoriaActivaActual = "all"; //---------guarda de forma global qué filtro tiene el usuario


document.addEventListener("DOMContentLoaded", function () {
    const modalVisor = document.getElementById("modalVisor");
    if (modalVisor) {
        document.body.appendChild(modalVisor);
    }

    //---Obtener el filtrado desde la URL al cargar---
    const params = new URLSearchParams(window.location.search);
    const catId = params.get("categoria");

    if (catId) {
        setTimeout(() => {
            window.filtrar(catId);
        }, 100);
    }
});

window.filtrar = function (catId, event) {
    window.categoriaActivaActual = catId; //---------Actualiza la categoría activa
    const tarjetas = document.querySelectorAll(".manual-card");

    //---filtrar tarjetas
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

    let botonActivo;

    //---Filtrado desde evento o url-----
    if (event && event.currentTarget) {
        botonActivo = event.currentTarget;
    } else {
        botonActivo = document.querySelector(`.filter-btn[data-id="${catId}"]`);
    }

    if (botonActivo) {
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

//----------------------Visor de manuales-------------------------
window.abrirVisor = function (url, titulo = "Recurso") {
    const ext = url.split(".").pop().toLowerCase();
    const visor = document.getElementById("contenedor-visor");
    const tituloVisor = document.getElementById("visor-titulo");
    const iconoVisor = document.getElementById("visor-icono");

    //--detectar tipo de dispositivo
    const esMovil = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    if (tituloVisor) tituloVisor.innerText = titulo;
    if (!visor) return;

    visor.innerHTML = `<div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#04003B]"></div>`;

    //--cargar contenido----------
    if (ext === "pdf") {
        if (iconoVisor) iconoVisor.innerText = "picture_as_pdf";

        //--si es movil abre en pestaña nueva para evitar bloqueos
        if (esMovil) {
            window.open(url, "_blank");
            window.cerrarVisor();
            return;
        } else {
            setTimeout(() => {
                visor.innerHTML = `
                <iframe src="${url}#view=FitH&toolbar=1"
                    class="w-full h-full border-none opacity-0 transition-opacity duration-500"
                    onload="this.classList.remove('opacity-0')">
                </iframe>`;
            }, 400);
        }
    } else {
        if (iconoVisor) iconoVisor.innerText = "movie";
        visor.innerHTML = `
            <video controls autoplay class="max-w-full max-h-full shadow-2xl">
                <source src="${url}" type="video/mp4">
                Tu navegador no soporta videos.
            </video>`;
    }

    const modal = document.getElementById("modalVisor");
    if (modal) {
        modal.classList.remove("hidden");
        modal.classList.add("flex"); //--centrar el visor
        document.body.style.overflow = "hidden"; //---bloquear el scroll de fondo
    }
};

window.cerrarVisor = function () {
    const modal = document.getElementById("modalVisor");
    const contenedor = document.getElementById("contenedor-visor");

    if (modal) {
        //-------------Solo restauramos el scroll si el modal realmente estaba desplegado
        if (!modal.classList.contains("hidden")) {
            document.body.style.overflow = "auto";
        }
        modal.classList.remove("flex");
        modal.classList.add("hidden");
    }
    if (contenedor) {
        contenedor.innerHTML = "";
    }
};

//----Cerrar también presionando la tecla Escape por comodidad del usuario ---
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        window.cerrarVisor();
    }
});