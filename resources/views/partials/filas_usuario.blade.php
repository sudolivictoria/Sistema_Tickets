@foreach($todosLosTickets as $ticket)
    <tr class="hover:bg-slate-50/50 transition-colors group">
        <td class="px-6 py-4 max-w-[150px] text-slate-600 font-bold">{{ $ticket->categoria->nombre_categoria }}</td>
        <td class="px-6 py-4 max-w-[150px] text-slate-600 font-bold">{{ $ticket->tipo_solicitud->nombre_tipo_solicitud }}
        </td>
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

        <td class="px-6 py-4">
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
            <span class="px-2 py-1 rounded-md border font-black text-[12px] uppercase {{ $clasePrio }}">
                {{ $prio }}
            </span>
        </td>

        <td class="px-6 py-4 text-slate-600 font-bold">{{ $ticket->created_at->format('d/m/Y') }}</td>

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