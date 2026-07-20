@extends('layouts.usuario')

@section('content')
    <div class="max-w-4xl w-full mx-auto p-4 md:p-8">
        <div class="mb-6 md:mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-2xl md:text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-3xl md:text-4xl text-primary">add_circle</span>
                Nueva Solicitud
            </h2>
        </div>

        {{-- Formulario de Creación de Ticket --}}
        <form action="{{ route('usuario.tickets.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6 md:space-y-8 bg-white p-6 md:p-10 rounded-2xl md:rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
            @csrf
            <!--asunto del ticket-->
            <div class="flex flex-col gap-2.5">
                <div class="flex justify-between items-center ml-1">
                    <label class="text-sm font-black text-secondary uppercase tracking-widest">Asunto del Ticket</label>
                    <span id="char-counter" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-400 border border-slate-200 transition-colors">
                        0/50
                    </span>
                </div>
                <input id="asunto-input" name="asunto" value="{{ old('asunto') }}" maxlength="50" minlength="5"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-300 !appearance-none !bg-none font-medium text-slate-700"
                    placeholder="Ej: Falla en mi laptop" type="text" required />
            </div>
            <!--categoría y tipo de solicitud-->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Categoría</label>
                    <div class="relative">
                        <select name="categoria_id"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all cursor-pointer appearance-none !bg-none font-medium text-slate-700"
                            required>
                            <option value="" disabled selected>Seleccione</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}"
                                    {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre_categoria }}
                                </option>
                            @endforeach
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            expand_more
                        </span>
                    </div>
                </div>
                <!--tipo de solicitud-->
                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Tipo de Solicitud</label>
                    <div class="relative">
                        <select name="tipo_solicitud_id" value="{{ old('tipo_solicitud_id') }}"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all cursor-pointer appearance-none !bg-none font-medium text-slate-700"
                            required>
                            <option value="" disabled selected>Seleccione</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            expand_more
                        </span>
                    </div>
                </div>
            </div>
            <!--información adicional del servicio-->
            <div id="info-extra"
                class="hidden mt-3 p-4 bg-blue-50 border-l-4 border-secondary rounded-r-xl transition-all animate-fade-in">
                <div class="flex gap-3">
                    <span class="material-symbols-outlined text-secondary">info</span>
                    <div>
                        <p class="text-xs font-black text-secondary uppercase tracking-wider">Información del servicio</p>
                        <p id="texto-ayuda" class="text-sm text-slate-600 mt-1 italic"></p>
                    </div>
                </div>
            </div>

             <!--recurso adicional para descargar pdf-->
             <div id="contenedor-pdf" class="hidden border-t border-blue-100 pt-4 mt-1">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-blue-100 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-red-500 text-3xl">picture_as_pdf</span>
                        <div>
                            <h5 class="text-xs font-black text-secondary uppercase tracking-wider">Formato</h5>
                            <p class="text-xs text-slate-400 font-medium">Llena el formato y acercate a RRHH para entregarlo.</p>
                        </div>
                    </div>
                    <a id="btn-descargar-pdf" href="#" target="_blank"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-secondary text-white text-xs font-black uppercase tracking-widest hover:bg-secondary/90 transition-all flex items-center justify-center gap-2 shadow-md shadow-secondary/10">
                        <span class="material-symbols-outlined text-sm">download</span>
                        Descargar PDF
                    </a>
                </div>
            </div>

            <!--descripción detallada del ticket-->
            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Descripción Detallada</label>
                <textarea name="descripcion"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all resize-none placeholder:text-slate-300 font-medium text-slate-700"
                    rows="5" placeholder="Explique brevemente el problema..." required>{{ old('descripcion') }}</textarea>
            </div>
            <!--prioridad del ticket-->
            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Nivel de Urgencia</label>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach ($prioridades as $prio)
                        <label class="cursor-pointer">
                            <input class="hidden peer" name="prioridad_id" type="radio" value="{{ $prio->id }}"
                                {{ $prio->nombre_prioridad == 'Media' ? 'checked' : '' }} />
                            <div
                                class="py-3 px-2 md:px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-500 font-bold text-center text-xs md:text-sm transition-all 
                                        peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary peer-checked:shadow-sm
                                        hover:border-primary hover:bg-primary/5 hover:text-primary hover:shadow-sm">
                                {{ $prio->nombre_prioridad }}
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="text-[12px] text-slate-400 italic mt-1 font-medium">* La prioridad final será asignada por el
                    técnico encargado.</p>
            </div>
            <!--subir imagen de evidencia-->
           <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Subir Imagen de Evidencia (Opcional)</label>
                <input type="file" name="evidencia" id="evidencia" accept="image/png, image/jpeg, image/jpg" class="hidden" />
                <button type="button" id="btn-seleccionar-imagen"
                    class="w-full flex items-center justify-center gap-3 px-5 py-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-primary/30 transition-all cursor-pointer group">
                    <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors">image</span>
                    <span class="font-bold text-slate-600 group-hover:text-primary transition-colors text-sm" id="texto-boton">
                        Seleccionar imagen desde tu dispositivo (JPG o PNG)
                    </span>
                </button>
                <p class="text-[12px] text-slate-400 italic mt-0.5 font-medium">* Solo se permite una imagen en formato JPG o PNG de hasta 2MB.</p>
            </div>

            <div class="flex flex-col-reverse md:flex-row items-center justify-end gap-4 pt-8 border-t border-slate-100">
                <a href="{{ route('usuario.dashboard') }}"
                    class="w-full md:w-auto text-center px-8 py-3.5 rounded-2xl font-black text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all uppercase tracking-widest text-xs">
                    Cancelar
                </a>
                <button
                    class="w-full md:w-auto px-10 py-3.5 rounded-2xl bg-primary text-secondary font-black hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-3 uppercase tracking-widest text-xs"
                    type="submit" id="btn-enviar">
                    <span>Enviar</span>
                    <span class="material-symbols-outlined text-lg">send</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputFile = document.getElementById('evidencia');
            const btnSeleccionar = document.getElementById('btn-seleccionar-imagen');
            const textoBoton = document.getElementById('texto-boton');
            if (btnSeleccionar && inputFile) {
                btnSeleccionar.addEventListener('click', (e) => {
                    e.preventDefault(); 
                    inputFile.click(); //--galeria o selector de archivos
                });
                inputFile.addEventListener('change', () => {
                    if (inputFile.files.length > 0) {
                        const archivo = inputFile.files[0];
                        //--la imagen supera el tamaño permitido
                        if (archivo.size > 2097152) {
                            Swal.fire({
                                title: 'Archivo muy pesado',
                                text: 'La imagen supera el límite permitido de 2MB.',
                                icon: 'warning',
                                confirmButtonColor: '#04003B'
                            });
                            inputFile.value = ""; 
                            textoBoton.innerText = "Seleccionar imagen desde tu dispositivo (JPG o PNG)";
                            return;
                        }
                        //--cambiar el texto del botón al nombre de la imagen seleccionada
                        textoBoton.innerText = `Seleccionado: ${archivo.name}`;
                    }
                });
            }
        });
    </script>

    <script>
        window.todosLosTipos = @json($tipos ?? []);
    </script>

    <script>
        window.__flashMessages = window.__flashMessages || {};
        window.__flashMessages.successTitle = '¡Ticket creado!';
        window.__flashMessages.validationTitle = 'No se pudo enviar';
        window.__flashMessages.redirectTo = "{{ route('usuario.dashboard') }}";
    </script>
@endpush

@push('page-scripts')
    @vite(['resources/js/ticket-form.js'])
    @vite(['resources/js/usuario-menu.js'])
@endpush
