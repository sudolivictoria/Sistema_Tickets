<div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModal()"></div>

        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-primary z-10 animate-fade-in">
            <div class="p-6 sm:p-8">

                <div class="block pb-4 border-b border-slate-100 mb-6 w-full">
                    <div class="space-y-3 w-full">
                        <h3 id="modalTitulo"
                            class="text-xl font-black text-green-950 uppercase w-full break-words whitespace-normal leading-tight">
                            ---
                        </h3>

                        <div class="flex items-center gap-1.5 text-slate-500 font-semibold text-[13px]">
                            <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Fecha de
                                Apertura:</label>
                            <span id="modalFechaApertura"
                                class="font-black text-green-800 bg-green-100/50 px-2 py-0.5 rounded-md">---</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="material-symbols-outlined text-[16px] text-primary">category</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Tipo de
                                Solicitud</label>
                        </div>
                        <div id="modalTipoSolicitud"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line">
                            ---
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="material-symbols-outlined text-[16px] text-primary">description</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Descripción
                                de la solicitud</label>
                        </div>
                        <div id="modalDescripcion"
                            class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-700 text-sm font-semibold leading-relaxed whitespace-pre-line max-h-[200px] overflow-y-auto custom-scrollbar break-words">
                            ---
                        </div>
                    </div>

                    <div id="wrapperDriveLink" class="hidden">
                        <div class="flex items-center gap-1.5 mb-2">
                            <span class="material-symbols-outlined text-[16px] text-primary">link</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Evidencia
                                Adjunta</label>
                        </div>
                        <a id="modalDriveLink" href="#" target="_blank"
                            class="flex items-center justify-between p-4 bg-green-50 border border-green-100 rounded-2xl text-green-700 text-sm font-bold hover:bg-green-100 transition-all">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined">open_in_new</span>
                                Ver Evidencia del Reporte
                            </span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>  
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button onclick="cerrarModal()"
                        class="w-full py-3.5 bg-primary text-green-950 font-black rounded-2xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-sm shadow-md shadow-primary/20 hover:shadow-lg hover:shadow-primary/30">
                        Cerrar Detalle
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>