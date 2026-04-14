<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Help Desk Istu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#1e3a8a",
                        "secondary": "#84cc16",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .dashed-button {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }

        .dashed-button:hover {
            border-color: #84cc16;
            background-color: #f7fee7;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /*size*/
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        /*pista*/
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        /*thumb*/
        ::-webkit-scrollbar-thumb {
            background: #1e3a8a;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }

        /*hover thumb*/
        ::-webkit-scrollbar-thumb:hover {
            background: #84cc16;
        }
    </style>
</head>

<!--cuerpo-->

<body class="bg-slate-50 font-display text-slate-900 antialiased">

    <header
        class="fixed top-0 w-full z-50 bg-white border-b border-slate-100 px-8 py-3 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <div class="size-9 bg-secondary rounded-lg flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined font-bold text-2xl">support_agent</span>
            </div>
            <h2 class="text-xl font-bold text-primary tracking-tight">Help Desk Istu</h2>
        </div>
    </header>

    <!--menu lateral-->
    <aside class="fixed left-0 top-0 h-full w-64 z-40 bg-primary border-r border-blue-800 flex flex-col pt-20 p-4">
        <nav class="space-y-1 gap-1-5 flex-1">
            <a class="flex items-center gap-3 px-4 py-3 bg-secondary text-primary rounded-xl font-bold shadow-lg mb-4"
                href="{{ route('cliente.dashboard') }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm">Dashboard</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                href="{{ route('cliente.crear-ticket') }}">
                <span class="material-symbols-outlined text-xl">add_circle</span>
                <span class="text-sm font-medium">Crear Ticket</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                href="{{ route('cliente.mis-tickets') }}">
                <span class="material-symbols-outlined text-xl">history</span>
                <span class="text-sm font-medium">Mis Tickets</span>
            </a>
            <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                href="{{ route('cliente.recursos') }}">
                <span class="material-symbols-outlined text-xl">library_books</span>
                <span class="text-sm font-medium">Recursos</span>
            </a>
        </nav>
        <!--final menu lateral-->

        <!--cierre sesion-->
        <div class="mt-auto pt-6 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:text-white hover:bg-blue-200/20 rounded-xl transition-all font-bold group">
                    <span
                        class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-500">logout</span>
                    <span class="text-sm">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!--contenido principal-->
    <main class="ml-64 pt-20 min-h-screen">
        <div class="max-w-4xl w-full mx-auto p-8">
            <div class="mb-10 border-b border-slate-200 pb-6">
                <h2 class="text-3xl font-black text-primary mb-2 flex items-center gap-3">
                    <span class="material-symbols-outlined text-4xl text-secondary">confirmation_number</span>
                    Enviar Nueva Solicitud
                </h2>
                <p class="text-slate-500 font-medium">Complete el formulario oficial para la gestión de su
                    requerimiento.</p>
            </div>

            <!--formulario de creacion de ticket-->
            <form action="{{ route('cliente.tickets.store') }}" method="POST"
                class="space-y-8 bg-white p-10 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100"
                enctype="multipart/form-data">
                @csrf

                <!--asunto del ticket-->
                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-primary uppercase tracking-widest ml-1">Asunto del
                        Ticket</label>
                    <input name="asunto" value="{{ old('asunto') }}"
                        class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all placeholder:text-slate-300 font-medium text-slate-700"
                        placeholder="Ej: Falla en equipo de cómputo" type="text" required />
                </div>

                <!--categoria-->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2.5">
                        <label class="text-sm font-black text-primary uppercase tracking-widest ml-1">Categoría</label>
                        <div class="relative">
                            <select name="categoria_id"
                                class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all cursor-pointer appearance-none font-medium text-slate-700"
                                required>
                                <option value="" disabled selected>Seleccione una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre_categoria }}</option>
                                @endforeach
                            </select>
                            <span
                                class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">expand_more</span>
                        </div>
                    </div>

                    <!--tipo de solicitud-->
                    <div class="flex flex-col gap-2.5">
                        <label class="text-sm font-black text-primary uppercase tracking-widest ml-1">Tipo de
                            Solicitud</label>
                        <div class="relative">
                            <select name="tipo_solicitud_id"
                                class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all cursor-pointer appearance-none font-medium text-slate-700"
                                required>
                                <option value="" disabled selected>Seleccione tipo</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre_tipo }}</option>
                                @endforeach
                            </select>
                            <span
                                class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">expand_more</span>
                        </div>
                    </div>

                    <!--info extra-->
                    <div id="info-extra"
                        class="hidden mt-3 p-4 bg-blue-50 border-l-4 border-primary rounded-r-xl transition-all animate-fade-in">
                        <div class="flex gap-3">
                            <span class="material-symbols-outlined text-primary">info</span>
                            <div>
                                <p class="text-xs font-black text-primary uppercase tracking-wider">Información del
                                    servicio</p>
                                <p id="texto-ayuda" class="text-sm text-slate-600 mt-1 italic"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!--descripcion detallada-->
                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-primary uppercase tracking-widest ml-1">Descripción
                        Detallada</label>
                    <textarea name="descripcion"
                        class="w-full px-5 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-secondary/10 focus:border-secondary outline-none transition-all resize-none font-medium text-slate-700"
                        rows="5" placeholder="Explique brevemente el problema..."
                        required>{{ old('descripcion') }}</textarea>
                </div>

                <!--nivel de urgencia-->
                <div class="flex flex-col gap-2.5">
                    <label class="text-sm font-black text-primary uppercase tracking-widest ml-1">Nivel de
                        Urgencia</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach(['Critica', 'Alta', 'Media', 'Baja'] as $prio)
                            <label class="cursor-pointer">
                                <input class="hidden peer" name="prioridad" type="radio" value="{{ $prio }}" {{ $prio == 'Media' ? 'checked' : '' }} />
                                <div class="py-3 px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-500 font-bold text-center transition-all 
                                                        peer-checked:border-secondary peer-checked:bg-secondary/5 peer-checked:text-secondary peer-checked:shadow-sm
                                                        hover:border-slate-200">
                                    {{ $prio }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[13px] text-slate-400 italic mt-1 font-medium">* La prioridad final será asignada por
                        el técnico encargado.</p>
                </div>

                <!--cancelar-->
                <div class="flex items-center justify-end gap-4 pt-8 border-t border-slate-100">
                    <a href="{{ route('cliente.dashboard') }}"
                        class="px-8 py-3.5 rounded-2xl font-black text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all uppercase tracking-widest text-xs">
                        Cancelar
                    </a>
                    <button
                        class="px-10 py-3.5 rounded-2xl bg-primary text-secondary font-black hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/20 flex items-center gap-3 uppercase tracking-widest text-xs"
                        type="submit">
                        <span>Enviar Requerimiento</span>
                        <span class="material-symbols-outlined text-lg">send</span>
                    </button>
                </div>
            </form>
        </div>
    </main>


    //------------filtrado dinamico tipo de solicitud segun categoria seleccionada----------------
    <script>
        //---captura de datos de tipos de solicitud desde el backend 
        const todosLosTipos = @json($tipos);

        //----cambio select en categoria para filtrar tipos de solicitud
        document.querySelector('select[name="categoria_id"]').addEventListener('change', function () {
            const categoriaId = this.value;
            const selectTipo = document.querySelector('select[name="tipo_solicitud_id"]');

            //---limpiamos opciones anteriores
            selectTipo.innerHTML = '<option value="" disabled selected>Seleccione tipo</option>';

            //--filtrar tipos de solicitud que pertenecen a la categoria seleccionada
            const filtrados = todosLosTipos.filter(tipo => tipo.categoria_id == categoriaId);

            //--nuevas opciones 
            filtrados.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.id;
                option.textContent = tipo.nombre_tipo_solicitud;
                selectTipo.appendChild(option);
            });
        });


        //----mostrar info extra segun tipo de solicitud seleccionada
        document.querySelector('select[name="tipo_solicitud_id"]').addEventListener('change', function () {
            const tipoId = this.value;
            const infoDiv = document.getElementById('info-extra');
            const textoAyuda = document.getElementById('texto-ayuda');

            //---buscar tipo seleccionado   
            const tipoSeleccionado = todosLosTipos.find(t => t.id == tipoId);

            if (tipoSeleccionado && tipoSeleccionado.descripcion_ayuda) {
                textoAyuda.textContent = tipoSeleccionado.descripcion_ayuda;
                infoDiv.classList.remove('hidden'); //---mostramos el div si hay info disponible
            } else {
                infoDiv.classList.add('hidden'); //--lo oculta si no hay info
            }
        });
    </script>

</body>

</html>