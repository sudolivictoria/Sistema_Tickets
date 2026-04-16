@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl w-full mx-auto p-8">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">confirmation_number</span>
                Enviar Nueva Solicitud
            </h2>
            <p class="text-slate-500 font-medium">Complete el formulario oficial para la gestión de su requerimiento.</p>
        </div>

        {{-- Formulario de Creación de Ticket --}}
        <form action="{{ route('admin.tickets.store') }}" method="POST"
            class="space-y-8 bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
            @csrf

            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Asunto del Ticket</label>
                <input name="asunto" value="{{ old('asunto') }}"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all placeholder:text-slate-300 !appearance-none !bg-none font-medium text-slate-700"
                    placeholder="Ej: Falla en equipo de cómputo" type="text" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Categoría</label>
                    <div class="relative">
                        <select name="categoria_id"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all cursor-pointer appearance-none !bg-none font-medium text-slate-700"
                            required>
                            <option value="" disabled selected>Seleccione categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
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

                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Tipo de
                        Solicitud</label>
                    <div class="relative">
                        <select name="tipo_solicitud_id"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all cursor-pointer appearance-none !bg-none font-medium text-slate-700"
                            required>
                            <option value="" disabled selected>Seleccione tipo</option>
                            {{-- Aquí iría tu foreach de tipos --}}
                        </select>
                        <span
                            class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            expand_more
                        </span>
                    </div>
                </div>
            </div>

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

            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Descripción
                    Detallada</label>
                <textarea name="descripcion"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all resize-none font-medium text-slate-700"
                    rows="5" placeholder="Explique brevemente el problema..." required>{{ old('descripcion') }}</textarea>
            </div>

            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Nivel de Urgencia</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($prioridades as $prio)
                        <label class="cursor-pointer">
                            <input class="hidden peer" name="prioridad_id" type="radio" value="{{ $prio->id }}" {{ $prio->nombre_prioridad == 'Media' ? 'checked' : '' }} />
                            <div class="py-3 px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-500 font-bold text-center transition-all 
                                        peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary peer-checked:shadow-sm
                                        hover:border-slate-200">
                                {{ $prio->nombre_prioridad }}
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="text-[13px] text-slate-400 italic mt-1 font-medium">* La prioridad final será asignada por el
                    técnico encargado.</p>
            </div>

            <div class="flex items-center justify-end gap-4 pt-8 border-t border-slate-100">
                <a href="{{ route('cliente.dashboard') }}"
                    class="px-8 py-3.5 rounded-2xl font-black text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all uppercase tracking-widest text-xs">
                    Cancelar
                </a>
                <button
                    class="px-10 py-3.5 rounded-2xl bg-secondary text-primary font-black hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/20 flex items-center gap-3 uppercase tracking-widest text-xs"
                    type="submit" id="btn-enviar">
                    <span>Enviar Requerimiento</span>
                    <span class="material-symbols-outlined text-lg">send</span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const todosLosTipos = @json($tipos);

        function filtrarTipos(categoriaId) {
            const selectTipo = document.querySelector('select[name="tipo_solicitud_id"]');
            selectTipo.innerHTML = '<option value="" disabled selected>Seleccione tipo</option>';

            const filtrados = todosLosTipos.filter(tipo => tipo.categoria_id == categoriaId);
            filtrados.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.id;
                option.textContent = tipo.nombre_tipo_solicitud;
                selectTipo.appendChild(option);
            });
            document.getElementById('info-extra').classList.add('hidden');
        }

        document.querySelector('select[name="categoria_id"]').addEventListener('change', function () {
            filtrarTipos(this.value);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const categoriaSelect = document.querySelector('select[name="categoria_id"]');
            const oldCategoria = '{{ old("categoria_id") }}';
            const oldTipo = '{{ old("tipo_solicitud_id") }}';

            if (oldCategoria) {
                categoriaSelect.value = oldCategoria;
                filtrarTipos(oldCategoria);

                if (oldTipo) {
                    const tipoSelect = document.querySelector('select[name="tipo_solicitud_id"]');
                    tipoSelect.value = oldTipo;
                    tipoSelect.dispatchEvent(new Event('change'));
                }
            }
        });

        //----mostrar descripcion del tipo de solicitud
        document.querySelector('select[name="tipo_solicitud_id"]').addEventListener('change', function () {
            const tipoId = this.value;
            const infoDiv = document.getElementById('info-extra');
            const textoAyuda = document.getElementById('texto-ayuda');
            const tipoSeleccionado = todosLosTipos.find(t => t.id == tipoId);

            if (tipoSeleccionado && tipoSeleccionado.descripcion_solicitud) {
                textoAyuda.textContent = tipoSeleccionado.descripcion_solicitud;
                infoDiv.classList.remove('hidden');
            } else {
                infoDiv.classList.add('hidden');
            }
        });

        //---prevencion doble click
        document.querySelector('form').addEventListener('submit', function () {
            const btnEnviar = document.getElementById('btn-enviar');
            btnEnviar.disabled = true;
            btnEnviar.classList.add('cursor-not-allowed', 'opacity-50');
            btnEnviar.innerHTML = `<span>Enviando...</span><svg class="animate-spin h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
        });
    </script>

    {{--alertas sweetalert--}}
    @if(session('success'))
        <script>
            Swal.fire({
                title: '¡Excelente!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#1e3a8a',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'rounded-3xl', confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs' }
            }).then(() => {
                window.location.href = "{{ route('admin.dashboard') }}";
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: 'No se pudo enviar',
                html: '{!! implode("<br>", $errors->all()) !!}',
                icon: 'error',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Corregir',
                customClass: { popup: 'rounded-3xl', confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs' }
            });
        </script>
    @endif
@endpush