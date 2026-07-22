<div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">

    <!--backdrop de fondo-->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="cerrarModal()"></div>

    <!--contenedor para ajustar la pantalla-->
    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">

        <!--contenedor principal-->
        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full flex flex-col max-h-[70vh] transform transition-all border-t-8 border-primary z-10 animate-fade-in overflow-hidden">

            <!-- 1. HEADER FIJO (TItulo + Fecha + Contador) -->
            <div class="p-5 sm:p-6 border-b border-slate-100 shrink-0 bg-white relative">
                <!-- Contador -->
                <div class="absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1 bg-green-50 border border-green-100 rounded-full text-green-600 font-black text-[11px] uppercase tracking-wider shadow-sm animate-pulse"
                    id="wrapperCountdown">
                    <span class="material-symbols-outlined text-sm">alarm</span>
                    <span id="modalCountdown">--:--:--</span>
                </div>

                <div class="pr-24 space-y-2">
                    <h3 id="modalTitulo"
                        class="text-lg sm:text-xl font-black text-green-950 uppercase break-words leading-tight">
                        ---
                    </h3>
                </div>
            </div>

            <!-- 2. CUERPO CON SCROLL INTERNO (Información + Historial de Comentarios) -->
            <div class="p-5 sm:p-6 overflow-y-auto custom-scrollbar space-y-5 flex-1 bg-slate-50/50">

                <!-- Tipo de solicitud -->
                <div>
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">category</span>
                        <label class="text-[10px] font-black uppercase tracking-widest text-green-950">Tipo de
                            Solicitud</label>
                    </div>
                    <div id="modalTipoSolicitud"
                        class="p-3.5 bg-white border border-slate-200/80 rounded-2xl text-slate-700 text-xs font-semibold leading-relaxed whitespace-pre-line shadow-sm">
                        ---
                    </div>
                </div>

                <!-- Descripción -->
                <div>
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">description</span>
                        <label class="text-[10px] font-black uppercase tracking-widest text-green-950">Descripción de la
                            solicitud</label>
                    </div>
                    <div id="modalDescripcion"
                        class="p-3.5 bg-white border border-slate-200/80 rounded-2xl text-slate-700 text-xs font-semibold leading-relaxed whitespace-pre-line break-words shadow-sm">
                        ---
                    </div>
                </div>

                <!-- Evidencia -->
                <div id="wrapperDriveLink" class="hidden">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">image</span>
                        <label class="text-[10px] font-black uppercase tracking-widest text-green-950">Evidencia
                            Adjunta</label>
                    </div>

                        <a id="modalDriveLink" href="#" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between p-3.5 bg-white border border-slate-200/80 rounded-2xl text-green-800 text-xs font-bold hover:bg-green-100/50 hover:border-green-300 transition-all shadow-sm group">
                            <span class="flex items-center gap-2">
                                <span
                                    class="material-symbols-outlined text-base text-primary group-hover:scale-110 transition-transform">open_in_new</span>
                                <span>Abrir evidencia en pestaña nueva</span>
                            </span>
                            <span
                                class="material-symbols-outlined text-xs text-slate-400 group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                        </a>
                </div>

                <!-- Historial de Comentarios -->
                <div class="pt-2 border-t border-slate-200/60">
                    <div class="flex items-center gap-2 mb-2.5">
                        <span class="material-symbols-outlined text-primary text-sm">forum</span>
                        <h4 class="text-[10px] font-black text-green-950 uppercase tracking-wider">Comentarios</h4>
                    </div>
                    <div id="modalListaComentarios" class="space-y-2 text-xs">
                        <!-- Carga dinámica vía AJAX -->
                    </div>
                </div>
            </div>

            <!-- 3. FOOTER FIJO (Formulario de Comentar + Botón Cerrar) -->
            <div class="p-4 sm:p-5 border-t border-slate-100 bg-white shrink-0 space-y-3">

                <!-- Formulario de Nuevo Comentario -->
                <form id="form-comentario-modal" class="space-y-2">
                    @csrf
                    <textarea id="contenido-comentario" required placeholder="Escribe un comentario o nota interna..."
                        class="w-full p-2.5 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-primary focus:border-primary resize-none h-11 bg-slate-50 placeholder:text-slate-400"></textarea>

                    <div class="flex items-center justify-between gap-2">
                            <label
                                class="inline-flex items-center gap-1.5 cursor-pointer text-[10px] font-bold text-green-900 uppercase tracking-wider select-none">
                                <input type="checkbox" id="es_privado"
                                    class="rounded text-primary focus:ring-primary border-slate-300 w-3.5 h-3.5">
                                <span>Nota Interna</span>
                            </label>
                        <button type="submit"
                            class="ml-auto px-3.5 py-1.5 bg-green-900 text-white text-[10px] font-black uppercase tracking-wider rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-1 shadow-sm">
                            Comentar
                        </button>
                    </div>
                </form>

                <!-- Botón de Cerrar Modal -->
                <button onclick="cerrarModal()" type="button"
                    class="w-full py-2.5 bg-primary text-green-950 font-black rounded-xl hover:bg-opacity-90 transition-all uppercase tracking-widest text-xs shadow-md shadow-primary/20 hover:shadow-lg hover:shadow-primary/30">
                    Cerrar Detalle
                </button>
            </div>

        </div>
    </div>
</div>