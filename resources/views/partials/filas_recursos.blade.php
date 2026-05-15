@foreach($manuales as $manual)
    <div class="manual-card bg-white rounded-xl shadow-md overflow-hidden" data-categoria="{{ $manual->categoria_id }}">

        <div class="h-24 bg-blue-900 relative p-4 bg-[url('https://www.transparenttextures.com/patterns/rocky-wall.png')]">
            <span
                class="relative z-10 bg-lime-500/20 text-lime-400 text-[12px] font-bold px-2 py-1 rounded border border-lime-500/50 uppercase">
                {{ $manual->categoria->nombre_categoria_manual }}
            </span>

            <div class="absolute inset-0 flex items-center justify-center">
                @if(Str::endsWith($manual->archivo_path, '.mp4'))
                    <span class="material-symbols-outlined text-white/30 text-6xl">play_circle</span>
                @else
                    <span class="material-symbols-outlined text-white/30 text-6xl">picture_as_pdf</span>
                @endif
            </div>
        </div>

        <div class="p-5">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-blue-900 font-bold text-xl leading-tight">{{ $manual->titulo }}</h3>
            </div>

            <hr class="border-slate-100 mb-4">
            <div class="flex justify-between items-center">
                <div class="flex gap-3 text-blue-900/60">
                    <button onclick="abrirVisor('{{ asset('storage/' . $manual->archivo_path) }}', '{{ $manual->titulo }}')"
                        class="hover:text-blue-900 transition-transform hover:scale-110">
                        <span class="material-symbols-outlined text-[24px]">visibility</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach