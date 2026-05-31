<div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModal()"></div>

        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-primary z-10 animate-fade-in">
            <div class="p-6 sm:p-8">

                <div class="flex justify-between items-start gap-4 pb-4 border-b border-slate-100 mb-6">
                    <div class="space-y-1.5">
                        <h3 id="modalTitulo"
                            class="text-lg sm:text-xl font-black text-secondary uppercase tracking-tight leading-snug">
                            ---</h3>

                        <div class="flex items-center gap-1.5 pt-3 text-slate-500 font-semibold text-[13px]">
                            <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-secondary">Fecha de
                                Apertura:</label>
                            <span id="modalFechaApertura"
                                class="font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">---</span>
                        </div>
                    </div>

                    <button onclick="cerrarModal()"
                        class="p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-all shrink-0">
                        <span class="material-symbols-outlined text-[22px]">close</span>
                    </button>
                </div>

                <div class="space-y-5">
                    <div>
                        <div class="flex items-center gap-1.5 mb-2 text-secondary">
                            <span class="material-symbols-outlined text-[16px] text-primary">category</span>
                            <label class="text-[11px] font-black uppercase tracking-widest">Tipo de Solicitud</label>
                        </div>
                        <div id="modalTipoSolicitud"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line">
                            ---
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-1.5 mb-2 text-secondary">
                            <span class="material-symbols-outlined text-[16px] text-primary">description</span>
                            <label class="text-[11px] font-black uppercase tracking-widest">Descripción de la
                                solicitud</label>
                        </div>
                        <div id="modalDescripcion"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line max-h-[200px] overflow-y-auto custom-scrollbar">
                            ---
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button onclick="cerrarModal()"
                        class="w-full py-3.5 bg-primary text-white font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-sm shadow-md shadow-primary/20 hover:shadow-lg hover:shadow-primary/30">
                        Cerrar Detalle
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>