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
});

//---funciones para ver detalles de los tickets
window.verDetalle = function (asunto, descripcion) {
    const modal = document.getElementById("modalTicket");
    const modalTitulo = document.getElementById("modalTitulo");
    const modalDescripcion = document.getElementById("modalDescripcion");

    if (modal && modalTitulo && modalDescripcion) {
        modalTitulo.innerText = asunto;
        modalDescripcion.innerText = descripcion;
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
