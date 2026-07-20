@foreach($misTickets as $ticket)
    <tr class="hover:bg-slate-50/80 transition-all">

        <td class="px-4 py-4 font-bold text-slate-900 whitespace-nowrap">
            <div class="flex items-center">
                {{-- Prefijo con estilo sutil --}}
                <span class="text-[#04003B] font-black text-[12px]">#</span>
                <span class="text-[#04003B] font-black text-[12px] tracking-tighter">TK</span>

                {{-- Número principal destacado --}}
                <span class="text-[#04003B] font-black tracking-tight text-[12px]">
                    {{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </td>

        {{-- Categoría --}}
        <td class="px-4 py-4 font-black uppercase">
            {{ $ticket->categoria->nombre_categoria ?? 'N/A' }}
        </td>

        {{-- Estado --}}
        <td class="px-4 py-4">
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
            <span
                class="status-label px-2 py-1 rounded-full border font-black text-[10px] uppercase whitespace-nowrap {{ $claseEstado }}">{{ ucfirst($estado) }}</span>
        </td>

        {{-- Prioridad --}}
        <td class="px-4 py-4">
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
            <span class="px-2 py-1 border font-black text-[10px] uppercase {{ $clasePrio }}">
                {{ $prio }}
            </span>
        </td>

        {{-- Técnico --}}
        <td class="px-4 py-4 font-black">
            {{ $ticket->tecnico->name ?? 'Pendiente de Asignación' }}
        </td>

        {{-- Fechas --}}
        <td class="px-4 py-4 font-black data-order=" {{ $ticket->created_at->timestamp }}">
            {{ $ticket->created_at->format('d/m/Y') }}
        </td>

        <td class="px-4 py-4 font-black">
            {{ $ticket->fecha_cierre ? \Carbon\Carbon::parse($ticket->fecha_cierre)->format('d/m/Y') : '---' }}
        </td>

        {{-- Botón Detalle (Descripción) --}}
        <td class="px-4 py-4 text-center">
            <button type="button"
                class="btn-ver-detalle p-2 bg-blue-100/50 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto"
                data-id="{{ $ticket->id }}"
                data-asunto="{{ $ticket->asunto }}" data-descripcion="{{ $ticket->descripcion }}"
                data-tipo="{{ $ticket->tipo_solicitud->nombre_tipo_solicitud }}" 
                data-drive="{{ $ticket->drive_link }}">
                <span class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
        </td>
    </tr>
@endforeach