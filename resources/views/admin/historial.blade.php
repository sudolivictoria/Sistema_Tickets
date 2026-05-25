@extends('layouts.admin')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/tickets.css') }}">
    <div class="flex justify-between items-center mb-8">
        <div>
            <div class="flex items-center gap-4">
                <h2 class="text-3xl font-black text-secondary mb-2 flex items-center gap-3">
                    <span class="material-symbols-outlined text-4xl text-primary">contract</span>
                    Historial
                </h2>
            </div>
            <p class="text-slate-500 text-sm font-medium italic py-4">Registro y Consulta de tickets.</p>
        </div>
        <div class="min-w-[180px] px-6 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col items-end">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha de
                Consulta</p>
            <span class="text-base font-black text-secondary">{{ date('d/m/Y') }}</span>
        </div>
    </div>
    <!--estadisticas generales-->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{--carga activa de trabajo--}}
        <div
            class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-orange-500 flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Abiertos (Últimas 24h)</p>
                <h3 class="text-3xl font-black text-orange-500">{{ $cargaTrabajo ?? 0 }}
                    <span class="text-sm font-bold text-slate-400">Tkts</span>
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
        </div>
        {{--Resueltos hoy--}}
        <div
            class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-primary flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Cerrados (Últimas 24h)</p>
                <h3 class="text-3xl font-black text-primary">{{ $resueltos24h ?? 0 }}</h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-50 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">bolt</span>
            </div>
        </div>
        {{--Eficiencia Mensual--}}
        <div
            class="rounded-2xl bg-white p-5 flex items-center justify-between shadow-sm border-t-4 border-secondary flex items-center gap-5 hover:translate-y-[-6px] transition-all duration-300">
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Tasa de Cierre Mensual</p>
                <h3 class="text-3xl font-black text-secondary">{{ $tasaCierre ?? 0 }}
                    <span class="text-sm font-bold text-secondary">%</span>
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-secondary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">analytics</span>
            </div>
        </div>
    </div>
    <!--final estadisticas generales-->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {{--BARRA DE FILTROS--}}
        <div
            class="p-5 border-b border-slate-200 bg-slate-50/50 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            {{--Búsqueda por ID o Nombre--}}
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
            {{--Fecha Inicio--}}
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Fecha Inicio</label>
                <input type="date" id="filtroFechaInicio"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold text-slate-600">
            </div>
            {{--Fecha Fin--}}
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Fecha Fin</label>
                <input type="date" id="filtroFechaFin"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-semibold text-slate-600">
            </div>
            {{--Filtrar por Estado--}}
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Estado Operacional</label>
                <select id="filtroEstado"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-black uppercase tracking-wider text-slate-600 cursor-pointer">
                    <option value="todos" class="font-black">ESTADOS</option>
                    @foreach($estados as $est)
                        @if($est->id == 1)
                            <option value="1" class="text-orange-500 font-black">{{ $est->nombre_estado }}</option>
                        @elseif($est->id == 2)
                            <option value="2" class="text-blue-500 font-black">{{ $est->nombre_estado }}</option>
                        @elseif($est->id == 3)
                            <option value="3" class="text-green-500 font-black">{{ $est->nombre_estado }}</option>
                        @elseif($est->id == 4)
                            <option value="4" class="text-red-500 font-black">{{ $est->nombre_estado }}</option>
                        @elseif($est->id == 5)
                            <option value="5" class="text-yellow-500 font-black">{{ $est->nombre_estado }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            {{--Filtrar por Categoria--}}
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Categoría</label>
                <select id="filtroCategoria"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-primary transition-all font-black uppercase tracking-wider text-slate-600 cursor-pointer">
                    <option value="todos" class="font-black">CATEGORIAS</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" class="font-black">{{ $categoria->nombre_categoria }}</option>
                    @endforeach
                </select>
            </div>
            {{--Botones de Ejecución--}}
            <div class="flex gap-2 w-full">
                <button type="button" onclick="aplicarFiltrosHistorial()"
                    class="flex-1 h-[38px] bg-secondary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md hover:bg-opacity-95 transition-all flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">filter_alt</span> Filtrar
                </button>
                <button type="button" onclick="limpiarFiltrosHistorial()"
                    class="w-[42px] h-[38px] bg-slate-200 text-slate-600 rounded-xl text-xs font-black hover:bg-slate-300 transition-all flex items-center justify-center"
                    title="Reiniciar Filtros">
                    <span class="material-symbols-outlined text-lg">restart_alt</span>
                </button>
            </div>
        </div>
        {{--TABLA DE HISTORIA--}}
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
                        <th class="px-2 py-4 border-b border-slate-200 font-black">Detalle</th>
                        <th class="hidden">Categoria</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[12px]">
                    @foreach($tickets as $ticket)
                        <tr class="fila-historial hover:bg-slate-50/60 transition-colors" data-id="{{ $ticket->id }}"
                            data-usuario="{{ optional($ticket->user)->name }}"
                            data-tecnico="{{ optional($ticket->tecnico)->name }}" data-estado-id="{{ $ticket->estado_id }}"
                            data-fecha="{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y-m-d') }}">
                            {{--ID--}}
                            <td class="px-5 py-4 font-black text-slate-700">
                                <span
                                    class="text-slate-400 font-semibold">#</span>TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <!--datos del usuario-->
                            <td class="px-2 py-4">
                                <div class="flex flex-col">
                                    <button type="button" onclick="verUsuario(
                                                                                                    '{{ $ticket->user->name }}', 
                                                                                                    '{{ $ticket->user->email }}', 
                                                                                                    '{{ $ticket->user->unidad->nombre_unidad}}', 
                                                                                                    '{{ $ticket->user->cargo }}', 
                                                                                                    '{{ $ticket->user->telefono ?? 'N/A' }}'
                                                                                                )"
                                        class="font-black hover:text-primary transition-all text-left flex items-center gap-1 group">
                                        {{ $ticket->user->name }}
                                        <span
                                            class="material-symbols-outlined text-[16px] text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                                            visibility
                                        </span>
                                    </button>
                                </div>
                            </td>
                            <!--final datos del usuario-->
                            {{--Prioridad--}}
                            <td class="px-5 py-4">
                                @php
                                    $prio = $ticket->prioridad->nombre_prioridad ?? 'Baja';
                                    $clasePrio = match ($prio) {
                                        'Critica' => 'bg-red-100 text-red-700 border-red-200',
                                        'Alta' => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'Media' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'Baja' => 'bg-green-100 text-[#008F7E] border-green-200',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full border font-black text-[10px] uppercase {{ $clasePrio }}">
                                    {{ $prio }}
                                </span>
                            </td>
                            {{--Estado--}}
                            <td class="px-5 py-4">
                                @php
                                    $nombreEstado = $ticket->estado->nombre_estado ?? 'Abierto';
                                    $estado = strtolower($nombreEstado);

                                    $claseEstado = match ($estado) {
                                        'abierto' => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'procesando' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'resuelto' => 'bg-green-100 text-[#008F7E] border-green-200',
                                        'equivocado' => 'bg-red-100 text-red-700 border-red-200',
                                        'no corresponde' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                                    };
                                @endphp
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $claseEstado }}">
                                    {{ $nombreEstado }}
                                </span>
                            </td>
                            {{--Técnico--}}
                            <td class="px-5 py-4 font-black">
                                {{ optional($ticket->tecnico)->name ?? 'Pendiente de asignación' }}
                            </td>
                            {{--Acciones--}}
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{--Detalle--}}
                                    <button type="button"
                                        class="btn-ver-detalle p-2 bg-slate-100 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto"
                                        data-asunto="{{ $ticket->asunto }}"
                                        data-descripcion="{{ $ticket->descripcion }}"
                                        data-tipo="{{ $ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A' }}"
                                        data-fecha="{{ $ticket->created_at->format('d/m/Y') }}">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                    {{--Comentarios--}}
                                    <button type="button" onclick="abrirComentariosTicket({{ $ticket->id }})"
                                        class="w-7 h-7 flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-secondary hover:text-white rounded-lg transition-all"
                                        title="Ver Comentarios / Bitácora">
                                        <span class="material-symbols-outlined text-[17px]">chat_bubble</span>
                                    </button>
                                </div>
                            </td>
                            {{-- categoria oculta --}}
                            <td class="hidden">{{ $ticket->categoria->id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- ----------------------------------------------MODAL DE DETALLE--------------------------------------- --}}
    <div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModal()"></div>
            <div
                class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-primary z-10 animate-fade-in">
                <div class="p-6 sm:p-8">
                    <div class="flex justify-between items-start gap-4 pb-4 border-b border-slate-100 mb-6">
                        <div class="space-y-1.5">
                            <h3 id="modalTitulo"
                                class="text-lg sm:text-xl font-black text-secondary uppercase tracking-tight leading-snug">
                                ---</h3>

                            <div class="flex items-center gap-1.5 pt-3 text-slate-500 font-semibold text-[13px]">
                                <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span>
                                <label class="text-[11px] font-black uppercase tracking-widest text-secondary">Fecha de
                                    Apertura:</label>
                                <span id="modalFechaApertura"
                                    class="font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">---</span>
                            </div>
                        </div>
                        <button onclick="cerrarModal()"
                            class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-all shrink-0">
                            <span class="material-symbols-outlined text-[22px]">close</span>
                        </button>
                    </div>
                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center gap-1.5 mb-2 text-secondary">
                                <span class="material-symbols-outlined text-[16px] text-primary">category</span>
                                <label class="text-[11px] font-black uppercase tracking-widest">Tipo de Solicitud</label>
                            </div>
                            <div id="modalTipoSolicitud"
                                class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line">
                                ---
                            </div>
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
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <button onclick="cerrarModal()"
                            class="w-full py-3.5 bg-primary text-white font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-sm shadow-md shadow-primary/20 hover:shadow-lg hover:shadow-primary/30">
                            Cerrar Detalle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ---------------------------------------- MODAL DE USUARIO ---------------------------------------- --}}
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
                                class="material-symbols-outlined text-primary group-hover:text-primary text-xl">email</span>
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
                            <span class="material-symbols-outlined text-primary text-xl">park</span>
                            <div>
                                <label class="text-[10px] font-black text-secondary uppercase tracking-widest block">Unidad
                                    / Parque</label>
                                <p id="userUnidad" class="text-sm text-slate-700 font-bold">---</p>
                            </div>
                        </div>
                        {{-- Cargo --}}
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">work</span>
                            <div>
                                <label
                                    class="text-[10px] font-black text-secondary uppercase tracking-widest block">Cargo</label>
                                <p id="userCargo" class="text-sm text-slate-700 font-bold">---</p>
                            </div>
                        </div>
                        {{-- Teléfono --}}
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">call</span>
                            <div>
                                <label
                                    class="text-[10px] font-black text-secondary uppercase tracking-widest block">Teléfono
                                    /
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
    <script src="{{ asset('js/historial.js') }}"></script>
@endpush