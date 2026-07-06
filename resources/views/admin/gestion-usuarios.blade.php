@extends('layouts.admin')

@section('content')
    @push('css')
        @vite(['resources/css/tickets.css'])
    @endpush

    <div class="space-y-6">
        <div class="flex items-center justify-between mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">group</span>
                Gestión de Usuarios
            </h2>
            <button onclick="abrirModal('agregar')"
                class="flex items-center gap-2 bg-primary text-secondary px-6 py-2.5 rounded-xl font-black shadow-lg hover:scale-[1.02] transition-all uppercase text-[12px] tracking-widest">
                <span class="material-symbols-outlined">person_add</span> Nuevo Usuario
            </button>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            {{-- CABECERA CON FILTROS --}}
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

                {{-- BUSCADOR --}}
                <div class="relative w-full md:w-72">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                    <input type="text" id="inputBusqueda"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-secondary/20 outline-none transition-all"
                        placeholder="Buscar...">
                </div>
            </div>

            {{-- TABLA --}}
            <table id="userTable" class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50 text-[13px] uppercase text-[#008F7E] font-black tracking-widest">
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Nombre</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Rol</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Email</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Unidad</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Cargo</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Teléfono</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black">Estado</th>
                        <th class="px-4 py-4 border-b border-slate-200 font-black text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $user)
                        <tr class="text-[13px]">
                            <td class="px-4 py-4 font-black">{{ $user->name }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->rol->nombre_rol }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->email }}</td>
                            <td class="px-4 py-4 font-black">{{ $user->unidad->nombre_unidad }}</td>
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
                                    {{-- BOTÓN EDITAR --}}
                                    <button type="button" onclick="abrirModal('editar', {{ json_encode($user) }})"
                                        @disabled($user->id === auth()->id())
                                        class="p-2 rounded-xl {{ $user->id === auth()->id() ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-100 hover:scale-105 transition-transform' }}">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>

                                    {{-- BOTÓN ESTADO ACTIVO / DESACTIVADO --}}
                                    <form action="{{ route('admin.usuarios.toggle', $user->id) }}" method="POST" class="m-0">
                                        @csrf @method('PATCH')

                                        <button type="submit" @disabled($user->id === auth()->id())
                                            class="p-2 rounded-xl {{ $user->activo ? 'bg-red-50 text-red-600 hover:bg-red-100 hover:scale-105 transition-transform' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-200 hover:scale-105 transition-transform' }} 
                                                                                            {{ $user->id === auth()->id() ? 'opacity-30 cursor-not-allowed' : 'hover:scale-105 transition-transform' }}">
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

    <!----------------MODAL AGREGAR USUARIO------------>
    <div id="modalAgregar"
        class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-primary p-6 text-secondary flex justify-between items-center">
                <h3 class="font-black uppercase tracking-widest text-lg">Nuevo Usuario</h3>
                <button onclick="cerrarModal('modalAgregar')" class="material-symbols-outlined font-bold">close</button>
            </div>
            <form id="formAgregar" action="{{ route('admin.usuarios.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Nombre Completo</label>
                    <input type="text" name="name" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                </div>
                <div>
                    <label class="text-[12px] font-black uppercase text-secondary">Email</label>
                    <input type="email" name="email" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                </div>
                <div class="relative">
                    <label class="text-[12px] font-black uppercase text-secondary">Contraseña</label>
                    <input type="password" name="password" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl"
                        minlength="6" required>
                    <button type="button" class="toggle-password"
                        style="position: absolute; top: 38px; right: 12px; background: transparent; border: none; cursor: pointer; padding: 0;">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#64748b"
                            viewBox="0 0 24 24">
                            <path
                                d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7 11-6.52 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5zm0-8.5c-1.931 0-3.5 1.569-3.5 3.5s1.569 3.5 3.5 3.5 3.5-1.569 3.5-3.5-1.569-3.5-3.5-3.5z" />
                        </svg>
                        <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#64748b"
                            viewBox="0 0 24 24" style="display:none;">
                            <path
                                d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7c1.645 0 3.223-.314 4.692-.884l3.186 3.186 1.414-1.414-18-18-1.414 1.414 3.705 3.705c-2.216 1.381-3.947 3.268-4.972 4.778 1.112 1.618 3.385 4.096 6.667 5.238l-1.38-1.38c-2.142-.702-3.715-2.43-4.268-3.506.982-1.336 3.124-3.932 7.481-3.932 1.763 0 3.34.408 4.683 1.09l1.937-1.937c-1.787-.926-3.847-1.561-6.62-1.561zm3.931 10.931l-1.655-1.655c.435-.484.724-1.121.724-1.776 0-1.378-1.122-2.5-2.5-2.5-.655 0-1.292.289-1.776.724l-1.655-1.655c.888-.66 1.957-1.064 3.431-1.064 2.761 0 5 2.239 5 5 0 1.474-.404 2.543-1.069 3.431z" />
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Rol</label>
                        <select name="rol_id" class="w-full mt-1 p-3 bg-slate-50 border rounded-md" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Unidad</label>
                        <select name="unidad_id" class="w-full mt-1 p-3 bg-slate-50 border rounded-md" required>
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
                    <label class="text-[12px] font-black uppercase text-secondary">Telefono</label>
                    <input type="text" name="telefono" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl "
                        maxlength="15">
                </div>


                <button type="submit"
                    class="w-full bg-secondary text-primary font-black py-4 rounded-xl shadow-lg mt-4 uppercase">Guardar
                    Usuario</button>
            </form>
        </div>
    </div>
    <!----------------FIN MODAL AGREGAR USUARIO------------>


    <!----------------MODAL EDITAR USUARIO------------>
    <div id="modalEditar"
        class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-primary p-6 text-secondary flex justify-between items-center">
                <h3 class="font-black uppercase tracking-widest text-lg">Editar Usuario</h3>
                <button onclick="cerrarModal('modalEditar')" class="material-symbols-outlined font-bold">close</button>
            </div>

            <form id="formEditar" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')

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

                {{-- opcional --}}
                <div class="relative">
                    <label class="text-[12px] font-black uppercase text-secondary">Contraseña (Dejar vacío para no
                        cambiar)</label>
                    <input type="password" name="password" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl "
                        minlength="6">

                    <button type="button" class="toggle-password"
                        style="position: absolute; top: 38px; right: 12px; background: transparent; border: none; cursor: pointer; padding: 0;">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#64748b"
                            viewBox="0 0 24 24">
                            <path
                                d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7 11-6.52 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5zm0-8.5c-1.931 0-3.5 1.569-3.5 3.5s1.569 3.5 3.5 3.5 3.5-1.569 3.5-3.5-1.569-3.5-3.5-3.5z" />
                        </svg>
                        <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#64748b"
                            viewBox="0 0 24 24" style="display:none;">
                            <path
                                d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7c1.645 0 3.223-.314 4.692-.884l3.186 3.186 1.414-1.414-18-18-1.414 1.414 3.705 3.705c-2.216 1.381-3.947 3.268-4.972 4.778 1.112 1.618 3.385 4.096 6.667 5.238l-1.38-1.38c-2.142-.702-3.715-2.43-4.268-3.506.982-1.336 3.124-3.932 7.481-3.932 1.763 0 3.34.408 4.683 1.09l1.937-1.937c-1.787-.926-3.847-1.561-6.62-1.561zm3.931 10.931l-1.655-1.655c.435-.484.724-1.121.724-1.776 0-1.378-1.122-2.5-2.5-2.5-.655 0-1.292.289-1.776.724l-1.655-1.655c.888-.66 1.957-1.064 3.431-1.064 2.761 0 5 2.239 5 5 0 1.474-.404 2.543-1.069 3.431z" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Rol</label>
                        <select name="rol_id" id="edit_rol" class="w-full mt-1 p-3 bg-slate-50 border rounded-md" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre_rol }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Unidad</label>
                        <select name="unidad_id" id="edit_unidad" class="w-full mt-1 p-3 bg-slate-50 border rounded-md"
                            required>
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
                    <label class="text-[12px] font-black uppercase text-secondary">Telefono</label>
                    <input type="text" name="telefono" id="edit_telefono"
                        class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" maxlength="15">
                </div>

                <button type="submit"
                    class="w-full bg-secondary text-primary font-black py-4 rounded-xl shadow-lg mt-4 uppercase">Actualizar
                    Usuario</button>
            </form>
        </div>
    </div>
    <!----------------FIN MODAL EDITAR USUARIO------------>
@endsection

@push('scripts')

    @if (session('success'))

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#04003B',
                });
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                let errores = {!! json_encode(implode("<br>", $errors->all())) !!};
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    html: `<div class="text-center font-sans text-sm">${errores}</div>`,
                    confirmButtonColor: '#dc2626',
                });
            })
        </script>
    @endif
@endpush

@push('page-scripts')
    @vite(['resources/js/gestion-usuarios.js'])
@endpush