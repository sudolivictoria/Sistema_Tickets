@extends('layouts.admin')

@section('content')

    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center gap-4">
                <span class="text-4xl font-medium text-slate-600">
                    Hola, <span class="text-secondary font-bold">{{ auth()->user()->name ?? 'Administrador'}}</span>
                </span>
            </div>
            <p class="text-slate-500 text-sm font-medium italic py-4">Administración, seguimiento y resolución eficiente de
                solicitudes para el ISTU.</p>
        </div>
        <div class="min-w-[180px] px-6 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col items-end">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha de
                Consulta</p>
            <span class="text-base font-black text-secondary">{{ date('d/m/Y') }}</span>
        </div>
    </div>

    <!--estadisticas generales-->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-red-500 flex items-center gap-5 hover:translate-y-[-4px] transition-all duration-300">
            <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-red-600 text-3xl">notification_important</span>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Abiertos</p>
                <h3 id="cont-abiertos" class="text-3xl font-black text-slate-800 leading-none">{{ $noAsignados ?? 0 }}</h3>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-blue-500 flex items-center gap-5 hover:translate-y-[-4px] transition-all duration-300">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-blue-600 text-3xl">pending_actions</span>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">En Proceso</p>
                <h3 id="cont-proceso" class="text-3xl font-black text-slate-800 leading-none">{{ $pendientes ?? 0 }}</h3>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-primary flex items-center gap-5 hover:translate-y-[-4px] transition-all duration-300">
            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary text-3xl">verified</span>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Resueltos</p>
                <h3 id="cont-resueltos" class="text-3xl font-black text-slate-800 leading-none">{{ $resueltos ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <!--final estadisticas generales-->

    <!--rendimiento anual-->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-8">

            <div id="contenedor-grafico" class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="mb-10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="material-symbols-outlined text-primary text-2xl">leaderboard</span>
                        </div>
                        <h2 class="text-lg font-black text-secondary uppercase tracking-tight">
                            Rendimiento Anual {{ date('Y') }}
                        </h2>
                    </div>
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2"><span
                                class="size-3 rounded-full bg-primary shadow-sm"></span><span
                                class="text-[10px] font-black text-primary uppercase">Resueltos</span>
                        </div>
                        <div class="flex items-center gap-2"><span
                                class="size-3 rounded-full bg-red-200 shadow-sm"></span><span
                                class="text-[10px] font-black text-red-500 uppercase">Pendientes</span>
                        </div>
                    </div>
                </div>

                <div
                    class="h-56 flex items-end justify-between px-4 gap-3 bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
                    @foreach($mesesGrafico as $mes)
                        <div class="flex-1 flex flex-col items-center gap-3 group h-full justify-end">
                            <div class="w-full max-w-[36px] flex flex-col justify-end gap-1 h-full">
                                <div class="w-full bg-red-500 rounded-t-md hover:brightness-110 transition-all duration-500 relative shadow-[0_-2px_10px_rgba(132,204,22,0.2)]"
                                    style="height: {{ $mes['pendientes_pct'] }}%">
                                    <span
                                        class="absolute -top-7 left-1/2 -translate-x-1/2 text-[10px] font-black text-red-500 bg-white px-1.5 py-0.5 rounded shadow-sm border border-slate-100 opacity-0 group-hover:opacity-100 transition-opacity z-10">{{ $mes['pendientes_pct'] }}%</span>
                                </div>
                                <div class="w-full bg-primary rounded-t-md hover:brightness-110 transition-all duration-500 relative shadow-[0_-2px_10px_rgba(132,204,22,0.2)]"
                                    style="height: {{ $mes['resueltos_pct'] }}%">
                                    <span
                                        class="absolute -top-7 left-1/2 -translate-x-1/2 text-[10px] font-black text-primary bg-white px-1.5 py-0.5 rounded shadow-sm border border-slate-100 opacity-0 group-hover:opacity-100 transition-opacity z-10">{{ $mes['resueltos_pct'] }}%</span>
                                </div>
                            </div>
                            <span
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ substr($mes['nombre'], 0, 3) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <!--final rendimiento anual-->

            <!--tickets registrados-->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                {{-- Cabecera con Filtros y Buscador --}}
                <div class="p-5 border-b border-slate-100 flex flex-wrap gap-4 justify-between items-center bg-white">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-bold text-secondary">Tickets</h2>
                        <div class="flex gap-2" id="filtrosEstado">
                            <button type="button" onclick="filtrarEstado('todos', this)"
                                class="filtro-btn px-4 py-1.5 bg-secondary text-white rounded-xl text-[12px] font-black uppercase shadow-md transition-all">Todos</button>
                            <button type="button" onclick="filtrarEstado('1', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-red-100 hover:text-red-600 transition-all">Abierto</button>
                            <button type="button" onclick="filtrarEstado('2', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-blue-100 hover:text-blue-600 transition-all">Procesando</button>
                            <button type="button" onclick="filtrarEstado('3', this)"
                                class="filtro-btn px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[12px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">Resuelto</button>
                        </div>
                    </div>
                </div>

                {{-- Contenedor con Scroll --}}
                <div id="refresco-tablaTickets">
                    <div class="overflow-y-auto" style="max-height: 400px;">
                        <table class="w-full text-left border-separate border-spacing-0" id="tablaTickets">
                            <thead class="sticky top-0 z-10 bg-slate-50 font-black">
                                <tr
                                    class="text-[14px] uppercase text-green-700 font-black tracking-widest border-b border-slate-200">
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Usuario</th>
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Solicitud</th>
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Prioridad</th>
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Estado</th>
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Apertura</th>
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Tecnico</th>
                                    <th class="px-4 py-4 border-b border-slate-200 font-black">Detalle</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBody" class="divide-y divide-slate-100 text-[12px]">
                                @foreach($todosLosTickets as $ticket)
                                    <tr class="hover:bg-slate-50/80 transition-all ticket-fila"
                                        data-estado-id="{{ $ticket->estado_id }}">
                                        <!--DATOS DEL USUARIO-->
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col">
                                                <button type="button" onclick="verUsuario(
                                                        '{{ $ticket->user->name }}', 
                                                        '{{ $ticket->user->email }}', 
                                                        '{{ $ticket->user->unidad->nombre_unidad}}', 
                                                        '{{ $ticket->user->cargo }}', 
                                                        '{{ $ticket->user->telefono ?? 'N/A' }}'
                                                    )"
                                                    class="text-slate-900 font-bold hover:text-primary transition-all text-left flex items-center gap-2 group">
                                                    {{ $ticket->user->name }}
                                                    <span
                                                        class="material-symbols-outlined text-[16px] text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                                                        visibility
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                        <!-- FINAL DATOS DE USUARIO -->

                                        <td class="px-4 py-4 max-w-[150px] text-slate-900 font-bold">
                                            {{ $ticket->tipo_solicitud->nombre_tipo_solicitud }}
                                        </td>

                                        <td class="px-4 py-4">
                                            @php
                                                $prio = $ticket->prioridad->nombre_prioridad ?? 'Baja';
                                                $clasePrio = match ($prio) {
                                                    'Critica' => 'bg-red-100 text-red-700 border-red-200',
                                                    'Alta' => 'bg-orange-100 text-orange-700 border-orange-200',
                                                    'Media' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                    'Baja' => 'bg-green-100 text-green-700 border-green-200',
                                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                                };
                                            @endphp
                                            <span
                                                class="px-2 py-1 rounded-md border font-black text-[10px] uppercase {{ $clasePrio }}">
                                                {{ $prio }}
                                            </span>
                                        </td>

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
                                                class="px-2 py-1 rounded-md border font-black text-[10px] uppercase {{ $claseEstado }}">
                                                {{ ucfirst($estado) }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-4 font-bold text-slate-900">
                                            {{ $ticket->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-4 font-bold text-slate-900 italic">
                                            {{ $ticket->tecnico->name ?? 'Pendiente' }}
                                        </td>

                                        {{-- BOTON DESCRIPCION DEL TICKET --}}
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
        </div>
        <!--final tickets-->

        <!--recursos-->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary/30">folder_open</span>
                </div>
                <h4 class="text-[14px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-primary rounded-full"></span>
                    Recursos
                </h4>
                <div class="space-y-3">
                    @foreach($manuales as $manual)
                        <a href="{{ $manual->url_archivo }}"
                            class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-primary/10 transition-all border border-transparent hover:border-primary/20 group">
                            <div
                                class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-primary">
                                <span class="material-symbols-outlined text-xl">description</span>
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-[11px] font-black text-slate-700 truncate">
                                    {{ $manual->titulo }}
                                </div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase">
                                    {{ $manual->categoria ?? 'General' }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('usuario.recursos') }}"
                    class="w-full mt-6 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-primary hover:text-primary transition-all flex items-center justify-center">
                    Ir al Repositorio
                </a>
            </div>
            <!--final recursos-->

            <!--protocolo critico-->
            <div class="bg-secondary p-8 rounded-3xl text-white shadow-xl relative overflow-hidden group">
                <div class="relative z-10">
                    <div
                        class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-secondary mb-4 shadow-lg group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined font-black">emergency</span>
                    </div>
                    <h4 class="text-sm font-black uppercase tracking-wider mb-2">Protocolo</h4>
                    <p class="text-[14px] text-slate-300 leading-relaxed font-medium">
                        Tickets con estado <span class="text-primary font-bold italic underline">CRÍTICO</span> requieren
                        solución inmediata.
                    </p>
                </div>
                <span
                    class="material-symbols-outlined absolute -right-4 -bottom-4 text-9xl text-white/5 pointer-events-none">warning</span>
            </div>
            <!--protocolo critico-->
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
                            <span class="material-symbols-outlined text-secondary text-xl">domain</span>
                            <div>
                                <label
                                    class="text-[10px] font-black text-secondary uppercase tracking-widest block">Unidad</label>
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
    <script src="{{ asset('js/admin.js') }}"></script>
    <script src="{{ asset('js/admin-refresco.js') }}"></script>
@endpush