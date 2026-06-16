<div id="modalUsuario" class="fixed inset-0 z-[60] hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="cerrarModalUsuario()"></div>
        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-sm w-full overflow-hidden transform transition-all border-b-8 border-t-8 border-secondary z-10 animate-fade-in">
            <div class="p-8 text-center">
                <div
                    class="w-20 h-20 bg-blue-100/50 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-md">
                    <span class="material-symbols-outlined text-4xl text-secondary">contacts_product</span>
                </div>
                <h3 id="userNombre" class="text-xl font-black text-secondary uppercase leading-tight mb-4">---</h3>
                <div class="space-y-3 text-left">
                    {{-- Correo --}}
                    <a id="linkCorreo" href="#" target="_blank"
                        class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3 transition-all hover:bg-blue-50 hover:border-blue-200 group cursor-pointer no-underline block">
                        <span
                            class="material-symbols-outlined text-blue-950 group-hover:text-xl">email</span>
                        <div class="flex-1">
                            <label
                                class="text-[10px] font-black text-secondary uppercase tracking-widest block">
                                Correo
                            </label>
                            <p id="userEmail" class="text-sm text-slate-700 font-bold">---</p>
                            <span
                                class="text-[9px] text-slate-400 font-medium italic hidden group-hover:block transition-all">
                                Abrir en Gmail
                            </span>
                        </div>
                        <span
                            class="material-symbols-outlined text-slate-400 group-hover:text-sm self-center">
                            open_in_new
                        </span>
                    </a>
                    {{-- Unidad --}}
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-950 text-xl">park</span>
                        <div>
                            <label class="text-[10px] font-black text-secondary uppercase tracking-widest block">Unidad
                                / Parque</label>
                            <p id="userUnidad" class="text-sm text-slate-700 font-bold">---</p>
                        </div>
                    </div>
                    {{-- Cargo --}}
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-950 text-xl">work</span>
                        <div>
                            <label
                                class="text-[10px] font-black text-secondary uppercase tracking-widest block">Cargo</label>
                            <p id="userCargo" class="text-sm text-slate-700 font-bold">---</p>
                        </div>
                    </div>
                    {{-- Teléfono --}}
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-blue-950 text-xl">call</span>
                        <div>
                            <label
                                class="text-[10px] font-black text-secondary uppercase tracking-widest block">Teléfono
                                /
                                Ext.</label>
                            <p id="userTelefono" class="text-sm text-slate-700 font-bold">---</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <button onclick="cerrarModalUsuario()"
                        class="w-full py-3 bg-secondary text-blue-200 font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-xs">
                        Cerrar Perfil
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
