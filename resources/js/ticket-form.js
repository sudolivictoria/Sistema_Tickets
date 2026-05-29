//--Inmediately Invoked Function Expression 
(function () {
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

    //--funcion inicializacion del formulario
    const initForm = () => {
        const categoriaSelect = document.querySelector(
            'select[name="categoria_id"]',
        );
        const tipoSelect = document.querySelector(
            'select[name="tipo_solicitud_id"]',
        );

        if (!categoriaSelect) return;

        //--si viene una categoria seleccionada
        if (categoriaSelect.value) {
            window.filtrarTipos(categoriaSelect.value);

            //--restaurar tipo seleccionado
            if (tipoSelect && tipoSelect.value) {
                tipoSelect.value =
                    tipoSelect.getAttribute("value") || tipoSelect.value;
                //--mostrar descripcion
                tipoSelect.dispatchEvent(new Event("change"));
            }
        }
    };

    //--escuchas universales
    document.addEventListener("DOMContentLoaded", () => {
        //--inicializar formulario
        initForm();

        //--evento de categoria
        document
            .querySelector('select[name="categoria_id"]')
            .addEventListener("change", function () {
                window.filtrarTipos(this.value);
            });

        //--evento de tipo solicitud para mostrar descripcion
        document
            .querySelector('select[name="tipo_solicitud_id"]')
            .addEventListener("change", function () {
                const infoDiv = document.getElementById("info-extra");
                const tipoSeleccionado = window.todosLosTipos?.find(
                    (t) => t.id == this.value,
                );

                if (infoDiv) {
                    if (tipoSeleccionado?.descripcion_solicitud) {
                        document.getElementById("texto-ayuda").textContent =
                            tipoSeleccionado.descripcion_solicitud;
                        infoDiv.classList.remove("hidden");
                    } else {
                        infoDiv.classList.add("hidden");
                    }
                }
            });
    });
})();
