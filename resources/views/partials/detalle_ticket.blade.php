<div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="cerrarModal()"></div>
        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-primary">
            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <h3 id="modalTitulo" class="text-xl font-black text-green-950 uppercase">---</h3>
                    <button onclick="cerrarModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="material-symbols-outlined text-[16px] text-primary">category</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Tipo de Solicitud</label>
                        </div>
                        <div id="modalTipoSolicitud"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line">
                            ---
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="material-symbols-outlined text-[16px] text-primary">description</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Descripción de la
                                solicitud</label>
                        </div>
                        <div id="modalDescripcion"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line max-h-[200px] overflow-y-auto custom-scrollbar">
                            ---
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <button onclick="cerrarModal()"
                        class="w-full py-4 bg-primary text-green-950 font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-sm shadow-lg shadow-primary/20">
                        Cerrar Detalle
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>