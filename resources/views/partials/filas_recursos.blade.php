@foreach($manuales as $manual)
    <div class="manual-card bg-white rounded-2xl shadow-md overflow-hidden flex flex-col group transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
        data-categoria="{{ $manual->categoria_id }}">

        <div class="h-36 bg-[#04003B] relative p-5 bg-[url('https://www.transparenttextures.com/patterns/swirl.png')]">
            <span
                class="relative z-10 bg-lime-500/20 text-lime-400 text-[12px] font-bold px-2 py-1 rounded border border-lime-500/50 uppercase">
                {{ $manual->categoria->nombre_categoria_manual }}
            </span>

            <h3
                class="relative pt-5 z-10 text-md font-black text-white uppercase break-words tracking-tight leading-none drop-shadow-md group-hover:text-[#9fd82b] transition-colors duration-300">
                {{ $manual->titulo }}
            </h3>

            <div class="absolute top-4 right-4 text-white/20 group-hover:text-white/40 transition-colors">
                @if(Str::endsWith($manual->archivo_path, '.mp4'))
                    <span class="material-symbols-outlined text-[24px]">play_circle</span>
                @else
                    <span class="material-symbols-outlined text-[24px]">picture_as_pdf</span>
                @endif
            </div>

        </div>

        <div class="relative flex-1 p-2 flex flex-col bg-white">
            <div>
                <div class="flex justify-between items-center pt-2">
                    <span class="text-xs font-bold text-slate-400">Ver Recurso</span>

                    <button onclick="abrirVisor('{{ asset('storage/' . $manual->archivo_path) }}', '{{ $manual->titulo }}')"
                        class="w-9 h-9 bg-[#9fd82b] hover:bg-[#04003B] hover:text-[#9fd82b] text-[#1c3000] rounded-xl flex items-center justify-center transition-all shadow-md shadow-lime-500/10 hover:scale-105 active:scale-95"
                        title="Abrir Visor">
                        <span class="material-symbols-outlined text-[18px] font-bold">visibility</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach