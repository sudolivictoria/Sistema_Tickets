@extends('layouts.cliente')

@section('content')
    <section class="bg-primary p-10 rounded-3xl relative overflow-hidden shadow-xl border border-blue-800">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-end gap-6">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-secondary mb-2">Sistema de Tickets</p>
                <h2 class="text-4xl font-black text-white tracking-tighter leading-tight">Hola, {{ auth()->user()->nombre_completo ?? 'Usuario' }}</h2>
                <p class="text-white/90 mt-3 max-w-200 text-sm font-medium italic">
                    Gestiona tus solicitudes de soporte, consulta recursos útiles y mantente al tanto del estado de tus tickets en un solo lugar. ¡Estamos aquí para ayudarte!
                </p>
            </div>
            <div class="pb-4 px-2">
                <a href="{{ route('cliente.crear-ticket') }}" class="w-32 flex items-center justify-center gap-2 bg-secondary text-primary font-black py-3 rounded-xl shadow-lg hover:scale-[1.02] transition-all uppercase text-[10px] tracking-widest">
                    <span class="material-symbols-outlined text-sm">add</span>
                    Nuevo Ticket
                </a>
            </div>
        </div>
    </section>

    {{-- Estadísticas de Tickets --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl border-b-4 border-primary shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-red-50 text-red-500 rounded-2xl"><span class="material-symbols-outlined text-2xl font-bold">priority_high</span></div>
                    <div>
                        <div class="text-2xl font-black text-primary">{{ $abiertos ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-slate-400">Abiertos</div>
                    </div>
                </div>
                <div class="bg-primary p-6 rounded-2xl shadow-lg flex items-center gap-4 border-b-4 border-secondary text-white">
                    <div class="p-3 bg-blue-700 text-secondary rounded-2xl"><span class="material-symbols-outlined text-2xl">engineering</span></div>
                    <div>
                        <div class="text-2xl font-black">{{ $enProceso ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-blue-200">En Proceso</div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border-b-4 border-primary shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-lime-50 text-secondary rounded-2xl"><span class="material-symbols-outlined text-2xl font-bold">check_circle</span></div>
                    <div>
                        <div class="text-2xl font-black text-primary">{{ $resueltos ?? 0 }}</div>
                        <div class="text-[14px] font-black uppercase text-slate-400">Resueltos</div>
                    </div>
                </div>
            </div>

            {{-- Tickets Recientes --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="font-black text-lg tracking-[0.2em] text-primary uppercase">Tickets Recientes</h3>
                </div>
                <div class="overflow-x-auto text-[14px]">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 border-b border-slate-100 uppercase font-black text-green-900">
                            <tr>
                                <th class="px-6 py-4">Asunto</th>
                                <th class="px-6 py-4">Categoría</th>
                                <th class="px-6 py-4">Solicitud</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4">Apertura</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[13px]">
                            @foreach($todosLosTickets as $ticket)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4 max-w-[150px] text-slate-600 font-bold" title="{{ $ticket->asunto }}">{{ $ticket->asunto }}</td>
                                    <td class="px-6 py-4 max-w-[150px] text-slate-600 font-bold">{{ $ticket->categoria->nombre_categoria }}</td>
                                    <td class="px-6 py-4 max-w-[150px] text-slate-600 font-bold">{{ $ticket->tipo_solicitud->nombre_tipo_solicitud }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $estado = strtolower($ticket->estado->nombre_estado ?? 'abierto');
                                            $claseEstado = match ($estado) {
                                                'abierto' => 'bg-red-100 text-red-700 border-red-200',
                                                'procesando' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'resuelto' => 'bg-green-100 text-green-700 border-green-200',
                                                default => 'bg-slate-100 text-slate-600 border-slate-200',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded-md border font-black uppercase text-[12px] {{ $claseEstado }}">
                                            {{ ucfirst($estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 font-bold">{{ $ticket->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recursos --}}
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-16 h-16 bg-secondary/5 rounded-bl-full flex items-center justify-center">
                    <span class="material-symbols-outlined text-secondary/30">folder_open</span>
                </div>
                <h4 class="text-[14px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 flex items-center gap-2">
                    <span class="w-1.5 h-4 bg-secondary rounded-full"></span> Recursos
                </h4>
                <div class="space-y-3">
                    @foreach($manuales as $manual)
                        <a href="{{ $manual->url_archivo }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-primary/10 transition-all border border-transparent hover:border-primary/20 group">
                            <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-primary">
                                <span class="material-symbols-outlined text-xl">description</span>
                            </div>
                            <div class="overflow-hidden text-[11px] font-black text-slate-700 truncate">{{ $manual->titulo }}</div>
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('cliente.recursos') }}" class="w-full mt-6 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-secondary hover:text-secondary transition-all flex items-center justify-center">
                    Ir al Repositorio
                </a>
            </div>

            {{-- Canales de Atención --}}
            <div class="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                <div class="flex items-start gap-4 mb-8">
                    <div class="bg-primary size-8 rounded-xl flex items-center justify-center text-secondary shadow-lg mt-1">
                        <span class="material-symbols-outlined text-xl font-light">headset_mic</span>
                    </div>
                    <div>
                        <h4 class="text-[14px] font-black uppercase tracking-[0.2em] text-primary mb-2">USTS</h4>
                        <p class="text-[12px] text-slate-500 font-medium">Contacto Directo</p>
                    </div>
                </div>
                <button id="toggle-canales" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-secondary hover:text-secondary transition-all flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined">expand_more</span> Ver Canales
                </button>
                 <div id="canales-list" class="space-y-4" style="display: none;">
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ljalvarez@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">ljalvarez@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=mnrodriguez@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">mnrodriguez@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=matorres@istu.gob.sv" target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">matorres@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=jjramirez@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">jjramirez@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ovquintanilla@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">ovquintanilla@istu.gob.sv
                                    </div>
                                </div>
                            </a>
                     </div>

                    <div class="mt-8 p-2 bg-slate-50 rounded-2xl border border-slate-100 flex gap-1.5 items-start">
                        <span class="material-symbols-outlined text-primary mt-0.5">info</span>
                        <p class="text-[12px] text-slate-600 leading-relaxed font-medium">
                            Al hacer clic en un correo, se redirige automáticamente.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('toggle-canales').addEventListener('click', function() {
        var list = document.getElementById('canales-list');
        var icon = this.querySelector('.material-symbols-outlined');
        if (list.style.display === 'none') {
            list.style.display = 'block';
            icon.textContent = 'expand_less';
        } else {
            list.style.display = 'none';
            icon.textContent = 'expand_more';
        }
    });
</script>
@endpush