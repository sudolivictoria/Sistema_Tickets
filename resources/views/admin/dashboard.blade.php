<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Help Desk Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
    <div class="relative flex min-h-screen flex-col">
        <header class="h-16 border-b border-slate-200 bg-white flex items-center justify-between px-8 sticky top-0 z-10">
            <h2 class="text-xl font-bold text-secondary">Dashboard Administrativo</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-slate-600">Hola, {{ auth()->user()->nombre_completo ?? 'Admin' }}</span>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <aside class="w-64 bg-secondary flex flex-col border-r border-slate-200">
                <div class="p-6 flex flex-col gap-8 h-full">
                    <nav class="flex flex-col gap-2 flex-1">
                        <a class="flex items-center gap-3 px-4 py-3 bg-primary text-secondary rounded-lg" href="#">
                            <span class="material-symbols-outlined">dashboard</span>
                            <span class="text-sm font-bold">Dashboard</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg" href="#">
                            <span class="material-symbols-outlined">group</span>
                            <span class="text-sm font-medium">Usuarios</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg" href="#">
                            <span class="material-symbols-outlined">confirmation_number</span>
                            <span class="text-sm font-medium">Asignación</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-3 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg" href="#">
                            <span class="material-symbols-outlined">menu_book</span>
                            <span class="text-sm font-medium">Manuales</span>
                        </a>
                    </nav>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-8">
                <div class="mx-auto max-w-6xl">
                    <h1 class="text-3xl font-black mb-8 text-slate-900">Estadísticas de Soporte</h1>

                    <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-medium text-slate-500 uppercase">No Asignados</p>
                            <h3 class="mt-2 text-4xl font-bold text-orange-600">{{ $noAsignados }}</h3>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-medium text-slate-500 uppercase">Pendientes</p>
                            <h3 class="mt-2 text-4xl font-bold text-blue-600">{{ $pendientes }}</h3>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-medium text-slate-500 uppercase">Resueltos</p>
                            <h3 class="mt-2 text-4xl font-bold text-green-600">{{ $resueltos }}</h3>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="mb-4 text-xl font-bold">Tickets Recientes</h2>
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 font-bold">ID</th>
                                        <th class="px-6 py-4 font-bold">Asunto</th>
                                        <th class="px-6 py-4 font-bold">Usuario</th>
                                        <th class="px-6 py-4 font-bold text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($ticketsRecientes as $ticket)
                                    <tr>
                                        <td class="px-6 py-4 text-primary font-bold">#{{ $ticket->id }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $ticket->asunto }}</td>
                                        <td class="px-6 py-4">{{ $ticket->user->nombre_completo }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <button class="text-slate-400 hover:text-primary"><span class="material-symbols-outlined">visibility</span></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>