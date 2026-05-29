@extends('layouts.usuario')

@section('content')
    <div class="max-w-4xl w-full mx-auto p-4 md:p-8">
        <div class="mb-6 md:mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-2xl md:text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-3xl md:text-4xl text-primary">confirmation_number</span>
                Enviar Nueva Solicitud
            </h2>
        </div>

        {{-- Formulario de Creación de Ticket --}}
        <form action="{{ route('usuario.tickets.store') }}" method="POST"
            class="space-y-6 md:space-y-8 bg-white p-6 md:p-10 rounded-2xl md:rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
            @csrf

            <div class="flex flex-col gap-2.5">
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Asunto del Ticket</label>
                <input name="asunto" value="{{ old('asunto') }}"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-300 font-medium text-slate-700"
                    placeholder="Ej: Falla en equipo de cómputo" type="text" required />
            </div>

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

                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Solicitud</label>
                    <div class="relative">
                        <select name="tipo_solicitud_id"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all cursor-pointer appearance-none !bg-none font-medium text-slate-700"
                            required>
                            <option value="" disabled selected>Seleccione</option>
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
                <label class="text-sm font-black text-secondary uppercase tracking-widest ml-1">Descripción Detallada</label>
                <textarea name="descripcion"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all resize-none font-medium text-slate-700"
                    rows="5" placeholder="Explique brevemente el problema..." required>{{ old('descripcion') }}</textarea>
            </div>

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
                                        hover:border-slate-200">
                                {{ $prio->nombre_prioridad }}
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="text-[13px] text-slate-400 italic mt-1 font-medium">* La prioridad final será asignada por el
                    técnico encargado.</p>
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
        window.todosLosTipos = @json($tipos ?? []);

        document.addEventListener('DOMContentLoaded', function() {
            const oldCategoria = '{{ old('categoria_id') }}';
            if (oldCategoria) {
                const catSelect = document.querySelector('select[name="categoria_id"]');
                if (catSelect) {
                    catSelect.value = oldCategoria;
                    window.filtrarTipos(oldCategoria);

                    const oldTipo = '{{ old('tipo_solicitud_id') }}';
                    const tipoSelect = document.querySelector('select[name="tipo_solicitud_id"]');
                    if (oldTipo && tipoSelect) {
                        tipoSelect.value = oldTipo;
                        tipoSelect.dispatchEvent(new Event('change'));
                    }
                }
            }
        });
    </script>

    {{-- alertas sweetalert --}}
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    title: '¡Excelente!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonColor: '#04003B',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs'
                    }
                }).then(() => {
                    window.location.href = "{{ route('usuario.dashboard') }}";
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    title: 'No se pudo enviar',
                    html: '{!! implode('<br>', $errors->all()) !!}',
                    icon: 'error',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Corregir',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs'
                    }
                });
            });
        </script>
    @endif
@endpush

@push('page-scripts')
    @vite(['resources/js/ticket-form.js'])
    @vite(['resources/js/usuario-menu.js'])
@endpush
