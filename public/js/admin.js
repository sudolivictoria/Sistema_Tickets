//--filtro por estado
window.filtrarEstado = function(estado, btn) {
    //--resetear estilos de botones
    document.querySelectorAll('.filtro-btn').forEach(b => {
        b.classList.remove('bg-secondary', 'text-white', 'shadow-md');
        b.classList.add('bg-slate-100', 'text-slate-500');
    });
    
    //---aplicar estilos al botón seleccionado
    btn.classList.remove('bg-slate-100', 'text-slate-500');
    btn.classList.add('bg-secondary', 'text-white', 'shadow-md');

    //--pasar estado a función de filtrado
    ejecutarFiltros(estado.trim().toLowerCase());
};

//--aplicar filtros de forma más limpia
function ejecutarFiltros(estadoFiltro) {
    document.querySelectorAll('.ticket-fila').forEach(fila => {
        const estadoId = fila.dataset.estadoId?.trim().toLowerCase() ?? '';
        
        //--si el filtro es 'todos' o coincide con el estado del ticket, se muestra; de lo contrario, se oculta
        const ocultar = (estadoFiltro !== 'todos' && estadoId !== estadoFiltro);
        fila.classList.toggle('hidden', ocultar);
    });
}