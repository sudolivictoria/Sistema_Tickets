<div id="modalTicket" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="cerrarModal()"></div>
        <div
            class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all border-t-8 border-primary">
            <!--contador-->
            <div class="absolute top-4 right-4 flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-100 rounded-full text-green-600 font-black text-xs uppercase tracking-wider shadow-sm animate-pulse"
                id="wrapperCountdown">
                <span class="material-symbols-outlined text-sm">alarm</span>
                <span id="modalCountdown">--:--:--</span>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <h3 id="modalTitulo"
                        class="text-xl font-black text-green-950 uppercase max-w-full break-words whitespace-normal">---
                    </h3>
                </div>
                <!--tipo solicitud-->
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
                    <!--descripcion-->
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
                            <span class="material-symbols-outlined text-[16px] text-primary">image</span>
                            <label class="text-[11px] font-black uppercase tracking-widest text-green-950">Evidencia
                                Adjunta</label>
                        </div>
                        <!--Contenedor visual de la imagen-->
                        <div class="p-3 bg-green-50 border border-green-100 rounded-2xl flex flex-col gap-3">
                            <!--Vista previa de la imagen-->
                            <div
                                class="w-full overflow-hidden rounded-xl border border-slate-200 bg-white flex justify-center">
                                <img id="modalEvidenciaImg" src="" alt="Evidencia del reporte"
                                    class="max-h-60 object-contain w-full" />
                            </div>
                            <!--Botón opcional por si quieren verla en pantalla completa/descargarla-->
                            <a id="modalDriveLink" href="#" target="_blank"
                                class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl text-green-700 text-xs font-bold hover:bg-green-100/50 transition-all">
                                <span class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    Abrir imagen en pestaña nueva
                                </span>
                                <span class="material-symbols-outlined text-xs">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    <!-- SECCIÓN DE COMENTARIOS -->
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <div id="seccion-historico-comentarios" class="mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="material-symbols-outlined text-primary text-sm">forum</span>
                                <h4 class="text-[11px] font-black text-green-950 uppercase tracking-wider">Comentarios
                                </h4>
                            </div>
                            <!--contenedor para cargar comentarios-->
                            <div id="modalListaComentarios" class="space-y-2 max-h-40 overflow-y-auto pr-1 text-xs">
                                <!--ajax-->
                            </div>
                        </div>
                        <!--agregar comentario (visible para que puedan escribir nuevos)-->
                        <form id="form-comentario-modal" class="space-y-2">
                            @csrf
                            <textarea id="contenido-comentario" required
                                placeholder="Escribe un comentario o nota interna..."
                                class="w-full p-2.5 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-primary focus:border-primary resize-none h-12 bg-slate-50 placeholder:text-slate-400"></textarea>

                            <div class="flex items-center justify-between gap-2">
                                @if(Auth::user()->tieneRol('Admin') || Auth::user()->tieneRol('Gestor'))
                                    <label
                                        class="inline-flex items-center gap-1.5 cursor-pointer text-[10px] font-bold text-green-900 uppercase tracking-wider">
                                        <input type="checkbox" id="es_privado"
                                            class="rounded text-primary focus:ring-primary border-slate-300 w-3.5 h-3.5">
                                        <span>Observación Interna</span>
                                    </label>
                                @endif
                                <button type="submit"
                                    class="ml-auto px-3 py-1.5 bg-secondary text-white text-[10px] font-black uppercase tracking-wider rounded-lg hover:bg-opacity-90 transition-all flex items-center gap-1">
                                    Comentar
                                </button>
                            </div>
                        </form>
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