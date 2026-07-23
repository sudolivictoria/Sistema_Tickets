// =====================================================================
//                 GESTIÓN DE COMENTARIOS EN TIEMPO REAL
// =====================================================================

let canalEchoActual = null;

/**
 * Escucha eventos en tiempo real (Reverb) para el ticket activo
 */
window.escucharComentariosWebSocket = function (idTicket) {
    if (!window.Echo) {
        console.warn("[Reverb] Echo no está inicializado globalmente.");
        return;
    }

    //---limpiar escucha previa si la hubiera
    window.desconectarComentariosWebSocket();

    canalEchoActual = idTicket;

    //----escuchar el canal público del ticket (new Channel('ticket.' . $id))
    window.Echo.channel(`ticket.${idTicket}`)
        .listen('.comentario.creado', (data) => {
            if (data && data.comentario) {
                window.agregarComentarioAlModal(data.comentario);
            }
        });
};

/**
 * Abandona el canal al cerrar el modal o cambiar de ticket
 */
window.desconectarComentariosWebSocket = function () {
    if (window.Echo && canalEchoActual) {
        window.Echo.leaveChannel(`ticket.${canalEchoActual}`);
        canalEchoActual = null;
    }
};

/**
 * Carga inicial de comentarios vía HTTP
 */
window.cargarComentariosDelTicket = function (idTicket, estadoNombre) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");
    const $formularioComentario = $("#form-comentario-modal");

    if (!idTicket) return;

    const estadosCerradosTextos = ["resuelto", "equivocado", "no corresponde", "cerrado"];
    const estadoStr = String(estadoNombre || "").toLowerCase().trim();
    const esCerradoPorTexto = estadosCerradosTextos.includes(estadoStr);

    if (esCerradoPorTexto) {
        $formularioComentario.hide();
    } else {
        $formularioComentario.show();
    }

    $.get(`/tickets/${idTicket}/comentarios`)
        .done(function (comentarios) {
            $lista.empty();

            if (!comentarios || comentarios.length === 0) {
                $seccionHistorico.hide();
                $("#preloaderGlobalModal").addClass("hidden");
                return;
            }

            $seccionHistorico.show();

            const fragment = document.createDocumentFragment();

            comentarios.forEach((com) => {
                const bg = com.es_privado
                    ? "bg-lime-50/80 border-lime-300"
                    : "bg-white border-slate-200";
                const tag = com.es_privado
                    ? '<span class="text-green-700 font-bold">[Nota Interna]</span> '
                    : "";

                const item = document.createElement("div");
                item.className = `p-2 rounded-xl border ${bg}`;
                if (com.id) item.setAttribute("data-comentario-id", com.id);

                item.innerHTML = `
                    <div class="flex justify-between font-bold text-green-950 mb-0.5">
                        <span>${tag}${com.user ? com.user.name : "Usuario"}</span>
                        <span class="text-[10px] text-slate-400 font-normal">${com.tiempo_legible || ""}</span>
                    </div>
                    <p class="text-slate-600 font-medium">${com.contenido}</p>
                `;
                fragment.appendChild(item);
            });

            $lista[0].appendChild(fragment);
            $lista.scrollTop($lista[0].scrollHeight);
        })
        .fail(function (err) {
            console.error("Error al obtener comentarios:", err);
        })
        .always(function () {
            $("#preloaderGlobalModal").addClass("hidden");
        });
};

/**
 * Agrega dinámicamente un comentario al contenedor con animación y control de duplicados
 */
window.agregarComentarioAlModal = function (comentario) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");
    if (!$lista.length) return;

    //----evitar duplicados
    if (comentario.id && $lista.find(`[data-comentario-id="${comentario.id}"]`).length > 0) {
        return;
    }

    $seccionHistorico.show();

    const bg = comentario.es_privado
        ? "bg-lime-50/80 border-lime-300"
        : "bg-white border-slate-200";
        
    const tag = comentario.es_privado
        ? '<span class="text-green-700 font-bold">[Nota Interna]</span> '
        : "";

    const dataAttr = comentario.id ? `data-comentario-id="${comentario.id}"` : "";

    const elComentario = `
        <div ${dataAttr} class="p-2 rounded-xl border ${bg} transition-all duration-300">
            <div class="flex justify-between font-bold text-green-950 mb-0.5">
                <span>${tag}${comentario.user ? comentario.user.name : "Usuario"}</span>
                <span class="text-[10px] text-slate-400 font-normal">${comentario.tiempo_legible || "Ahora mismo"}</span>
            </div>
            <p class="text-slate-600 font-medium">${comentario.contenido}</p>
        </div>
    `;

    $lista.append(elComentario);
    $lista.scrollTop($lista[0].scrollHeight);
};