// =====================================================================
//                    NÚCLEO CENTRALIZADO DE TICKETS
// =====================================================================

// ----------------------------Variables Globales----------------------->
window.timerSLA = null;
window.ticketIdActual = null;
window.ticketEstadoActual = null;
window.canalEchoActual = null;

// ---------------------------------------------------------------------
//              GESTIÓN DE WEB SOCKETS (REVERB)
// ---------------------------------------------------------------------
window.escucharComentariosWebSocket = function (idTicket) {
    if (typeof Echo === "undefined") {
        console.warn("[Reverb] Echo no está inicializado globalmente.");
        return;
    }
    window.desconectarComentariosWebSocket();
    window.canalEchoActual = idTicket;

    // Canal privado: requiere autorización vía /broadcasting/auth (routes/channels.php)
    Echo.private(`ticket.${idTicket}`).listen(".comentario.creado", (e) => {
        if (e && e.comentario) {
            window.agregarComentarioAlModal(e.comentario);
        }
    });
};

window.desconectarComentariosWebSocket = function () {
    if (typeof Echo !== "undefined" && window.canalEchoActual) {
        //---.leave() limpia tanto el canal público como el privado/presence, evitando fugas de suscripción
        Echo.leave(`ticket.${window.canalEchoActual}`);
        window.canalEchoActual = null;
    }
};

