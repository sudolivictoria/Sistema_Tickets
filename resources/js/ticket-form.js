//--Inmediately Invoked Function Expression
(function () {
    //--filtrar subcategorias
    window.filtrarTipos = function (categoriaId) {
        const selectTipo = document.querySelector(
            'select[name="tipo_solicitud_id"]',
        );
        if (!selectTipo) return;

        //--reiniciar opciones
        selectTipo.innerHTML =
            '<option value="" disabled selected>Seleccione</option>';

        //--validacion existencia datos
        if (!window.todosLosTipos || window.todosLosTipos.length === 0) return;

        const filtrados = window.todosLosTipos.filter(
            (tipo) => tipo.categoria_id == categoriaId,
        );

        filtrados.forEach((tipo) => {
            const option = document.createElement("option");
            option.value = tipo.id;
            option.textContent = tipo.nombre_tipo_solicitud;
            selectTipo.appendChild(option);
        });
    };

    //--función de inicialización del formulario
    const initForm = () => {
        const categoriaSelect = document.querySelector(
            'select[name="categoria_id"]',
        );
        const tipoSelect = document.querySelector(
            'select[name="tipo_solicitud_id"]',
        );

        if (!categoriaSelect || !tipoSelect) return;

        if (categoriaSelect.value) {
            //---options del select
            window.filtrarTipos(categoriaSelect.value);
            const idTipoViejo = tipoSelect.getAttribute("value");
            if (idTipoViejo) {
                tipoSelect.value = idTipoViejo;
                //---cuadro azul informativo
                tipoSelect.dispatchEvent(new Event("change"));
            }
        }
    };

    //-- Escuchas universales seguras bajo la carga del DOM
    document.addEventListener("DOMContentLoaded", () => {
        const inputAsunto = document.getElementById("asunto-input");
        const contador = document.getElementById("char-counter");
        const categoriaSelect = document.querySelector(
            'select[name="categoria_id"]',
        );
        const tipoSelect = document.querySelector(
            'select[name="tipo_solicitud_id"]',
        );
        const formulario = document.querySelector("form");
        const btnEnviar = document.getElementById("btn-enviar");

        const contenedorPdf = document.getElementById("contenedor-pdf"); 
        const btnDescargarPdf = document.getElementById("btn-descargar-pdf"); 

        //----retraso controlado para garantizar que Blade cargó window.todosLosTipos----
        setTimeout(() => {
            initForm();
        }, 150);

        //----CONTADOR INFORMATIVO (Solo cuenta caracteres)----
        if (inputAsunto && contador) {
            const lenInicial = inputAsunto.value.length;
            contador.textContent = `${lenInicial}/50`;

            inputAsunto.addEventListener("input", () => {
                const longitud = inputAsunto.value.length;
                contador.textContent = `${longitud}/50`;

                //---badge informativa roja numero de caracteres
                if (longitud < 5 || longitud >= 50) {
                    contador.classList.remove(
                        "bg-slate-100",
                        "text-slate-400",
                        "border-slate-200",
                    );
                    contador.classList.add(
                        "bg-red-50",
                        "text-red-500",
                        "border-red-200",
                    );
                } else {
                    contador.classList.remove(
                        "bg-red-50",
                        "text-red-500",
                        "border-red-200",
                    );
                    contador.classList.add(
                        "bg-slate-100",
                        "text-slate-400",
                        "border-slate-200",
                    );
                }
            });
        }

        // ----PREVENCIÓN DE DOBLE ENVÍO REAL----
        if (formulario && btnEnviar) {
            formulario.addEventListener("submit", function () {
                //---al no haber preventDefault, el formulario viaja normalmente, pero congelamos el botón
                btnEnviar.disabled = true;
                btnEnviar.innerHTML = `<span>Enviando...</span> <span class="animate-spin material-symbols-outlined text-lg">sync</span>`;
            });
        }

        //--evento de cambio de categoría
        if (categoriaSelect) {
            categoriaSelect.addEventListener("change", function () {
                window.filtrarTipos(this.value);
            });
        }

        //--evento de tipo solicitud para mostrar descripción informativa (Cuadro azul)
        if (tipoSelect) {
            tipoSelect.addEventListener("change", function () {
                const infoDiv = document.getElementById("info-extra");
                const tipoSeleccionado = window.todosLosTipos?.find(
                    (t) => t.id == this.value,
                );
                if (infoDiv) {
                    const tieneDescripcion =
                        !!tipoSeleccionado?.descripcion_solicitud;
                    const tieneManual = !!(
                        tipoSeleccionado?.ruta_manual &&
                        tipoSeleccionado.ruta_manual.trim() !== ""
                    );
                    if (tieneDescripcion || tieneManual) {
                        //---contenedor informativo
                        infoDiv.classList.remove("hidden");

                        //---texto descriptivo
                        const textoAyuda =
                            document.getElementById("texto-ayuda");
                        if (textoAyuda) {
                            textoAyuda.textContent =
                                tipoSeleccionado?.descripcion_solicitud || "";

                            const bloqueTexto = textoAyuda.closest(".flex");
                            if (bloqueTexto) {
                                tieneDescripcion
                                    ? bloqueTexto.classList.remove("hidden")
                                    : bloqueTexto.classList.add("hidden");
                            }
                        }
                        //---botón de descarga de manual
                        if (contenedorPdf && btnDescargarPdf) {
                            if (tieneManual) {
                                //----Si tiene manual, habilitamos el botón y le asignamos la ruta
                                btnDescargarPdf.setAttribute(
                                    "href",
                                    `/storage/${tipoSeleccionado.ruta_manual}`,
                                );
                                contenedorPdf.classList.remove("hidden");
                            } else {
                                contenedorPdf.classList.add("hidden");
                            }
                        }
                    } else {
                        //--si no tiene manual ni descripción, ocultamos el cuadro azul
                        infoDiv.classList.add("hidden");
                    }
                }
            });
        }
    });
})();
