@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center gap-4">
                <span class="text-4xl font-black text-slate-600">
                    Hola, <span class="text-secondary font-black">{{ auth()->user()->name ?? 'Administrador' }}</span>
                </span>
            </div>
            <p class="text-slate-500 text-sm font-medium italic py-4">Administración, seguimiento y resolución eficiente de
                solicitudes para el ISTU.</p>
        </div>
        <div class="min-w-[180px] px-6 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col items-end">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha de
                Consulta</p>
            <span class="text-base font-black text-green-900">{{ date('d/m/Y') }}</span>
        </div>
    </div>

    <!--estadisticas generales-->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-orange-500 flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-orange-600 text-3xl">notification_important</span>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Abiertos</p>
                <h3 id="contador-abiertos" class="text-3xl font-black text-slate-800 leading-none">{{ $noAsignados ?? 0 }}
                </h3>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-secondary flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-secondary text-3xl">pending_actions</span>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">En Proceso</p>
                <h3 id="contador-proceso" class="text-3xl font-black text-slate-800 leading-none">{{ $pendientes ?? 0 }}
                </h3>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-primary flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary text-3xl">verified</span>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none">
                        Cerrados
                    </p>
                    <span class="text-[11px] font-semibold text-slate-400 lowercase italic font-sans leading-none">
                        (este mes)
                    </span>
                </div>
                <h3 id="contador-resueltos" class="text-3xl font-black text-slate-800 leading-none">{{ $resueltos ?? 0 }}
                </h3>
            </div>
        </div>
    </div>
    <!--final estadisticas generales-->

    <!--rendimiento anual-->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-8">

            <div id="contenedor-grafico" class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="mb-10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="material-symbols-outlined text-primary text-2xl">leaderboard</span>
                        </div>
                        <h2 class="text-lg font-black text-secondary uppercase tracking-tight">
                            Rendimiento Anual {{ date('Y') }}
                        </h2>
                    </div>
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2"><span
                                class="size-3 rounded-full bg-primary shadow-sm"></span><span
                                class="text-[10px] font-black text-primary uppercase">Cerrados</span>
                        </div>
                        <div class="flex items-center gap-2"><span
                                class="size-3 rounded-full bg-red-200 shadow-sm"></span><span
                                class="text-[10px] font-black text-red-500 uppercase">Pendientes</span>
                        </div>
                    </div>
                </div>

                <div id="barras-rendimiento"
                    class="h-56 flex items-end justify-between px-4 gap-3 bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
                    @include('partials.grafico_rendimiento', ['mesesGrafico' => $mesesGrafico])
                </div>
            </div>
            <!--final rendimiento anual-->

            {{-- Tickets Registrados --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                {{-- Cabecera con Filtros y Buscador --}}
                <div class="p-5 border-b border-slate-100 flex flex-wrap gap-4 justify-between items-center bg-white">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)" data-estado="todos"
                                class="filtro-btn px-4 py-1.5 bg-secondary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">
                                Todos
                            </button>
                            <button type="button" onclick="filtrarEstado('abierto', this)" data-estado="abierto"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-red-100 hover:text-red-600 transition-all">
                                Abierto
                            </button>
                            <button type="button" onclick="filtrarEstado('procesando', this)" data-estado="procesando"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-blue-100 hover:text-blue-600 transition-all">
                                Pendientes
                            </button>
                            <button type="button" onclick="filtrarEstado('resuelto,equivocado,no corresponde', this)"
                                data-estado="resuelto,equivocado,no corresponde"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">
                                Cerrado
                            </button>
                        </div>
                    </div>
                </div>
                {{-- Contenedor con Scroll --}}
                <div
                    class="w-full bg-white rounded-2xl shadow-sm border border-slate-100/80 h-[400px] overflow-y-auto overflow-x-auto">
                    <table id="tablaAdmin" class="w-full text-left border-collapse table-fixed">
                        <thead class="sticky top-0 z-10 bg-slate-50 font-black">
                            <tr
                                class="text-[13px] uppercase text-[#008F7E] font-black tracking-widest border-b border-slate-200">
                                <th class="px-4 py-4 bg-slate-50 font-black border-b border-slate-200 w-[10%]">ID</th>
                                <th class="px-4 py-4 bg-slate-50 font-black border-b border-slate-200 w-[30%]">Usuario</th>
                                <th class="px-4 py-4 bg-slate-50 font-black border-b border-slate-200 w-[15%]">Estado</th>
                                <th class="px-4 py-4 bg-slate-50 font-black border-b border-slate-200 w-[15%]">Prioridad
                                </th>
                                <th class="px-4 py-4 bg-slate-50 font-black border-b border-slate-200 w-[20%]">Tecnico</th>
                                <th class="px-4 py-4 bg-slate-50 font-black border-b border-slate-200 w-[10%] text-center">
                                    Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" data-tipo="dashboard" class="divide-y divide-slate-100 text-[12px]">
                            @include('partials.filas_dashboard', ['todosLosTickets' => $todosLosTickets])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- Final Tickets Registrados --}}

        <div class="lg:col-span-1 space-y-6">
            <!--contador de tickets asignados-->
            <div class="bg-secondary p-8 rounded-3xl text-white shadow-xl relative overflow-hidden group">
                <div class="relative z-10">
                    <div
                        class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-secondary mb-4 shadow-lg group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined font-black">approval_delegation</span>
                    </div>
                    <h4 class="text-sm font-black uppercase tracking-wider mb-2">
                        Tickets Asignados
                    </h4>
                    <p id="contador-asignados" class="text-5xl font-black text-primary leading-none">
                        {{ $ticketsAsignados }}
                    </p>
                    <p class="text-sm text-slate-300 mt-3 font-medium">
                        Tickets asignados actualmente a tu usuario.
                    </p>
                </div>
                <span
                    class="material-symbols-outlined absolute -right-4 -bottom-4 text-9xl text-white/5 pointer-events-none">
                    assignment
                </span>
            </div>

            <!--prioridades-->
            <div class="mt-6 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-blue-100/50 rounded-bl-full flex items-center justify-center pointer-events-none">
                    <span class="material-symbols-outlined text-blue-200">low_priority</span>
                </div>
                <h4 class="text-[12px] font-black uppercase tracking-[0.2em] text-secondary mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-blue-200 rounded-full"></span>
                    Prioridades
                </h4>

                @php
                    $prioConfig = [
                        'critica' => [
                            'border' => 'border-red-300',
                            'numero' => 'text-red-500',
                            'texto' => 'text-red-800'
                        ],
                        'alta' => [
                            'border' => 'border-orange-300',
                            'numero' => 'text-orange-500',
                            'texto' => 'text-orange-800'
                        ],
                        'media' => [
                            'border' => 'border-amber-300',
                            'numero' => 'text-amber-500',
                            'texto' => 'text-amber-800'
                        ],
                        'baja' => [
                            'border' => 'border-green-300',
                            'numero' => 'text-green-500',
                            'texto' => 'text-green-800'
                        ],
                    ];
                @endphp

                <div class="grid grid-cols-2 gap-3">
                    @foreach($prioConfig as $prio => $clases)
                        <div
                            class="flex flex-col items-center justify-center p-4 rounded-xl bg-white border-2 border-dashed {{ $clases['border'] }} hover:scale-105 transition-transform shadow-sm">

                            <span id="prio-{{$prio}}"
                                class="text-[24px] font-black {{ $clases['numero'] }} tabular-nums leading-none">
                                {{ $prioridades[$prio] ?? 0 }}
                            </span>

                            <span class="font-black text-[9px] uppercase tracking-widest {{ $clases['texto'] }} mt-2">
                                {{ ucfirst($prio) }}
                            </span>

                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Call to Action --}}
            <div class="relative overflow-hidden bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-full flex items-center justify-center pointer-events-none">
                    <span class="material-symbols-outlined text-primary/30">auto_stories</span>
                </div>

                <div class="relative z-10">
                    <h4
                        class="text-[12px] font-black uppercase tracking-[0.2em] text-green-900 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-primary/50 rounded-full"></span>
                        Recursos
                    </h4>
                    <p class="text-[12px] text-slate-500 font-medium mb-6">
                        Explora nuestros recursos disponibles.
                    </p>
                    <a href="https://anyflip.com/bookcase/ghert" target="_blank"
                        class="group w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-primary hover:text-primary transition-all flex items-center justify-center bg-slate-50/50 hover:bg-white gap-2">
                        <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">
                            arrow_forward
                        </span>
                        Abrir Biblioteca
                    </a>
                </div>
            </div>
            {{-- Final Call to Action --}}
        </div>
    </div>

    {{-- detalle ticket --}}
    @include('partials.detalle_ticket_completo')

    {{-- detalle usuario --}}
    @include('partials.detalle_usuario')
@endsection

@push('page-scripts')
@vite(['resources/js/admin.js'])
@endpush

@push('sse-scripts')
@vite(['resources/js/api.js'])
@endpush