// ---------------------------------------------------------------------
//            GESTIÓN DE COMENTARIOS (CARGA Y RENDERIZADO)
// ---------------------------------------------------------------------
window.cargarComentariosDelTicket = function (idTicket, estadoNombre) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");
    const $formularioComentario = $("#form-comentario-modal");

    if (!idTicket) return;

    const estadosCerrados = [
        "resuelto",
        "equivocado",
        "no corresponde",
        "cerrado",
    ];
    const estadoStr = String(estadoNombre || "")
        .toLowerCase()
        .trim();

    if (estadosCerrados.includes(estadoStr)) {
        $formularioComentario.hide();
    } else {
        $formularioComentario.show();
    }

    $.get(`/tickets/${idTicket}/comentarios`)
        .done(function (comentarios) {
            $lista.empty();

            const esVistaAdmin = document.getElementById("es_privado") !== null;

            const comentariosVisibles = esVistaAdmin
                ? comentarios || []
                : (comentarios || []).filter(
                      (com) => !com.es_privado && com.es_privado != 1,
                  );

            if (comentariosVisibles.length === 0) {
                $seccionHistorico.hide();
                $("#preloaderGlobalModal").addClass("hidden");
                return;
            }

            $seccionHistorico.show();
            const fragment = document.createDocumentFragment();

            comentariosVisibles.forEach((com) => {
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
        .fail((err) => console.error("Error al obtener comentarios:", err))
        .always(() => $("#preloaderGlobalModal").addClass("hidden"));
};

//-----------------------------------------------------------------------//
//---------------------------AGREGAR COMENTARIO--------------------------//
//-----------------------------------------------------------------------//

window.agregarComentarioAlModal = function (comentario) {
    const $lista = $("#modalListaComentarios");
    const $seccionHistorico = $("#seccion-historico-comentarios");
    if (!$lista.length) return;
    //----------LOGICA COMENTARIOS---------------------------------------->
    const esPrivado =
        comentario.es_privado == 1 ||
        comentario.es_privado === true ||
        comentario.es_privado === "true";
    const esVistaAdmin = document.getElementById("es_privado") !== null;

    if (esPrivado && !esVistaAdmin) return;

    if (
        comentario.id &&
        $lista.find(`[data-comentario-id="${comentario.id}"]`).length > 0
    )
        return;

    $seccionHistorico.show();

    const bg = esPrivado
        ? "bg-lime-50/80 border-lime-300"
        : "bg-white border-slate-200";
    const tag = esPrivado
        ? '<span class="text-green-700 font-bold">[Nota Interna]</span> '
        : "";
    const dataAttr = comentario.id
        ? `data-comentario-id="${comentario.id}"`
        : "";

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

// ---------------------------------------------------------------------
//              ENVÍO DE COMENTARIOS (AJAX CENTRALIZADO)
// ---------------------------------------------------------------------
//---Hallazgo B6: .off() antes de .on(), igual que el resto de handlers delegados
//---del proyecto, por si este archivo llegara a cargarse más de una vez
$(document)
    .off("submit", "#form-comentario-modal")
    .on("submit", "#form-comentario-modal", function (e) {
    e.preventDefault();
    if (!window.ticketIdActual) return;

    const $inputContenido = $("#contenido-comentario");
    const contenido = $inputContenido.val().trim();
    if (contenido === "") return;

    const $checkboxPrivado = $("#es_privado");
    const esPrivado =
        $checkboxPrivado.length && $checkboxPrivado.is(":checked") ? 1 : 0;
    const $btnSubmit = $(this).find('button[type="submit"]');
    const textoOriginal = $btnSubmit.html();

    $btnSubmit
        .prop("disabled", true)
        .addClass("opacity-75 cursor-not-allowed")
        .html(
            '<span class="inline-block animate-spin mr-2">⏳</span> Guardando...',
        );

    $.ajax({
        url: `/tickets/${window.ticketIdActual}/comentarios`,
        method: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            contenido: contenido,
            es_privado: esPrivado,
        },
    })
        .done(function (response) {
            if (response.success || response.comentario) {
                $inputContenido.val("");
                if ($checkboxPrivado.length)
                    $checkboxPrivado.prop("checked", false);
                window.agregarComentarioAlModal(
                    response.comentario || response,
                );
            }
        })
        .fail(() =>
            alert("Ocurrió un error al intentar publicar el comentario."),
        )
        .always(() =>
            $btnSubmit
                .prop("disabled", false)
                .removeClass("opacity-75 cursor-not-allowed")
                .html(textoOriginal),
        );
});

// ---------------------------------------------------------------------
//              CONTROL DE MODALES (TICKETS Y USUARIOS)
// ---------------------------------------------------------------------

window.verDetalle = function (datos) {
    window.ticketIdActual = datos.idTicket;
    window.ticketEstadoActual = datos.estadoNombre;

    const modal = document.getElementById("modalTicket");
    const titulo = document.getElementById("modalTitulo");
    const desc = document.getElementById("modalDescripcion");
    const tipo = document.getElementById("modalTipoSolicitud");
    const fecha = document.getElementById("modalFechaApertura");
    const wrapper = document.getElementById("wrapperDriveLink");
    const linkAnchor = document.getElementById("modalDriveLink");

    $("#preloaderGlobalModal").removeClass("hidden");

    if (modal && titulo && desc && tipo) {
        if (modal.parentElement !== document.body)
            document.body.appendChild(modal);

        titulo.textContent = datos.asunto || "";
        desc.textContent = datos.descripcion || "";
        tipo.textContent = datos.tipoNombre || "";
        if (fecha && datos.fechaApertura)
            fecha.textContent = datos.fechaApertura;

        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    if (
        datos.drive &&
        String(datos.drive).trim() !== "" &&
        datos.drive !== "null"
    ) {
        const pathLimpio = String(datos.drive).startsWith("/")
            ? String(datos.drive).substring(1)
            : datos.drive;
        if (linkAnchor)
            linkAnchor.href = `${window.location.origin}/storage/${pathLimpio}`;
        if (wrapper) wrapper.classList.remove("hidden");
    } else {
        if (linkAnchor) linkAnchor.href = "#";
        if (wrapper) wrapper.classList.add("hidden");
    }

    $("#contenido-comentario").val("");
    if ($("#es_privado").length) $("#es_privado").prop("checked", false);

    window.cargarComentariosDelTicket(
        window.ticketIdActual,
        window.ticketEstadoActual,
    );
    window.escucharComentariosWebSocket(window.ticketIdActual);

    if (datos.datosSLA && typeof window.iniciarContadorSLA === "function") {
        window.iniciarContadorSLA(datos.datosSLA);
    }
};
//------------cierre modal---------------------------------------------->
window.cerrarModal = function () {
    const modal = document.getElementById("modalTicket");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
        if (window.timerSLA) clearInterval(window.timerSLA);
        window.ticketIdActual = null;
        window.desconectarComentariosWebSocket();
    }
};

//----------------------------PERFIL USUARIO-------------------------------->
window.verUsuario = function (name, email, unidad, cargo, telefono) {
    const modal = document.getElementById("modalUsuario");
    if (!modal) return;

    const setElemText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text || "---";
    };

    setElemText("userNombre", name);
    setElemText("userEmail", email);
    setElemText("userUnidad", unidad);
    setElemText("userCargo", cargo);
    setElemText("userTelefono", telefono);
    //-------------gmail---------------------------------------->
    const elLinkCorreo = document.getElementById("linkCorreo");
    if (elLinkCorreo) {
        if (email && email !== "---") {
            elLinkCorreo.href = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}&su=${encodeURIComponent("Consulta sobre su Ticket")}&body=${encodeURIComponent(`Hola ${name || ""},`)}`;
            elLinkCorreo.classList.remove("opacity-50", "pointer-events-none");
        } else {
            elLinkCorreo.href = "javascript:void(0)";
            elLinkCorreo.classList.add("opacity-50", "pointer-events-none");
        }
    }
    modal.classList.remove("hidden");
    document.body.style.overflow = "hidden";
};

//---------------cierre modal usuario
window.cerrarModalUsuario = function () {
    const modal = document.getElementById("modalUsuario");
    if (modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
    }
};

// ---------------------------------------------------------------------
// -----------------------CRONÓMETRO SLA-------------------------------
// ---------------------------------------------------------------------
window.iniciarContadorSLA = function (datosSLA) {
    if (window.timerSLA) clearInterval(window.timerSLA);

    const wrapper = document.getElementById("wrapperCountdown");
    const display = document.getElementById("modalCountdown");
    if (!wrapper || !display) return;

    const { estadoNombre, fechaLimite, tiempoRespuesta } = datosSLA;
    const segundosTranscurridos = Number(tiempoRespuesta) || 0;
    const estadoCheck = String(estadoNombre || "")
        .toLowerCase()
        .trim();
    const estadosCerrados = ["resuelto", "equivocado", "no corresponde"];

    if (estadosCerrados.includes(estadoCheck) || segundosTranscurridos > 0) {
        wrapper.classList.remove("hidden");
        wrapper.className =
            "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-green-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300";

        if (segundosTranscurridos === 0) {
            display.textContent = "Finalizado";
            return;
        }
        const dias = Math.floor(segundosTranscurridos / 86400);
        const horas = Math.floor((segundosTranscurridos % 86400) / 3600);
        const minutos = Math.floor((segundosTranscurridos % 3600) / 60);
        const segundos = segundosTranscurridos % 60;

        display.textContent =
            dias > 0
                ? `Respuesta: ${dias}d ${horas}h ${minutos}m`
                : horas > 0
                  ? `Respuesta: ${horas}h ${minutos}m`
                  : minutos > 0
                    ? `Respuesta: ${minutos}m ${segundos}s`
                    : `Respuesta: ${segundos}s`;
        return;
    }

    if (!fechaLimite) {
        wrapper.classList.add("hidden");
        return;
    }

    wrapper.classList.remove("hidden");
    const limite = new Date(fechaLimite).getTime();

    const tick = () => {
        const restante = limite - Date.now();
        if (restante <= 0) {
            clearInterval(window.timerSLA);
            display.textContent = "Vencido";
            wrapper.className =
                "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-red-50 border border-red-200 rounded-full text-red-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300 animate-pulse";
            return;
        }

        wrapper.className =
            "absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-green-600 font-bold text-xs uppercase tracking-wider shadow-sm transition-all duration-300";

        const dias = Math.floor(restante / (1000 * 60 * 60 * 24));
        const horas = Math.floor(
            (restante % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
        );
        const minutos = Math.floor((restante % (1000 * 60 * 60)) / (1000 * 60));
        const segundos = Math.floor((restante % (1000 * 60)) / 1000);
        const pad = (num) => String(num).padStart(2, "0");

        display.textContent =
            dias > 0
                ? `${dias}d ${pad(horas)}h ${pad(minutos)}m`
                : horas > 0
                  ? `${horas}h ${pad(minutos)}m ${pad(segundos)}s`
                  : minutos > 0
                    ? `${minutos}m ${pad(segundos)}s`
                    : `${segundos}s`;
    };
    tick();
    window.timerSLA = setInterval(tick, 1000);
};
