@extends('layouts.gestor')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">

    <div class="p-1">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">confirmation_number</span>
                Mis tickets
            </h2>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{--Tabla--}}
            <div class="p-5">
                {{-- Cabecera con Filtros y Buscador --}}
                <div class="p-5 flex flex-wrap gap-4 justify-between items-center bg-white">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)"
                                class="filtro-btn px-4 py-1.5 bg-primary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">Todos</button>
                            <button type="button" onclick="filtrarEstado('abierto', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-red-100 hover:text-red-600 transition-all">Abierto</button>
                            <button type="button" onclick="filtrarEstado('procesando', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-blue-100 hover:text-blue-600 transition-all">Procesando</button>
                            <button type="button" onclick="filtrarEstado('resuelto', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">Resuelto</button>
                        </div>
                    </div>

                    <!---buscador-->
                    <div class="relative w-full md:w-72">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                        <input type="text" id="inputBusqueda"
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all"
                            placeholder="Buscar...">
                    </div>
                </div>

                <table id="tablaMisTickets" class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50 text-[13px] uppercase text-[#008F7E] font-black tracking-widest">
                            <th class="px-4 py-4 border-b border-slate-200 font-black">ID</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Categoría</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Solicitud</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Estado</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Prioridad</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Técnico</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Apertura</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Cierre</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody" data-tipo="mis_tickets" class="divide-y divide-slate-100 text-[13px]">
                        @include('partials.filas_mis_tickets', ['misTickets' => $misTickets])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{------------------------------------------------MODAL DE DETALLE-----------------------------------------}}
    <div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="cerrarModal()"></div>
            <div
                class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-primary">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <h3 id="modalTitulo" class="text-xl font-black text-secondary uppercase">---</h3>
                        <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 mb-2 text-secondary">
                            <span class="material-symbols-outlined text-[16px] text-primary">description</span>
                            <label class="text-[11px] font-black uppercase tracking-widest">Descripción de la
                                solicitud</label>
                        </div>
                        <div id="modalDescripcion"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line max-h-[200px] overflow-y-auto custom-scrollbar">
                            ---
                        </div>
                    </div>
                    <div class="mt-8">
                        <button onclick="cerrarModal()"
                            class="w-full py-4 bg-primary text-white font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-sm shadow-lg shadow-primary/20">
                            Cerrar Detalle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- SCRIPTS --}}
@push('scripts')
    <script src="{{ asset('js/mis-tickets.js') }}"></script>
@endpush