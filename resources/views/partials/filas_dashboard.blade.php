@foreach($todosLosTickets as $ticket)
    <tr class="hover:bg-slate-50/80 transition-all ticket-fila" data-estado-id="{{ $ticket->estado_id }}">
        <td class="px-2 py-4 font-bold text-slate-900 whitespace-nowrap">
            <div class="flex items-center">
                {{-- Prefijo con estilo sutil --}}
                <span class="text-secondary font-black text-[12px]">#</span>
                <span class="text-secondary font-black text-[12px] tracking-tighter">TK</span>

                {{-- Número principal destacado --}}
                <span class="text-secondary font-black tracking-tight text-[12px]">
                    {{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </td>
        <!--DATOS DEL USUARIO-->
        <td class="px-2 py-4">
            <div class="flex flex-col">
                <button type="button" onclick="verUsuario(
                        '{{ $ticket->user->name }}', 
                        '{{ $ticket->user->email }}', 
                        '{{ $ticket->user->unidad->nombre_unidad}}', 
                        '{{ $ticket->user->cargo }}', 
                        '{{ $ticket->user->telefono ?? 'N/A' }}'
                    )"
                    class="text-slate-900 font-bold hover:text-primary transition-all text-left flex items-center gap-1 group">
                    {{ $ticket->user->name }}
                    <span
                        class="material-symbols-outlined text-[16px] text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                        visibility
                    </span>
                </button>
            </div>
        </td>
        <!-- FINAL DATOS DE USUARIO -->

        <td class="px-2 py-4">
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
            <span class="px-2 py-1 rounded-md border font-black text-[10px] uppercase {{ $clasePrio }}">
                {{ $prio }}
            </span>
        </td>

        <td class="px-2 py-4">
            @php
                $estado = strtolower($ticket->estado->nombre_estado ?? 'abierto');
                $claseEstado = match ($estado) {
                    'abierto' => 'bg-orange-100 text-orange-700 border-orange-200',
                    'procesando' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'resuelto' => 'bg-green-100 text-[#008F7E] border-green-200',
                    'equivocado' => 'bg-red-100 text-red-700 border-red-200',
                    'no corresponde' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
            @endphp
            <span class="px-2 py-1 rounded-md border font-black text-[10px] uppercase {{ $claseEstado }}">
                {{ ucfirst($estado) }}
            </span>
        </td>
        
        <td class="px-2 py-4 font-bold text-slate-900">
            {{ $ticket->tecnico->name ?? 'Pendiente' }}
        </td>

        {{-- BOTON DESCRIPCION DEL TICKET --}}
        <td class="px-2 py-4 text-center">
            <button type="button"
                onclick="verDetalle('{{ addslashes($ticket->asunto) }}', '{{ addslashes($ticket->descripcion) }}', '{{ addslashes($ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A') }}', '{{ addslashes($ticket->created_at->format('d/m/Y')) }}')"
                class="p-2 bg-slate-100 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
        </td>
    </tr>
@endforeach