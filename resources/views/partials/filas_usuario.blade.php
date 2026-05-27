@foreach($todosLosTickets as $ticket)
    <tr class="hover:bg-slate-50/50 transition-colors group">
        <td class="px-2 py-4 font-bold text-slate-900 whitespace-nowrap">
            <div class="flex items-center">
                <span class="text-primary font-black text-[12px]">#</span>
                <span class="text-primary font-black text-[12px] tracking-tighter">TK</span>
                <span class="text-primary font-black tracking-tight text-[12px]">
                    {{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </td>
        <td class="px-6 py-4 max-w-[150px] font-black text-[13px]">{{ $ticket->categoria->nombre_categoria }}</td>
        </td>
        <td class="px-6 py-4">
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
            <span class="px-2 py-1 rounded-full border font-black uppercase text-[11px] whitespace-nowrap {{ $claseEstado }}">
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
                    'Baja' => 'bg-green-100 text-[#008F7E] border-green-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
            @endphp
            <span class="px-2 py-1 rounded-full border font-black text-[11px] uppercase {{ $clasePrio }}">
                {{ $prio }}
            </span>
        </td>

        <td class="px-6 py-4 font-black text-[13px]">{{ $ticket->created_at->format('d/m/Y') }}</td>

        {{-- Botón Detalle (Descripción) --}}
        <td class="px-4 py-4 text-center">
            <button type="button"
                class="btn-ver-detalle p-2 bg-slate-100 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto"
                data-asunto="{{ $ticket->asunto }}" data-descripcion="{{ $ticket->descripcion }}"
                data-tipo="{{ $ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A' }}">
                <span class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
        </td>
    </tr>
@endforeach