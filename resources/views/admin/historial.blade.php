@extends('layouts.admin')
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
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha de Consulta</p>
            <span class="text-base font-black text-secondary">{{ date('d/m/Y') }}</span>
        </div>
    </div>

    {{-- MÉTRICAS DE RESUMEN --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
            class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-orange-500 hover:translate-y-[-6px] transition-all duration-300">
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
        <div
            class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-primary hover:translate-y-[-6px] transition-all duration-300">
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Cerrados (Últimas 24h)</p>
                <h3 class="text-3xl font-black text-primary" id="metric-resueltos-24h">{{ $resueltos24h ?? 0 }}
                    <span class="text-sm font-bold text-slate-400">Tkts</span>
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-50 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">bolt</span>
            </div>
        </div>
        <div
            class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-secondary hover:translate-y-[-6px] transition-all duration-300">
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

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {{-- BARRA DE FILTROS --}}
        <div
            class="p-5 border-b border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
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
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Fecha Inicio</label>
                <input type="date" id="filtroFechaInicio"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold text-slate-600">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Fecha Fin</label>
                <input type="date" id="filtroFechaFin"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold text-slate-600">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Estado Operacional</label>
                <select id="filtroEstado"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-black uppercase tracking-wider text-slate-600 cursor-pointer">
                    <option value="todos" class="font-black">TODOS</option>
                    @foreach ($estados as $est)
                        <option value="{{ $est->id }}" class="font-black">{{ $est->nombre_estado }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Categoría</label>
                <select id="filtroCategoria"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-black uppercase tracking-wider text-slate-600 cursor-pointer">
                    <option value="todos" class="font-black">TODAS</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" class="font-black">{{ $categoria->nombre_categoria }}</option>
                    @endforeach
                </select>
            </div>

            {{-- BOTONES DE ACCIÓN Y EXPORTACIÓN --}}
            <div class="flex flex-col gap-1.5 w-full">
                <div class="flex gap-1.5 w-full">
                    <button type="button" onclick="aplicarFiltrosHistorial()"
                        class="flex-1 h-[36px] bg-secondary text-white rounded-xl text-[11px] font-black uppercase tracking-widest shadow-md hover:bg-opacity-95 transition-all flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">filter_alt</span> Filtrar
                    </button>
                    <button type="button" onclick="limpiarFiltrosHistorial()"
                        class="w-[36px] h-[36px] bg-slate-200 text-slate-600 rounded-xl hover:bg-slate-300 transition-all flex items-center justify-center"
                        title="Reiniciar Filtros">
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                    </button>
                </div>
                <div class="flex gap-1.5 w-full">
                    <button type="button" onclick="exportarHistorial('pdf')"
                        class="flex-1 h-[32px] bg-red-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-red-700 transition-all flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-xs">picture_as_pdf</span> PDF
                    </button>
                    <button type="button" onclick="exportarHistorial('excel')"
                        class="flex-1 h-[32px] bg-green-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-green-800 transition-all flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-xs">description</span> Excel
                    </button>
                </div>
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
                <tbody id="tablaBody" data-tipo="historial" class="divide-y divide-slate-100 text-[12px]">
                    @include('partials.filas_historial', ['tickets' => $tickets])
                </tbody>
            </table>
        </div>
    </div>

    @include('partials.detalle_ticket')
    @include('partials.detalle_usuario')
@endsection

@push('scripts')
    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Swal.fire({
                    title: '¡Atención!',
                    text: '{!! session('error') !!}',
                    icon: 'error',
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#04003B'
                });
            });
        </script>
    @endif
@endpush

@push('page-scripts')
    @vite(['resources/js/historial.js'])
@endpush
@push('sse-scripts')
    @vite(['resources/js/api.js'])
@endpush