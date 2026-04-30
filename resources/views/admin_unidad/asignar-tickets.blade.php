@extends('layouts.admin_unidad')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
        
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">

    <div class="p-1">
        <div class="mb-10 border-b border-slate-200 pb-6">
            <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                <span class="material-symbols-outlined text-4xl text-primary">confirmation_number</span>
                Asignar Tickets
            </h2>
            <p class="text-slate-500 font-medium italic">Asigne tickets a técnicos disponibles</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{--Tabla--}}
            <div class="p-5">
                {{-- Cabecera con Filtros y Buscador --}}
                <div class="p-5 flex flex-wrap gap-4 justify-between items-center bg-white">
                    <div class="relative w-full md:w-72">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                        <input type="text" id="inputBusqueda"
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none transition-all"
                            placeholder="Buscar por asunto, técnico...">
                    </div>
                </div>

                <table id="tablaAsignarTickets" class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50 text-[14px] uppercase text-green-900 font-black tracking-widest">
                            <th class="px-4 py-4 border-b border-slate-200">Usuario</th>
                            <th class="px-4 py-4 border-b border-slate-200">Unidad</th>
                            <th class="px-4 py-4 border-b border-slate-200">Solicitud</th>
                            <th class="px-4 py-4 border-b border-slate-200">Estado</th>
                            <th class="px-4 py-4 border-b border-slate-200">Prioridad</th>
                            <th class="px-4 py-4 border-b border-slate-200">Técnico</th>
                            <th class="px-4 py-4 border-b border-slate-200">Apertura</th>
                            <th class="px-4 py-4 border-b border-slate-200 text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13px]">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-slate-50/80 transition-all">
                                {{-- Usuario --}}
                                <td class="px-4 py-4 font-bold text-slate-900">
                                    {{ $ticket->user->nombre_completo ?? 'N/A' }}
                                </td>

                                {{-- Unidad --}}
                                <td class="px-4 py-4 font-bold text-slate-900">
                                    {{ $ticket->user->unidad->nombre_unidad ?? 'N/A' }}
                                </td>

                                {{-- Tipo Solicitud --}}
                                <td class="px-4 py-4 text-slate-900 font-bold">
                                    {{ $ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A' }}
                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-4">
                                    @php
                                        $estado = strtolower($ticket->estado->nombre_estado ?? 'abierto');
                                        $claseEstado = match ($estado) {
                                            'abierto' => 'bg-red-100 text-red-700 border-red-200',
                                            'procesando' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'resuelto' => 'bg-green-100 text-green-700 border-green-200',
                                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                                        };
                                    @endphp
                                    <span
                                        class="status-label px-2 py-1 rounded-md border font-black text-[10px] uppercase {{ $claseEstado }}">{{ ucfirst($estado) }}</span>
                                </td>

                                {{-- Prioridad --}}
                                <td class="px-4 py-4">
                                    <form action="{{ route('adminunidad.actualizar-prioridad', $ticket->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <select name="prioridad_id" onchange="this.form.submit()"
                                            class="bg-transparent font-black text-secondary text-[12px] border-none focus:ring-0 cursor-pointer">
                                            <option value="1" {{ $ticket->prioridad_id == 1 ? 'selected' : '' }}>Critica</option>
                                            <option value="2" {{ $ticket->prioridad_id == 2 ? 'selected' : '' }}>Alta</option>
                                            <option value="3" {{ $ticket->prioridad_id == 3 ? 'selected' : '' }}>Media</option>
                                            <option value="4" {{ $ticket->prioridad_id == 4 ? 'selected' : '' }}>Baja</option>
                                        </select>
                                    </form>
                                </td>

                                {{-- Técnico --}}
                                <td class="px-4 py-4">
                                    <form action="{{ route('adminunidad.actualizar-tecnico', $ticket->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <select name="tecnico_id" onchange="this.form.submit()"
                                            class="bg-transparent font-black text-secondary text-[12px] border-none focus:ring-0 cursor-pointer w-32">
                                            <option value="">Pendiente</option>
                                            @foreach($tecnicos as $tecnico)
                                                <option value="{{ $tecnico->id }}" {{ $ticket->tecnico_id == $tecnico->id ? 'selected' : '' }}>
                                                    {{ $tecnico->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>

                                {{-- Fechas --}}
                                <td class="px-4 py-4 font-bold text-slate-900">
                                    {{ $ticket->created_at->format('d/m/Y') }}
                                </td>
        

                                {{-- Botón Detalle (Descripción) --}}
                                <td class="px-4 py-4 text-center">
                                    <button type="button"
                                        onclick="verDetalle('{{ addslashes($ticket->asunto) }}', '{{ addslashes($ticket->descripcion) }}')"
                                        class="p-2 bg-slate-100 text-primary rounded-xl hover:bg-primary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
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
                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] font-black text-secondary uppercase tracking-widest">Descripción de la
                                solicitud</label>
                            <div id="modalDescripcion"
                                class="mt-2 p-5 bg-slate-50 border border-slate-100 rounded-2xl text-slate-600 text-sm leading-relaxed whitespace-pre-line italic">
                                ---
                            </div>
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


@push('scripts')
    {{-- Librerías --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwind.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('js/asignar-tickets.js') }}"></script>

    <script>
        $(document).ready(function () {
            inicializarTablaTickets('#tablaAsignarTickets');
        });
    </script>

    @if(session('sweet_success'))
        <script>
            Swal.fire({
            title: '¡Actualizado Correctamente!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#1e3a8a',
            confirmButtonText: 'Entendido',
            customClass: { popup: 'rounded-3xl', confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs' }
        });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                title: 'No se pudo actualizar',
                html: '{!! implode("<br>", $errors->all()) !!}',
                icon: 'error',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Corregir',
                customClass: { popup: 'rounded-3xl', confirmButton: 'px-10 py-3.5 rounded-2xl font-black uppercase tracking-widest text-xs' }
            });
        </script>
    @endif

@endpush