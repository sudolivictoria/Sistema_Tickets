@extends('layouts.usuario')

@section('content')
    <section class="bg-secondary p-6 md:p-10 rounded-3xl relative overflow-hidden shadow-xl border border-blue-800">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
        </div>
        <div class="relative z-10 flex flex-col xl:flex-row justify-between xl:items-end gap-6">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-secondary mb-2">Sistema de Solicitudes</p>
                <h2 class="text-2xl md:text-4xl sm:text-xl font-black text-white tracking-tighter leading-tight">Hola,
                    {{ auth()->user()->name ?? 'Usuario' }}
                </h2>
                <p class="text-white/90 mt-3 max-w-200 text-sm font-medium italic">
                    Gestiona tus solicitudes, consulta recursos útiles y mantente al tanto del estado de tus tickets en un
                    solo lugar. ¡Estamos aquí para ayudarte!
                </p>
            </div>
            <div class="w-full md:w-auto md:pb-4 md:px-2">
                <a href="{{ route('usuario.crear-ticket') }}"
                    class="w-full md:w-32 flex items-center justify-center gap-2 bg-primary text-secondary font-black py-3 rounded-xl shadow-lg hover:scale-[1.02] transition-all uppercase text-[10px] tracking-widest"
                    style="height: 40px; min-width: 120px;">
                    Nuevo Ticket
                </a>
            </div>
        </div>
    </section>

    {{-- Estadísticas de Tickets --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 md:gap-8">
        <div class="xl:col-span-3 space-y-6 md:space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <div class="bg-white p-6 rounded-2xl border-b-4 border-secondary shadow-sm flex items-center gap-4 hover:translate-y-[-6px] transition-all duration-300">
                    <div class="p-3 bg-red-50 text-red-500 rounded-2xl"><span
                            class="material-symbols-outlined text-2xl font-bold">priority_high</span></div>
                    <div>
                        <div id="contador-abiertos" class="text-2xl font-black text-secondary">{{ $abiertos ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-slate-400">Abiertos</div>
                    </div>
                </div>
                <div
                    class="bg-secondary p-6 rounded-2xl shadow-lg flex items-center gap-4 border-b-4 border-secondary text-white hover:translate-y-[-6px] transition-all duration-300">
                    <div class="p-3 bg-blue-700 text-secondary rounded-2xl"><span
                            class="material-symbols-outlined text-2xl">engineering</span></div>
                    <div>
                        <div id="contador-proceso" class="text-2xl font-black">{{ $enProceso ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-blue-200">En Proceso</div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border-b-4 border-secondary shadow-sm flex items-center gap-4 hover:translate-y-[-6px] transition-all duration-300">
                    <div class="p-3 bg-lime-50 text-primary rounded-2xl"><span
                            class="material-symbols-outlined text-2xl font-bold">check_circle</span></div>
                    <div>
                        <div id="contador-resueltos" class="text-2xl font-black text-secondary">{{ $resueltos ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-slate-400">Cerrados</div>
                    </div>
                </div>
            </div>

            {{-- Tickets Recientes --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-black text-lg tracking-[0.2em] text-secondary uppercase">Tickets Recientes</h3>
                </div>

                <div class="flex items-center gap-2 mb-2 lg:hidden text-slate-400">
                    <span class="material-symbols-outlined text-[18px] animate-bounce-x">swipe_left</span>
                    <span class="text-[11px] font-medium italic">Desliza para ver más detalles</span>
                </div>

                <div class="overflow-x-auto text-[13px] md:text-[14px]">
                    <table class="w-full min-w-[760px] text-left" id="tablaTicketsUsuario">
                        <thead class="bg-slate-50/50 border-b border-slate-100 uppercase font-black text-[#008F7E]">
                            <tr>
                                <th class="px-6 py-4 font-black">ID</th>
                                <th class="px-6 py-4 font-black">Categoría</th>
                                <th class="px-6 py-4 font-black">Estado</th>
                                <th class="px-6 py-4 font-black">Prioridad</th>
                                <th class="px-6 py-4 font-black">Apertura</th>
                                <th class="px-4 py-4 border-b border-slate-200 text-center font-black">Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" data-tipo="usuario" class="divide-y divide-slate-100 text-[12px]">
                            @include('partials.filas_usuario', ['tickets' => $todosLosTickets])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recursos --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-secondary/5 rounded-bl-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-secondary/30">folder_open</span>
                </div>

                <h4 class="text-[14px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-secondary rounded-full"></span> Recursos
                </h4>

                {{-- contenedor de categorias con scroll --}}
                <div class="space-y-3 overflow-y-auto pr-2 custom-scroll" style="max-height: 320px;">
                    @foreach ($categorias as $cat)
                        <a href="{{ route('usuario.recursos', ['categoria' => $cat->id]) }}"
                            class="flex items-center gap-2 p-3 rounded-lg bg-slate-50 hover:bg-secondary/10 transition-all group border border-transparent hover:border-secondary/20">
                            <div
                                class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-secondary transition-colors">
                                <span class="material-symbols-outlined text-lg">folder</span>
                            </div>
                            <div
                                class="overflow-hidden text-[12px] font-black text-slate-700 truncate group-hover:text-secondary transition-colors uppercase">
                                {{ $cat->nombre_categoria_manual }}
                            </div>
                        </a>
                    @endforeach
                </div>

                <a href="{{ route('usuario.recursos') }}"
                    class="w-full mt-6 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-secondary hover:text-secondary transition-all flex items-center justify-center bg-slate-50/50 hover:bg-white">
                    Ir al Repositorio
                </a>
            </div>
            {{-- Final Recursos --}}

            {{-- Canales de Atención --}}
            <div class="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                <div class="flex items-start gap-4 mb-8">
                    <div
                        class="bg-secondary size-8 rounded-xl flex items-center justify-center text-primary shadow-lg mt-1">
                        <span class="material-symbols-outlined text-xl font-light">headset_mic</span>
                    </div>
                    <div>
                        <h4 class="text-[14px] font-black uppercase tracking-[0.2em] text-secondary mb-2">USTS</h4>
                        <p class="text-[12px] text-slate-500 font-medium">Contacto Directo</p>
                    </div>
                </div>
                <button id="toggle-canales"
                    class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-primary hover:text-primary transition-all flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined">expand_more</span> Ver Canales
                </button>
                <div id="canales-list" class="space-y-4" style="display: none;">
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ljalvarez@istu.gob.sv" target="_blank"
                        class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                        <div
                            class="size-8 rounded-lg flex items-center justify-center text-secondary group-hover:text-primary">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-700 text-[12px] truncate">ljalvarez@istu.gob.sv
                            </div>
                        </div>
                    </a>

                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=mnrodriguez@istu.gob.sv" target="_blank"
                        class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                        <div
                            class="size-8 rounded-lg flex items-center justify-center text-secondary group-hover:text-primary">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-700 text-[12px] truncate">mnrodriguez@istu.gob.sv
                            </div>
                        </div>
                    </a>

                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=matorres@istu.gob.sv" target="_blank"
                        class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                        <div
                            class="size-8 rounded-lg flex items-center justify-center text-secondary group-hover:text-primary">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-700 text-[12px] truncate">matorres@istu.gob.sv
                            </div>
                        </div>
                    </a>

                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=jjramirez@istu.gob.sv" target="_blank"
                        class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                        <div
                            class="size-8 rounded-lg flex items-center justify-center text-secondary group-hover:text-primary">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-700 text-[12px] truncate">jjramirez@istu.gob.sv
                            </div>
                        </div>
                    </a>

                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ovquintanilla@istu.gob.sv" target="_blank"
                        class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                        <div
                            class="size-8 rounded-lg flex items-center justify-center text-secondary group-hover:text-primary">
                            <span class="material-symbols-outlined">mail</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-700 text-[12px] truncate">ovquintanilla@istu.gob.sv
                            </div>
                        </div>
                    </a>
                </div>

                <div class="mt-8 p-2 bg-slate-50 rounded-2xl border border-slate-100 flex gap-1.5 items-start">
                    <span class="material-symbols-outlined text-secondary mt-0.5">info</span>
                    <p class="text-[12px] text-slate-600 leading-relaxed font-medium">
                        Al hacer clic en un correo, se redirige automáticamente.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- detalle ticket --}}
    @include('partials.detalle_ticket')
@endsection

{{-- SCRIPTS --}}
@push('page-scripts')
    @vite(['resources/js/usuario.js'])
    @vite(['resources/js/usuario-menu.js'])
@endpush

@push('sse-scripts')
    @vite(['resources/js/api.js'])
@endpush
