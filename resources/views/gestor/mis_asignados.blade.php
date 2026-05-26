@extends('layouts.gestor')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">

    <div class="p-1">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">assignment_ind</span>
                Mis Asignados
            </h2>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{-- Tabla --}}
            <div class="p-5">
                {{-- Cabecera con Filtros y Buscador --}}
                <div class="p-5 flex flex-wrap gap-4 justify-between items-center bg-white">
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)"
                                class="filtro-btn px-4 py-1.5 bg-secondary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">Todos</button>
                            <button type="button" onclick="filtrarEstado('procesando', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-blue-100 hover:text-blue-600 transition-all">Procesando</button>
                            <button type="button" onclick="filtrarEstado('resuelto,equivocado,no corresponde', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">Cerrado</button>
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

                <table id="tablaMisAsignados" class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50 text-[13px] uppercase text-[#008F7E] font-black tracking-widest">
                            <th class="px-4 py-4 border-b border-slate-200 font-black">ID</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Usuario</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Estado</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Prioridad</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Técnico</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black text-center">Detalle</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody" data-tipo="mis_asignados" class="divide-y divide-slate-100 text-[12px]">
                        @include('partials.filas_mis_asignados', ['tickets' => $tickets])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- detalle ticket --}}
    @include('partials.detalle_ticket_completo')

    {{-- detalle usuario --}}
    @include('partials.detalle_usuario')
@endsection


@push('scripts')
    <script src="{{ asset('js/mis-asignados.js') }}"></script>

    @if (session('sweet_success'))
        <script>
            Swal.fire({
                title: '¡Actualizado Correctamente!',
                text: "{{ session('sweet_success') }}",
                icon: 'success',
                confirmButtonColor: '#04003B',
                confirmButtonText: 'Entendido',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs'
                }
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: 'No se pudo actualizar',
                html: '{!! implode('<br>', $errors->all()) !!}',
                icon: 'error',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Corregir',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs'
                }
            });
        </script>
    @endif
@endpush
