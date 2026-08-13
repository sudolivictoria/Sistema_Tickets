//---------------Immediately Invoked Function Expression----------------------------------------->
(function () {
    //--------------Filtrar subcategorías (Global para poder invocarse desde Blade si es necesario)
    window.filtrarTipos = function (categoriaId) {
        const selectTipo = document.querySelector('select[name="tipo_solicitud_id"]');
        if (!selectTipo) return;
        //--------------------------Reiniciar opciones---------------------------------------------
        selectTipo.innerHTML = '<option value="" disabled selected>Seleccione</option>';
        //------------------Validación existencia de datos----------------------------------------
        if (!window.todosLosTipos || window.todosLosTipos.length === 0) return;

        const filtrados = window.todosLosTipos.filter(
            (tipo) => tipo.categoria_id == categoriaId
        );
        filtrados.forEach((tipo) => {
            const option = document.createElement("option");
            option.value = tipo.id;
            option.textContent = tipo.nombre_tipo_solicitud;
            selectTipo.appendChild(option);
        });
    };
    //---------------------Función de inicialización del formulario------------------>
    const initForm = () => {
        const categoriaSelect = document.querySelector('select[name="categoria_id"]');
        const tipoSelect = document.querySelector('select[name="tipo_solicitud_id"]');

        if (!categoriaSelect || !tipoSelect) return;

        if (categoriaSelect.value) {
            window.filtrarTipos(categoriaSelect.value);
            const idTipoViejo = tipoSelect.getAttribute("value");
            if (idTipoViejo) {
                tipoSelect.value = idTipoViejo;
                tipoSelect.dispatchEvent(new Event("change"));
            }
        }
    };
    //-------------Escuchas universales seguras bajo la carga del DOM-------------->
    document.addEventListener("DOMContentLoaded", () => {
        const inputAsunto = document.getElementById("asunto-input");
        const contador = document.getElementById("char-counter");
        const categoriaSelect = document.querySelector('select[name="categoria_id"]');
        const tipoSelect = document.querySelector('select[name="tipo_solicitud_id"]');
        const formulario = document.querySelector("#formCrearTicket");
        const btnEnviar = document.getElementById("btn-enviar");

        const contenedorPdf = document.getElementById("contenedor-pdf"); 
        const btnDescargarPdf = document.getElementById("btn-descargar-pdf"); 
        //-----Retraso controlado para garantizar que Blade cargó window.todosLosTipos----->
        setTimeout(() => {
            initForm();
        }, 150);
        //--------------------------CONTADOR INFORMATIVO ASUNTO----------------------------->
        if (inputAsunto && contador) {
            const lenInicial = inputAsunto.value.length;
            contador.textContent = `${lenInicial}/50`;

            inputAsunto.addEventListener("input", () => {
                const longitud = inputAsunto.value.length;
                contador.textContent = `${longitud}/50`;

                if (longitud < 5 || longitud > 50) {
                    contador.classList.remove("bg-slate-100", "text-slate-400", "border-slate-200");
                    contador.classList.add("bg-red-50", "text-red-500", "border-red-200");
                } else {
                    contador.classList.remove("bg-red-50", "text-red-500", "border-red-200");
                    contador.classList.add("bg-slate-100", "text-slate-400", "border-slate-200");
                }
            });
        }
        // ---------------MANEJO DE ENVÍO AJAX + PREVENCIÓN DE DOBLE CLIC------------------------>
        if (formulario && btnEnviar) {
            formulario.addEventListener("submit", function (e) {
                e.preventDefault(); // Previene el refresco estándar

                const $form = $(this);
                const contenidoOriginal = btnEnviar.innerHTML;

                //--------------Estado de carga: Deshabilitar botón y mostrar animación------------>
                btnEnviar.disabled = true;
                btnEnviar.classList.add("opacity-75", "cursor-not-allowed");
                btnEnviar.innerHTML = `
                    <div class="flex items-center justify-center gap-2">
                        <span class="animate-spin material-symbols-outlined text-lg">sync</span>
                        <span>Enviando...</span>
                    </div>
                `;
                //--------Envío AJAX usando FormData (Soporta archivos e inputs normales)------------>
                $.ajax({
                    url: $form.attr("action"),
                    method: $form.attr("method") || "POST",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        const redirectTo = formulario.dataset.redirectSuccess || null;
                        window.mostrarFlashMessages({
                            success: response?.message || "Solicitud enviada correctamente.",
                            redirectTo: redirectTo,
                        });
                        if (!redirectTo) {
                            btnEnviar.disabled = false;
                            btnEnviar.classList.remove("opacity-75", "cursor-not-allowed");
                            btnEnviar.innerHTML = contenidoOriginal;
                        }
                    },
                    error: function (xhr) {
                        //---------------------Captura de errores de validación de Laravel (HTTP 422)------->
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errores = Object.values(xhr.responseJSON.errors).flat();
                            window.mostrarFlashMessages({
                                validationTitle: 'Verifique los campos',
                                validationErrors: errores
                            });
                        } else {
                            window.mostrarFlashMessages({
                                errorTitle: 'Ocurrió un error',
                                error: xhr.responseJSON?.message || 'No se pudo enviar la solicitud. Intente nuevamente.'
                            });
                        }
                        //-----------------Restaurar botón para permitir reintentar------------------------->
                        btnEnviar.disabled = false;
                        btnEnviar.classList.remove("opacity-75", "cursor-not-allowed");
                        btnEnviar.innerHTML = contenidoOriginal;
                    }
                });
            });
        }
        //--Evento de cambio de categoría-------------------------------------->
        if (categoriaSelect) {
            categoriaSelect.addEventListener("change", function () {
                window.filtrarTipos(this.value);
            });
        }
        //---------EVENTO PARA MOSTRAR LA DESCRIPCION DE TIPO DE SOLICITUD---->
        if (tipoSelect) {
            tipoSelect.addEventListener("change", function () {
                const infoDiv = document.getElementById("info-extra");
                const tipoSeleccionado = window.todosLosTipos?.find(
                    (t) => t.id == this.value
                );
                if (infoDiv) {
                    const tieneDescripcion = !!tipoSeleccionado?.descripcion_solicitud;
                    const tieneManual = !!(
                        tipoSeleccionado?.ruta_manual &&
                        tipoSeleccionado.ruta_manual.trim() !== ""
                    );    
                    if (tieneDescripcion || tieneManual) {
                        infoDiv.classList.remove("hidden");

                        const textoAyuda = document.getElementById("texto-ayuda");
                        if (textoAyuda) {
                            textoAyuda.textContent = tipoSeleccionado?.descripcion_solicitud || "";
                            const bloqueTexto = textoAyuda.closest(".flex");
                            if (bloqueTexto) {
                                tieneDescripcion
                                    ? bloqueTexto.classList.remove("hidden")
                                    : bloqueTexto.classList.add("hidden");
                            }
                        }
                        //----------------pdfs------------------------------------------>
                        if (contenedorPdf && btnDescargarPdf) {
                            if (tieneManual) {
                                btnDescargarPdf.setAttribute(
                                    "href",
                                    `/storage/manuales/${tipoSeleccionado.ruta_manual}`
                                );
                                contenedorPdf.classList.remove("hidden");
                            } else {
                                contenedorPdf.classList.add("hidden");
                            }
                        }
                    } else {
                        infoDiv.classList.add("hidden");
                    }
                }
            });
        }
    });
})();