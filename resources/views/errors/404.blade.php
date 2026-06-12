<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recurso no encontrado | Help Desk ISTU</title>
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
            class="relative w-32 h-32 flex items-center justify-center border-2 border-[#84cc16]/30 rounded-xl bg-[#04003B]/60 mb-8 shadow-xl">
            <span class="material-symbols-outlined text-6xl text-[#84cc16]"
                style="font-variation-settings: 'FILL' 1;">page_info</span>
        </div>
        <h1 class="text-5xl font-black uppercase mb-4" style="color: #84cc16;">Error 404</h1>
        <p class="text-lg font-medium text-blue-200 max-w-lg mb-10 leading-relaxed">El recurso solicitado no existe. Verifica la
            URL.</p>

        <a href="{{ auth()->check() ? url()->previous() : route('login') }}"
            class="px-8 py-4 font-black uppercase tracking-widest text-sm transition-transform active:scale-95 hover:-translate-y-1"
            style="background-color: #84cc16; color: #04003B;">
            {{ auth()->check() ? 'Regresar' : 'Ir al Login' }}
        </a>
    </main>
</body>

</html>