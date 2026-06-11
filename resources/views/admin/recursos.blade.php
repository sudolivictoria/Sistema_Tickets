@extends('layouts.admin')

@section('content')
    <div class="p-8 bg-slate-50 min-h-screen">

        <div class="flex justify-between items-center mb-10 border-b border-slate-200 pb-6">
            <div>
                <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                    <span class="material-symbols-outlined text-4xl text-primary">library_books</span>
                    Recursos
                </h2>
            </div>
        </div>

        {{--
        =============================================================================
        NOTA DE RESTAURACIÓN: En el caso de restauración de la función nativa para
        gestionar manuales, descomentar este bloque y asegurarse de agregar el
        script en la vista: 
        @push('page-scripts')
        @vite(['resources/js/recursos.js'])
        @endpush
        =============================================================================

        <div class="flex gap-3 mb-10 overflow-x-auto pb-2" id="contenedor-categorias">
            @include('partials.filtros_recursos', ['categorias' => $categorias])
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-tipo="recursos" id="contenedor-manuales">
            @include('partials.filas_recursos', ['manuales' => $manuales])
        </div>

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
                                Previa del Recurso</h3>
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
        --}}

        <div
            class="bg-white rounded-2xl md:rounded-3xl shadow-xl border border-slate-200 overflow-hidden w-full h-[450px] sm:h-[600px] lg:h-[800px] relative">

            <div id="loader-anyflip"
                class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50 z-10 transition-opacity duration-500">
                <span class="material-symbols-outlined animate-spin text-primary text-5xl mb-4">refresh</span>
                <p class="text-[12px] font-black text-slate-400 animate-pulse tracking-widest uppercase">
                    Cargando Biblioteca...
                </p>
            </div>

            <iframe src="https://anyflip.com/bookcase/ghert"
                class="absolute top-0 left-0 w-full h-full border-0 opacity-0 transition-opacity duration-1000"
                allowfullscreen="true" scrolling="no" loading="lazy"
                onload="document.getElementById('loader-anyflip').style.opacity='0'; setTimeout(() => document.getElementById('loader-anyflip').style.display='none', 500); this.classList.remove('opacity-0');">
            </iframe>
        </div>

    </div>
@endsection