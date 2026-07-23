@extends('layouts.usuario')

@section('content')
    @push('css')
        @vite(['resources/css/tickets.css'])
    @endpush

    <div class="p-1">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">history</span>
                Mis tickets
            </h2>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{-- Contenedor Principal --}}
            <div class="p-5">
                {{-- Contenedor de Filtros con Scroll Horizontal en móviles --}}
                <div class="flex flex-col xl:flex-row gap-4 justify-between items-start xl:items-center bg-white mb-4">
                    <div class="w-full xl:w-auto overflow-x-auto pb-2 xl:pb-0 scrollbar-hide">
                        {{-- El contenedor de los botones --}}
                        <div class="flex gap-2 whitespace-nowrap min-w-max" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)" data-estado="todos"
                                class="filtro-btn px-4 py-1.5 bg-secondary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">
                                Todos
                            </button>
                            <button type="button" onclick="filtrarEstado('abierto', this)" data-estado="abierto"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase transition-all">
                                Abierto
                            </button>
                            <button type="button" onclick="filtrarEstado('procesando', this)" data-estado="procesando"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase transition-all">
                                Procesando
                            </button>
                            <button type="button" onclick="filtrarEstado('resuelto,equivocado,no corresponde', this)"
                                data-estado="resuelto,equivocado,no corresponde"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase transition-all">
                                Cerrado
                            </button>
                        </div>
                    </div>

                    <div class="relative w-full xl:w-72 shrink-0">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                        <input type="text" id="inputBusqueda"
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all"
                            placeholder="Buscar...">
                    </div>

                    <div class="flex items-center gap-2 mb-2 lg:hidden text-slate-400">
                        <span class="material-symbols-outlined text-[18px] animate-bounce-x">swipe_left</span>
                        <span class="text-[11px] font-medium italic">Desliza para ver más detalles</span>
                    </div>

                </div>

                <div class="p-0 w-full overflow-x-auto">
                    <table id="tablaMisTickets" class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 text-[13px] uppercase text-[#008F7E] font-black tracking-widest">
                                <th class="px-4 py-4 border-b border-slate-200 font-black whitespace-nowrap">ID</th>
                                <th class="px-4 py-4 border-b border-slate-200 whitespace-nowrap">Categoría</th>
                                <th class="px-4 py-4 border-b border-slate-200 whitespace-nowrap">Estado</th>
                                <th class="px-4 py-4 border-b border-slate-200 whitespace-nowrap">Prioridad</th>
                                <th class="px-4 py-4 border-b border-slate-200 whitespace-nowrap">Técnico</th>
                                <th class="px-4 py-4 border-b border-slate-200 whitespace-nowrap">Apertura</th>
                                <th class="px-4 py-4 border-b border-slate-200 whitespace-nowrap">Cierre</th>
                                <th class="px-4 py-4 border-b border-slate-200 text-center whitespace-nowrap">Detalle
                                </th>
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

    {{-- detalle usuario --}}
@endsection

@push('page-scripts')
    @vite(['resources/js/mis-tickets.js'])
    @vite(['resources/js/usuario-menu.js'])
@endpush

@push('sse-scripts')
    @vite(['resources/js/api.js', 'resources/js/comentarios.js'])
@endpush
