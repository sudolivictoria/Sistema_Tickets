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

            {{-- Tabla --}}
            <div class="p-5">
                {{-- Cabecera con Filtros y Buscador --}}
                <div
                    class="p-4 md:p-6 flex flex-col lg:flex-row gap-6 justify-between items-center bg-white border-b border-slate-100 whitespace-nowrap">

                    <div class="flex flex-col md:flex-row items-center gap-4 w-full lg:w-auto">
                        {{-- Filtros de Estados --}}
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start w-full" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)"
                                class="filtro-btn px-4 py-1.5 bg-secondary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">Todos</button>
                            <button type="button" onclick="filtrarEstado('abierto', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-red-100 hover:text-red-600 transition-all">Abierto</button>
                            <button type="button" onclick="filtrarEstado('procesando', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-blue-100 hover:text-blue-600 transition-all">Procesando</button>
                            <button type="button" onclick="filtrarEstado('resuelto,equivocado,no corresponde', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">Cerrado</button>
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
                            <tr class="bg-slate-50 text-[13px] uppercase text-[#008F7E] font-black tracking-widest">
                                <th class="px-4 py-4 border-b border-slate-200 font-black">ID</th>
                                <th class="px-4 py-4 border-b border-slate-200 font-black">Categoría</th>
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

    {{-- detalle ticket --}}
    @include('partials.detalle_ticket_usuario')
@endsection

{{-- SCRIPTS --}}
@push('scripts')
    <script src="{{ asset('js/mis-tickets.js') }}"></script>
@endpush
