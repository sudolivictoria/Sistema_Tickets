@extends('layouts.admin')

@section('content')
<div class="p-8 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center mb-10 border-b border-slate-200 pb-6">
        <div>
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">folder_shared</span>
                Gestión de Recursos
            </h2>

        </div>

        <div class="flex gap-2">
            <button onclick="abrirModal('modalCategorias')"
                class="flex items-center gap-2 bg-primary text-secondary px-6 py-2.5 rounded-xl font-black shadow-lg hover:scale-[1.02] transition-all uppercase text-[12px] tracking-widest">
                <span class="material-symbols-outlined">add</span> Nueva Categoría
            </button>

            <button onclick="abrirModal('modalCrear')"
                class="flex items-center gap-2 bg-secondary text-primary px-6 py-2.5 rounded-xl font-black shadow-lg hover:scale-[1.02] transition-all uppercase text-[12px] tracking-widest">
                <span class="material-symbols-outlined">add</span> Nuevo Manual
            </button>
        </div>
    </div>

    <!--------------------------FILTROS------------------------------>
    <div class="flex gap-3 mb-10 overflow-x-auto pb-2">
        <button onclick="filtrar('all', event)"
            class="filter-btn active bg-white text-[#04003B] px-6 py-2 rounded-full border-2 border-[#04003B] font-black uppercase text-xs tracking-wider hover:border-[#04003B] hover:text-[#04003B] transition">
            Todos
        </button>

        @foreach ($categorias as $cat)
            <button type="button"
                class="filter-btn bg-white text-secondary  font-black  border-2 border-slate-200 px-6 py-2 rounded-full uppercase text-xs tracking-wider hover:border-[#04003B] hover:text-[#04003B] transition"
                data-id="{{ $cat->id }}" onclick="filtrar({{ $cat->id }}, event)">
                {{ $cat->nombre_categoria_manual }}
            </button>
        @endforeach
    </div>

    <!--------------------------CARDS------------------------------>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        @foreach($manuales as $manual)
            <div class="manual-card bg-white rounded-2xl shadow-md overflow-hidden flex flex-col group transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
                data-categoria="{{ $manual->categoria_id }}">

                <div
                    class="h-32 bg-[#04003B] relative p-5 bg-[url('https://www.transparenttextures.com/patterns/swirl.png')]">
                    <span
                        class="relative z-10 bg-lime-500/20 text-lime-400 text-[12px] font-bold px-2 py-1 rounded border border-lime-500/50 uppercase">
                        {{ $manual->categoria->nombre_categoria_manual }}
                    </span>

                    <h3
                        class="relative pt-5 z-10 text-md font-black text-white uppercase break-words tracking-tight leading-none drop-shadow-md group-hover:text-[#9fd82b] transition-colors duration-300">
                        {{ $manual->titulo }}
                    </h3>

                    <div class="absolute top-4 right-4 text-white/20 group-hover:text-white/40 transition-colors">
                        @if(Str::endsWith($manual->archivo_path, '.mp4'))
                            <span class="material-symbols-outlined text-[24px]">play_circle</span>
                        @else
                            <span class="material-symbols-outlined text-[24px]">picture_as_pdf</span>
                        @endif
                    </div>

                </div>

                <div class="relative flex-1 p-5 flex flex-col bg-white">
                    <div>
                        <div class="flex gap-3 text-[#04003B]/60">


                            <button
                                onclick="abrirVisor('{{ asset('storage/' . $manual->archivo_path) }}', '{{ $manual->titulo }}')"
                                class="w-9 h-9 bg-[#9fd82b] hover:bg-[#04003B] hover:text-[#9fd82b] text-[#1c3000] rounded-xl flex items-center justify-center transition-all shadow-md shadow-lime-500/10 hover:scale-105 active:scale-95"
                                title="Abrir Visor">
                                <span class="material-symbols-outlined text-[18px] font-bold">visibility</span>
                            </button>
                            <button
                                onclick="editarManual({{ $manual->id }}, '{{ $manual->titulo }}', {{ $manual->categoria_id }})"
                                class="w-9 h-9 bg-[#9fd82b] hover:bg-[#04003B] hover:text-[#9fd82b] text-[#1c3000] rounded-xl flex items-center justify-center transition-all shadow-md shadow-lime-500/10 hover:scale-105 active:scale-95"
                                title="Editar Recurso">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button onclick="eliminarManual({{ $manual->id }})"
                                class="w-9 h-9 bg-[#9fd82b] hover:bg-[#04003B] hover:text-[#9fd82b] text-[#1c3000] rounded-xl flex items-center justify-center transition-all shadow-md shadow-lime-500/10 hover:scale-105 active:scale-95"
                                title="Eliminar Recurso">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!---------------------------------------------------------MODALES-------------------------------------------->

        <!--CREAR MANUAL-->
        <div id="modalCrear"
            class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="bg-primary p-6 text-secondary flex justify-between items-center">
                    <h3 class="font-black uppercase tracking-widest text-lg">Nuevo Recurso</h3>
                    <button onclick="cerrarModal('modalCrear')"
                        class="material-symbols-outlined font-bold">close</button>
                </div>
                <form id="formAgregar" action="{{ route('admin.manuales.store') }}" method="POST"
                    enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Titulo del Recurso</label>
                        <input type="text" name="titulo" class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Categoría</label>
                        <select name="categoria_id" class="w-full mt-1 p-3 bg-slate-50 border rounded-md" required>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria_manual }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Archivo (PDF o MP4)</label>
                        <input type="file" name="archivo" accept=".pdf,.mp4"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            required>
                    </div>
                    <button type="submit"
                        class="w-full bg-secondary text-primary font-black py-3 rounded-xl shadow-lg mt-4 uppercase">Guardar
                        Recurso</button>
                </form>
            </div>
        </div>

        <!---------------------------------------EDITAR MANUAL---------------------------------------------------------------->
        <div id="modalEditar"
            class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="bg-primary p-6 text-secondary flex justify-between items-center">
                    <h3 class="font-black uppercase tracking-widest text-lg">Editar Recurso</h3>
                    <button onclick="cerrarModal('modalEditar')"
                        class="material-symbols-outlined font-bold">close</button>
                </div>
                <form id="formEditar" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Titulo del Recurso</label>
                        <input type="text" name="titulo" id="edit_titulo"
                            class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Categoría</label>
                        <select name="categoria_id" id="edit_categoria_id"
                            class="w-full mt-1 p-3 bg-slate-50 border rounded-md" required>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria_manual }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Reemplazar Archivo (PDF o
                            MP4)</label>
                        <input type="file" name="archivo" id="archivo_edit" accept=".pdf,.mp4"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <button type="submit"
                        class="w-full bg-secondary text-primary font-black py-3 rounded-xl shadow-lg mt-4 uppercase">Actualizar</button>
                </form>
            </div>
        </div>

        <!------------------------------------CREAR CATEGORIA MANUAL----------------------------------------------------->
        <div id="modalCategorias"
            class="fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-[#04003B]/40 backdrop-blur-sm">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden">
                <div class="bg-primary p-6 text-secondary flex justify-between items-center">
                    <h3 class="font-black uppercase tracking-widest text-lg">Nueva Categoría</h3>
                    <button onclick="cerrarModal('modalCategorias')"
                        class="material-symbols-outlined font-bold">close</button>
                </div>
                <form id="formAgregar" action="{{ route('admin.categorias.store') }}" method="POST"
                    enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="text-[12px] font-black uppercase text-secondary">Nombre de la Categoría</label>
                        <input type="text" name="nombre_categoria_manual"
                            class="w-full mt-1 p-3 bg-slate-50 border rounded-xl" required>
                    </div>
                    <button type="submit"
                        class="w-full bg-secondary text-primary font-black py-3 rounded-xl shadow-lg mt-4 uppercase">Guardar
                        Categoría</button>
                </form>
            </div>
        </div>


        <!----------------------------------------VISOR DE MANUALES--------------------------------------------->
        <div id="modalVisor"
            class="hidden fixed inset-0 bg-[#04003B]/90 backdrop-blur-md flex items-center justify-center z-[10000] p-4">
            <div class="bg-white w-full h-full max-w-6xl rounded-3xl shadow-2xl overflow-hidden flex flex-col relative">
                <div class="bg-slate-50 px-8 py-4 flex justify-between items-center border-b border-slate-200">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white">
                            <span class="material-symbols-outlined" id="visor-icono">description</span>
                        </div>
                        <div>
                            <h3 id="visor-titulo"
                                class="font-black text-[#04003B] uppercase tracking-tight text-lg leading-tight">Vista
                                Previa
                                del
                                Recurso</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Sistema de Gestión
                                de
                                Manuales</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button onclick="cerrarVisor()"
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-200 text-slate-600 hover:bg-red-500 hover:text-white transition-all">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>

                <div id="contenedor-visor" class="flex-1 bg-slate-800 flex items-center justify-center overflow-hidden">
                </div>
            </div>
        </div>

        @endsection

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#04003B',
            });
        </script>
        @endif

        @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor verifique los datos ingresados.',
                confirmButtonColor: '#dc2626',
            });
        </script>
        @endif

        <script src="{{ asset('js/gestion-recursos.js') }}"></script>
        @endpush