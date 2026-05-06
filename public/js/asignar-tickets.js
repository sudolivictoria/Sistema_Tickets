var table;

document.addEventListener("DOMContentLoaded", function () {

    inicializarTablaTickets('#tablaAsignarTickets');
    /**
     * Función de inicialización
     */
    function inicializarTablaTickets(selectorId) {
        const tableElement = $(selectorId);
        if (!tableElement.length) return;

        table = tableElement.DataTable({
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
                    previous: '<span class="material-symbols-outlined text-[20px] leading-none">chevron_left</span>',
                },
            },
            responsive: false, 
            autoWidth: false,
            pageLength: 5,
            order: [[6, "desc"]], 
            dom: 'rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
        });

  
        $("#inputBusqueda").on("keyup", function () {
            table.search(this.value).draw();
        });
    }

    /**
     * Gestión de Modal de detalles
     */
    window.verDetalle = function (asunto, descripcion) {
        const modal = document.getElementById("modalTicket");
        const titulo = document.getElementById("modalTitulo");
        const desc = document.getElementById("modalDescripcion");

        if (modal && titulo && desc) {
            titulo.innerText = asunto;
            desc.innerText = descripcion;
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
});