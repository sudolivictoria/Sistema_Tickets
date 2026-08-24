@extends('layouts.admin')

@section('content')
    @push('css')
        @vite(['resources/css/tickets.css'])
        {{-- Select2 servido local (public/vendor/select2), sin CDN externo ni paso de build --}}
        <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
        <style>
            .select-rol {
                height: 3.25rem;
            }
            .select2-container--default {
                box-sizing: border-box;
                font-size: 1rem;
                font-family: inherit;
            }
            .select2-container--default .select2-selection--single {
                box-sizing: border-box;
                display: flex !important;
                align-items: center;
                height: 3.25rem !important;
                padding: 0 0.9rem;
                border: 1px solid #04003b;
                border-radius: 0.75rem;
                background-color: #f8fafc;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                padding: 0;
                line-height: normal !important;
                color: #1e293b;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                top: 50% !important;
                right: 10px !important;
                transform: translateY(-50%);
                height: auto !important;
            }
            .select2-dropdown {
                border-radius: 0.75rem;
                overflow: hidden;
                border-color: #e2e8f0;
                font-size: 1rem;
            }
            .select2-search--dropdown .select2-search__field {
                border-radius: 0.5rem;
                padding: 0.5rem 0.75rem;
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #84cc16;
            }
            .select2-container--default .select2-results__options {
                max-height: 100px !important;
            }
        </style>
    @endpush

    {{-- Datos flash de Laravel para JS, como JSON (no ejecutable) para evitar problemas de escape/inyección --}}
    <script id="flash-data"
        type="application/json">{!! json_encode(['success' => session('success'), 'error' => session('error')]) !!}</script>
    <script>
        (function () {
            const el = document.getElementById('flash-data');
            let datos = {};
            try {
                datos = el ? JSON.parse(el.textContent) : {};
            } catch (e) {
                datos = {};
            }
            window.__flashMessages = Object.assign({
                successTitle: '¡Usuario Guardado!',
                validationTitle: 'Campos requeridos',
                errorTitle: 'Acción No Permitida',
            }, datos);
        })();
    </script>

    <div class="space-y-6">
        <div class="flex items-center justify-between mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">group</span>
                Gestión de Usuarios
            </h2>
            <button onclick="openModalUsuario('agregar')"
                class="flex items-center gap-2 bg-primary text-secondary px-6 py-2.5 rounded-xl font-black shadow-lg hover:scale-[1.02] transition-all uppercase text-[12px] tracking-widest">
                <span class="material-symbols-outlined">person_add</span> Nuevo Usuario
            </button>
        </div>

        {{-- CONTENEDOR REEMPLAZABLE POR AJAX --}}
        <div id="tabla-contenedor" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

            {{-- Filtros y Buscador --}}
            <div class="p-5 flex flex-wrap gap-4 justify-between items-center bg-white">
                <div class="flex items-center gap-4">
                    <div class="flex gap-2" id="filtrosEstado">
                        <button type="button" onclick="filtrarEstado('', this)"
                            class="filtro-btn px-4 py-1.5 bg-secondary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">Todos</button>
                        <button type="button" onclick="filtrarEstado('Activo', this)"
                            class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">Activos</button>
                        <button type="button" onclick="filtrarEstado('Inactivo', this)"
                            class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-red-100 hover:text-red-600 transition-all">Inactivos</button>
                    </div>
                </div>

                <div class="relative w-full md:w-72">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                    <input type="text" id="inputBusqueda"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-secondary/20 outline-none transition-all"
                        placeholder="Buscar...">
                </div>
            </div>

            {{-- Tabla de Usuarios --}}
            <table id="userTable" class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50 text-[13px] uppercase text-[#008F7E] font-black tracking-widest">
                        <th class="px-4 py-4 border-b border-slate-200">Nombre</th>
                        <th class="px-4 py-4 border-b border-slate-200">Rol</th>
                        <th class="px-4 py-4 border-b border-slate-200">Email</th>
                        <th class="px-4 py-4 border-b border-slate-200">Unidad</th>
                        <th class="px-4 py-4 border-b border-slate-200">Cargo</th>
                        <th class="px-4 py-4 border-b border-slate-200">Contacto</th>
                        <th class="px-4 py-4 border-b border-slate-200">Estado</th>
                        <th class="px-4 py-4 border-b border-slate-200 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $user)
                        <tr class="text-[13px]">
                            <td class="px-4 py-4 font-black">{{ $user->name }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->rol->nombre_rol ?? '' }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->email }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->unidad->nombre_unidad ?? '' }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->cargo }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->telefono ?? 'N/A' }}</td>
                            <td class="px-4 py-4">
                                <span
                                    class="px-2 py-1 rounded-full border font-black text-[9px] uppercase {{ $user->activo == 1 ? 'bg-green-100 text-[#008F7E] border-green-200' : 'bg-red-100 text-red-700 border-red-200' }}">
                                    {{ $user->activo == 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Botón Editar --}}
                                    <button type="button" onclick="openModalUsuario('editar', {{ json_encode($user) }})"
                                        @disabled($user->id === auth()->id())
                                        class="p-2 rounded-xl {{ $user->id === auth()->id() ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>

                                    {{-- Formulario Toggle Estado --}}
                                    <form action="{{ route('admin.usuarios.toggle', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" @disabled($user->id === auth()->id())
                                            class="p-2 rounded-xl {{ $user->activo ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-200' }} {{ $user->id === auth()->id() ? 'opacity-30 cursor-not-allowed' : '' }}">
                                            <span class="material-symbols-outlined text-[18px]">power_settings_new</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL AGREGAR --}}
    <div id="modalAgregar"
        class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">
            <div class="bg-primary p-6 text-secondary flex justify-between items-center shrink-0">
                <h3 class="font-black uppercase tracking-widest text-lg">Nuevo Usuario</h3>
                <button type="button" onclick="closeModalUsuario('modalAgregar')"
                    class="material-symbols-outlined font-bold">close</button>
            </div>
            <form id="formAgregar" action="{{ route('admin.usuarios.store') }}" method="POST"
                class="p-6 space-y-4 overflow-y-auto">
                @csrf
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Nombre Completo</label>
                    <input type="text" name="name" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Email</label>
                    <input type="email" name="email" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Contraseña</label>
                    <div class="relative mt-1">
                        <input type="password" name="password" class="w-full p-3 bg-slate-50 border rounded-xl"
                            minlength="6" required>
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2">
                            <span class="material-symbols-outlined text-slate-400">visibility</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Rol</label>
                        <select name="rol_id" class="select-rol w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Unidad</label>
                        <select name="unidad_id" id="unidad_id_agregar" class="select-unidad w-full mt-1" required>
                            @foreach ($unidades as $u)
                                <option value="{{ $u->id }}">{{ $u->nombre_unidad }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Cargo</label>
                    <input type="text" name="cargo" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Teléfono</label>
                    <input type="text" name="telefono" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" maxlength="30">
                </div>
            </form>
            <div class="p-6 pt-4 border-t border-slate-100 shrink-0">
                <button type="submit" form="formAgregar"
                    class="w-full bg-secondary text-primary font-black py-4 rounded-xl shadow-lg uppercase">
                    Guardar Usuario
                </button>
            </div>
        </div>
    </div>
    {{-- FIN MODAL AGREGAR --}}

    {{-- MODAL EDITAR --}}
    <div id="modalEditar"
        class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg max-h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col">
            <div class="bg-primary p-6 text-secondary flex justify-between items-center shrink-0">
                <h3 class="font-black uppercase tracking-widest text-lg">Editar Usuario</h3>
                <button type="button" onclick="closeModalUsuario('modalEditar')"
                    class="material-symbols-outlined font-bold">close</button>
            </div>
            <form id="formEditar" method="POST" class="p-6 space-y-4 overflow-y-auto">
                @csrf
                @method('PATCH')
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Nombre Completo</label>
                    <input type="text" name="name" id="edit_nombre" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl"
                        required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl"
                        required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Contraseña (Opcional)</label>
                    <div class="relative mt-1">
                        <input type="password" name="password" id="edit_password"
                            class="w-full p-3 bg-slate-50 border rounded-xl" minlength="6">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2">
                            <span class="material-symbols-outlined text-slate-400">visibility</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Rol</label>
                        <select name="rol_id" id="edit_rol" class="select-rol w-full mt-1 p-3 bg-slate-50 border rounded-xl"
                            required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Unidad</label>
                        <select name="unidad_id" id="edit_unidad" class="select-unidad w-full mt-1" required>
                            @foreach ($unidades as $u)
                                <option value="{{ $u->id }}">{{ $u->nombre_unidad }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Cargo</label>
                    <input type="text" name="cargo" id="edit_cargo" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl"
                        required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Teléfono</label>
                    <input type="text" name="telefono" id="edit_telefono"
                        class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" maxlength="30">
                </div>
            </form>
            <div class="p-6 pt-4 border-t border-slate-100 shrink-0">
                <button type="submit" form="formEditar"
                    class="w-full bg-secondary text-primary font-black py-4 rounded-xl shadow-lg uppercase">
                    Actualizar Usuario
                </button>
            </div>
        </div>
    </div>
    {{-- FIN MODAL EDITAR --}}
@endsection
{{-- LOGICA GESTION DE USERS --}}
@push('page-scripts')
    <script defer src="{{ asset('vendor/select2/select2.min.js') }}"></script>
    @vite(['resources/js/gestion-usuarios.js'])
@endpush