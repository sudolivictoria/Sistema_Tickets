<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Help Desk - Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#84cc16",
                        "secondary": "#1e3a8a",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        .table-container::-webkit-scrollbar {
            height: 6px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display overflow-hidden">
    <div class="relative flex h-screen flex-col">

        <header
            class="h-16 border-b border-slate-200 bg-white flex items-center justify-between px-8 shrink-0 z-20 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-9 bg-primary rounded-lg flex items-center justify-center text-secondary shadow-sm">
                    <span class="material-symbols-outlined font-bold text-2xl">support_agent</span>
                </div>
                <h2 class="text-xl font-bold text-secondary tracking-tight">Help Desk Istu</h2>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600">
                    Hola, <span
                        class="text-secondary font-bold">{{ auth()->user()->nombre_completo ?? 'Administrador'}}</span>
                </span>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!--begin sidebar-->
            <aside class="w-64 bg-secondary flex flex-col shrink-0 z-10 shadow-2xl">
                <div class="p-6 flex flex-col h-full overflow-y-auto">
                    <nav class="flex flex-col gap-1.5 flex-1">
                        <a class="flex items-center gap-3 px-4 py-3 bg-primary text-secondary rounded-xl font-bold shadow-lg mb-4"
                            href="#">
                            <span class="material-symbols-outlined">dashboard</span>
                            <span class="text-sm">Dashboard</span>
                        </a>
                        <!--admin-->
                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-4 px-4 font-black">
                            Administración</p>
                             <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">confirmation_number</span>
                            <span class="text-sm font-medium">Asignar Tickets</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">group</span>
                            <span class="text-sm font-medium">Gestión Usuarios</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">folder_shared</span>
                            <span class="text-sm font-medium">Gestión Recursos</span>
                        </a>
                        <!--cliente-->
                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-6 px-4 font-black">Servicios
                        </p>
                        <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">add_circle</span>
                            <span class="text-sm font-medium">Crear Ticket</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">history</span>
                            <span class="text-sm font-medium">Mis Tickets</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-2.5 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all"
                            href="#">
                            <span class="material-symbols-outlined text-xl">library_books</span>
                            <span class="text-sm font-medium">Recursos</span>
                        </a>
                    </nav>

                    <div class="mt-auto pt-6 border-t border-white/10">
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 text-red-300 hover:text-white hover:bg-red-500/20 rounded-xl transition-all font-bold group">
                            <span
                                class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-500">logout</span>
                            <span class="text-sm">Cerrar Sesión</span>
                        </button>
                    </div>
                </div>
            </aside>
            <!--end sidebar-->

            <main class="flex-1 overflow-y-auto p-8 bg-slate-50">
                <div class="mx-auto max-w-7xl">

                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Panel de Control</h1>
                            <p class="text-slate-500 text-sm font-medium">Bienvenido al sistema de gestión de incidencias.</p>
                        </div>
                        <div
                            class="min-w-[180px] px-6 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col items-end">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fecha de
                                Consulta</p>
                            <span class="text-base font-black text-secondary">{{ date('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!--estadisticas generales-->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-orange-500 flex items-center gap-5 hover:translate-y-[-4px] transition-all duration-300">
                            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center shrink-0">
                                <span
                                    class="material-symbols-outlined text-orange-600 text-3xl">notification_important</span>
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Sin Asignar</p>
                                <h3 class="text-3xl font-black text-slate-800 leading-none">{{ $noAsignados ?? 0 }}</h3>
                            </div>
                        </div>

                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-blue-500 flex items-center gap-5 hover:translate-y-[-4px] transition-all duration-300">
                            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-blue-600 text-3xl">pending_actions</span>
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">En Proceso</p>
                                <h3 class="text-3xl font-black text-slate-800 leading-none">{{ $pendientes ?? 0 }}</h3>
                            </div>
                        </div>

                        <div
                            class="bg-white p-6 rounded-2xl shadow-sm border-b-4 border-primary flex items-center gap-5 hover:translate-y-[-4px] transition-all duration-300">
                            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary text-3xl">verified</span>
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Resueltos</p>
                                <h3 class="text-3xl font-black text-slate-800 leading-none">{{ $resueltos ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <!--final estadisticas generales-->

                    <!--rendimiento anual-->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        <div class="lg:col-span-3 space-y-8">

                            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                                <div class="mb-10 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                                            <span
                                                class="material-symbols-outlined text-primary text-2xl">leaderboard</span>
                                        </div>
                                        <h2 class="text-lg font-black text-secondary uppercase tracking-tight">
                                            Rendimiento Anual {{ date('Y') }}
                                        </h2>
                                    </div>
                                    <div class="flex gap-6">
                                        <div class="flex items-center gap-2"><span
                                                class="size-3 rounded-full bg-primary shadow-sm"></span><span
                                                class="text-[10px] font-black text-slate-500 uppercase">Resueltos</span>
                                        </div>
                                        <div class="flex items-center gap-2"><span
                                                class="size-3 rounded-full bg-slate-200 shadow-sm"></span><span
                                                class="text-[10px] font-black text-slate-500 uppercase">Pendientes</span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="h-56 flex items-end justify-between px-4 gap-3 bg-slate-50/50 rounded-2xl p-6 border border-slate-100">
                                    @foreach($mesesGrafico as $mes)
                                        <div class="flex-1 flex flex-col items-center gap-3 group h-full justify-end">
                                            <div class="w-full max-w-[36px] flex flex-col justify-end gap-1 h-full">
                                                <div class="w-full bg-slate-200/70 rounded-t-md hover:bg-slate-300 transition-colors relative group/bar shadow-sm"
                                                    style="height: {{ $mes['pendientes_pct'] }}%">
                                                    <span
                                                        class="absolute -top-7 left-1/2 -translate-x-1/2 text-[10px] font-black text-slate-600 bg-white px-1.5 py-0.5 rounded shadow-sm border border-slate-100 opacity-0 group-hover/bar:opacity-100 transition-opacity z-10">{{ $mes['pendientes_pct'] }}%</span>
                                                </div>
                                                <div class="w-full bg-primary rounded-t-md hover:brightness-110 transition-all relative group/bar2 shadow-[0_-2px_10px_rgba(132,204,22,0.2)]"
                                                    style="height: {{ $mes['resueltos_pct'] }}%">
                                                    <span
                                                        class="absolute -top-7 left-1/2 -translate-x-1/2 text-[10px] font-black text-primary bg-white px-1.5 py-0.5 rounded shadow-sm border border-slate-100 opacity-0 group-hover/bar2:opacity-100 transition-opacity z-10">{{ $mes['resueltos_pct'] }}%</span>
                                                </div>
                                            </div>
                                            <span
                                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ substr($mes['nombre'], 0, 3) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!--tickets registrados-->
                            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-white">
                                    <div class="flex items-center gap-4">
                                        <h2 class="text-lg font-bold text-secondary tracking-tight">
                                            Tickets Registrados</h2>
                                        <div class="flex gap-2 ml-4">
                                            <button
                                                class="px-4 py-1.5 bg-secondary text-white rounded-xl text-[10px] font-black uppercase shadow-md transition-all">Todos</button>
                                            <button
                                                class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase hover:bg-orange-100 hover:text-orange-600 transition-all">Abierto</button>
                                            <button
                                                class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase hover:bg-blue-100 hover:text-blue-600 transition-all">Proceso</button>
                                            <button
                                                class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase hover:bg-green-100 hover:text-green-600 transition-all">Resuelto</button>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <span
                                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                                        <input type="text" placeholder="Filtrar por ID, nombre..."
                                            class="pl-10 pr-4 py-2 border-slate-200 rounded-xl text-xs w-64 focus:ring-primary focus:border-primary shadow-sm">
                                    </div>
                                </div>

                                <div class="table-container overflow-x-auto">
                                    <table class="w-full text-left whitespace-nowrap">
                                        <thead>
                                            <tr
                                                class="bg-slate-50/80 text-[10px] uppercase text-slate-500 font-black tracking-widest border-b border-slate-200">
                                                <th class="px-6 py-4 text-center">ID</th>
                                                <th class="px-4 py-4">Usuario</th>
                                                <th class="px-4 py-4">Asunto</th>
                                                <th class="px-4 py-4">Prioridad</th>
                                                <th class="px-4 py-4">Técnico</th>
                                                <th class="px-4 py-4">Estado</th>
                                                <th class="px-4 py-4">Apertura</th>
                                                <th class="px-4 py-4 text-right pr-8">Cierre</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-[11px]">
                                            @foreach($todosLosTickets as $ticket)
                                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                                    <td class="px-6 py-4 font-black text-secondary text-center">
                                                        #{{ $ticket->id }}</td>
                                                    <td class="px-4 py-4 font-bold text-slate-700 uppercase">
                                                        {{ $ticket->user->name ?? 'N/A' }}</td>
                                                    <td class="px-4 py-4">
                                                        <div class="max-w-[150px] truncate font-medium text-slate-600"
                                                            title="{{ $ticket->asunto }}">{{ $ticket->asunto }}</div>
                                                    </td>
                                                    <td class="px-4 py-4">
                                                        <span
                                                            class="font-black uppercase tracking-tighter {{ ($ticket->prioridad->nombre ?? '') == 'Alta' ? 'text-red-600' : 'text-slate-500' }}">
                                                            {{ $ticket->prioridad->nombre ?? 'Normal' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 text-slate-500 font-bold uppercase">
                                                        {{ $ticket->tecnico->name ?? 'Sin Asignar' }}</td>
                                                    <td class="px-4 py-4">
                                                        <span
                                                            class="px-2 py-1 rounded-md bg-white border border-slate-200 text-slate-600 font-black uppercase text-[9px] shadow-sm">
                                                            {{ $ticket->estado ?? 'Abierto' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-4 text-slate-500 font-medium">
                                                        {{ $ticket->created_at->format('d/m/Y') }}</td>
                                                    <td class="px-4 py-4 text-slate-400 italic text-right pr-8">
                                                        {{ $ticket->fecha_cierre ? $ticket->fecha_cierre->format('d/m/Y') : '---' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!--recursos y protocolo critico-->
                        <div class="lg:col-span-1 space-y-6">
                            <div
                                class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                                <div
                                    class="absolute top-0 right-0 w-16 h-16 bg-primary/5 rounded-bl-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary/30">folder_open</span>
                                </div>
                                <h4
                                    class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6 flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-primary rounded-full"></span>
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
                                                    {{ $manual->titulo }}</div>
                                                <div class="text-[9px] text-slate-400 font-bold uppercase">
                                                    {{ $manual->categoria ?? 'General' }}</div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                <button
                                    class="w-full mt-6 py-3 border-2 border-dashed border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:border-primary hover:text-primary transition-all">
                                    Ir al Repositorio
                                </button>
                            </div>

                            <div
                                class="bg-secondary p-8 rounded-3xl text-white shadow-xl relative overflow-hidden group">
                                <div class="relative z-10">
                                    <div
                                        class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-secondary mb-4 shadow-lg group-hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined font-black">emergency</span>
                                    </div>
                                    <h4 class="text-sm font-black uppercase tracking-wider mb-2">Protocolo Crítico</h4>
                                    <p class="text-[11px] text-slate-300 leading-relaxed font-medium">
                                        Tickets con estado <span
                                            class="text-primary font-bold italic underline">CRÍTICO</span> requieren
                                        respuesta inmediata.
                                    </p>
                                </div>
                                <span
                                    class="material-symbols-outlined absolute -right-4 -bottom-4 text-9xl text-white/5 pointer-events-none">warning</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>

</html>