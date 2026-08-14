@foreach ($tickets as $ticket)
    <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-4 py-4 whitespace-nowrap">
            <div class="flex items-center">
                <span class="text-secondary font-black text-[12px]">#</span>
                <span class="text-secondary font-black text-[12px] tracking-tighter">TK</span>
                <span class="text-secondary font-black tracking-tight text-[12px]">
                    {{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        </td>

        {{-- Usuario --}}
        <td class="px-4 py-4">
            <div class="flex flex-col">
                <button type="button"
                    class="btn-ver-usuario font-black hover:text-primary transition-all text-left flex items-center gap-2 group"
                    data-nombre="{{ $ticket->user->name }}" 
                    data-email="{{ $ticket->user->email }}"
                    data-unidad="{{ $ticket->user->unidad->nombre_unidad }}" 
                    data-cargo="{{ $ticket->user->cargo }}"
                    data-telefono="{{ $ticket->user->telefono ?? '----' }}">

                    {{ $ticket->user->name }}

                    <span class="material-symbols-outlined text-[16px] text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                        visibility
                    </span>
                </button>
            </div>
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
            <span class="status-label px-2 py-1 rounded-full border font-black text-[10px] uppercase {{ $claseEstado }}">
                {{ ucfirst($estado) }}
            </span>
        </td>

        {{-- Prioridad (AJAX) --}}
        <td class="px-4 py-4" data-search="{{ $ticket->prioridad->nombre_prioridad }}">
            @php
                $rutaPrioridad = route(auth()->user()->tieneRol('Admin') ? 'admin.actualizar-prioridad' : 'gestor.actualizar-prioridad', $ticket->id);
                $estaCerrado = in_array($ticket->estado_id, [3, 4, 5]);
            @endphp
            <select data-id="{{ $ticket->id }}"
                    data-url="{{ $rutaPrioridad }}"
                    {{ $estaCerrado ? 'disabled' : '' }} 
                    class="select-prioridad-ajax bg-transparent font-black text-[12px] border-none focus:ring-0 
                           {{ $estaCerrado ? 'text-slate-400' : 'text-secondary cursor-pointer' }}">
                <option value="1" {{ $ticket->prioridad_id == 1 ? 'selected' : '' }}>Critica</option>
                <option value="2" {{ $ticket->prioridad_id == 2 ? 'selected' : '' }}>Alta</option>
                <option value="3" {{ $ticket->prioridad_id == 3 ? 'selected' : '' }}>Media</option>
                <option value="4" {{ $ticket->prioridad_id == 4 ? 'selected' : '' }}>Baja</option>
            </select>
        </td>

        {{-- Reasignar o Devolver (AJAX) --}}
        <td class="px-4 py-4">
            @php
                $rutaTecnico = route(auth()->user()->tieneRol('Admin') ? 'admin.actualizar-tecnico' : 'gestor.actualizar-tecnico', $ticket->id);
            @endphp
            <select data-id="{{ $ticket->id }}"
                    data-url="{{ $rutaTecnico }}"
                    {{ $estaCerrado ? 'disabled' : '' }} 
                    class="select-tecnico-ajax bg-transparent font-black text-[12px] border-none focus:ring-0 w-full
                           {{ $estaCerrado ? 'text-slate-400' : 'text-secondary cursor-pointer' }}">
                <option value="" {{ is_null($ticket->tecnico_id) ? 'selected' : '' }} class="text-red-600 font-bold">
                    ❌ Devolver a Pendientes
                </option>

                <optgroup label="Reasignar a:">
                    @foreach ($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}" {{ $ticket->tecnico_id == $tecnico->id ? 'selected' : '' }}>
                            👤 {{ $tecnico->name }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
        </td>

        {{-- Detalle del Ticket --}}
        <td class="px-2 py-4 text-center">
            <button type="button"
                class="btn-ver-detalle p-2 bg-blue-100/50 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto"
                data-id="{{ $ticket->id }}" data-asunto="{{ $ticket->asunto }}"
                data-descripcion="{{ $ticket->descripcion }}"
                data-tipo="{{ $ticket->tipo_solicitud->nombre_tipo_solicitud }}"
                data-fecha="{{ $ticket->created_at->format('d/m/Y') }}" data-drive="{{ $ticket->drive_link }}" {{-- Datos
                para el temporizador de SLA --}} data-estado="{{ $ticket->estado->nombre_estado }}"
                data-state="{{ $ticket->estado_sla }}"
                data-fecha-limite="{{ $ticket->fecha_vencimiento_sla ? $ticket->fecha_vencimiento_sla->format('c') : '' }}"
                data-tiempo-respuesta="{{ $ticket->tiempo_respuesta ?? 0 }}">
                <span class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
        </td>

        {{-- Acciones (AJAX) --}}
        <td class="px-4 py-4">
            @php
                $prefix = auth()->user()->tieneRol('Admin') ? 'admin' : 'gestor';
                $rutaResolver = route($prefix . '.tickets.resolver', $ticket->id);
                $rutaEquivocacion = route($prefix . '.tickets.equivocacion', $ticket->id);
                $rutaNoCorresponde = route($prefix . '.tickets.no-corresponde', $ticket->id);
                $estaCerrado = in_array($ticket->estado_id, [3, 4, 5]);
            @endphp

            <div class="flex items-center justify-center gap-2">
                {{-- Botón Resolver --}}
                <button type="button" 
                    data-id="{{ $ticket->id }}" 
                    data-url="{{ $rutaResolver }}"
                    {{ $estaCerrado ? 'disabled' : 'onclick=confirmarResolver(this)' }}
                    class="p-2 font-black rounded-xl transition-all shadow-sm border flex items-center justify-center
                        {{ $estaCerrado ? 'bg-slate-50 text-slate-300 border-slate-100' : 'bg-green-50 text-green-600 border-green-100 hover:bg-green-600 hover:text-white' }}" 
                    title="Marcar como Resuelto">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                </button>

                {{-- Botón Equivocado --}}
                <button type="button" 
                    data-id="{{ $ticket->id }}" 
                    data-url="{{ $rutaEquivocacion }}"
                    {{ $estaCerrado ? 'disabled' : 'onclick=confirmarEquivocado(this)' }}
                    class="p-2 font-black rounded-xl transition-all shadow-sm border flex items-center justify-center
                        {{ $estaCerrado ? 'bg-slate-50 text-slate-300 border-slate-100' : 'bg-orange-50 text-orange-600 border-orange-100 hover:bg-orange-600 hover:text-white' }}"
                    title="Marcar como Equivocado">
                    <span class="material-symbols-outlined text-[16px]">do_not_touch</span>
                </button>

                {{-- Botón No Corresponde --}}
                <button type="button" 
                    data-id="{{ $ticket->id }}" 
                    data-url="{{ $rutaNoCorresponde }}"
                    {{ $estaCerrado ? 'disabled' : 'onclick=confirmarNoCorresponde(this)' }}
                    class="p-2 font-black rounded-xl transition-all shadow-sm border flex items-center justify-center
                        {{ $estaCerrado ? 'bg-slate-50 text-slate-300 border-slate-100' : 'bg-yellow-50 text-yellow-600 border-yellow-100 hover:bg-yellow-600 hover:text-white' }}"
                    title="Marcar como No Corresponde">
                    <span class="material-symbols-outlined text-[16px]">thumb_down</span>
                </button>
            </div>
        </td>
    </tr>
@endforeach