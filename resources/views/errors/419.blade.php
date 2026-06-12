<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Expirada | Help Desk ISTU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <style>
        .blueprint-grid {
            background-color: #04003B;
            background-image:
                linear-gradient(rgba(132, 204, 22, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(132, 204, 22, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>

<body class="h-full bg-[#04003B] overflow-hidden">

    <main class="h-full relative overflow-hidden flex flex-col items-center justify-center blueprint-grid">
        <div class="relative z-10 w-full max-w-xl px-8 flex flex-col items-center text-center">

            <div class="mb-8 relative">
                <div
                    class="relative w-32 h-32 flex items-center justify-center border-2 border-[#84cc16]/30 rounded-xl bg-[#04003B]/60 backdrop-blur-sm shadow-xl">
                    <span class="material-symbols-outlined text-6xl text-[#84cc16]"
                        style="font-variation-settings: 'FILL' 1;">lock_clock</span>
                </div>
            </div>

            <h1 class="text-4xl font-black tracking-tighter mb-4 uppercase" style="color: #84cc16;">
                ¡Ups! Sesión expirada
            </h1>

            <p class="text-lg font-medium text-blue-200 max-w-lg mb-10 leading-relaxed">
                Por seguridad, tu sesión ha caducado. No te preocupes, tus datos están a salvo.
            </p>

            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="inline-flex items-center gap-3 px-8 py-4 font-black uppercase tracking-widest text-sm transition-transform active:scale-95 hover:-translate-y-1"
                style="background-color: #84cc16; color: #04003B;">
                REGRESAR AL LOGIN
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>

        <div class="absolute bottom-8 right-8 pointer-events-none opacity-10">
            <div class="text-6xl font-black text-white tracking-tighter uppercase">ISTU</div>
        </div>
    </main>
</body>
</html>