@extends('layouts.usuario')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">

    <div class="p-1">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-primary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-secondary">confirmation_number</span>
                Mis tickets
            </h2>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{--Tabla--}}
            <div class="p-5">
                {{-- Cabecera con Filtros y Buscador --}}
                <div
                    class="p-4 md:p-6 flex flex-col lg:flex-row gap-6 justify-between items-center bg-white border-b border-slate-100 whitespace-nowrap">

                    <div class="flex flex-col md:flex-row items-center gap-4 w-full lg:w-auto">
                        <h2 class="text-xl font-bold text-primary hidden xl:block">Tickets</h2>

                        {{-- Filtros de Estados --}}
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start w-full" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)"
                                class="filtro-btn flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-xl text-[11px] font-black uppercase shadow-md transition-all whitespace-nowrap">
                                Todos
                            </button>
                            <button type="button" onclick="filtrarEstado('abierto', this)"
                                class="filtro-btn flex-1 sm:flex-none px-4 py-2 bg-slate-100 text-slate-500 rounded-xl text-[11px] font-black uppercase hover:text-red-600 hover:bg-red-100 transition-all whitespace-nowrap">
                                Abierto
                            </button>
                            <button type="button" onclick="filtrarEstado('procesando', this)"
                                class="filtro-btn flex-1 sm:flex-none px-4 py-2 bg-slate-100 text-slate-500 rounded-xl text-[11px] font-black uppercase hover:text-blue-600 hover:bg-blue-100 transition-all whitespace-nowrap">
                                Procesando
                            </button>
                            <button type="button" onclick="filtrarEstado('resuelto', this)"
                                class="filtro-btn flex-1 sm:flex-none px-4 py-2 bg-slate-100 text-slate-500 rounded-xl text-[11px] font-black uppercase hover:text-green-600 hover:bg-green-100 transition-all whitespace-nowrap">
                                Resuelto
                            </button>
                        </div>
                    </div>

                    {{-- Buscador --}}
                    <div class="relative w-full md:max-w-md lg:w-72">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                        <input type="text" id="inputBusqueda"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all placeholder:text-slate-400 font-medium"
                            placeholder="Buscar...">
                    </div>
                </div>


                <div class="flex items-center gap-2 mb-2 lg:hidden text-slate-400">
                    <span class="material-symbols-outlined text-[18px] animate-bounce-x">swipe_left</span>
                    <span class="text-[11px] font-medium italic">Desliza para ver más detalles</span>
                </div>


                <div class="p-0 w-full overflow-x-auto">
                    <table id="tablaMisTickets" class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 text-[13px] uppercase text-green-700 font-black tracking-widest">
                                <th class="px-4 py-4 border-b border-slate-200 font-black">ID</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Categoría</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Solicitud</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Estado</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Prioridad</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Técnico</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Apertura</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Cierre</th>
                                <th class="px-4 py-4 border-b border-slate-200 text-center font-black">Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" data-tipo="mis_tickets" class="divide-y divide-slate-100 text-[13px]">
                            @include('partials.filas_mis_tickets', ['misTickets' => $misTickets])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{------------------------------------------------MODAL DE DETALLE-----------------------------------------}}
    <div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="cerrarModal()"></div>
            <div
                class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-secondary">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <h3 id="modalTitulo" class="text-xl font-black text-primary uppercase">---</h3>
                        <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] font-black text-primary uppercase tracking-widest">Descripción de la
                                solicitud</label>
                            <div id="modalDescripcion"
                                class="mt-2 p-5 bg-slate-50 border border-slate-100 rounded-2xl text-slate-600 text-sm leading-relaxed whitespace-pre-line italic">
                                ---
                            </div>
                        </div>
                    </div>
                    <div class="mt-8">
                        <button onclick="cerrarModal()"
                            class="w-full py-4 bg-secondary text-white font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-sm shadow-lg shadow-secondary/20">
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