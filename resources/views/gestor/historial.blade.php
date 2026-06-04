@extends('layouts.gestor')
@section('content')
    @push('css')
        @vite(['resources/css/tickets.css'])
    @endpush
<div class="flex justify-between items-center mb-8">
    <div>
        <div class="flex items-center gap-4">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">analytics</span>
                Historial
            </h2>
        </div>
    </div>
    <div class="min-w-[180px] px-6 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col items-end">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha de
            Consulta</p>
        <span class="text-base font-black text-secondary">{{ date('d/m/Y') }}</span>
    </div>
</div>
<!--estadisticas generales-->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    {{-- carga activa de trabajo --}}
    <div
        class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-orange-500 flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
        <div>
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Abiertos (Últimas 24h)</p>
            <h3 class="text-3xl font-black text-orange-500" id="metric-carga-trabajo">{{ $cargaTrabajo ?? 0 }}
                <span class="text-sm font-bold text-slate-400">Tkts</span>
            </h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">pending_actions</span>
        </div>
    </div>
    {{-- Resueltos hoy --}}
    <div
        class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-primary flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
        <div>
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Cerrados (Últimas 24h)</p>
            <h3 class="text-3xl font-black text-primary" id="metric-resueltos-24h">{{ $resueltos24h ?? 0 }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-green-50 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">bolt</span>
        </div>
    </div>
    {{-- Eficiencia Mensual --}}
    <div
        class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-secondary flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
        <div>
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Tasa de Cierre Mensual %</p>
            <h3 class="text-3xl font-black text-secondary" id="metric-tasa-cierre">{{ $tasaCierre ?? 0 }}
                <span class="text-sm font-bold text-secondary">%</span>
            </h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl">analytics</span>
        </div>
    </div>
</div>
<!--final estadisticas generales-->
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    {{-- BARRA DE FILTROS --}}
    <div
        class="p-5 border-b border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
        {{-- Búsqueda por ID o Nombre --}}
        <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Identificación</label>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" id="filtroBuscar"
                    class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold"
                    placeholder="ID, Usuario o Técnico...">
            </div>
        </div>
        {{-- Fecha Inicio --}}
        <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Fecha Inicio</label>
            <input type="date" id="filtroFechaInicio"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold text-slate-600">
        </div>
        {{-- Fecha Fin --}}
        <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Fecha Fin</label>
            <input type="date" id="filtroFechaFin"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold text-slate-600">
        </div>
        {{-- Filtrar por Estado --}}
        <div class="space-y-1.5">
            <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Estado Operacional</label>
            <select id="filtroEstado"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-black uppercase tracking-wider text-slate-600 cursor-pointer">
                <option value="todos" class="font-black">TODOS</option>
                @foreach ($estados as $est)
                    @if ($est->id == 1)
                        <option value="1" class="text-orange-500 font-black">{{ $est->nombre_estado }}</option>
                    @elseif($est->id == 2)
                        <option value="2" class="text-blue-500 font-black">{{ $est->nombre_estado }}</option>
                    @elseif($est->id == 3)
                        <option value="3" class="text-green-500 font-black">{{ $est->nombre_estado }}</option>
                    @elseif($est->id == 4)
                        <option value="4" class="text-red-500 font-black">{{ $est->nombre_estado }}</option>
                    @elseif($est->id == 5)
                        <option value="5" class="text-yellow-500 font-black">{{ $est->nombre_estado }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        {{-- Botones de Ejecución --}}
        <div class="flex gap-2 w-full">
            <button type="button" onclick="aplicarFiltrosHistorial()"
                class="flex-1 h-[38px] bg-secondary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md hover:bg-opacity-95 transition-all flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-base">filter_alt</span> Filtrar
            </button>
            <button type="button" onclick="limpiarFiltrosHistorial()"
                class="w-[42px] h-[38px] bg-slate-200 text-slate-600 rounded-xl text-xs font-black hover:bg-slate-300 transition-all flex items-center justify-center"
                title="Reiniciar Filtros">
                <span class="material-symbols-outlined text-lg">restart_alt</span>
            </button>
        </div>
    </div>
    {{-- TABLA DE HISTORIA --}}
    <div class="overflow-x-auto px-6 pb-6">
        <table id="tablaHistorial" class="w-full text-left border-separate border-spacing-0">
            <thead>
                <tr
                    class="bg-slate-50 text-[12px] uppercase text-[#008F7E] font-black tracking-widest border-b border-slate-200">
                    <th class="px-2 py-4 border-b border-slate-200 font-black">ID</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Usuario</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Prioridad</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Estado</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Tecnico</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Apertura</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Cierre</th>
                    <th class="px-2 py-4 border-b border-slate-200 font-black">Detalle</th>
                    <th class="hidden">Categoria</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-[12px]">
                @include('partials.filas_historial', ['tickets' => $tickets])
            </tbody>
        </table>
    </div>
</div>

{{-- detalle ticket --}}
@include('partials.detalle_ticket')

{{-- detalle usuario --}}
@include('partials.detalle_usuario')
@endsection

@push('page-scripts')
@vite(['resources/js/historial.js'])
@endpush

@push('sse-scripts')
@vite(['resources/js/api.js'])
@endpush
