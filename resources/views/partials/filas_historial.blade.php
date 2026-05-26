 @foreach ($tickets as $ticket)
     <tr class="fila-historial hover:bg-slate-50/60 transition-colors" data-id="{{ $ticket->id }}"
         data-usuario="{{ optional($ticket->user)->name }}" data-tecnico="{{ optional($ticket->tecnico)->name }}"
         data-estado-id="{{ $ticket->estado_id }}"
         data-fecha="{{ \Carbon\Carbon::parse($ticket->created_at)->format('Y-m-d') }}">
         {{-- ID --}}
         <td class="px-5 py-4 font-black text-slate-700">
             <span class="text-slate-400 font-semibold">#</span>TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
         </td>
         <!--datos del usuario-->
         <td class="px-2 py-4">
             <div class="flex flex-col">
                 <button type="button"
                     onclick="verUsuario(
                                            '{{ $ticket->user->name }}', 
                                            '{{ $ticket->user->email }}', 
                                            '{{ $ticket->user->unidad->nombre_unidad }}', 
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
         {{-- Prioridad --}}
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
         {{-- Estado --}}
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
         {{-- Técnico --}}
         <td class="px-5 py-4 font-black">
             {{ optional($ticket->tecnico)->name ?? 'Pendiente de asignación' }}
         </td>
         {{-- Acciones --}}
         <td class="px-5 py-4 text-center">
             <div class="flex items-center justify-center gap-0.5">
                 {{-- Detalle --}}
                 <button type="button"
                     class="btn-ver-detalle p-2 bg-slate-100 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto"
                     data-asunto="{{ $ticket->asunto }}" data-descripcion="{{ $ticket->descripcion }}"
                     data-tipo="{{ $ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A' }}"
                     data-fecha="{{ $ticket->created_at->format('d/m/Y') }}">
                     <span class="material-symbols-outlined text-[20px]">visibility</span>
                 </button>
                 {{-- Comentarios --}}
                 <button type="button" onclick="abrirComentariosTicket({{ $ticket->id }})"
                     class="w-7 h-7 flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-primary hover:text-white rounded-lg transition-all"
                     title="Ver Comentarios / Bitácora">
                     <span class="material-symbols-outlined text-[17px]">chat_bubble</span>
                 </button>
             </div>
         </td>
         {{-- categoria oculta --}}
         <td class="hidden">{{ $ticket->categoria->id }}</td>
     </tr>
 @endforeach
