@extends('layouts.admin')
@section('content')
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
                        <tr class="bg-slate-50 text-[14px] uppercase text-green-700 font-black tracking-widest">
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Usuario</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Solicitud</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Estado</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Prioridad</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Técnico</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black">Apertura</th>
                            <th class="px-4 py-4 border-b border-slate-200 font-black text-center">Detalle</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody" data-tipo="asignar" class="divide-y divide-slate-100 text-[12px]">
                        @include('partials.filas_asignar', ['tickets' => $tickets])
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

    {{------------------------------------------ MODAL DE USUARIO ------------------------------------------}}
    <div id="modalUsuario" class="fixed inset-0 z-[60] hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="cerrarModalUsuario()"></div>

            <div
                class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full overflow-hidden transform transition-all border-b-8 border-t-8 border-primary">
                <div class="p-8 text-center">
                    <div
                        class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-md">
                        <span class="material-symbols-outlined text-4xl text-secondary">account_circle</span>
                    </div>

                    <h3 id="userNombre" class="text-xl font-black text-secondary uppercase leading-tight mb-4">---</h3>

                    <div class="space-y-3 text-left">

                        {{-- Correo --}}
                        <a id="linkCorreo" href="#" target="_blank"
                            class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3 transition-all hover:bg-blue-50 hover:border-blue-200 group cursor-pointer no-underline block">

                            <span
                                class="material-symbols-outlined text-secondary group-hover:text-primary text-xl">email</span>

                            <div class="flex-1">
                                <label
                                    class="text-[10px] font-black text-secondary uppercase tracking-widest block group-hover:text-primary">
                                    Correo
                                </label>
                                <p id="userEmail" class="text-sm text-slate-700 font-bold">---</p>
                                <span
                                    class="text-[9px] text-slate-400 font-medium italic hidden group-hover:block transition-all">
                                    Abrir en Gmail
                                </span>
                            </div>

                            <span
                                class="material-symbols-outlined text-slate-300 group-hover:text-primary text-sm self-center">
                                open_in_new
                            </span>
                        </a>

                        {{-- Unidad --}}
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-xl">park</span>
                            <div>
                                <label class="text-[10px] font-black text-secondary uppercase tracking-widest block">Unidad
                                    / Parque</label>
                                <p id="userUnidad" class="text-sm text-slate-700 font-bold">---</p>
                            </div>
                        </div>

                        {{-- Cargo --}}
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-xl">work</span>
                            <div>
                                <label
                                    class="text-[10px] font-black text-secondary uppercase tracking-widest block">Cargo</label>
                                <p id="userCargo" class="text-sm text-slate-700 font-bold">---</p>
                            </div>
                        </div>

                        {{-- Teléfono --}}
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-xl">call</span>
                            <div>
                                <label
                                    class="text-[10px] font-black text-secondary uppercase tracking-widest block">Teléfono /
                                    Ext.</label>
                                <p id="userTelefono" class="text-sm text-slate-700 font-bold">---</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button onclick="cerrarModalUsuario()"
                            class="w-full py-3 bg-slate-100 text-slate-500 font-black rounded-2xl hover:bg-slate-200 transition-all uppercase tracking-widest text-xs">
                            Cerrar Perfil
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script src="{{ asset('js/asignar-tickets.js') }}"></script>

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