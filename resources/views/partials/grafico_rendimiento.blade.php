@foreach($mesesGrafico as $mes)
    <div class="flex-1 flex flex-col items-center gap-3 group h-full justify-end">
        <div class="w-full max-w-[36px] flex flex-col justify-end gap-1 h-full">
            <div class="w-full bg-red-500 rounded-t-md hover:brightness-110 transition-all duration-500 relative shadow-[0_-2px_10px_rgba(132,204,22,0.2)]"
                style="height: {{ $mes['pendientes_pct'] }}%">
                <span
                    class="absolute -top-7 left-1/2 -translate-x-1/2 text-[10px] font-black text-red-500 bg-white px-1.5 py-0.5 rounded shadow-sm border border-slate-100 opacity-0 group-hover:opacity-100 transition-opacity z-10">{{ $mes['pendientes_pct'] }}%</span>
            </div>
            <div class="w-full bg-primary rounded-t-md hover:brightness-110 transition-all duration-500 relative shadow-[0_-2px_10px_rgba(132,204,22,0.2)]"
                style="height: {{ $mes['resueltos_pct'] }}%">
                <span
                    class="absolute -top-12 left-1/2 -translate-x-1/2 text-[10px] font-black text-primary bg-white px-1.5 py-0.5 rounded shadow-sm border border-slate-100 opacity-0 group-hover:opacity-100 transition-opacity z-10">{{ $mes['resueltos_pct'] }}%</span>
            </div>
        </div>
        <span
            class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ substr($mes['nombre'], 0, 3) }}</span>
    </div>
@endforeach