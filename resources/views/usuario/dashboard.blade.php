@extends('layouts.usuario')

@section('content')
    <section class="bg-secondary p-6 md:p-10 rounded-3xl relative overflow-hidden shadow-xl border border-blue-800">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
        </div>
        <div class="relative z-10 flex flex-col xl:flex-row justify-between xl:items-end gap-6">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary mb-2">Sistema de Solicitudes</p>
                <h2 class="text-2xl md:text-4xl sm:text-xl font-black text-white tracking-tighter leading-tight">Hola,
                    {{ auth()->user()->name ?? 'Usuario' }}
                </h2>
                <p class="text-white/90 mt-3 max-w-200 text-sm font-medium italic">
                    Gestiona tus solicitudes, consulta recursos útiles y mantente al tanto del estado de tus tickets en un
                    solo lugar. ¡Estamos aquí para ayudarte!
                </p>
            </div>
            <div class="w-full md:w-auto md:pb-4 md:px-2">
                <a href="{{ route('usuario.crear-ticket') }}"
                    class="w-full md:w-32 flex items-center justify-center gap-2 bg-primary text-secondary font-black py-3 rounded-xl shadow-lg hover:scale-[1.07] transition-all uppercase text-[10px] tracking-widest"
                    style="height: 40px; min-width: 120px;">
                    Nuevo Ticket
                </a>
            </div>
        </div>
    </section>

    {{-- Estadísticas de Tickets --}}
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 md:gap-8">
        <div class="xl:col-span-3 space-y-6 md:space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                <div
                    class="bg-white p-6 rounded-2xl border-b-4 border-blue-100 shadow-sm flex items-center gap-4 hover:translate-y-[-6px] transition-all duration-300">
                    <div class="p-3 bg-red-50 text-red-500 rounded-2xl"><span
                            class="material-symbols-outlined text-2xl font-bold">priority_high</span></div>
                    <div>
                        <div id="contador-abiertos" class="text-2xl font-black text-secondary">{{ $abiertos ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-slate-400">Abiertos</div>
                    </div>
                </div>
                <div
                    class="bg-secondary p-6 rounded-2xl shadow-lg flex items-center gap-4 border-b-4 border-secondary text-white hover:translate-y-[-6px] transition-all duration-300">
                    <div class="p-3 bg-blue-700 text-secondary rounded-2xl"><span
                            class="material-symbols-outlined text-2xl">engineering</span></div>
                    <div>
                        <div id="contador-proceso" class="text-2xl font-black">{{ $enProceso ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-blue-200">En Proceso</div>
                    </div>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl border-b-4 border-blue-100 shadow-sm flex items-center gap-4 hover:translate-y-[-6px] transition-all duration-300">
                    <div class="p-3 bg-lime-50 text-primary rounded-2xl"><span
                            class="material-symbols-outlined text-2xl font-bold">check_circle</span></div>
                    <div>
                        <div id="contador-resueltos" class="text-2xl font-black text-secondary">{{ $resueltos ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-slate-400">
                            Cerrados <span class="text-[11px] font-semibold text-slate-400 lowercase italic font-sans">(este
                                mes)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tickets Recientes --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-black text-lg tracking-[0.2em] text-secondary uppercase flex items-center gap-2">
                        <span class="material-symbols-outlined text-xl">history_toggle_off</span>
                        Tickets Recientes
                    </h3>
                </div>

                <div class="overflow-x-auto text-[13px] md:text-[14px]">
                    <table class="w-full text-left tabla-card-mobile" id="tablaTicketsUsuario">
                        <thead class="bg-slate-50/50 border-b border-slate-100 uppercase font-black text-[#008F7E]">
                            <tr>
                                <th class="px-6 py-4 font-black">ID</th>
                                <th class="px-6 py-4 font-black">Categoría</th>
                                <th class="px-6 py-4 font-black">Estado</th>
                                <th class="px-6 py-4 font-black">Prioridad</th>
                                <th class="px-6 py-4 font-black">Apertura</th>
                                <th class="px-4 py-4 border-b border-slate-200 text-center font-black">Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody" data-tipo="usuario" class="divide-y divide-slate-100 text-[12px]">
                            @include('partials.filas_usuario', ['tickets' => $todosLosTickets])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Call to Action dr Recursos --}}
            <div class="relative overflow-hidden bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-secondary/5 rounded-bl-full flex items-center justify-center pointer-events-none">
                    <span class="material-symbols-outlined text-secondary/30">auto_stories</span>
                </div>

                <div class="relative z-10">
                    <h4
                        class="text-[12px] font-black uppercase tracking-[0.2em] text-blue-900 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-secondary/50 rounded-full"></span>
                        Biblioteca 
                    </h4>
                    <p class="text-[12px] text-slate-500 font-medium mb-6">
                        Explora nuestros recursos disponibles.
                    </p>
                    <a href="https://anyflip.com/bookcase/ghert" target="_blank"
                        class="group w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-secondary hover:text-secondary transition-all flex items-center justify-center bg-slate-50/50 hover:bg-white gap-2">
                        <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">
                            arrow_forward
                        </span>
                        Abrir Biblioteca
                    </a>
                </div>
            </div>
            {{-- Final Call to Action --}}

            {{-- Canales de Atención --}}
            <div class="relative overflow-hidden bg-white p-6 rounded-2xl border border-slate-100">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-blue-100/50 rounded-bl-full flex items-center justify-center pointer-events-none">
                    <span class="material-symbols-outlined text-blue-200">contact_mail</span>
                </div>

                <div class="relative z-10">
                    <h4
                        class="text-[12px] font-black uppercase tracking-[0.2em] text-secondary mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-blue-200 rounded-full"></span>
                        USTS
                    </h4>
                    <p class="text-[12px] text-slate-500 font-medium mb-6">
                        Contacto Directo.
                    </p>

                    <button id="toggle-canales"
                        class="w-full py-2 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-blue-400 hover:text-blue-400 transition-all flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined">expand_more</span> Ver Canales
                    </button>

                    <div id="canales-list" class="space-y-4" style="display: none;">
                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ljalvarez@istu.gob.sv" target="_blank"
                            class="flex items-center gap-1 p-1 rounded-2xl bg-white hover:bg-blue-50 border border-slate-100 hover:border-blue-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-blue-200 group-hover:text-blue-600 transition-colors">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] truncate transition-colors">
                                    ljalvarez@istu.gob.sv</div>
                            </div>
                        </a>

                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=mnrodriguez@istu.gob.sv" target="_blank"
                            class="flex items-center gap-1 p-1 rounded-2xl bg-white hover:bg-blue-50 border border-slate-100 hover:border-blue-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-blue-200 group-hover:text-blue-600 transition-colors">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] truncate transition-colors">
                                    mnrodriguez@istu.gob.sv</div>
                            </div>
                        </a>

                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=matorres@istu.gob.sv" target="_blank"
                            class="flex items-center gap-1 p-1 rounded-2xl bg-white hover:bg-blue-50 border border-slate-100 hover:border-blue-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-blue-200 group-hover:text-blue-600 transition-colors">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] truncate transition-colors">
                                    matorres@istu.gob.sv</div>
                            </div>
                        </a>

                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=jjramirez@istu.gob.sv" target="_blank"
                            class="flex items-center gap-1 p-1 rounded-2xl bg-white hover:bg-blue-50 border border-slate-100 hover:border-blue-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-blue-200 group-hover:text-blue-600 transition-colors">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] truncate transition-colors">
                                    jjramirez@istu.gob.sv</div>
                            </div>
                        </a>
                    </div>

                    <div class="mt-8 p-2 bg-blue-100/50 rounded-xl border border-slate-200/60 flex gap-1.5 items-start">
                        <span class="material-symbols-outlined text-blue-900 mt-0.5 text-[16px]">info</span>
                        <p class="text-[11px] text-blue-900 leading-relaxed font-medium">
                            Al hacer clic en un correo, se redirige automáticamente.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Canales RRHH --}}
            <div class="relative overflow-hidden bg-white p-6 rounded-2xl border border-slate-100">
                <div
                    class="absolute top-0 right-0 w-16 h-16 bg-lime-100/50 rounded-bl-full flex items-center justify-center pointer-events-none">
                    <span class="material-symbols-outlined text-lime-300">person</span>
                </div>

                <div class="relative z-10">
                    <h4
                        class="text-[12px] font-black uppercase tracking-[0.2em] text-lime-800 mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-4 bg-lime-400 rounded-full"></span>
                        RRHH
                    </h4>
                    <p class="text-[12px] text-slate-500 font-medium mb-6">
                        Contacto Directo.
                    </p>

                    <button id="toggle-canales-rrhh"
                        class="w-full py-2 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-lime-500 hover:text-lime-700 transition-all flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined">expand_more</span> Ver Contactos
                    </button>

                    <div id="canales-list-rrhh" class="space-y-4" style="display: none;">
                        <div
                            class="flex items-center gap-2 p-1 rounded-2xl bg-white hover:bg-lime-50 border border-slate-100 hover:border-lime-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-lime-400 group-hover:text-lime-600 transition-colors">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] transition-colors">
                                    Irene Coto <span class="text-slate-400 font-medium">(Jefe)</span></div>
                                <div class="text-lime-700 font-black text-[12px]">2121-6750</div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 p-1 rounded-2xl bg-white hover:bg-lime-50 border border-slate-100 hover:border-lime-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-lime-400 group-hover:text-lime-600 transition-colors">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] transition-colors">
                                    Karen Ascencio <span class="text-slate-400 font-medium">(Reclutamiento)</span></div>
                                <div class="text-lime-700 font-black text-[12px]">2121-6769</div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 p-1 rounded-2xl bg-white hover:bg-lime-50 border border-slate-100 hover:border-lime-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-lime-400 group-hover:text-lime-600 transition-colors">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] transition-colors">
                                    Elida Torres <span class="text-slate-400 font-medium">(Planilla)</span></div>
                                <div class="text-lime-700 font-black text-[12px]">2121-6749</div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 p-1 rounded-2xl bg-white hover:bg-lime-50 border border-slate-100 hover:border-lime-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-lime-400 group-hover:text-lime-600 transition-colors">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] transition-colors">
                                    Narcisa Alvarado <span class="text-slate-400 font-medium">(Prestaciones)</span></div>
                                <div class="text-lime-700 font-black text-[12px]">2121-6746</div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 p-1 rounded-2xl bg-white hover:bg-lime-50 border border-slate-100 hover:border-lime-300 transition-all group shadow-sm">
                            <div
                                class="size-8 rounded-lg flex items-center justify-center text-lime-400 group-hover:text-lime-600 transition-colors">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-slate-600 group-hover:text-slate-800 text-[12px] transition-colors">
                                    Heydi Diaz <span class="text-slate-400 font-medium">(Capacitaciones)</span></div>
                                <div class="text-lime-700 font-black text-[12px]">2121-6784</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 p-2 bg-lime-100/50 rounded-xl border border-slate-200/60 flex gap-1.5 items-start">
                        <span class="material-symbols-outlined text-lime-900 mt-0.5 text-[16px]">info</span>
                        <p class="text-[11px] text-lime-900 leading-relaxed font-medium">
                            Estos son los contactos directos de RRHH, por si necesitás comunicarte directamente con la unidad.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- detalle ticket --}}
    @include('partials.detalle_ticket_usuario')
@endsection

{{-- SCRIPTS --}}
@push('page-scripts')
    @vite(['resources/js/usuario.js'])
    @vite(['resources/js/usuario-menu.js'])
@endpush
{{-- ACTUALIZACION AUTOMATICA --}}
@push('sse-scripts')
    @vite(['resources/js/api.js'])
@endpush