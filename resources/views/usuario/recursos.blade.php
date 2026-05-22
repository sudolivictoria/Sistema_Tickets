@extends('layouts.usuario')

@section('content')
    <div class="p-8 bg-slate-50 min-h-screen">
        <div class="flex justify-between items-center mb-10 border-b border-slate-200 pb-6">
            <div>
                <h2 class="text-3xl font-black text-primary mb-2 flex items-center gap-3">
                    <span class="material-symbols-outlined text-4xl text-secondary">library_books</span>
                    Recursos
                </h2>
            </div>
        </div>

        <!--------------------------FILTROS------------------------------>
        <div class="flex flex-wrap gap-3 mb-10">

            <button onclick="filtrar('all', event)"
                class="filter-btn active bg-white text-[#04003B] px-6 py-2 rounded-full border-2 border-[#04003B] font-semibold uppercase text-xs tracking-wider hover:border-[#04003B] hover:text-[#04003B] transition">
                Todos
            </button>

            @foreach ($categorias as $cat)
                <button type="button"
                    class="filter-btn bg-white text-slate-600 border-2 border-slate-200 px-6 py-2 rounded-full font-semibold uppercase text-xs tracking-wider hover:border-[#04003B] hover:text-[#04003B] transition"
                    data-id="{{ $cat->id }}" onclick="filtrar({{ $cat->id }}, event)">

                    {{ $cat->nombre_categoria_manual }}
                </button>
            @endforeach

        </div>
        <!--------------------------CARDS------------------------------>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-tipo="recursos" id="contenedor-manuales">
            @include('partials.filas_recursos', ['manuales' => $manuales])
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
                            class="font-black text-[#04003B] uppercase tracking-tight text-lg leading-tight">Vista Previa del
                            Recurso</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Sistema de Gestión de
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

    @push('scripts')
        <script src="{{ asset('js/recursos.js') }}"></script>
    @endpush
@endsection
