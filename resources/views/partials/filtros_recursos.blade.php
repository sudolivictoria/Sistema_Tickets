<button onclick="filtrar('all', event)"
    class="filter-btn active bg-white text-[#04003B] px-6 py-2 rounded-full border-2 border-[#04003B] font-black uppercase text-xs tracking-wider hover:border-[#04003B] hover:text-[#04003B] transition">
    Todos
</button>

@foreach ($categorias as $cat)
    <button type="button"
        class="filter-btn bg-white font-black text-secondary border-2 border-slate-200 px-6 py-2 rounded-full uppercase text-xs tracking-wider hover:border-[#04003B] hover:text-[#04003B] transition whitespace-nowrap"
        data-id="{{ $cat->id }}"
        onclick="filtrar({{ $cat->id }}, event)">{{ $cat->nombre_categoria_manual }}</button>
@endforeach