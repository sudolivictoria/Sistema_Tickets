<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del sistema | Help Desk ISTU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        .blueprint-grid {
            background-color: #04003B;
            background-image: linear-gradient(rgba(132, 204, 22, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(132, 204, 22, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>

<body class="h-full bg-[#04003B] overflow-hidden">
    <main class="h-full flex flex-col items-center justify-center blueprint-grid text-center px-8">
        <div
            class="relative w-32 h-32 flex items-center justify-center border-2 border-red-500/30 rounded-xl bg-[#04003B]/60 mb-8 shadow-xl">
            <span class="material-symbols-outlined text-6xl text-red-500"
                style="font-variation-settings: 'FILL' 1;">engineering</span>
        </div>
        <h1 class="text-5xl font-black uppercase mb-4 text-red-500">Error 500</h1>
        <p class="text-lg font-medium text-blue-200 max-w-lg mb-10 leading-relaxed">Algo falló en el núcleo del sistema. Estamos trabajando en una
            reparación inmediata.</p>

        <div class="flex gap-4">
            <a href="{{ route('login') }}"
                class="px-8 py-4 border border-red-500 text-red-500 font-black uppercase tracking-widest text-sm">
                Reportar Soporte
            </a>
            <a href="/" class="px-8 py-4 font-black uppercase tracking-widest text-sm"
                style="background-color: #84cc16; color: #04003B;">
                Intentar de nuevo
            </a>
        </div>
    </main>
</body>

</html>