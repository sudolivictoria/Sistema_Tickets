@extends('layouts.gestor')

@section('content')
    @push('css')
        @vite(['resources/css/tickets.css'])
    @endpush

    <div class="p-1">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">confirmation_number</span>
                Asignar Tickets
            </h2>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{-- Tabla --}}
            <div class="p-5">
                {{-- Cabecera con Filtros y Buscador --}}
                <div class="p-5 flex flex-wrap gap-4 justify-between items-center bg-white">
                    <div class="relative w-full md:w-72">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                        <input type="text" id="inputBusqueda"
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all"
                            placeholder="Buscar...">
                    </div>
                </div>

                <table id="tablaAsignarTickets" class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr
                            class="text-[13px] uppercase text-[#008F7E] font-extrabold tracking-widest border-b border-slate-200">
                            <th class="px-4 py-4 border-b border-slate-200 font-black">ID</th>
                            <th class="px-4 py-4 border-b border-slate-200">Usuario</th>
                            <th class="px-4 py-4 border-b border-slate-200">Estado</th>
                            <th class="px-4 py-4 border-b border-slate-200">Prioridad</th>
                            <th class="px-4 py-4 border-b border-slate-200">Técnico</th>
                            <th class="px-4 py-4 border-b border-slate-200">Apertura</th>
                            <th class="px-4 py-4 border-b border-slate-200 text-center">Detalle</th>
                        </tr>
                    </thead>

                    <tbody id="tablaBody" data-tipo="asignar" class="divide-y divide-slate-100 text-[13px]">
                        @include('partials.filas_asignar', ['tickets' => $tickets])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- detalle ticket --}}
    @include('partials.detalle_ticket')

    {{-- detalle usuario --}}
    @include('partials.detalle_usuario')
@endsection
@push('page-scripts')
    @vite(['resources/js/asignar-tickets.js'])
@endpush

@push('sse-scripts')
    @vite(['resources/js/api.js', 'resources/js/comentarios.js'])
@endpush
