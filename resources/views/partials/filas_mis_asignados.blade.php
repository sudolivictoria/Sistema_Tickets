@foreach ($tickets as $ticket)
    <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-4 py-4 font-bold text-slate-900 whitespace-nowrap">
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
        {{--Usuario--}}
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

        {{--Estado--}}
        <td class="px-4 py-4">
            @php
                $estado = strtolower($ticket->estado->nombre_estado ?? 'abierto');
                $claseEstado = match ($estado) {
                    'abierto' => 'bg-red-100 text-red-700 border-red-200',
                    'procesando' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'resuelto' => 'bg-green-100 text-green-700 border-green-200',
                    'equivocado' => 'bg-orange-100 text-orange-700 border-orange-200',
                    'no corresponde' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
            @endphp
            <span
                class="status-label px-2 py-1 rounded-md border font-black text-[10px] uppercase {{ $claseEstado }}">{{ ucfirst($estado) }}</span>
        </td>
        {{--Prioridad--}}
        <td class="px-4 py-4" data-search="{{ $ticket->prioridad->nombre_prioridad }}">
            @php
                $rutaPrioridad = Auth::user()->rol_id == 1 ? 'admin.actualizar-prioridad' : 'gestor.actualizar-prioridad';
            @endphp
            <form action="{{ route($rutaPrioridad, $ticket->id) }}" method="POST">
                @csrf @method('PATCH')
                <select name="prioridad_id" onchange="this.form.submit()"
                    class="bg-transparent font-black text-secondary text-[12px] border-none focus:ring-0 cursor-pointer">
                    <option value="1" {{ $ticket->prioridad_id == 1 ? 'selected' : '' }}>Critica</option>
                    <option value="2" {{ $ticket->prioridad_id == 2 ? 'selected' : '' }}>Alta</option>
                    <option value="3" {{ $ticket->prioridad_id == 3 ? 'selected' : '' }}>Media</option>
                    <option value="4" {{ $ticket->prioridad_id == 4 ? 'selected' : '' }}>Baja</option>
                </select>
            </form>
        </td>

        {{--Reasignar o Devolver--}}
        <td class="px-4 py-4">
            {{--ruta segun el rol--}}
            @php
                $rutaTecnico = Auth::user()->rol_id == 1 ? 'admin.actualizar-tecnico' : 'gestor.actualizar-tecnico';
            @endphp

            {{--actualizar el tecnico--}}
            <form action="{{ route($rutaTecnico, $ticket->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <select name="tecnico_id" onchange="this.form.submit()"
                    class="bg-transparent font-black text-secondary text-[12px] border-none focus:ring-0 cursor-pointer w-full">

                    {{--Desasignar tecnico y mandar ticket a pendientes--}}
                    <option value="" {{ is_null($ticket->tecnico_id) ? 'selected' : '' }} class="text-red-600 font-bold">
                        ❌ Devolver a Pendientes
                    </option>

                    {{--Lista de tecnicos--}}
                    <optgroup label="Reasignar a:">
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ $ticket->tecnico_id == $tecnico->id ? 'selected' : '' }}>
                                👤 {{ $tecnico->name }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </form>
        </td>


        {{--Fecha de Apertura--}}
        <td class="px-4 py-4 font-bold text-slate-900" data-order="{{ $ticket->created_at->timestamp }}">
            {{ $ticket->created_at->format('d/m/Y') }}
        </td>

        {{--Detalle--}}
        <td class="px-4 py-4 text-center">
            <button type="button"
                onclick="verDetalle('{{ addslashes($ticket->asunto) }}', '{{ addslashes($ticket->descripcion) }}', '{{ addslashes($ticket->tipo_solicitud->nombre_tipo_solicitud ?? 'N/A') }}')"
                class="p-2 bg-slate-100 text-secondary rounded-xl hover:bg-secondary hover:text-white transition-all shadow-sm flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[16px]">visibility</span>
            </button>
        </td>


        {{--Acciones--}}
        <td class="px-4 py-4">
            @php
                $prefix = Auth::user()->rol_id == 1 ? 'admin' : 'gestor';
                $rutaResolver = $prefix . '.tickets.resolver';
                $rutaEquivocacion = $prefix . '.tickets.equivocacion';
                $rutaNoCorresponde = $prefix . '.tickets.no-corresponde';
            @endphp

            <div class="flex items-center justify-center gap-2">

                <form action="{{ route($rutaResolver, $ticket->id) }}" method="POST" class="form-resolver m-0">
                    @csrf
                    @method('PATCH')
                    <button type="button" onclick="confirmarResolver(this)"
                        class="p-2 bg-green-50 text-green-600 hover:bg-green-600 hover:text-white rounded-xl transition-all shadow-sm border border-green-100 flex items-center justify-center"
                        title="Marcar como Resuelto">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    </button>
                </form>

                <form action="{{ route($rutaEquivocacion, $ticket->id) }}" method="POST" class="form-equivocacion m-0">
                    @csrf
                    @method('PATCH')
                    <button type="button" onclick="confirmarEquivocado(this)"
                        class="p-2 bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white rounded-xl transition-all shadow-sm border border-orange-100 flex items-center justify-center"
                        title="Marcar como Equivocado">
                        <span class="material-symbols-outlined text-[16px]">do_not_touch</span>
                    </button>
                </form>

                <form action="{{ route($rutaNoCorresponde, $ticket->id) }}" method="POST" class="form-no-corresponde m-0">
                    @csrf
                    @method('PATCH')
                    <button type="button" onclick="confirmarNoCorresponde(this)"
                        class="p-2 bg-yellow-50 text-yellow-600 hover:bg-yellow-600 hover:text-white rounded-xl transition-all shadow-sm border border-yellow-100 flex items-center justify-center"
                        title="Marcar como No Corresponde">
                        <span class="material-symbols-outlined text-[16px]">thumb_down</span>
                    </button>
                </form>

            </div>
        </td>
    </tr>
@endforeach