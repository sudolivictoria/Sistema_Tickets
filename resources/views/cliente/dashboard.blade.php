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
        <div class="p-8 max-w-[1400px] mx-auto space-y-8">

            <section class="bg-primary p-10 rounded-3xl relative overflow-hidden shadow-xl border border-blue-800">
                <div
                    class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
                </div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-end gap-6">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-secondary mb-2">Sistema de
                            Tickets</p>
                        </p>
                        <h2 class="text-4xl font-black text-white tracking-tighter leading-tight">Hola,
                            {{ auth()->user()->nombre_completo ?? 'Usuario'}}
                        </h2>
                        <p class="text-white/90 mt-3 max-w-200 text-sm font-medium italic">
                            Gestiona tus solicitudes de soporte, consulta recursos útiles y mantente al tanto del estado
                            de tus tickets en un solo lugar. ¡Estamos aquí para ayudarte!
                        </p>
                    </div>
                    <div class="pb-4 px-2">
                        <a href="{{ route('cliente.crear-ticket') }}"
                            class="w-32 flex items-center justify-center gap-2 bg-secondary text-primary font-black py-3 rounded-xl shadow-lg hover:scale-[1.02] transition-all uppercase text-[10px] tracking-widest">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Nuevo Ticket
                        </a>
                    </div>
                </div>
            </section>

            <!--estadisticas-->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                <div class="lg:col-span-3 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="bg-white p-6 rounded-2xl border-b-4 border-primary shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-red-50 text-red-500 rounded-2xl"><span
                                    class="material-symbols-outlined text-2xl font-bold">priority_high</span></div>
                            <div>
                                <div class="text-2xl font-black text-primary">{{ $abiertos ?? 0 }}</div>
                                <div class="text-[14px] font-black uppercase text-slate-400">Abiertos</div>
                            </div>
                        </div>
                        <div
                            class="bg-primary p-6 rounded-2xl shadow-lg flex items-center gap-4 border-b-4 border-secondary text-white">
                            <div class="p-3 bg-blue-700 text-secondary rounded-2xl"><span
                                    class="material-symbols-outlined text-2xl">engineering</span></div>
                            <div>
                                <div class="text-2xl font-black">{{ $enProceso ?? 0 }}</div>
                                <div class="text-[14px] font-black uppercase text-blue-200">En Proceso</div>
                            </div>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl border-b-4 border-primary shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-lime-50 text-secondary rounded-2xl"><span
                                    class="material-symbols-outlined text-2xl font-bold">check_circle</span></div>
                            <div>
                                <div class="text-2xl font-black text-primary">{{ $resueltos ?? 0 }}</div>
                                <div class="text-[14px] font-black uppercase text-slate-400">Resueltos</div>
                            </div>
                        </div>
                    </div>
                    <!--final estadisticas-->

                    <!--tickets-->

                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                            <h3 class="font-black text-xs tracking-[0.2em] text-primary uppercase">Historial de Tickets
                            </h3>
                        </div>
                        <div class="overflow-x-auto text-[13px]">
                            <table class="w-full text-left">
                                <thead
                                    class="bg-slate-50/50 border-b border-slate-100 uppercase font-black text-slate-400">
                                    <tr>
                                        <th class="px-6 py-4">Asunto</th>
                                        <th class="px-6 py-4">Categoría</th>
                                        <th class="px-6 py-4">Estado</th>
                                        <th class="px-6 py-4">Apertura</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-[13px]">
                                    @foreach($todosLosTickets as $ticket)
                                        <tr class="hover:bg-slate-50/50 transition-colors group">
                                            <td class="px-6 py-4">
                                                <div class="max-w-[150px] truncate font-medium text-slate-600"
                                                    title="{{ $ticket->asunto }}">{{ $ticket->asunto }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="max-w-[150px] truncate font-medium text-slate-600"
                                                    title="{{ $ticket->categoria->nombre_categoria }}">
                                                    {{ $ticket->categoria->nombre_categoria }}
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-2 py-1 rounded-md bg-white border border-slate-200 text-slate-600 font-black uppercase text-[12px] shadow-sm">
                                                    {{ $ticket->estado->nombre_estado ?? 'Abierto' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-500 font-medium">
                                                {{ $ticket->created_at->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!--final tickets-->

                <!--recursos-->
                <div class="space-y-6">

                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-16 h-16 bg-secondary/5 rounded-bl-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-secondary/30">folder_open</span>
                        </div>
                        <h4
                            class="text-[14px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-secondary rounded-full"></span>
                            Recursos
                        </h4>
                        <div class="space-y-3">
                            @foreach($manuales as $manual)
                                <a href="{{ $manual->url_archivo }}"
                                    class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-primary/10 transition-all border border-transparent hover:border-primary/20 group">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-primary">
                                        <span class="material-symbols-outlined text-xl">description</span>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-[11px] font-black text-slate-700 truncate">
                                            {{ $manual->titulo }}
                                        </div>
                                        <div class="text-[9px] text-slate-400 font-bold uppercase">
                                            {{ $manual->categoria ?? 'General' }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('cliente.recursos') }}"
                            class="w-full mt-6 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-secondary hover:text-secondary transition-all flex items-center justify-center">
                            Ir al Repositorio
                        </a>
                    </div>
                    <!--final recursos-->


                    <!--contacto de soporte tecnico-->
                    <div class="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                        <div class="flex items-start gap-4 mb-8">
                            <div
                                class="bg-primary size-8 rounded-xl flex items-center justify-center text-secondary shadow-lg mt-1">
                                <span class="material-symbols-outlined text-xl font-light">headset_mic</span>
                            </div>
                            <div>
                                <h4 class="text-[12px] font-black uppercase tracking-[0.2em] text-primary mb-2">
                                    ATENCIÓN DIRECTA</h4>
                                <p class="text-[12px] text-slate-500 font-medium">USTS</p>
                            </div>
                        </div>

                        <button id="toggle-canales" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-secondary hover:text-secondary transition-all flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined">expand_more</span>
                            Ver Canales
                        </button>

                        <div id="canales-list" class="space-y-4" style="display: none;">
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ljalvarez@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">ljalvarez@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=mnrodriguez@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">mnrodriguez@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=matorres@istu.gob.sv" target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">matorres@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=jjramirez@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">jjramirez@istu.gob.sv
                                    </div>
                                </div>
                            </a>

                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ovquintanilla@istu.gob.sv"
                                target="_blank"
                                class="flex items-center gap-1 p-1 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-100 hover:border-slate-200 transition-all group">
                                <div
                                    class="size-8 rounded-lg flex items-center justify-center text-primary group-hover:text-secondary">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-700 text-[12px] truncate">ovquintanilla@istu.gob.sv
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="mt-8 p-2 bg-slate-50 rounded-2xl border border-slate-100 flex gap-1.5 items-start">
                            <span class="material-symbols-outlined text-primary mt-0.5">info</span>
                            <p class="text-[12px] text-slate-600 leading-relaxed font-medium">
                                Al hacer clic en un correo, se redirige automáticamente.
                            </p>
                        </div>
                    </div>
                    <!--final contacto de soporte tecnico-->

                </div>
            </div>
        </div>
    </main>

    <!--script para toggle de canales de soporte tecnico-->
    <script>
        document.getElementById('toggle-canales').addEventListener('click', function() {
            var list = document.getElementById('canales-list');
            var icon = this.querySelector('.material-symbols-outlined');
            if (list.style.display === 'none') {
                list.style.display = 'block';
                icon.textContent = 'expand_less';
            } else {
                list.style.display = 'none';
                icon.textContent = 'expand_more';
            }
        });
    </script>

</body>

</html>