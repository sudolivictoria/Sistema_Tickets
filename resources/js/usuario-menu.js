const initSidebar = () => {
    const menuToggle = document.getElementById("menu-toggle");
    const menuIcon = document.getElementById("menu-icon");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebar-overlay");

    //---precaucion para no rompel el codigo
    if (!menuToggle || !sidebar || !overlay) return;

    //--funciones para abrir y cerrar el menu
    const abrirMenu = () => {
        sidebar.classList.remove("-translate-x-full");
        overlay.classList.remove("hidden");
        if (menuIcon) {
            menuIcon.textContent = "close";
            menuIcon.classList.replace("text-primary", "text-white");
        }
    };

    const cerrarMenu = () => {
        sidebar.classList.add("-translate-x-full");
        overlay.classList.add("hidden");
        if (menuIcon) {
            menuIcon.textContent = "menu";
            menuIcon.classList.replace("text-white", "text-primary");
        }
    };

    menuToggle.addEventListener("click", () => {
        const isOpen = !sidebar.classList.contains("-translate-x-full");
        isOpen ? cerrarMenu() : abrirMenu();
    });

    overlay.addEventListener("click", cerrarMenu);

    window.addEventListener("resize", () => {
        if (window.innerWidth >= 1024) {
            cerrarMenu();
        }
    });
};

//----ejecuta solo si el dom esta listo
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initSidebar);
} else {
    initSidebar();
}


//-------OBSERVER
const observarModalTicket = () => {
    const modal = document.getElementById("modalTicket");
    const header = document.getElementById("headerUsuario"); 

    if (!modal || !header) return;
    const observer = new MutationObserver(() => {
        if (modal.classList.contains("hidden")) {
            header.classList.remove("hidden");
        } else {
            header.classList.add("hidden");    
        }
    });
    observer.observe(modal, { attributes: true, attributeFilter: ["class"] });
};
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", observarModalTicket);
} else {
    observarModalTicket();
}